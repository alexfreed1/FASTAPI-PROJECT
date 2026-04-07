from sqlalchemy import Column, Integer, String, ForeignKey, Date, Enum as SQLEnum, Check
from sqlalchemy.orm import relationship
from sqlalchemy.dialects.postgresql import ENUM
from app.core.database import Base

# PostgreSQL enum
lesson_enum = ENUM('L1', 'L2', 'L3', 'L4', name='lesson_enum')
status_enum = ENUM('present', 'absent', name='status_enum')

class Department(Base):
    __tablename__ = "departments"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(100), unique=True, index=True)
    
    classes = relationship("Class", back_populates="department")
    trainers = relationship("Trainer", back_populates="department")

class Class(Base):
    __tablename__ = "classes"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(100), index=True)
    department_id = Column(Integer, ForeignKey("departments.id"))
    
    department = relationship("Department", back_populates="classes")
    students = relationship("Student", back_populates="class_")
    class_units = relationship("ClassUnit", back_populates="class_")

class Student(Base):
    __tablename__ = "students"
    
    id = Column(Integer, primary_key=True, index=True)
    admission_number = Column(String(50), unique=True, index=True)
    full_name = Column(String(200))
    email = Column(String(255), nullable=True)
    password = Column(String(128))  # hashed
    class_id = Column(Integer, ForeignKey("classes.id"))
    
    class_ = relationship("Class", back_populates="students")
    attendances = relationship("Attendance", back_populates="student")

class Unit(Base):
    __tablename__ = "units"
    
    id = Column(Integer, primary_key=True, index=True)
    code = Column(String(50), unique=True, index=True)
    name = Column(String(200))
    
    class_units = relationship("ClassUnit", back_populates="unit")
    attendances = relationship("Attendance", back_populates="unit")

class Trainer(Base):
    __tablename__ = "trainers"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(200))
    username = Column(String(100), unique=True, index=True)
    password = Column(String(128))  # hashed
    department_id = Column(Integer, ForeignKey("departments.id"), nullable=True)
    
    department = relationship("Department", back_populates="trainers")
    class_units = relationship("ClassUnit", back_populates="trainer")
    attendances = relationship("Attendance", back_populates="trainer")

class ClassUnit(Base):
    __tablename__ = "class_units"
    
    id = Column(Integer, primary_key=True)
    class_id = Column(Integer, ForeignKey("classes.id"))
    unit_id = Column(Integer, ForeignKey("units.id"))
    trainer_id = Column(Integer, ForeignKey("trainers.id"))
    
    class_ = relationship("Class", back_populates="class_units")
    unit = relationship("Unit", back_populates="class_units")
    trainer = relationship("Trainer", back_populates="class_units")

class Attendance(Base):
    __tablename__ = "attendance"
    
    id = Column(Integer, primary_key=True, index=True)
    student_id = Column(Integer, ForeignKey("students.id"))
    unit_id = Column(Integer, ForeignKey("units.id"))
    trainer_id = Column(Integer, ForeignKey("trainers.id"))
    lesson = Column(lesson_enum)
    week = Column(Integer, CheckConstraint('week >=1 AND week <=52'))
    attendance_date = Column(Date)
    status = Column(status_enum)
    created_at = Column(Date, server_default='CURRENT_TIMESTAMP')
    
    student = relationship("Student", back_populates="attendances")
    unit = relationship("Unit", back_populates="attendances")
    trainer = relationship("Trainer", back_populates="attendances")

class Admin(Base):
    __tablename__ = "admins"
    
    id = Column(Integer, primary_key=True)
    username = Column(String(100), unique=True, index=True)
    password = Column(String(128))  # hashed

