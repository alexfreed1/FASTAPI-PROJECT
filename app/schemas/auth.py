from pydantic import BaseModel, EmailStr
from typing import Optional
from datetime import datetime

class Token(BaseModel):
    access_token: str
    token_type: str = "bearer"

class TokenData(BaseModel):
    username: Optional[str] = None
    role: str

class LoginRequest(BaseModel):
    username: str
    password: str

class StudentRegister(BaseModel):
    admission_number: str
    full_name: str
    email: EmailStr
    password: str
    class_id: int

