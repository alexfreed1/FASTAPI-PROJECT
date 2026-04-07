from pydantic_settings import BaseSettings
from pydantic import Field
from typing import Optional

class Settings(BaseSettings):
    DATABASE_URL: str = Field(..., env="DATABASE_URL")
    
    SECRET_KEY: str = Field(default="change-me-super-secret-key-for-jwt", env="SECRET_KEY")
    
    ALCHEMY_DATABASE_URL: Optional[str] = Field(None, env="ALCHEMY_DATABASE_URL")
    
    PROJECT_NAME: str = "Attendance System"
    VERSION: str = "1.0.0"
    
    class Config:
        env_file = ".env"
        case_sensitive = False

settings = Settings()

