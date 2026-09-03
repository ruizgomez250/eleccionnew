SET @sistema_id := 7;

UPDATE dirigente x JOIN mig_luque_dirigente b ON b.id=x.id AND b.sistema_id=@sistema_id SET x.id_equipo=b.id_equipo;
UPDATE puntero x JOIN mig_luque_puntero b ON b.id=x.id AND b.sistema_id=@sistema_id SET x.id_equipo=b.id_equipo;
UPDATE vehiculo x JOIN mig_luque_vehiculo b ON b.id=x.id AND b.sistema_id=@sistema_id SET x.id_equipo=b.id_equipo;
UPDATE miembros_de_mesa x JOIN mig_luque_miembro b ON b.id=x.id AND b.sistema_id=@sistema_id SET x.idequipo=b.id_equipo;
UPDATE mesas x JOIN mig_luque_mesa b ON b.id=x.id AND b.sistema_id=@sistema_id SET x.equipo_id=b.id_equipo;

DELETE e FROM equipo e JOIN mig_luque_equipo_creado c ON c.equipo_id=e.id AND c.sistema_id=@sistema_id
WHERE NOT EXISTS (SELECT 1 FROM dirigente d WHERE d.id_equipo=e.id)
  AND NOT EXISTS (SELECT 1 FROM puntero p WHERE p.id_equipo=e.id)
  AND NOT EXISTS (SELECT 1 FROM vehiculo v WHERE v.id_equipo=e.id)
  AND NOT EXISTS (SELECT 1 FROM miembros_de_mesa m WHERE m.idequipo=e.id)
  AND NOT EXISTS (SELECT 1 FROM mesas me WHERE me.equipo_id=e.id)
  AND NOT EXISTS (SELECT 1 FROM votanteaux va WHERE va.id_equipo=e.id);

SELECT ROW_COUNT() AS equipos_nuevos_eliminados;
