# Migración de equipos de LUQUE a locales generales

Estos scripts migran **un sistema por vez**. No los ejecute directamente en producción sin probar primero una copia de la base.

Orden:

1. `01_diagnostico.sql`: vista previa, no modifica datos.
2. `02_preparar.sql`: crea respaldos y los 25 equipos generales faltantes.
3. `03_reasignar.sql`: reasigna punteros, dirigentes, vehículos, miembros y mesas.
4. `04_verificar_y_limpiar.sql`: solo muestra pendientes; no modifica datos.
5. `05_eliminar_antiguos.sql`: elimina únicamente equipos antiguos sin referencias, después de revisar el paso 4.
6. `05_rollback.sql`: restaura las asociaciones respaldadas y elimina los equipos creados por esta migración si quedaron sin referencias.

En cada archivo cambie solamente:

```sql
SET @sistema_id := 7;
```

El valor es `sistemas.id`, no el número consecutivo mostrado en la primera
columna de `/useradmin`. `01_diagnostico.sql` lista todos los IDs reales de
los sistemas de LUQUE. Ejecute los pasos 2 a 4 por separado para cada ID.

Reglas de asignación:

- Puntero: local general con mayor cantidad de sus votantes en `padron`.
- Dirigente: local general con mayor cantidad de votantes de todos sus punteros.
- Vehículo: local general con mayor cantidad de votantes de sus punteros vinculados en `puntero_vehiculo`.
- Miembro de mesa: local general correspondiente a su propia cédula en `padron`.
- Mesa: local escrito antes de ` - Mesa ` en `mesas.codigo_mesa`.

Los empates se resuelven de forma determinista por el menor `equipo.id`. Los registros sin coincidencia permanecen en su equipo anterior y aparecen en la verificación.

`03_reasignar.sql` crea una tabla temporal indexada con el padrón de LUQUE para evitar uniones repetidas y lentas contra el padrón completo. Esta tabla desaparece al cerrar la conexión.
