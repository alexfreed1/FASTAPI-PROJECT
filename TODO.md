## TODO: FastAPI Attendance System Migration

### [ ] Phase 1: Backup & Structure
- [ ] Backup PHP to legacy/
- [ ] Create requirements.txt
- [ ] Create .gitignore
- [ ] Update README.md
- [ ] Create render.yaml (Blueprint)
- [ ] Init project dirs (app/core etc.)

### [ ] Phase 2: Core Setup
- [ ] app/main.py (FastAPI app)
- [ ] core/config.py, database.py, security.py
- [ ] models/base.py + entities.py (SQLAlchemy)
- [ ] schemas/ (Pydantic)

### [ ] Phase 3: DB Migration
- [ ] alembic.ini + env.py
- [ ] alembic revision --autogenerate init
- [ ] Adapt migration to PostgreSQL
- [ ] Seed script

### [ ] Phase 4: Auth & Routers
- [ ] routers/auth.py (JWT login)
- [ ] Dependencies (current_user)

### [ ] Phase 5: Business Logic
- [ ] routers/admin.py (CRUD)
- [ ] routers/lecturer.py (dashboard, submit)
- [ ] routers/student.py

### [ ] Phase 6: UI/Templates
- [ ] templates/ (Jinja2 + Tailwind reuse)
- [ ] static/assets/ (copy logo)

### [ ] Phase 7: Deploy & Test
- [ ] Local Docker PG + test
- [ ] Git init/push
- [ ] Render deploy
- [ ] Full verification

**Current Progress: Phase 2 complete - DB ready. Phase 3: Migrations & Auth**
- [x] venv setup command
- [ ] alembic revision
- [ ] routers/auth.py

