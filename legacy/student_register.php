<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admission_number = trim($_POST['admission_number']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;

    if ($admission_number && $password && $fullname && $email && $class_id) {
        // Check if admission number already exists
        $stmt = $conn->prepare("SELECT id, email FROM students WHERE admission_number = ?");
        $stmt->bind_param("s", $admission_number);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows == 0) {
            $error = "Admission Number not found in the system. Only students added by the Admin can register.";
        } else {
            $row = $res->fetch_assoc();
            if (!empty($row['email'])) {
                $error = "Account already registered. Please login.";
            } else {
                // Update existing record
                $stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, password = ?, class_id = ? WHERE id = ?");
                $stmt->bind_param("sssii", $fullname, $email, $password, $class_id, $row['id']);
                
                if ($stmt->execute()) {
                    header("Location: student_login.php?registered=1");
                    exit;
                } else {
                    $error = "Registration failed: " . $conn->error;
                }
            }
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch classes for dropdown
$classes = $conn->query("SELECT id, name FROM classes ORDER BY name");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Student Registration</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #1e5a9f 0%, #2e75b6 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .register-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
    .register-box h2 { color: #1e5a9f; margin-bottom: 30px; text-align: center; }
    .error { color: #d32f2f; background-color: #ffebee; padding: 12px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
    .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
    .form-group input:focus { outline: none; border-color: #1e5a9f; box-shadow: 0 0 4px rgba(30, 90, 159, 0.2); }
    .form-group button { width: 100%; padding: 12px; background-color: #ff9800; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
    .form-group button:hover { background-color: #f57c00; }
    .footer { text-align: center; margin-top: 20px; font-size: 14px; }
    .footer a { color: #1e5a9f; text-decoration: none; margin: 0 5px; }
    .footer a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="register-box">
    <div style="text-align:center; margin-bottom:20px;"><img src="assets/THIKATTILOGO.jpg" alt="Logo" style="height:80px;"></div>
    <h2>Student Registration</h2>
    <?php if(isset($error)) echo '<div class="error">'.htmlspecialchars($error).'</div>'; ?>
    <form method="post">
        <div class="form-group"><label>Full Name</label><input type="text" name="fullname" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Admission Number</label><input type="text" name="admission_number" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <div class="form-group">
            <label>Class</label>
            <select name="class_id" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                <option value="">Select Class</option>
                <?php if($classes) while($c = $classes->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <button type="submit">Register</button>
        </div>
    </form>
    <div class="footer">
        <p>Already have an account? <a href="student_login.php">Login here</a></p>
        <p style="margin-top:10px;"><a href="index.php">Back to Home</a></p>
    </div>
  </div>
</body>
</html>