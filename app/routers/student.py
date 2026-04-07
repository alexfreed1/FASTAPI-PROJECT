from fastapi import APIRouter
router = APIRouter()
@router.get("/")
async def student_root():
    return {"message": "Student router - dashboard"}

