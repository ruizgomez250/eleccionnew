-- SOLO LECTURA. Cambiar por el sistema que se desea migrar.
SET @sistema_id := 7;
SET @distrito := 'LUQUE';

-- 1. Todos los sistemas de LUQUE. Use el valor de "sistema_id" (no el número
-- de orden mostrado en /useradmin) para ejecutar la migración uno por vez.
SELECT s.id AS sistema_id, s.nombre, s.tipo, ce.descripcion AS ciudad,
       COUNT(e.id) AS equipos_actuales
FROM sistemas s
JOIN ciudades_electorales ce ON ce.id = s.id_ciudad_electoral
LEFT JOIN equipo e ON CAST(e.sist AS UNSIGNED) = s.id
WHERE UPPER(TRIM(ce.descripcion)) COLLATE utf8mb4_unicode_ci
      = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci
GROUP BY s.id, s.nombre, s.tipo, ce.descripcion
ORDER BY s.id;

-- 2. Sistema seleccionado mediante @sistema_id.
SELECT s.id, s.nombre, s.tipo, ce.descripcion AS ciudad
FROM sistemas s
LEFT JOIN ciudades_electorales ce ON ce.id = s.id_ciudad_electoral
WHERE s.id = @sistema_id;

SELECT e.id, e.descripcion, e.colegio, e.ciudad,
       (SELECT COUNT(*) FROM dirigente d WHERE d.id_equipo = e.id) AS dirigentes,
       (SELECT COUNT(*) FROM puntero p WHERE p.id_equipo = e.id) AS punteros,
       (SELECT COUNT(*) FROM vehiculo v WHERE v.id_equipo = e.id) AS vehiculos,
       (SELECT COUNT(*) FROM miembros_de_mesa m WHERE m.idequipo = e.id) AS miembros,
       (SELECT COUNT(*) FROM mesas me WHERE me.equipo_id = e.id) AS mesas
FROM equipo e
WHERE CAST(e.sist AS UNSIGNED) = @sistema_id
ORDER BY e.descripcion;

SELECT TRIM(pa.local_generales) AS local_general, COUNT(*) AS electores
FROM padron pa
WHERE UPPER(TRIM(pa.distrito_nombre)) COLLATE utf8mb4_unicode_ci
      = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci
  AND NULLIF(TRIM(pa.local_generales), '') IS NOT NULL
GROUP BY TRIM(pa.local_generales)
ORDER BY TRIM(pa.local_generales);

SELECT COUNT(DISTINCT TRIM(pa.local_generales)) AS locales_generales_padron,
       COUNT(DISTINCT CASE WHEN e.id IS NOT NULL THEN TRIM(pa.local_generales) END) AS ya_creados
FROM padron pa
LEFT JOIN equipo e
  ON CAST(e.sist AS UNSIGNED) = @sistema_id
 AND UPPER(TRIM(e.ciudad)) COLLATE utf8mb4_unicode_ci
     = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci
 AND CONVERT(TRIM(pa.local_generales) USING utf8mb4) COLLATE utf8mb4_unicode_ci
     = TRIM(e.colegio) COLLATE utf8mb4_unicode_ci
WHERE UPPER(TRIM(pa.distrito_nombre)) COLLATE utf8mb4_unicode_ci
      = CONVERT(@distrito USING utf8mb4) COLLATE utf8mb4_unicode_ci;
