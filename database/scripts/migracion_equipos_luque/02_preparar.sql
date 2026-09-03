SET @sistema_id := 7;
SET @distrito := 'LUQUE';

-- Respaldos persistentes e idempotentes.
CREATE TABLE IF NOT EXISTS mig_luque_dirigente (
    sistema_id BIGINT UNSIGNED NOT NULL, id BIGINT UNSIGNED NOT NULL, id_equipo BIGINT UNSIGNED NULL,
    PRIMARY KEY (sistema_id, id)
);
CREATE TABLE IF NOT EXISTS mig_luque_puntero (
    sistema_id BIGINT UNSIGNED NOT NULL, id BIGINT UNSIGNED NOT NULL, id_equipo BIGINT NULL,
    PRIMARY KEY (sistema_id, id)
);
CREATE TABLE IF NOT EXISTS mig_luque_vehiculo (
    sistema_id BIGINT UNSIGNED NOT NULL, id BIGINT UNSIGNED NOT NULL, id_equipo BIGINT NULL,
    PRIMARY KEY (sistema_id, id)
);
CREATE TABLE IF NOT EXISTS mig_luque_miembro (
    sistema_id BIGINT UNSIGNED NOT NULL, id BIGINT UNSIGNED NOT NULL, id_equipo BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (sistema_id, id)
);
CREATE TABLE IF NOT EXISTS mig_luque_mesa (
    sistema_id BIGINT UNSIGNED NOT NULL, id BIGINT UNSIGNED NOT NULL, id_equipo BIGINT UNSIGNED NULL,
    PRIMARY KEY (sistema_id, id)
);
CREATE TABLE IF NOT EXISTS mig_luque_equipo_creado (
    sistema_id BIGINT UNSIGNED NOT NULL, equipo_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (sistema_id, equipo_id)
);

INSERT IGNORE INTO mig_luque_dirigente
SELECT @sistema_id, d.id, d.id_equipo FROM dirigente d JOIN equipo e ON e.id=d.id_equipo
WHERE CAST(e.sist AS UNSIGNED)=@sistema_id;
INSERT IGNORE INTO mig_luque_puntero
SELECT @sistema_id, p.id, p.id_equipo FROM puntero p JOIN equipo e ON e.id=p.id_equipo
WHERE CAST(e.sist AS UNSIGNED)=@sistema_id;
INSERT IGNORE INTO mig_luque_vehiculo
SELECT @sistema_id, v.id, v.id_equipo FROM vehiculo v
WHERE v.id_sistema=@sistema_id OR v.id_equipo IN (SELECT id FROM equipo WHERE CAST(sist AS UNSIGNED)=@sistema_id);
INSERT IGNORE INTO mig_luque_miembro
SELECT @sistema_id, m.id, m.idequipo FROM miembros_de_mesa m JOIN equipo e ON e.id=m.idequipo
WHERE CAST(e.sist AS UNSIGNED)=@sistema_id;
INSERT IGNORE INTO mig_luque_mesa
SELECT @sistema_id, me.id, me.equipo_id FROM mesas me JOIN equipo e ON e.id=me.equipo_id
WHERE CAST(e.sist AS UNSIGNED)=@sistema_id;

DROP TEMPORARY TABLE IF EXISTS tmp_equipos_antes;
CREATE TEMPORARY TABLE tmp_equipos_antes AS
SELECT id FROM equipo WHERE CAST(sist AS UNSIGNED)=@sistema_id;

INSERT INTO equipo (sist, ciudad, colegio, descripcion, created_at, updated_at)
SELECT @sistema_id, @distrito, x.local_general, x.local_general, NOW(), NOW()
FROM (
    SELECT DISTINCT TRIM(local_generales) AS local_general
    FROM padron
    WHERE UPPER(TRIM(distrito_nombre)) COLLATE utf8mb4_unicode_ci
          = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci
      AND NULLIF(TRIM(local_generales),'') IS NOT NULL
) x
WHERE NOT EXISTS (
    SELECT 1 FROM equipo e WHERE CAST(e.sist AS UNSIGNED)=@sistema_id
      AND UPPER(TRIM(e.ciudad)) COLLATE utf8mb4_unicode_ci
          = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci
      AND e.descripcion NOT LIKE '%Importado internas%'
      AND TRIM(e.colegio) COLLATE utf8mb4_unicode_ci
          = CONVERT(x.local_general USING utf8mb4) COLLATE utf8mb4_unicode_ci
);

INSERT IGNORE INTO mig_luque_equipo_creado
SELECT @sistema_id, e.id FROM equipo e
WHERE CAST(e.sist AS UNSIGNED)=@sistema_id
  AND NOT EXISTS (SELECT 1 FROM tmp_equipos_antes a WHERE a.id=e.id);

SELECT e.* FROM equipo e WHERE CAST(e.sist AS UNSIGNED)=@sistema_id ORDER BY e.descripcion;

-- Resumen de preparación. Si los equipos generales son 25 y los respaldos
-- reflejan los datos existentes, el sistema está listo para 03_reasignar.sql.
SELECT
    @sistema_id AS sistema_id,
    (SELECT COUNT(*) FROM equipo e
     WHERE CAST(e.sist AS UNSIGNED)=@sistema_id
       AND UPPER(TRIM(e.ciudad)) COLLATE utf8mb4_unicode_ci
           = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci
       AND e.descripcion NOT LIKE '%Importado internas%') AS equipos_generales,
    (SELECT COUNT(*) FROM equipo e
     WHERE CAST(e.sist AS UNSIGNED)=@sistema_id
       AND e.descripcion LIKE '%Importado internas%') AS equipos_internos,
    (SELECT COUNT(*) FROM mig_luque_equipo_creado WHERE sistema_id=@sistema_id) AS equipos_creados_registrados,
    (SELECT COUNT(*) FROM mig_luque_dirigente WHERE sistema_id=@sistema_id) AS dirigentes_respaldados,
    (SELECT COUNT(*) FROM mig_luque_puntero WHERE sistema_id=@sistema_id) AS punteros_respaldados,
    (SELECT COUNT(*) FROM mig_luque_vehiculo WHERE sistema_id=@sistema_id) AS vehiculos_respaldados,
    (SELECT COUNT(*) FROM mig_luque_miembro WHERE sistema_id=@sistema_id) AS miembros_respaldados,
    (SELECT COUNT(*) FROM mig_luque_mesa WHERE sistema_id=@sistema_id) AS mesas_respaldadas;
