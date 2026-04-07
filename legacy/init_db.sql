-- Unit Attendance System Database Schema
DROP DATABASE IF EXISTS attendancesystem;
CREATE DATABASE attendancesystem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendancesystem;

-- Departments Table
CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

-- Classes Table
CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  department_id INT NOT NULL,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Students Table
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admission_no VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(200) NOT NULL,
  class_id INT NOT NULL,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Units Table
CREATE TABLE units (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  title VARCHAR(200) NOT NULL
);

-- Trainers Table
CREATE TABLE trainers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(100) NOT NULL,
  department_id INT,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Class Units Assignment Table
CREATE TABLE class_units (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT NOT NULL,
  unit_id INT NOT NULL,
  trainer_id INT NOT NULL,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE,
  UNIQUE KEY unique_assignment (class_id, unit_id, trainer_id)
);

-- Attendance Records Table
CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  unit_id INT NOT NULL,
  trainer_id INT NOT NULL,
  lesson ENUM('L1','L2','L3','L4') NOT NULL,
  week INT NOT NULL,
  date DATE NOT NULL,
  status ENUM('present','absent') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE
);

-- Admin Accounts Table
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(100) NOT NULL
);

-- INSERT SEED DATA

-- Insert Departments
INSERT INTO departments (name) VALUES 
('Electrical'),
('Mechanical'),
('Civil');

-- Insert Classes
INSERT INTO classes (name, department_id) VALUES 
('ELECT-1', 1),
('ELECT-2', 1),
('MECH-1', 2);

-- Insert Students
INSERT INTO students (admission_no, name, class_id) VALUES
('E001', 'Alice Mwangi', 1),
('E002', 'Brian Otieno', 1),
('E003', 'Catherine Njoroge', 2),
('M001', 'Daniel Kimani', 3);

-- Insert Units
INSERT INTO units (code, title) VALUES 
('EE101', 'Circuit Theory'),
('EE102', 'Digital Systems'),
('ME101', 'Engineering Drawing');

-- Insert Trainers
INSERT INTO trainers (name, username, password, department_id) VALUES
('John Trainer', 'john', 'john123', 1),
('Mary Trainer', 'mary', 'mary123', 2);

-- Assign Units to Classes with Trainers
INSERT INTO class_units (class_id, unit_id, trainer_id) VALUES
(1, 1, 1),
(1, 2, 1),
(2, 2, 1),
(3, 3, 2);

-- Insert Admin Account
INSERT INTO admins (username, password) VALUES 
('admin', 'admin123');
