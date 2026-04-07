<?php
require 'config.php';
if(isset($_POST['username'])) {
    $u = $conn->real_escape_string(trim($_POST['username']));
    $p = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM trainers WHERE username='$u'");
    if($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify password (Plain text)
        if ($p === $row['password']) {
            $_SESSION['trainer'] = $row;
            // Requirement: Choose department before dashboard
            header("Location: select_department.php");
            exit;
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Lecturer Login</title></head>
<body>
<h2>Trainer Login</h2>
<?php if(isset($error)) echo '<p style="color:red">'.h($error).'</p>'; ?>
<form method="post">
    <label>Username: <input type="text" name="username" required></label><br><br>
    <label>Password: <input type="password" name="password" required></label><br><br>
    <button type="submit">Login</button>
</form>
<p><a href="index.php">Back to Home</a></p>
<hr>
<p><strong>Student?</strong> <a href="student_login.php">Login here</a> or <a href="student_register.php">Register</a></p>
<p><a href="forgot_password.php">Forgot Password?</a></p>
</body>
</html>