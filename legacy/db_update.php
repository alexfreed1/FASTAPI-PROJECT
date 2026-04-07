<?php
require 'config.php';

echo "<h2>Database Update Script</h2>";

// 1. Ensure students table exists
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Checked 'students' table.<br>";
} else {
    die("Error checking/creating table: " . $conn->error);
}

// 2. Check for admission_number column
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'admission_number'");
if ($result->num_rows == 0) {
    // Check for admission_no (common variation)
    $check_no = $conn->query("SHOW COLUMNS FROM students LIKE 'admission_no'");
    if ($check_no->num_rows > 0) {
        $conn->query("ALTER TABLE students CHANGE COLUMN admission_no admission_number VARCHAR(50) UNIQUE NOT NULL");
        echo "Updated 'students' table: Renamed 'admission_no' to 'admission_number'.<br>";
    } else {
        // Check if username exists (from previous version)
        $result = $conn->query("SHOW COLUMNS FROM students LIKE 'username'");
        if ($result->num_rows > 0) {
            // Rename username to admission_number
            $conn->query("ALTER TABLE students CHANGE COLUMN username admission_number VARCHAR(50) UNIQUE NOT NULL");
            echo "Updated 'students' table: Renamed 'username' to 'admission_number'.<br>";
        } else {
            // Add admission_number column
            // 1. Add column without constraints first to avoid duplicate entry error on existing rows
            $conn->query("ALTER TABLE students ADD COLUMN admission_number VARCHAR(50) AFTER id");
            // 2. Populate existing rows with unique values
            $conn->query("UPDATE students SET admission_number = CONCAT('ADM', id)");
            // 3. Apply constraints
            $conn->query("ALTER TABLE students MODIFY COLUMN admission_number VARCHAR(50) NOT NULL UNIQUE");
            echo "Updated 'students' table: Added 'admission_number' column.<br>";
        }
    }
} else {
    echo "'admission_number' column already exists.<br>";
    // Remove conflicting admission_no column if it exists
    $check_no = $conn->query("SHOW COLUMNS FROM students LIKE 'admission_no'");
    if ($check_no->num_rows > 0) {
        $conn->query("ALTER TABLE students DROP COLUMN admission_no");
        echo "Updated 'students' table: Dropped conflicting 'admission_no' column.<br>";
    }
}

// 3. Check for password column
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'password'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN password VARCHAR(255) NOT NULL");
    echo "Updated 'students' table: Added 'password' column.<br>";
}

// 4. Check for full_name column
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'full_name'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN full_name VARCHAR(100) NOT NULL");
    echo "Updated 'students' table: Added 'full_name' column.<br>";
}

// 5. Check for email column
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'email'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN email VARCHAR(100) NOT NULL");
    echo "Updated 'students' table: Added 'email' column.<br>";
}

// 6. Check for class_id column
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'class_id'");
if ($result->num_rows == 0) {
    // Ensure classes table exists (basic check)
    $conn->query("CREATE TABLE IF NOT EXISTS classes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, department_id INT)");
    
    $conn->query("ALTER TABLE students ADD COLUMN class_id INT");
    $conn->query("ALTER TABLE students ADD CONSTRAINT fk_student_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE");
    echo "Updated 'students' table: Added 'class_id' column and foreign key.<br>";
}

// 7. Check for attendance table and its columns
$result = $conn->query("SHOW TABLES LIKE 'attendance'");
if ($result->num_rows == 0) {
    $sql = "CREATE TABLE attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        unit_id INT NOT NULL,
        unit_code VARCHAR(50) NOT NULL,
        status ENUM('Present', 'Absent') DEFAULT 'Present',
        attendance_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )";
    if($conn->query($sql)) echo "Created 'attendance' table.<br>";
} else {
    $result = $conn->query("SHOW COLUMNS FROM attendance LIKE 'unit_code'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE attendance ADD COLUMN unit_code VARCHAR(50) NOT NULL");
        echo "Updated 'attendance' table: Added 'unit_code' column.<br>";
    }
    $result = $conn->query("SHOW COLUMNS FROM attendance LIKE 'status'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE attendance ADD COLUMN status ENUM('Present', 'Absent') DEFAULT 'Present'");
        echo "Updated 'attendance' table: Added 'status' column.<br>";
    }
    $result = $conn->query("SHOW COLUMNS FROM attendance LIKE 'attendance_date'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE attendance ADD COLUMN attendance_date DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "Updated 'attendance' table: Added 'attendance_date' column.<br>";
    }
    $result = $conn->query("SHOW COLUMNS FROM attendance LIKE 'unit_id'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE attendance ADD COLUMN unit_id INT NOT NULL AFTER student_id");
        echo "Updated 'attendance' table: Added 'unit_id' column.<br>";
    }
    $result = $conn->query("SHOW COLUMNS FROM attendance LIKE 'week'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE attendance ADD COLUMN week INT NOT NULL DEFAULT 1");
        echo "Updated 'attendance' table: Added 'week' column.<br>";
    }
    $result = $conn->query("SHOW COLUMNS FROM attendance LIKE 'lesson'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE attendance ADD COLUMN lesson VARCHAR(10) NOT NULL DEFAULT 'L1'");
        echo "Updated 'attendance' table: Added 'lesson' column.<br>";
    }
    $result = $conn->query("SHOW COLUMNS FROM attendance LIKE 'trainer_id'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE attendance ADD COLUMN trainer_id INT NOT NULL");
        echo "Updated 'attendance' table: Added 'trainer_id' column.<br>";
    }
}

// 8. Check for units table
$result = $conn->query("SHOW TABLES LIKE 'units'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL
    )");
    // Insert sample data
    $conn->query("INSERT INTO units (code, name) VALUES 
        ('COM 100', 'Communication Skills'),
        ('ICT 101', 'Introduction to ICT'),
        ('MATH 101', 'Engineering Mathematics')
    ");
    echo "Created 'units' table with samples.<br>";
} else {
    // If table exists but is empty, insert samples
    $check = $conn->query("SELECT COUNT(*) as count FROM units");
    if ($check && $check->fetch_assoc()['count'] == 0) {
        $conn->query("INSERT INTO units (code, name) VALUES 
            ('COM 100', 'Communication Skills'),
            ('ICT 101', 'Introduction to ICT'),
            ('MATH 101', 'Engineering Mathematics')
        ");
        echo "Populated 'units' table with samples.<br>";
    }
    // If table exists, ensure 'name' column exists
    $result = $conn->query("SHOW COLUMNS FROM units LIKE 'name'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE units ADD COLUMN name VARCHAR(100) NOT NULL");
        echo "Updated 'units' table: Added 'name' column.<br>";
    }

    // Check for title column (legacy) and migrate to name if needed
    $check_title = $conn->query("SHOW COLUMNS FROM units LIKE 'title'");
    if ($check_title->num_rows > 0) {
        $conn->query("UPDATE units SET name = title WHERE name = '' OR name IS NULL");
    }
}

// 9. Check for class_units table (Assignments)
$result = $conn->query("SHOW TABLES LIKE 'class_units'");
if ($result->num_rows == 0) {
    $sql = "CREATE TABLE class_units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        unit_id INT NOT NULL,
        trainer_id INT NOT NULL,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
        FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE
    )";
    if($conn->query($sql)) echo "Created 'class_units' table.<br>";
}

// 10. Check trainers password column length
$result = $conn->query("SHOW COLUMNS FROM trainers LIKE 'password'");
if ($result && $result->num_rows > 0) {
    // Ensure password column is long enough for hashes (255 is safe)
    $conn->query("ALTER TABLE trainers MODIFY COLUMN password VARCHAR(255) NOT NULL");
    echo "Checked/Updated 'trainers' table: password column length.<br>";
}

echo "<p><strong>Success!</strong> Database is up to date.</p>";
echo "<p><a href='student_dashboard.php'>Go to Student Dashboard</a> | <a href='student_register.php'>Go to Student Registration</a></p>";
?>