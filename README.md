# Kanban MW

Aplicación de gestión de proyectos y tareas construida con Laravel, Filament, Livewire y Tailwind CSS.

## Requisitos

- PHP 8.3+
- Composer 2+
- Node.js 22+
- Docker y Docker Compose (para el entorno local completo)

## Desarrollo local

```bash
cp .env.example .env
composer setup
```

El comando `composer setup` instala dependencias, genera la clave, ejecuta las migraciones con datos demo y compila los assets. Con Docker Compose, la aplicación queda disponible en `http://localhost:8080`:

```bash
docker compose up -d
```

Las cuentas creadas por el seeder son exclusivamente para desarrollo. Nunca ejecutes `db:seed` en producción.

## Despliegue en Coolify

El despliegue de producción usa `docker-compose.coolify.yml` desde el repositorio público `https://github.com/Misterwings/kanban-mw`.

1. Crea una aplicación nueva desde el repositorio y selecciona el build pack **Docker Compose**.
2. Configura `docker-compose.coolify.yml` como ubicación del Compose.
3. Asigna el dominio únicamente al servicio `web` en el puerto interno `80`. No lo asignes a `app`: el puerto `9000` es FastCGI interno y no acepta tráfico HTTP.
4. Define en Coolify `APP_URL`, `APP_KEY`, `DB_PASSWORD` y `MYSQL_ROOT_PASSWORD`.
5. Revisa las variables de correo antes de activar los recordatorios por email.
6. Despliega. El servicio `migrate` ejecuta `php artisan migrate --force` antes de iniciar la aplicación, worker y scheduler.

MySQL utiliza el volumen persistente `kanban_mysql`. Configura copias de seguridad del volumen desde Coolify. Los archivos públicos de la aplicación utilizan `kanban_storage`.

### Variables principales

```text
APP_URL=https://tu-dominio.example
APP_KEY=base64:...
DB_PASSWORD=...
MYSQL_ROOT_PASSWORD=...
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS=...
```

Después del primer despliegue, abre el terminal del servicio `app` y crea el primer usuario administrador. Sustituye los valores antes de ejecutar; no uses las cuentas demo del entorno local.

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@tu-dominio.example',
    'password' => 'REEMPLAZA_CON_UNA_CLAVE_LARGA',
    'role' => \App\Enums\UserRole::Admin,
    'is_active' => true,
]);
```

## Tests

```bash
php artisan test --compact
npm run build
```

## Licencia

Este proyecto se distribuye bajo la licencia MIT.
