# DocuBrain

> Gestor de documentos con preguntas y respuestas (RAG) — proyecto de aprendizaje profundo.

## Stack

| Capa | Tecnología |
|------|------------|
| API | Laravel 13 + Lighthouse (GraphQL) |
| Auth | Laravel Sanctum |
| DB | PostgreSQL 18 + pgvector |
| Cache / Colas | Redis 7 |
| Frontend | Vue 3 + Vuetify 3 + Apollo *(Fase 7)* |
| Infra | Docker Compose |

## Inicio rápido

**Requisitos:** Docker Desktop instalado y corriendo.

```bash
# 1. Clonar el repo
git clone <repo-url> docubrain
cd docubrain

# 2. Levantar la infraestructura (Postgres 18 + pgvector, Redis, app PHP 8.5)
docker compose up --build -d

# 3. Ejecutar migraciones
docker compose exec app php artisan migrate

# 4. (Opcional) Crear usuario de prueba vía Tinker
docker compose exec app php artisan tinker

# 5. Abrir GraphiQL en el navegador
open http://localhost:8000/graphiql
```

## GraphQL — Operaciones disponibles (Fase 0)

```graphql
# Registrar usuario
mutation {
  register(input: {
    name: "Ada Lovelace"
    email: "ada@example.com"
    password: "secret123"
    password_confirmation: "secret123"
  }) {
    token
    user { id name email }
  }
}

# Login
mutation {
  login(input: {
    email: "ada@example.com"
    password: "secret123"
  }) {
    token
    user { id name email }
  }
}

# Perfil (requiere header: Authorization: Bearer <token>)
query {
  me { id name email created_at }
}

# Logout (requiere header: Authorization: Bearer <token>)
mutation {
  logout { message }
}
```

## Estructura del proyecto

```
docubrain/
├── backend/          # Laravel 13 (PHP 8.5)
│   ├── app/GraphQL/  # Resolvers de Lighthouse
│   ├── graphql/      # schema.graphql
│   └── database/     # Migraciones
├── frontend/         # Vue 3 + Vuetify — se crea en Fase 7
├── docker/
│   ├── app/          # Dockerfile PHP 8.5
│   └── postgres/     # Dockerfile Postgres 18 + pgvector + init.sql
├── docker-compose.yml
└── DOCUBRAIN_SPEC.md # Spec completa + roadmap de fases
```

## Roadmap

| Fase | Descripción |
|------|-------------|
| **0** ✅ | Bootstrap: Docker + Laravel + Lighthouse + Auth |
| 1 | Testing: PHPUnit + Lighthouse helpers + Faker |
| 2 | CI: GitHub Actions |
| 3 | SQL avanzado: EXPLAIN ANALYZE + índices + N+1 |
| 4 | Redis: Queues + Cache + Pub/Sub |
| 5 | Nginx: Reverse proxy |
| 6 | GraphQL avanzado: Subscriptions + mutations RAG |
| 7 | Vue 3 + Vuetify: SPA completa |
| 8 | pgvector / RAG: chunking + embeddings + similarity search |
| 9 | Integración y despliegue |