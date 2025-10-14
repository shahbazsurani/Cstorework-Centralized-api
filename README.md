# Centralized API (Laravel)

A Laravel-based REST API for centralizing application, user, and location management. This repository exposes authenticated endpoints (Laravel Sanctum) and role/permission controls (Spatie) to manage:

- Applications (SuperAdmin)
- Locations (SuperAdmin, LocationAdmin, UserAdmin)
- Users (authentication, registration; Sanctum-protected)

TODO: Add a high-level domain description (what business this API serves, key data model, and consumers).


## Stack

- Language: PHP ^8.2
- Framework: Laravel ^12
- Auth: Laravel Sanctum ^4
- Authorization/ACL: spatie/laravel-permission ^6
- Queues/Jobs: Laravel queue (configured for database by default in .env.example)
- Database: SQLite by default (via .env.example); MySQL/PostgreSQL can be configured
- Frontend tooling: Vite ^7, Tailwind CSS ^4, axios (for any HTTP clients)
- Dev utilities: Laravel Pail (live log viewer), Laravel Pint (code style), PHPUnit ^11
- Node package manager: npm (type: module)
- PHP package manager: Composer


## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+ and npm 9+
- SQLite (default) or another DB supported by Laravel (MySQL/MariaDB, PostgreSQL, SQL Server)
- Redis optional (if switching cache/queue driver)

Windows, macOS, and Linux are supported. Docker is available via Laravel Sail (dev dependency) but is not preconfigured for this project.


## Project structure (partial)

- app/ … application code (Controllers, Models, Policies, Providers)
- routes/
  - api.php … API routes under /api (Sanctum + role middleware)
  - user.php … Additional API routes under /api (login, user, register)
- database/ … factories, migrations, seeders
- public/ … web server document root (entry point: public/index.php)
- resources/ … assets (css/js) built by Vite
- tests/ … PHPUnit tests (Feature, Unit)
- artisan … Laravel CLI
- composer.json … PHP dependencies and Composer scripts
- package.json … Node dependencies and scripts
- phpunit.xml … PHPUnit configuration
- vite.config.js … Vite configuration


## Routes and entry points

- HTTP entry point: public/index.php served by a web server or php artisan serve
- API route files are registered with prefixes /api/v1 (primary) and /api (legacy alias) in app/Providers/RouteServiceProvider.php:
  - routes/api.php (protected routes using Sanctum and role middleware)
  - routes/user.php (auth endpoints and user info; login is throttled)

Examples (see controllers for details):
- POST /api/v1/login → AuthController@login (rate limited)
- GET /api/v1/user (auth:sanctum)
- POST /api/v1/register (auth:sanctum)
- REST /api/v1/applications (role: SuperAdmin)
- REST /api/v1/locations (roles: SuperAdmin|LocationAdmin|UserAdmin)

TODO: Document all endpoints with request/response examples or provide an OpenAPI/Swagger document.


## Environment variables

Copy .env.example to .env, then adjust as needed:

- APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL
- APP_LOCALE, APP_FALLBACK_LOCALE, APP_FAKER_LOCALE
- LOG_CHANNEL, LOG_LEVEL
- DB_CONNECTION (default sqlite) and related DB_* settings if not using SQLite
- SESSION_DRIVER (default database)
- QUEUE_CONNECTION (default database)
- CACHE_STORE (default database)
- REDIS_* if using Redis
- MAIL_* for outbound email
- VITE_APP_NAME

See .env.example for full list and defaults.


## Setup

1) Install PHP and Node dependencies
- composer install
- npm install

2) Create a local env file and app key
- copy .env.example .env
- php artisan key:generate

3) Configure database
- For SQLite (default): ensure the file exists: database/database.sqlite
  - On Windows PowerShell: New-Item -ItemType File -Path .\database\database.sqlite -Force
- Or set DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env for MySQL/Postgres

4) Run migrations (and optionally seeders)
- php artisan migrate
- Optional: php artisan db:seed

5) Ensure queue tables exist if using database queues
- php artisan queue:table
- php artisan migrate


## Run (development)

Option A — All-in-one dev script (PHP server + queue listener + logs + Vite)
- composer run dev
  - Runs: php artisan serve, php artisan queue:listen --tries=1, php artisan pail --timeout=0, and npm run dev concurrently

Option B — Run individually
- php artisan serve
- php artisan queue:listen --tries=1
- php artisan pail --timeout=0
- npm run dev

The API will be available at: http://localhost:8000/api (default artisan port).


## Scripts

Composer scripts
- dev … Concurrently run server, queue, logs, and Vite
- test … Clears config cache then runs the test suite

npm scripts
- dev … Start Vite in dev mode
- build … Production build via Vite


## Testing

- Run all tests: composer test
- Or: php artisan test

Add new tests under tests/Feature and tests/Unit. PHPUnit is configured via phpunit.xml.


## Development notes

- Authentication uses Laravel Sanctum; issue tokens or use cookie-based SPA auth per your client.
- Authorization uses spatie/laravel-permission. Ensure roles/permissions are seeded and assigned as required by your app logic.
- Sessions, cache, and queues default to database drivers in .env.example. You may switch to Redis or others as needed.

TODO: Add seeding instructions for roles/permissions and any initial admin user.


## Production

- Build assets: npm run build
- Configure a web server (Nginx/Apache) to serve public/ as the document root
- Set APP_ENV=production, APP_DEBUG=false, cache config/routes/views as appropriate
- Configure a queue worker service (e.g., Supervisor) if using queues


## RBAC: roles and permissions

Roles (App\\Enums\\Role)
- SuperAdmin (master)
- ApplicationAdmin
- LocationAdmin
- UserAdmin
- ReadOnly
- ReadWrite

Permissions (App\\Enums\\Permission)
- Users: users.view, users.create, users.update, users.delete
- Locations: locations.view, locations.create, locations.update, locations.delete
- Applications: applications.view, applications.create, applications.update, applications.delete

Default role → permission mapping (seeded)
- SuperAdmin: all permissions
- ApplicationAdmin: applications.*
- LocationAdmin: locations.*
- UserAdmin: users.*
- ReadOnly: *.view
- ReadWrite: view/create/update across resources (no delete)

Seeding
- Run core migrations then seed RBAC:
  - php artisan migrate
  - php artisan db:seed --class=RolesAndPermissionsSeeder
- Or run the app DatabaseSeeder (also can create an initial SuperAdmin if env vars are provided):
  - php artisan db:seed
  - .env variables (optional) to auto-create a SuperAdmin:
    - SEED_ADMIN_EMAIL=admin@example.com
    - SEED_ADMIN_NAME="Super Admin"
    - SEED_ADMIN_PASSWORD=changeMe

Permission cache
- Spatie permissions are cached for 24 hours by default.
- To reset after changing roles/permissions: php artisan permission:cache-reset

API versioning
- Primary prefix: /api/v1
- Legacy alias retained temporarily: /api

## License

This project is open-sourced software licensed under the MIT license. See the LICENSE file if present. If a LICENSE file is missing, the license in composer.json applies (MIT).
