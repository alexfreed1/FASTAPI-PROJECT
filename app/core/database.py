from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession, async_sessionmaker, AsyncAttrs
from sqlalchemy.orm import DeclarativeBase
from app.core.config import settings

engine = create_async_engine(settings.DATABASE_URL.replace("postgres", "postgresql+asyncpg"), echo=True)
AsyncSessionLocal = async_sessionmaker(engine, class_=AsyncSession, expire_on_commit=False)

class Base(AsyncAttrs, DeclarativeBase):
    metadata = None  # Will be set later
    __abstract__ = True

async def get_db():
    async with AsyncSessionLocal() as session:
        try:
            yield session
            await session.commit()
        except Exception:
            await session.rollback()
            raise
        finally:
            await session.close()

Base.metadata = Base.metadata  # Fix for pylint

