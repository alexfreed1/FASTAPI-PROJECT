# FastAPI Attendance System

Ready-to-deploy FastAPI + PostgreSQL attendance system, migrated from PHP.

## Local Setup

1. Copy `.env.example` to `.env`, update DB URL (use docker-compose)
2. `python -m venv venv`
3. `venv\Scripts\activate`
4. `pip install -r requirements.txt`
5. `alembic upgrade head`
6. `uvicorn app.main:app --reload`

## Docker Local

```bash
docker-compose up db
# In new term: alembic upgrade head (in venv)
docker-compose up api
```

## Deploy to Render

1. Git push (git init, add ., commit -m "initial", github.com)
2. Render.com → New Blueprint → connect repo
3. Auto-deploys with postgres DB

## Test / Endpoints

- GET /health
- GET /docs (Swagger)

## Default Creds (seed after migrate)
Admin: admin / admin123 (hashed on login)
Trainer: john / john123
etc.

## Render Ready
✓ Blueprint yaml
✓ Postgres
✓ Static files
✓ Migrations

