# Fase 0 — Bootstrap de DocuBrain

Configurar la infraestructura base del proyecto: Docker Compose con Postgres+pgvector y Redis, proyecto Laravel 13 con Sanctum y Lighthouse GraphQL, y la estructura de carpetas `backend/` y `frontend/`.

## Entregable

- Docker Compose levanta **Postgres 17 con pgvector**, **Redis** y el **app container (PHP/Laravel)**
- Laravel 13 instalado con **Sanctum** (auth por tokens) y **Lighthouse** (GraphQL)
- Schema GraphQL mínimo: query `me`, mutations `register` y `login`
- Carpeta `frontend/` creada (vacía, placeholder para Fase 7)
- Todo conectado y funcionando con un solo `docker compose up`

---

## Proposed Changes

### Docker Infrastructure

#### [NEW] [docker-compose.yml](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/docker-compose.yml)
Archivo Docker Compose en la raíz del proyecto con 3 servicios:
- **`app`**: PHP 8.3 + Laravel (build desde `docker/app/Dockerfile`)
  - Volumen montando `./backend` a `/var/www/html`
  - Depende de `postgres` y `redis`
- **`postgres`**: Imagen `pgvector/pgvector:pg17`
  - Puerto `5432` expuesto
  - Volumen persistente para datos
  - DB: `docubrain`, User: `docubrain`, Password: `docubrain`
- **`redis`**: Imagen `redis:7-alpine`
  - Puerto `6379` expuesto

#### [NEW] [docker/app/Dockerfile](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/docker/app/Dockerfile)
Imagen PHP 8.3-FPM con:
- Extensiones: `pdo_pgsql`, `pgsql`, `redis`, `pcntl`, `bcmath`, `zip`
- Composer instalado
- Working directory: `/var/www/html`
- Comando: `php artisan serve --host=0.0.0.0 --port=8000`

#### [NEW] [docker/app/php.ini](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/docker/app/php.ini)
Configuración PHP personalizada (upload_max_filesize, memory_limit para PDFs).

#### [NEW] [docker/postgres/init.sql](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/docker/postgres/init.sql)
Script de inicialización que ejecuta `CREATE EXTENSION IF NOT EXISTS vector;` al crear la DB.

---

### Backend (Laravel 13)

#### [NEW] backend/
Proyecto Laravel 13 creado con `composer create-project laravel/laravel`. Paquetes adicionales:
- `laravel/sanctum` (viene incluido en Laravel 13)
- `nuwave/lighthouse` — GraphQL server
- `mll-lab/laravel-graphql-playground` — dev tool para testear queries

#### [NEW] [backend/.env](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/backend/.env)
Configurado para conectar con los servicios Docker:
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=docubrain
DB_USERNAME=docubrain
DB_PASSWORD=docubrain

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

#### [NEW] [backend/graphql/schema.graphql](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/backend/graphql/schema.graphql)
Schema mínimo de Lighthouse:
```graphql
type Query {
    me: User @auth @guard(with: ["sanctum"])
}

type Mutation {
    register(input: RegisterInput! @spread): AuthPayload!
    login(input: LoginInput! @spread): AuthPayload!
    logout: LogoutResponse! @guard(with: ["sanctum"])
}

type User {
    id: ID!
    name: String!
    email: String!
    created_at: DateTime!
    updated_at: DateTime!
}

input RegisterInput {
    name: String!
    email: String! @rules(apply: ["email", "unique:users,email"])
    password: String! @rules(apply: ["min:8"])
    password_confirmation: String!
}

input LoginInput {
    email: String!
    password: String!
}

type AuthPayload {
    token: String!
    user: User!
}

type LogoutResponse {
    message: String!
}
```

#### [NEW] [backend/app/GraphQL/Mutations/Register.php](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/backend/app/GraphQL/Mutations/Register.php)
Resolver para la mutation `register`: crea usuario, genera token Sanctum, retorna `AuthPayload`.

#### [NEW] [backend/app/GraphQL/Mutations/Login.php](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/backend/app/GraphQL/Mutations/Login.php)
Resolver para la mutation `login`: valida credenciales, genera token Sanctum, retorna `AuthPayload`.

#### [NEW] [backend/app/GraphQL/Mutations/Logout.php](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/backend/app/GraphQL/Mutations/Logout.php)
Resolver para la mutation `logout`: revoca token actual.

#### [NEW] [backend/app/GraphQL/Queries/Me.php](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/backend/app/GraphQL/Queries/Me.php)
Resolver para la query `me`: retorna el usuario autenticado.

---

### Frontend (Placeholder)

#### [NEW] [frontend/README.md](file:///c:/Users/BTWSunshide/Documents/Proyectos/DocuBrain/frontend/README.md)
Carpeta placeholder con README indicando que el frontend (Vue 3 + Vuetify) se construye en Fase 7.

---

## Pasos de Ejecución

1. Crear estructura de directorios (`docker/`, `frontend/`)
2. Crear archivos Docker (`Dockerfile`, `docker-compose.yml`, `init.sql`, `php.ini`)
3. Crear proyecto Laravel 13 en `backend/` via `composer create-project`
4. Instalar dependencias: Lighthouse, GraphQL Playground
5. Configurar `.env` para Postgres y Redis
6. Crear schema GraphQL y resolvers de auth
7. `docker compose up --build` para verificar que todo levanta
8. Correr `php artisan migrate` dentro del container
9. Verificar conectividad: GraphQL Playground en `http://localhost:8000/graphql-playground`

## Verification Plan

### Automated
- `docker compose up -d` levanta los 3 servicios sin errores
- `docker compose exec app php artisan migrate` corre sin errores
- `docker compose exec app php artisan tinker --execute="DB::connection()->getPDO();"` confirma conexión a Postgres
- `docker compose exec app php artisan tinker --execute="Illuminate\Support\Facades\Redis::ping();"` confirma conexión a Redis

### Manual
- Acceder a `http://localhost:8000/graphql-playground` y ejecutar:
  - Mutation `register` → recibir token
  - Mutation `login` → recibir token
  - Query `me` con header `Authorization: Bearer <token>` → datos del usuario

> [!NOTE]
> La spec menciona `docker/docker-compose.yml` en la estructura de repo, pero colocaré `docker-compose.yml` en la **raíz** del proyecto para facilitar el uso (`docker compose up` desde la raíz). Los archivos de configuración específicos de cada servicio sí van dentro de `docker/`.
