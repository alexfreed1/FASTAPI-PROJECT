"""Alembic environment script."""
import asyncio
from logging.config import fileConfig
from sqlalchemy.ext.asyncio import create_async_engine
from sqlalchemy import pool
from alembic import context

from app.core.config import settings
from app.core.database import Base
from app.models.entities import *  # noqa

# Alembic Config object
config = context.config

if config.config_file_name is not None:
    fileConfig(config.config_file_name)

target_metadata = Base.metadata

def run_migrations_offline() -> None:
    """Run migrations in 'offline' mode."""
    context.configure(
        url=settings.ALCHEMY_DATABASE_URL or settings.DATABASE_URL,
        target_metadata=target_metadata,
        literal_binds=True,
        dialect_opts={"paramstyle": "named"},
    )

    with context.begin_transaction():
        context.run_migrations()

async def run_async_migrations() -> None:
    """Run migrations in 'online' mode."""
    connectable = create_async_engine(
        settings.DATABASE_URL.replace("postgres", "postgresql+asyncpg"),
        echo=True
    )

    async with connectable.connect() as connection:
        await connection.run_sync(do_run_migrations)

    await connectable.dispose()

def do_run_migrations(connection):
    context.configure(
        connection=connection,
        target_metadata=target_metadata
    )

    with context.begin_transaction():
        context.run_migrations()

async def run_migrations_online() -> None:
    """Run migrations in 'online' mode."""
    await run_async_migrations()

if context.is_offline_mode():
    run_migrations_offline()
else:
    asyncio.run(run_migrations_online())

