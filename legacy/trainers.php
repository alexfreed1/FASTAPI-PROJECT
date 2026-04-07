<?php
require '../config.php';
if(empty($_SESSION['admin'])){ header('Location: login.php'); exit; }

// Handle Add
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_trainer'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $dept_id = (int)$_POST['department_id'];
    
    if($name && $username && $password && $dept_id) {
        $conn->query("INSERT INTO trainers (name, username, password, department_id) VALUES ('$name', '$username', '$password', $dept_id)");
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM trainers WHERE id=$id");
    header("Location: trainers.php"); exit;
}

$departments = $conn->query("SELECT * FROM departments");
$trainers = $conn->query("SELECT t.*, d.name as dept_name FROM trainers t LEFT JOIN departments d ON t.department_id = d.id");
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Manage Trainers</title></head>
<body>
<h2>Manage Trainers</h2>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<h3>Add New Trainer</h3>
<form method="post">
    <label>Name: <input type="text" name="name" required></label><br><br>
    <label>Username: <input type="text" name="username" required></label><br><br>
    <label>Password: <input type="text" name="password" required></label><br><br>
    <label>Department: 
        <select name="department_id" required>
            <option value="">Select Department</option>
            <?php while($d = $departments->fetch_assoc()): ?>
                <option value="<?php echo $d['id']; ?>"><?php echo h($d['name']); ?></option>
            <?php endwhile; ?>
        </select>
    </label><br><br>
    <button type="submit" name="add_trainer">Add Trainer</button>
</form>

<h3>Existing Trainers</h3>
<table border="1" cellpadding="5">
    <tr><th>Name</th><th>Username</th><th>Department</th><th>Action</th></tr>
    <?php while($t = $trainers->fetch_assoc()): ?>
    <tr>
        <td><?php echo h($t['name']); ?></td>
        <td><?php echo h($t['username']); ?></td>
        <td><?php echo h($t['dept_name']); ?></td>
        <td><a href="?delete=<?php echo $t['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a></td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>