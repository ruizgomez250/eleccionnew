-- DESTRUCTIVO: ejecutar solamente después de revisar 04_verificar_y_limpiar.sql.
SET @sistema_id := 7;

-- Elimina solo equipos claramente marcados como internos y sin referencias.
DELETE e FROM equipo e
WHERE CAST(e.sist AS UNSIGNED)=@sistema_id
  AND e.descripcion LIKE '%Importado internas%'
  AND NOT EXISTS (SELECT 1 FROM dirigente d WHERE d.id_equipo=e.id)
  AND NOT EXISTS (SELECT 1 FROM puntero p WHERE p.id_equipo=e.id)
  AND NOT EXISTS (SELECT 1 FROM vehiculo v WHERE v.id_equipo=e.id)
  AND NOT EXISTS (SELECT 1 FROM miembros_de_mesa m WHERE m.idequipo=e.id)
  AND NOT EXISTS (SELECT 1 FROM mesas me WHERE me.equipo_id=e.id)
  AND NOT EXISTS (SELECT 1 FROM votanteaux va WHERE va.id_equipo=e.id);

SELECT ROW_COUNT() AS equipos_antiguos_eliminados;
