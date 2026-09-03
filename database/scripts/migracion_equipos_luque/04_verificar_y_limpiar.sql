SET @sistema_id := 7;

-- SOLO LECTURA. Debe revisarse antes de ejecutar 05_eliminar_antiguos.sql.
SELECT 'dirigente' entidad, COUNT(*) pendientes FROM dirigente x JOIN mig_luque_dirigente b ON b.id=x.id AND b.sistema_id=@sistema_id JOIN equipo e ON e.id=x.id_equipo WHERE e.descripcion LIKE '%Importado internas%'
UNION ALL SELECT 'puntero', COUNT(*) FROM puntero x JOIN mig_luque_puntero b ON b.id=x.id AND b.sistema_id=@sistema_id JOIN equipo e ON e.id=x.id_equipo WHERE e.descripcion LIKE '%Importado internas%'
UNION ALL SELECT 'vehiculo', COUNT(*) FROM vehiculo x JOIN mig_luque_vehiculo b ON b.id=x.id AND b.sistema_id=@sistema_id JOIN equipo e ON e.id=x.id_equipo WHERE e.descripcion LIKE '%Importado internas%'
UNION ALL SELECT 'miembro', COUNT(*) FROM miembros_de_mesa x JOIN mig_luque_miembro b ON b.id=x.id AND b.sistema_id=@sistema_id JOIN equipo e ON e.id=x.idequipo WHERE e.descripcion LIKE '%Importado internas%'
UNION ALL SELECT 'mesa', COUNT(*) FROM mesas x JOIN mig_luque_mesa b ON b.id=x.id AND b.sistema_id=@sistema_id JOIN equipo e ON e.id=x.equipo_id WHERE e.descripcion LIKE '%Importado internas%';

SELECT e.id,e.descripcion,e.colegio,
 (SELECT COUNT(*) FROM dirigente d WHERE d.id_equipo=e.id) dirigentes,
 (SELECT COUNT(*) FROM puntero p WHERE p.id_equipo=e.id) punteros,
 (SELECT COUNT(*) FROM vehiculo v WHERE v.id_equipo=e.id) vehiculos,
 (SELECT COUNT(*) FROM miembros_de_mesa m WHERE m.idequipo=e.id) miembros,
 (SELECT COUNT(*) FROM mesas me WHERE me.equipo_id=e.id) mesas
FROM equipo e WHERE CAST(e.sist AS UNSIGNED)=@sistema_id ORDER BY e.descripcion;
