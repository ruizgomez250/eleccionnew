SET @sistema_id := 7;
SET @distrito := 'LUQUE';

-- Recorta e indexa el padrón de LUQUE una sola vez. Esto evita repetir
-- uniones costosas contra más de un millón de filas sin índice por cédula.
DROP TEMPORARY TABLE IF EXISTS tmp_padron_luque;
CREATE TEMPORARY TABLE tmp_padron_luque AS
SELECT CONVERT(cedula USING utf8mb4) COLLATE utf8mb4_unicode_ci AS cedula,
       CONVERT(TRIM(local_generales) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS local_generales
FROM padron
WHERE UPPER(TRIM(distrito_nombre)) COLLATE utf8mb4_unicode_ci
      = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci
  AND NULLIF(TRIM(local_generales),'') IS NOT NULL;
ALTER TABLE tmp_padron_luque ADD INDEX idx_cedula (cedula), ADD INDEX idx_local (local_generales);

DROP TEMPORARY TABLE IF EXISTS tmp_destinos;
CREATE TEMPORARY TABLE tmp_destinos AS
SELECT MIN(e.id) AS equipo_id, TRIM(e.colegio) AS local_general
FROM equipo e
JOIN (
    SELECT DISTINCT TRIM(local_generales) AS local_general
    FROM tmp_padron_luque
) pg ON TRIM(e.colegio) COLLATE utf8mb4_unicode_ci
        = CONVERT(pg.local_general USING utf8mb4) COLLATE utf8mb4_unicode_ci
WHERE CAST(e.sist AS UNSIGNED)=@sistema_id
  AND UPPER(TRIM(e.ciudad)) COLLATE utf8mb4_unicode_ci
      = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci
  AND e.descripcion NOT LIKE '%Importado internas%'
GROUP BY TRIM(e.colegio);
ALTER TABLE tmp_destinos ADD PRIMARY KEY (equipo_id), ADD INDEX (local_general);

-- Punteros: mayoría de sus votantes.
DROP TEMPORARY TABLE IF EXISTS tmp_rank_puntero;
CREATE TEMPORARY TABLE tmp_rank_puntero AS
SELECT v.idpuntero AS entidad_id, d.equipo_id, COUNT(*) AS cantidad,
       ROW_NUMBER() OVER (PARTITION BY v.idpuntero ORDER BY COUNT(*) DESC, d.equipo_id) AS rn
FROM votante v
JOIN mig_luque_puntero b ON b.id=v.idpuntero AND b.sistema_id=@sistema_id
JOIN tmp_padron_luque pa ON pa.cedula = v.cedula COLLATE utf8mb4_unicode_ci
JOIN tmp_destinos d ON pa.local_generales = d.local_general COLLATE utf8mb4_unicode_ci
GROUP BY v.idpuntero, d.equipo_id;
UPDATE puntero p JOIN tmp_rank_puntero r ON r.entidad_id=p.id AND r.rn=1
SET p.id_equipo=r.equipo_id, p.updated_at=NOW();

-- Dirigentes: mayoría entre todos los votantes de sus punteros.
DROP TEMPORARY TABLE IF EXISTS tmp_rank_dirigente;
CREATE TEMPORARY TABLE tmp_rank_dirigente AS
SELECT p.id_dirigente AS entidad_id, d.equipo_id, COUNT(*) AS cantidad,
       ROW_NUMBER() OVER (PARTITION BY p.id_dirigente ORDER BY COUNT(*) DESC, d.equipo_id) AS rn
FROM puntero p
JOIN mig_luque_dirigente b ON b.id=p.id_dirigente AND b.sistema_id=@sistema_id
JOIN votante v ON v.idpuntero=p.id
JOIN tmp_padron_luque pa ON pa.cedula = v.cedula COLLATE utf8mb4_unicode_ci
JOIN tmp_destinos d ON pa.local_generales = d.local_general COLLATE utf8mb4_unicode_ci
GROUP BY p.id_dirigente, d.equipo_id;
UPDATE dirigente di JOIN tmp_rank_dirigente r ON r.entidad_id=di.id AND r.rn=1
SET di.id_equipo=r.equipo_id, di.updated_at=NOW();

-- Vehículos: mayoría de votantes de los punteros vinculados.
DROP TEMPORARY TABLE IF EXISTS tmp_rank_vehiculo;
CREATE TEMPORARY TABLE tmp_rank_vehiculo AS
SELECT pv.vehiculo_id AS entidad_id, d.equipo_id, COUNT(*) AS cantidad,
       ROW_NUMBER() OVER (PARTITION BY pv.vehiculo_id ORDER BY COUNT(*) DESC, d.equipo_id) AS rn
FROM puntero_vehiculo pv
JOIN mig_luque_vehiculo b ON b.id=pv.vehiculo_id AND b.sistema_id=@sistema_id
JOIN votante v ON v.idpuntero=pv.puntero_id
JOIN tmp_padron_luque pa ON pa.cedula = v.cedula COLLATE utf8mb4_unicode_ci
JOIN tmp_destinos d ON pa.local_generales = d.local_general COLLATE utf8mb4_unicode_ci
GROUP BY pv.vehiculo_id, d.equipo_id;
UPDATE vehiculo ve JOIN tmp_rank_vehiculo r ON r.entidad_id=ve.id AND r.rn=1
SET ve.id_equipo=r.equipo_id, ve.id_sistema=@sistema_id, ve.updated_at=NOW();

-- Miembros: escuela general de su propia cédula.
UPDATE miembros_de_mesa m
JOIN mig_luque_miembro b ON b.id=m.id AND b.sistema_id=@sistema_id
JOIN tmp_padron_luque pa ON pa.cedula = m.cedula COLLATE utf8mb4_unicode_ci
JOIN tmp_destinos d ON pa.local_generales = d.local_general COLLATE utf8mb4_unicode_ci
SET m.idequipo=d.equipo_id, m.updated_at=NOW();

-- Mesas: nombre del local contenido en codigo_mesa (el número solo no es único).
UPDATE mesas me
JOIN mig_luque_mesa b ON b.id=me.id AND b.sistema_id=@sistema_id
JOIN tmp_destinos d
  ON UPPER(TRIM(SUBSTRING_INDEX(me.codigo_mesa, ' - Mesa ', 1))) COLLATE utf8mb4_unicode_ci
     = UPPER(TRIM(d.local_general)) COLLATE utf8mb4_unicode_ci
SET me.equipo_id=d.equipo_id, me.updated_at=NOW()
WHERE UPPER(TRIM(me.distrito)) COLLATE utf8mb4_unicode_ci
      = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci;

SELECT 'punteros' AS entidad, COUNT(*) AS reasignados FROM tmp_rank_puntero WHERE rn=1
UNION ALL SELECT 'dirigentes', COUNT(*) FROM tmp_rank_dirigente WHERE rn=1
UNION ALL SELECT 'vehiculos', COUNT(*) FROM tmp_rank_vehiculo WHERE rn=1;
