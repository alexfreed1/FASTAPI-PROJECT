<?php
require '../config.php';
if(isset($_POST['username'])) {
    $u = $conn->real_escape_string($_POST['username']);
    $p = $conn->real_escape_string($_POST['password']);
    
    $result = $conn->query("SELECT * FROM trainers WHERE username='$u' AND password='$p'");
    if($result && $result->num_rows > 0) {
        $_SESSION['trainer'] = $result->fetch_assoc();
        // Requirement: Choose department before dashboard
        header("Location: select_department.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Trainer Login</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #1e5a9f 0%, #2e75b6 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
    .login-box h1 { color: #1e5a9f; margin-bottom: 30px; text-align: center; }
    .error { color: #d32f2f; background-color: #ffebee; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
    .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
    .form-group input:focus { outline: none; border-color: #1e5a9f; box-shadow: 0 0 4px rgba(30, 90, 159, 0.2); }
    .form-group button { width: 100%; padding: 12px; background-color: #1e5a9f; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; }
    .form-group button:hover { background-color: #154070; }
    .footer { text-align: center; margin-top: 20px; }
    .footer a { color: #1e5a9f; text-decoration: none; }
    .footer a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="login-box">
    <div style="text-align:center; margin-bottom:20px;"><img src="../assets/THIKATTILOGO.jpg" alt="Logo" style="height:80px;"></div>
    <h1>Trainer Login</h1>
    <?php if(isset($error)) echo '<div class="error">'.h($error).'</div>'; ?>
    <form method="post">
      <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" required>
      </div>
      <div class="form-group">
        <label>Password:</label>
        <input type="password" name="password" required>
      </div>
      <div class="form-group">
        <button type="submit">Login</button>
      </div>
    </form>
    <div class="footer">
      <a href="../index.php">Back to Home</a>
    </div>
  </div>
</body>
</html>
