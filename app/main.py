from fastapi import FastAPI
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.cors import CORSMiddleware

from app.core.config import settings
from app.routers import auth, admin, lecturer, student

app = FastAPI(title="Attendance System API", version="1.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.mount('/static', StaticFiles(directory="app/static"), name="static")

templates = Jinja2Templates(directory="app/templates")

app.include_router(auth.router, prefix="/api/v1/auth", tags=["auth"])
app.include_router(admin.router, prefix="/api/v1/admin", tags=["admin"])
app.include_router(lecturer.router, prefix="/api/v1/lecturer", tags=["lecturer"])
app.include_router(student.router, prefix="/api/v1/student", tags=["student"])

@app.get("/")
async def root():
    return {"message": "Attendance System API - Ready to deploy!"}

@app.get("/health")
async def health():
    return {"status": "healthy", "db": settings.DATABASE_URL[:20] + "..."}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)

