from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from app.core.database import get_db
from app.core.security import verify_password, create_access_token
from app.schemas.auth import LoginRequest, Token
from app.models.entities import Admin, Trainer, Student

router = APIRouter()

@router.post("/login/admin", response_model=Token):
async def login_admin(request: LoginRequest, db: AsyncSession = Depends(get_db)):
    admin = await db.get(Admin, request.username)  # Mock for now
    if not admin or not verify_password(request.password, admin.password):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    token = create_access_token({"sub": admin.username, "role": "admin"})
    return Token(access_token=token)

@router.post("/login/trainer", response_model=Token):
async def login_trainer(request: LoginRequest, db: AsyncSession = Depends(get_db)):
    trainer = await db.get(Trainer, request.username)
    if not trainer or not verify_password(request.password, trainer.password):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    token = create_access_token({"sub": trainer.username, "role": "trainer"})
    return Token(access_token=token)

@router.post("/login/student", response_model=Token):
async def login_student(request: LoginRequest, db: AsyncSession = Depends(get_db)):
    student = await db.get(Student, request.admission_number)
    if not student or not verify_password(request.password, student.password):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    token = create_access_token({"sub": student.admission_number, "role": "student"})
    return Token(access_token=token)

