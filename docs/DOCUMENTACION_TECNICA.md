# Documentación técnica

## 1. Identificación del proyecto

| Elemento | Valor |
| --- | --- |
| Nombre | Kanban MW |
| Tipo | Aplicación web interna de gestión de proyectos y tareas |
| Framework | Laravel 13 (`^13.17`) |
| PHP | 8.3 o superior |
| Panel | Filament 5 |
| Interactividad | Livewire, Alpine y componentes de Filament |
| Estilos y assets | Tailwind CSS 4 y Vite 8 |
| Base de datos local/propuesta | MySQL 8.4 |
| Pruebas | PHPUnit 12, con SQLite en memoria |
| Idioma por defecto | Español (`es`) |
| Licencia | MIT |
| Entrada web | `/` redirige a `/admin` |
| Health check | `/up` |

La fecha, la hora de los recordatorios y la fecha funcional de "hoy" usan `KANBAN_TIMEZONE`, cuyo valor predeterminado es `America/Bogota`. Laravel mantiene la zona horaria general de la aplicación en UTC, por lo que ambas configuraciones deben considerarse al analizar timestamps.

## 2. Propósito y alcance funcional

El sistema permite:

- Crear y consultar proyectos.
- Definir propietario, líder, fechas y estado del proyecto.
- Crear tareas dentro de un proyecto y asignarlas a uno o varios usuarios.
- Gestionar tareas mediante un tablero Kanban.
- Consultar una línea de tiempo con escala adaptativa.
- Registrar observaciones, notas de seguimiento y costos.
- Registrar oportunidades de mejora al finalizar un proyecto.
- Clonar proyectos como punto de partida para un nuevo seguimiento.
- Mostrar indicadores de actividad ajustados al perfil del usuario.
- Enviar correos de asignación y recordatorios diarios de tareas.
- Administrar usuarios, perfiles y activación de cuentas.

No forma parte del alcance actual:

- API REST, GraphQL o rutas de integración públicas.
- Aplicación móvil o frontend público independiente.
- Eliminación desde la interfaz de proyectos, tareas, comentarios, costos o usuarios.
- Historial/auditoría de cambios de campos.
- Reordenación manual de tareas en el Kanban.
- Sistema de notificaciones internas persistentes; las notificaciones de asignación y recordatorio son correo electrónico.

## 3. Arquitectura de alto nivel

```text
Navegador
   |
   v
Filament Panel / Livewire / Alpine
   |
   +--> Resources: Projects, Users
   +--> Pages: Dashboard, ProjectBoard
   +--> Widgets: indicadores, atención, portafolio
   |
   v
Policies + Project::visibleTo()
   |
   v
ProjectService
   |
   +--> Eloquent Models
   +--> Transacciones de base de datos
   +--> Sincronización del estado del proyecto
   +--> Jobs de correo después del commit
   |
   v
MySQL / cola database / correo
```

### 3.1 Capas y responsabilidades

| Capa | Ubicación | Responsabilidad |
| --- | --- | --- |
| Entrada web | `routes/web.php`, `bootstrap/app.php` | Redirección raíz y configuración de routing/health check |
| Panel | `app/Providers/Filament/AdminPanelProvider.php` | Panel `admin`, navegación, tema, autenticación y descubrimiento de componentes |
| Recursos CRUD | `app/Filament/Resources` | Listados y formularios de proyectos y usuarios |
| Página de trabajo | `app/Filament/Pages/ProjectBoard.php` | Tablero, timeline, formularios Livewire y acciones de proyecto |
| Vistas | `resources/views/filament` | Presentación del panel, tablero, timeline y widgets |
| Dominio | `app/Models`, `app/Enums` | Entidades, relaciones, estados y scopes |
| Autorización | `app/Policies` | Reglas por usuario, rol, propiedad y estado |
| Mutaciones | `app/Services/ProjectService.php` | Transacciones, validaciones de tareas, sincronización y asignaciones |
| Asíncrono | `app/Jobs`, `app/Mail`, `routes/console.php` | Digest de correos, recordatorios y programación diaria |
| Persistencia | `database/migrations` | Esquema, índices, claves foráneas y restricciones |
| Verificación | `tests/Feature`, `tests/Unit` | Pruebas de dominio, recordatorios, autorización y renderizado |

### 3.2 Navegación del panel

El panel se identifica como `admin`, usa la ruta base `/admin`, el nombre de marca `Kanban MW`, un tema teal y dos grupos de navegación:

- **Trabajo**: Proyectos.
- **Administración**: Usuarios, visible únicamente para administradores activos mediante las policies.

El tablero no aparece como elemento independiente en la barra lateral. Se abre desde una fila de proyecto o desde un enlace a `/admin/projects/{project}/board`.

### 3.3 Rutas y entradas principales

| URL | Función | Acceso |
| --- | --- | --- |
| `/` | Redirige a `/admin` | Público |
| `/up` | Health check de Laravel | Público, según el proxy |
| `/admin/login` | Inicio de sesión personalizado | Público |
| `/admin` | Escritorio | Usuario autenticado y activo |
| `/admin/projects` | Lista de proyectos | Usuario autenticado y activo |
| `/admin/projects/create` | Crear proyecto | Administrador o colaborador autorizado |
| `/admin/projects/{record}/edit` | Editar encabezado | Administrador o propietario |
| `/admin/projects/{project}/board` | Tablero y timeline | Usuario con visibilidad del proyecto |
| `/admin/users` | Lista de usuarios | Administrador activo |
| `/admin/users/create` | Crear usuario | Administrador activo |
| `/admin/users/{record}/edit` | Editar usuario | Administrador activo |

Las rutas de recursos son generadas por Filament. Las acciones del tablero se ejecutan como solicitudes Livewire, no como endpoints CRUD independientes.

## 4. Estructura del repositorio

```text
app/
  Console/Commands/       Comandos Artisan
  Enums/                  Roles, estados y tipos de entrada
  Filament/
    Pages/                Dashboard, tablero y login
    Resources/            Recursos de proyectos y usuarios
    Widgets/              Indicadores y portafolio del dashboard
  Jobs/                   Jobs de correos
  Mail/                   Mailables
  Models/                 Modelos Eloquent
  Policies/               Autorización
  Services/               ProjectService
  Providers/              Providers de Laravel y Filament
database/
  factories/              Factories para pruebas
  migrations/             Esquema de base de datos
  seeders/                Usuarios demo local/testing
docker/                   Imágenes y configuración local de PHP/nginx
resources/
  css/                    CSS global y tema de Filament
  js/                     Entrada Vite; la lógica principal vive en Livewire/Alpine
  views/                  Vistas Blade y plantillas de correo
routes/
  web.php                 Redirección de `/`
  console.php             Schedule de recordatorios
tests/
  Feature/                Pruebas de integración y funcionalidades
  Unit/                   Pruebas unitarias
Dockerfile                Imagen multi-stage de producción
docker-compose.yml        Entorno local completo
docker-compose.coolify.yml Compose para Coolify/producción
```

## 5. Modelo de dominio

### 5.1 Usuario

Tabla `users`:

| Campo | Tipo/contenido | Notas |
| --- | --- | --- |
| `id` | entero autoincremental | Identificador |
| `name` | string | Nombre visible |
| `email` | string único | Credencial y destino de correo |
| `password` | string hash | Se transforma mediante el cast `hashed` |
| `role` | `admin`, `collaborator`, `observer` | Se transforma a `UserRole` |
| `is_active` | boolean | Controla acceso y asignabilidad |
| `email_verified_at` | timestamp nullable | Campo estándar de Laravel |
| timestamps | timestamps | Alta y actualización |

Relaciones principales: proyectos propios, proyectos liderados, tareas asignadas, comentarios y costos.

### 5.2 Proyecto

Tabla `projects`:

| Campo | Tipo/contenido | Notas |
| --- | --- | --- |
| `name` | string | Nombre del proyecto |
| `start_date` | date nullable | Inicio planeado |
| `end_date` | date nullable | Fin planeado |
| `leader_id` | FK nullable a `users` | Solo usuarios activos administradores/colaboradores |
| `owner_id` | FK a `users` | Propietario funcional; no se puede eliminar mientras tenga proyectos |
| `status` | enum persistido como string | `created`, `in_progress`, `finished` |
| `completed_at` | timestamp nullable | Se rellena cuando todas las tareas quedan en `done` |
| `cloned_from_id` | FK nullable a `projects` | Proyecto de origen de una copia |
| `improvement_opportunities` | text nullable | Retroalimentación posterior al cierre |

Relaciones: `owner`, `leader`, `clonedFrom`, `clones`, `tasks`, `comments` y `costs`.

Las tareas se ordenan por `position` y luego por `id`. Los comentarios se muestran del más reciente al más antiguo; los costos por fecha de ocurrencia descendente.

### 5.3 Tarea

Tabla `tasks`:

| Campo | Tipo/contenido | Notas |
| --- | --- | --- |
| `project_id` | FK | Se elimina en cascada al eliminar el proyecto |
| `description` | text | Resultado o trabajo esperado |
| `start_date` | date nullable | Inicio de la tarea |
| `end_date` | date nullable | Fin de la tarea |
| `status` | enum persistido como string | `pending`, `in_progress`, `done` |
| `position` | entero sin signo | Orden de consulta; no se edita desde la UI |
| `created_by_id` | FK nullable a `users` | Creador |
| `completed_at` | timestamp nullable | Fecha/hora al pasar a `done` |

Una tarea es considerada **borrador** cuando le falta alguna fecha o no tiene responsables. Las tareas normales requieren descripción, fecha de inicio, fecha final y al menos un responsable asignable. Las tareas que vienen de una copia se crean sin fechas ni responsables y permanecen en `pending` hasta configurarse.

### 5.4 Asignaciones

La tabla `task_user` representa la relación muchos-a-muchos entre tareas y usuarios. Tiene clave primaria compuesta (`task_id`, `user_id`) y guarda `assigned_at`. Solo se pueden asignar usuarios activos con rol administrador o colaborador; los observadores no son asignables.

### 5.5 Seguimiento y costos

- `project_comments`: une proyecto, usuario, tipo (`note` u `observation`) y texto.
- `project_costs`: une proyecto y usuario con importe decimal de 15,2, descripción y fecha de ocurrencia.

La interfaz presenta los importes como pesos colombianos (`COP`), pero el modelo no guarda un campo de moneda. El valor de negocio de la moneda es, por tanto, una convención de la interfaz.

### 5.6 Control de entregas de correo

- `task_reminder_deliveries`: evita más de un recordatorio por tarea, usuario y fecha mediante una restricción única.
- `task_assignment_notifications`: agrupa IDs de tareas por operación y usuario, y registra `sent_at`.

## 6. Enums y estados

### 6.1 Roles de usuario

| Valor interno | Etiqueta | Uso |
| --- | --- | --- |
| `admin` | Administrador | Administración global y operación completa |
| `collaborator` | Colaborador | Crea proyectos y trabaja sobre proyectos propios; puede ser responsable |
| `observer` | Observador | Consulta todos los proyectos y registra observaciones |

Un usuario debe estar activo y tener un rol válido para acceder al panel. La inactividad conserva relaciones y datos, pero impide el acceso normal y la asignación de nuevas tareas.

### 6.2 Estados del proyecto

| Estado | Regla |
| --- | --- |
| `created` / Creado | No tiene tareas |
| `in_progress` / En proceso | Tiene tareas y al menos una no está en `done` |
| `finished` / Finalizado | Todas sus tareas están en `done` |

El estado se sincroniza por `ProjectService::syncStatus()` después de crear o actualizar tareas. No existe un formulario para cambiar manualmente el estado del proyecto. En un proyecto finalizado se registra `completed_at`; la rutina también contempla limpiar esa fecha si el estado observado vuelve a no finalizado, aunque las acciones normales del panel bloquean la reapertura de un proyecto finalizado.

### 6.3 Estados de tarea

| Estado | Uso |
| --- | --- |
| `pending` / Pendiente | Trabajo aún no iniciado |
| `in_progress` / En curso | Trabajo activo |
| `done` / Hecho | Trabajo terminado; registra `completed_at` |

El arrastre entre columnas y el selector de cada tarjeta cambian el estado. El arrastre no cambia la posición de la tarea dentro de una columna.

## 7. Autorización

La autorización se aplica tanto en la interfaz como en las policies y en `ProjectService`. La visibilidad de proyectos para listados usa el scope `Project::visibleTo()`.

### 7.1 Visibilidad de proyectos

- Administradores activos: todos los proyectos.
- Observadores activos: todos los proyectos.
- Colaboradores activos: proyectos de los que son propietarios, líderes o en los que tienen una tarea asignada.
- Usuarios inactivos: ninguno y sin acceso al panel.


| Capacidad | Administrador | Colaborador | Observador |
| --- | --- | --- | --- |
| Acceder al panel si está activo | Sí | Sí | Sí |
| Ver todos los proyectos | Sí | No | Sí |
| Crear proyectos | Sí | Sí | No |
| Editar encabezado | Cualquier proyecto | Solo proyectos propios | No |
| Clonar proyecto | Cualquier proyecto | Solo proyectos propios | No |
| Crear/editar tareas | Cualquier proyecto activo | Solo proyectos propios activos | No |
| Cambiar estado de tareas | Cualquier proyecto activo | Solo proyectos propios activos | No |
| Ser líder o responsable | Sí | Sí | No |
| Añadir observación | En proyectos visibles | En proyectos visibles | En proyectos visibles |
| Añadir nota | Proyecto activo y visible | Proyecto activo y visible | No |
| Registrar costo | Proyecto activo y visible | Proyecto activo y visible | No |
| Guardar oportunidades de mejora | Proyecto finalizado | Si es propietario o líder | No |
| Gestionar usuarios | Sí | No | No |

Ser líder o responsable de una tarea da visibilidad, pero no concede por sí mismo permisos para editar el proyecto o cambiar el estado de sus tareas. El propietario y el administrador son quienes tienen permisos de edición operativa.

## 8. Flujos funcionales

### 8.1 Dashboard

El dashboard (`/admin`) muestra indicadores según el rol:

- Administrador: proyectos activos, proyectos por configurar, tareas vencidas y usuarios activos.
- Observador: proyectos activos, proyectos finalizados, tareas abiertas y cierres de proyectos en los próximos 14 días.
- Colaborador: proyectos activos visibles, tareas para hoy, tareas vencidas y tareas que vencen en los próximos 7 días. Las métricas de tareas se limitan a tareas asignadas al colaborador.

El widget de atención operativa muestra hasta cinco tareas vencidas, hasta cinco tareas próximas y hasta cinco proyectos con líder o fechas pendientes. Para colaboradores se presenta como **Mi agenda**.

La tabla inferior muestra portafolio o proyectos propios, avance calculado como tareas hechas entre tareas totales y fecha final planeada. Los proyectos sin tareas muestran `Sin tareas`.

### 8.2 Creación y edición de proyectos

`CreateProject` y `EditProject` delegan las mutaciones en `ProjectService`.

1. El propietario se toma del usuario autenticado.
2. Si no se elige líder, se usa el propietario como líder.
3. El líder debe ser un usuario activo administrador o colaborador.
4. Un proyecto nuevo comienza en `created`.
5. La UI exige nombre, fechas y líder para un proyecto normal.
6. La fecha final debe ser igual o posterior a la inicial en el formulario y al actualizar.
7. Después de guardar se redirige al tablero del proyecto.

El encabezado editable contiene nombre, fecha de inicio, fecha final y líder. No permite cambiar propietario ni estado manualmente.

### 8.3 Clonación

El propietario o un administrador puede clonar desde el tablero. La copia:

- Se nombra con el sufijo ` (Copia)`.
- Tiene como propietario al usuario que ejecutó la acción.
- Guarda `cloned_from_id`.
- Copia únicamente descripción y posición de las tareas.
- Reinicia fechas, líder, responsables, estados, fechas de finalización, comentarios, costos y oportunidades de mejora.
- Redirige al nuevo tablero.

Una copia se reconoce como borrador de proyecto cuando no tiene fechas ni líder. Sus tareas nacen sin fechas ni responsables. Para mover una tarea clonada fuera de `Pendiente` primero se deben completar sus fechas y responsables.

Nota de implementación: si la copia ya contiene tareas, la sincronización actual puede dejar su estado persistido como `En proceso`, aunque el encabezado siga incompleto. La UI muestra igualmente el aviso de copia pendiente de configurar.

### 8.4 Creación y edición de tareas

Las tareas no tienen recurso Filament propio; se administran desde el tablero.

1. El propietario o un administrador abre **Nueva tarea**.
2. Se indica descripción.
3. Se indican fecha de inicio y fecha final.
4. Se seleccionan uno o varios responsables.
5. Los responsables deben estar activos y ser administradores o colaboradores.
6. La tarea se crea en `Pendiente` y recibe la siguiente posición disponible.
7. Los nuevos responsables reciben un digest de correo encolado.

Al editar una tarea pueden cambiarse descripción, fechas y responsables. La eliminación no se expone en la interfaz.

### 8.5 Cambio de estado y cierre

El propietario o un administrador puede cambiar el estado mediante el selector de la tarjeta o arrastrando la tarjeta a otra columna. El servicio vuelve a verificar autorización, fechas y estado del proyecto.

- Pasar a `Hecho` registra `completed_at` en la tarea.
- Salir de `Hecho` limpia `completed_at`.
- Cuando todas las tareas están en `Hecho`, el proyecto pasa a `Finalizado`.
- En un proyecto finalizado las tareas quedan bloqueadas en `Hecho`.
- Las tareas borrador solo pueden permanecer en `Pendiente`.

### 8.6 Línea de tiempo

El tablero permite alternar entre **Tablero Kanban** y **Línea de tiempo**.

- Hasta 31 días: escala diaria.
- Más de 31 y hasta 120 días: escala semanal.
- Más de 120 días: escala mensual.
- Se marcan el inicio y el fin del proyecto si existen.
- Se marca **Hoy** cuando cae dentro del rango.
- Las tareas sin ambas fechas aparecen en **Tareas por programar** y no se dibujan como barras.
- Quien puede editar abre una tarea desde su barra o desde el botón **Programar**.
- Los usuarios de solo lectura pueden consultar la gráfica, pero no editar desde ella.

No existe una validación que obligue a que las fechas de una tarea estén dentro del periodo del proyecto; una tarea puede extenderse fuera del rango del encabezado.

### 8.7 Seguimiento

En la sección **Seguimiento**:

- Una **observación** la puede registrar cualquier usuario que pueda ver el proyecto, incluido un observador y también sobre un proyecto finalizado.
- Una **nota de seguimiento** la puede registrar un administrador o colaborador visible mientras el proyecto esté activo.
- Ambos textos son obligatorios y tienen un máximo de 5.000 caracteres.
- Se muestran autor y fecha/hora.
- No hay edición ni eliminación desde la interfaz.

### 8.8 Costos

En proyectos activos, administradores y colaboradores con visibilidad pueden registrar costos:

- Valor en COP, mínimo `0.01`.
- Fecha de ocurrencia.
- Descripción obligatoria, máximo 255 caracteres.

Se muestra cada registro con descripción, fecha, usuario e importe, además del total acumulado. No hay edición ni eliminación desde la interfaz.

### 8.9 Oportunidades de mejora

La sección aparece después de finalizar el proyecto. Los administradores y los colaboradores que sean propietarios o líderes pueden guardar texto de hasta 10.000 caracteres. Los demás perfiles solo pueden leerlo si existe.

### 8.10 Correos y recordatorios

#### Asignación

Al crear o editar una tarea y añadir responsables:

1. Se registra la operación por usuario.
2. Se agrupan las tareas de esa operación en un digest.
3. Se despacha `SendTaskAssignmentDigest` después del commit de la transacción.
4. El worker carga las tareas y envía un correo de asignación.

El correo contiene proyecto, tarea, periodo, estado, responsables y enlace al tablero.

#### Recordatorio diario

El schedule ejecuta `tasks:send-reminders` diariamente a `KANBAN_REMINDER_TIME` y con `KANBAN_TIMEZONE`. El comando toma tareas que:

- No están en `Hecho`.
- Tienen ambas fechas.
- Incluyen la fecha del recordatorio entre inicio y fin, inclusive.
- Pertenecen a un proyecto no finalizado.

Las tareas se agrupan por responsable y se evita duplicarlas en la misma fecha. El job vuelve a comprobar que el usuario esté activo, siga asignado y que la tarea continúe dentro de la ventana antes de enviar.

## 9. Base de datos y relaciones

### 9.1 Tablas de negocio

| Tabla | Relación principal | Eliminación/configuración |
| --- | --- | --- |
| `users` | Usuarios del sistema | Email único; `is_active` indexado |
| `projects` | Propietario, líder y proyecto origen | Tareas/comentarios/costos dependen del proyecto |
| `tasks` | Pertenece a proyecto | Se elimina en cascada con el proyecto |
| `task_user` | Tareas y responsables | Clave primaria compuesta; usuario con `restrictOnDelete` |
| `project_comments` | Seguimiento | Cascada con proyecto; usuario restringido |
| `project_costs` | Gastos | Cascada con proyecto; usuario restringido |
| `task_reminder_deliveries` | Deduplificación de recordatorios | Cascada con tarea y usuario |
| `task_assignment_notifications` | Digest de asignación | IDs de tareas en JSON; cascada con usuario |

Laravel también crea tablas de infraestructura para sesiones, cache y cola según la configuración del entorno.

### 9.2 Integridad y restricciones relevantes

- El propietario de un proyecto no puede eliminarse mientras la FK restrictiva lo impida; no hay eliminación de usuarios en la UI.
- El líder puede quedar nulo si la FK se elimina directamente.
- Eliminar un proyecto elimina sus tareas, comentarios y costos en cascada.
- No hay una restricción de base de datos que derive automáticamente el estado del proyecto; la consistencia depende de los flujos de `ProjectService`.
- La relación de asignación no permite duplicados.

## 10. Configuración

### 10.1 Variables principales

| Variable | Desarrollo | Producción/uso |
| --- | --- | --- |
| `APP_ENV` | `local` | `production` |
| `APP_KEY` | Generada por `key:generate` | Obligatoria y secreta |
| `APP_URL` | `http://localhost:8080` | URL pública HTTPS |
| `APP_LOCALE` | `es` | Idioma de la aplicación |
| `DB_CONNECTION` | `mysql` | `mysql` |
| `DB_HOST` | `127.0.0.1` fuera de Compose / `db` dentro | `mysql` en Coolify |
| `DB_PORT` | `3307` desde el host / `3306` dentro | `3306` |
| `DB_DATABASE` | `kanban` | Base de datos productiva |
| `DB_USERNAME` | `kanban` | Usuario productivo |
| `DB_PASSWORD` | `kanban` solo en Compose local | Secreto productivo |
| `SESSION_DRIVER` | `database` | `database` |
| `CACHE_STORE` | `database` | `database` |
| `QUEUE_CONNECTION` | `database` | `database` |
| `MAIL_MAILER` | `log` | SMTP u otro proveedor real |
| `MAIL_FROM_ADDRESS` | Dirección local | Remitente real |
| `KANBAN_TIMEZONE` | `America/Bogota` | Zona horaria funcional |
| `KANBAN_REMINDER_TIME` | `08:00` | Hora diaria de recordatorios |

No se deben reutilizar las credenciales de ejemplo fuera de un entorno local controlado.

### 10.2 Entorno local con Docker Compose

`docker-compose.yml` define:

- `app`: PHP-FPM con el código montado.
- `nginx`: servidor HTTP en `http://localhost:8080`.
- `db`: MySQL 8.4, puerto del host `3307`, volumen `kanban_db`.
- `worker`: `php artisan queue:work` con tres intentos y timeout de 90 segundos.
- `scheduler`: ejecuta `schedule:run` cada 60 segundos.
- `vite`: Node 22 con servidor de desarrollo en `http://localhost:5173`.

El Compose local no incluye un servicio de migración automático. Las migraciones y los datos demo deben ejecutarse antes de usar la aplicación.

En el checkout Windows/WSL documentado por el proyecto, PHP/Composer/Artisan se ejecutan desde WSL usando `/home/coordsistemas/proyectos/kanban-mw`, mientras Node/npm se ejecuta desde Windows o mediante Docker. El comando `composer setup` incluye `npm install` y `npm run build`, por lo que solo debe utilizarse desde un entorno donde estén disponibles ambos toolchains.

### 10.3 Entorno de producción con Coolify

`docker-compose.coolify.yml` usa una imagen multi-stage:

1. Instala dependencias PHP.
2. Compila assets con Node 22.
3. Ejecuta PHP-FPM en `app`.
4. Sirve archivos y HTTP mediante `web`/nginx.

El orden de arranque es:

```text
mysql saludable
      -> migrate termina correctamente
            -> app, worker y scheduler
                  -> web expone HTTP
```

El dominio público debe apuntar únicamente a `web:80`. El servicio `app:9000` es FastCGI interno y no es un servidor HTTP.

Persistencia configurada:

- `kanban_mysql`: datos MySQL.
- `kanban_storage`: `storage/app/public`.

Variables mínimas para desplegar: `APP_URL`, `APP_KEY`, `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`; para correo real también `MAIL_MAILER`, host, puerto, credenciales, esquema y remitente. Se recomienda configurar backups del volumen MySQL y verificar el volumen de storage antes de publicar.

Después del primer despliegue, crear un administrador real desde el terminal del servicio `app` mediante Tinker. No ejecutar el seeder demo en producción.

## 11. Instalación y operación

### 11.1 Instalación genérica

Con PHP, Composer y Node disponibles en el mismo entorno:

```bash
cp .env.example .env
composer setup
```

`composer setup` instala dependencias PHP, crea `.env` si no existe, genera la clave, ejecuta `migrate --seed --force`, instala dependencias JavaScript y compila assets.

Si la base de datos MySQL no está disponible, el paso de migración fallará. En el entorno local del proyecto se puede iniciar primero:

```bash
docker compose up -d db
```

Después de preparar el código y la base de datos:

```bash
docker compose up -d
```

### 11.2 Comandos habituales

```bash
# Servidor de desarrollo del stack Laravel
php artisan serve

# Compilar assets para producción/verificación
npm run build

# Ejecutar todas las pruebas
php artisan test --compact

# Limpiar configuración y ejecutar la suite mediante Composer
composer test

# Formatear PHP modificado
vendor/bin/pint --dirty --format agent

# Ejecutar manualmente recordatorios para una fecha determinista
php artisan tasks:send-reminders --date=YYYY-MM-DD

# Worker fuera de Docker
php artisan queue:work

# Ejecutar el scheduler una vez
php artisan schedule:run

# Scheduler persistente fuera de Docker
php artisan schedule:work
```

En local, `MAIL_MAILER=log` escribe los correos en `storage/logs/laravel.log`; no los entrega a destinatarios externos. Como la cola predeterminada es `database`, los jobs necesitan un worker para procesarse cuando no se usa la conexión `sync` de pruebas.

## 12. Pruebas y calidad

La suite fuerza:

- SQLite en memoria.
- Cache `array`.
- Mail `array`.
- Cola `sync`.
- Sesiones `array`.
- Locale español.

Pruebas cubiertas:

- `tests/Feature/ProjectServiceTest.php`: ciclo de vida del proyecto, asignación, clonación, validaciones, permisos y seguimiento.
- `tests/Feature/TaskReminderTest.php`: agrupación, deduplicación y revalidación de recordatorios.
- `tests/Feature/FilamentSmokeTest.php`: login, dashboard por rol, tablero, modo solo lectura, timeline y escalas.
- `tests/Feature/ExampleTest.php` y `tests/Unit/ExampleTest.php`: pruebas base del scaffold.

Verificación recomendada antes de una entrega:

```bash
composer install --prefer-dist --no-interaction --no-progress
npm ci
npm run build
php artisan test --compact
composer audit --no-interaction
vendor/bin/pint --dirty --format agent
```

## 13. Convenciones de mantenimiento

- Las mutaciones de proyectos y tareas deben centralizarse en `app/Services/ProjectService.php`.
- Las decisiones de acceso deben conservar las policies y `Project::visibleTo()`.
- No duplicar roles como strings si existe `UserRole` o sus helpers (`isAdmin`, `isCollaborator`, `isObserver`).
- Los usuarios asignables deben consultarse con el scope `assignable()`.
- Toda modificación de estado de tarea debe pasar por la sincronización del estado del proyecto.
- Los cambios que agreguen correo deben considerar la cola, `afterCommit()` y el worker.
- Los cambios de PHP deben formatearse con Pint.
- Los cambios de frontend deben verificarse con `npm run build`; no hay scripts de lint, typecheck o tests JavaScript configurados.

## 14. Limitaciones y riesgos conocidos

Estas notas describen el estado actual, no funcionalidades recomendadas:

1. No existe eliminación en la interfaz. Aunque `TaskPolicy` tiene una capacidad `delete`, no hay acción ni flujo que la use.
2. El servicio de creación de proyectos es más permisivo que el formulario: una llamada directa puede omitir fechas y no repite la validación de que el fin no sea anterior al inicio. La UI sí exige los campos en proyectos normales.
3. El estado del proyecto no está protegido por un trigger o constraint de base de datos; escrituras directas pueden dejarlo inconsistente.
4. Las tareas no se pueden reordenar desde el tablero. `position` se asigna al crear y se conserva al clonar.
5. No hay restricción que mantenga las fechas de las tareas dentro de las fechas del proyecto.
6. La moneda COP está asumida por la vista, no modelada en la base de datos.
7. Los colaboradores asignados a una tarea obtienen visibilidad, no permisos de edición ni de cambio de estado.
8. Los proyectos finalizados permiten editar su encabezado y clonarse, aunque sus tareas, notas y costos quedan bloqueados.
9. Las observaciones siguen permitidas después del cierre; las notas y costos no.
10. El dashboard puede mostrar a observadores proyectos pendientes de configuración, aunque no puedan configurarlos.
11. La notificación de asignación guarda IDs de tareas y el job no vuelve a comprobar la asignación individual antes de enviar; una modificación de asignaciones mientras el job espera puede producir un correo desactualizado.
12. Un usuario inactivo conserva asignaciones existentes; no recibe recordatorios mientras esté inactivo.
13. La zona horaria funcional (`KANBAN_TIMEZONE`) y la zona horaria general UTC pueden producir diferencias entre fechas de negocio y timestamps.
14. No hay pruebas específicas para todas las rutas de eliminación, concurrencia de asignaciones, desactivación del último administrador, validaciones directas del servicio de proyectos o cambios de autorización durante una sesión Livewire.
15. `resources/views/welcome.blade.php` es el scaffold de Laravel y no pertenece al flujo real porque `/` redirige al panel.

## 15. Mapa de archivos clave

| Archivo | Propósito |
| --- | --- |
| `app/Providers/Filament/AdminPanelProvider.php` | Configuración del panel y navegación |
| `app/Filament/Pages/ProjectBoard.php` | Estado y acciones Livewire del tablero |
| `resources/views/filament/pages/project-board.blade.php` | Kanban, seguimiento, costos y modal de tareas |
| `resources/views/filament/pages/project-timeline.blade.php` | Línea de tiempo |
| `app/Services/ProjectService.php` | Casos de uso de proyectos y tareas |
| `app/Models/Project.php` | Proyecto, relaciones, visibilidad y costos |
| `app/Models/Task.php` | Tarea, recordatorios y borradores |
| `app/Policies/ProjectPolicy.php` | Acceso a proyectos y seguimiento |
| `app/Policies/TaskPolicy.php` | Acceso a tareas |
| `routes/console.php` | Programación del recordatorio diario |
| `app/Console/Commands/SendTaskReminders.php` | Selección y deduplicación de recordatorios |
| `app/Jobs/SendTaskAssignmentDigest.php` | Envío de asignaciones |
| `app/Jobs/SendTaskReminderDigest.php` | Envío de recordatorios |
| `database/seeders/DatabaseSeeder.php` | Usuarios demo local/testing |
| `docker-compose.yml` | Stack local |
| `docker-compose.coolify.yml` | Stack de producción |

## 16. Glosario

- **Propietario**: usuario que creó o recibió la propiedad del proyecto; tiene control operativo junto con el administrador.
- **Líder**: usuario responsable del liderazgo del proyecto; su condición da visibilidad, pero no edición por sí sola.
- **Responsable**: usuario asignado a una tarea; obtiene visibilidad del proyecto y recibe correos.
- **Proyecto activo**: proyecto cuyo estado no es `finished`; incluye `created` y `in_progress`.
- **Borrador de tarea**: tarea sin alguna fecha o sin responsables.
- **Copia clonada**: proyecto creado desde otro, con estructura básica de tareas pero sin planificación ni seguimiento histórico.
- **Digest**: correo agrupado con varias tareas de una misma operación o fecha.
