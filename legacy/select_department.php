<?php
require '../config.php';
if(empty($_SESSION['trainer'])){ header('Location: login.php'); exit; }
// trainer must choose department first
$depts = $conn->query('SELECT * FROM departments');
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['department_id'])){
  $_SESSION['selected_department'] = (int)$_POST['department_id'];
  header('Location: dashboard.php'); exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Choose Department</title></head><body>
<h2>Choose Department (required before dashboard)</h2>
<form method="post">
  <label>Department <select name="department_id"><?php while($d=$depts->fetch_assoc()) echo "<option value=\"{$d['id']}\">".h($d['name'])."</option>"; ?></select></label>
  <button>Proceed</button>
</form>
<p><a href="logout.php">Logout</a></p>
</body></html>
