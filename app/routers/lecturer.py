from fastapi import APIRouter
router = APIRouter()
@router.get("/")
async def lecturer_root():
    return {"message": "Lecturer router - attendance dashboard"}

