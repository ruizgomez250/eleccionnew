# Certificados de internas

Ruta: `/certificados-internas`. Menú: **Certificados de internas**.
Usa el permiso **Carga Certificados** para consultar y cargar. La eliminación conserva el permiso adicional **Guardar Certificados** del módulo original.

## Tablas a exportar de la base de internas

Exportar **estructura y datos**, conservando los IDs:

| Origen | Destino nuevo |
| --- | --- |
| equipo | internas_equipo |
| locales_internas | internas_locales_internas |
| partidos | internas_partidos |
| mesas | internas_mesas |
| candidatos | internas_candidatos |
| veedores | internas_veedores |
| votos_mesa | internas_votos_mesa |

Incluir `veedores` aunque esté vacía. No se necesitan `users`, permisos, roles, votantes, punteros ni dirigentes.
Los IDs de usuarios externos se conservan en `usuario_origen_id`; `user_id` queda vacío para evitar atribuir certificados a usuarios actuales con el mismo número. Se conserva `escaneado_por`. Los tokens de veedores no se importan.

## Instalación en el hosting

Subir los nuevos modelos de `app/Models/Internas`, el controlador `CertificadoInternaController`, las vistas `resources/views/certificados-internas`, el importador `app/Imports/CertificadosInternasImport.php`, el comando `app/Console/Commands/ImportarCertificadosInternas.php`, la migración y los cambios de rutas y menú.

Desde la raíz del proyecto:

```bash
php artisan migrate --path=database/migrations/2026_09_10_180000_create_certificados_internas_tables.php --force
php artisan optimize:clear
```

La migración crea siete tablas nuevas vacías copiando los tipos e índices actuales. No modifica las tablas originales. Requiere MySQL/MariaDB y que las siete tablas de base existan. Las relaciones nuevas apuntan a tablas `internas_`; sólo `user_id` de futuras cargas web apunta a los usuarios actuales.

## Importación

1. Crear una **base de datos separada** en el mismo servidor, por ejemplo `respaldo_internas` (en hosting puede llevar el prefijo de la cuenta).
2. Importar allí las siete tablas exportadas, con sus nombres originales. No importar ese SQL en la base de la aplicación. Si el exportador incluye referencias a `users`, desactivar la comprobación de claves externas únicamente durante la restauración de ese respaldo separado; el importador comprueba las relaciones de los certificados.
3. Dar al usuario MySQL configurado en la aplicación acceso de lectura a esa base separada. Mantener ese respaldo sin modificaciones durante la copia.
4. Ejecutar primero la comprobación y luego la importación, reemplazando el nombre de ejemplo por el nombre completo real:

```bash
php artisan internas:importar respaldo_internas --check
php artisan internas:importar respaldo_internas
```

El comando sólo lee el origen y escribe en los destinos `internas_`. Verifica columnas y relaciones, copia en lotes y conserva IDs. Exige que las siete tablas de destino estén vacías; no mezcla ni reemplaza cargas anteriores. La copia se realiza en una transacción y se revierte completa si falla. `--check` verifica tablas, columnas, referencias y cantidades, pero no sustituye las comprobaciones de tipos y restricciones realizadas al insertar.

Después de importar, verificar los conteos que imprime el comando y abrir `/certificados-internas`. No cargar certificados nuevos en este módulo antes de terminar la importación.

La base externa todavía no se ha importado: debe ser provista/restaurada siguiendo estos pasos.
