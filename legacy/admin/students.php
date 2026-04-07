<?php
require '../config.php';
if(empty($_SESSION['admin'])){ header('Location: login.php'); exit; }

// Handle Add
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $adm = $conn->real_escape_string($_POST['admission_number']);
    $class_id = (int)$_POST['class_id'];
    
    if($name && $adm && $class_id) {
        // Check duplicate
        $chk = $conn->query("SELECT id FROM students WHERE admission_number='$adm'");
        if($chk->num_rows > 0){
            $error = "Admission number already exists.";
        } else {
            // Default password for admin-created students (e.g., '123456')
            $def_pass = '123456';
            $conn->query("INSERT INTO students (full_name, admission_number, class_id, password) VALUES ('$name', '$adm', $class_id, '$def_pass')");
        }
    }
}

// Handle CSV Import
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
    if($_FILES['csv_file']['error'] == 0){
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Format: Name, Admission Number, Class ID
            if(count($data) >= 3) {
                $name = $conn->real_escape_string(trim($data[0]));
                $adm = $conn->real_escape_string(trim($data[1]));
                $class_ref = $conn->real_escape_string(trim($data[2]));
                
                $class_id = 0;
                // 1. Check if input is a valid numeric Class ID
                if(is_numeric($class_ref)){
                    $cr = $conn->query("SELECT id FROM classes WHERE id=".(int)$class_ref);
                    if($cr->num_rows > 0) $class_id = (int)$class_ref;
                }
                // 2. If not a valid ID, check if it is a Class Name
                if($class_id == 0){
                    $cr = $conn->query("SELECT id FROM classes WHERE name='$class_ref'");
                    if($cr->num_rows > 0) $class_id = $cr->fetch_assoc()['id'];
                }
                
                if($class_id > 0){
                    $chk = $conn->query("SELECT id FROM students WHERE admission_number='$adm'");
                    if($chk->num_rows == 0){
                        $def_pass = '123456';
                        $conn->query("INSERT INTO students (full_name, admission_number, class_id, password) VALUES ('$name', '$adm', $class_id, '$def_pass')");
                        $count++;
                    }
                }
            }
        }
        fclose($handle);
        $success = "Imported $count students successfully.";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM students WHERE id=$id");
    header("Location: students.php"); exit;
}

// Fetch classes for dropdowns
$classes_res = $conn->query("SELECT * FROM classes ORDER BY name"); 
$classes_list = [];


while($c = $classes_res->fetch_assoc()){
    $classes_list[] = $c;
}

// Filter Logic
$filter_class = isset($_GET['filter_class']) ? (int)$_GET['filter_class'] : 0;
$where_clause = $filter_class ? "WHERE s.class_id = $filter_class" : "";
$filter_adm = isset($_GET['filter_adm']) ? $conn->real_escape_string($_GET['filter_adm']) : '';
$where_clauses = [];
if($filter_class) $where_clauses[] = "s.class_id = $filter_class";
if($filter_adm) $where_clauses[] = "s.admission_number LIKE '%$filter_adm%'";
$where_clause = count($where_clauses) ? "WHERE ".implode(' AND ', $where_clauses) : "";
$students = $conn->query("SELECT s.*, c.name as class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id $where_clause ORDER BY s.admission_number"); 
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Students</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
    .header { background: white; padding: 20px; border-bottom: 3px solid #1e5a9f; display: flex; justify-content: space-between; align-items: center; }
    .header h2 { margin: 0; color: #1e5a9f; }
    .header a { color: #1e5a9f; text-decoration: none; font-weight: bold; }
    .container { max-width: 1200px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    h3 { color: #1e5a9f; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .form-section { background-color: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
    label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
    input[type="text"], input[type="file"], select { width: 100%; max-width: 400px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; display: block; }
    button { padding: 10px 20px; background-color: #1e5a9f; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    button:hover { background-color: #154070; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table th { background-color: #1e5a9f; color: white; padding: 12px; text-align: left; }
    table td { padding: 12px; border-bottom: 1px solid #ddd; }
    table tr:hover { background-color: #f9f9f9; }
    .error { color: #d32f2f; background-color: #ffebee; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    .success { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
  </style>
</head>
<body>
<div class="header">
  <h2><i class="fas fa-user-graduate"></i> Manage Students</h2>
  <a href="welcome.php">Back to Dashboard</a>
</div>

<div class="container">
    <?php if(isset($error)) echo '<div class="error">'.h($error).'</div>'; ?>
    <?php if(isset($success)) echo '<div class="success">'.h($success).'</div>'; ?>

    <div class="form-section">
        <h3>Add New Student</h3>
        <form method="post">
            <label>Full Name:</label> <input type="text" name="name" required>
            <label>Admission Number:</label> <input type="text" name="admission_number" required>
            <label>Class:</label> 
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php foreach($classes_list as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo h($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_student">Add Student</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Import Students (CSV)</h3>
        <form method="post" enctype="multipart/form-data">
            <p style="margin-bottom: 10px; color: #666;"><strong>Format:</strong> Full Name, Admission Number, Class ID</p>
            <input type="file" name="csv_file" required accept=".csv">
            <button type="submit" name="import_csv">Import CSV</button>
        </form>
    </div>

    <div class="form-section" style="background-color: #e3f2fd;">
        <h3>Filter Students</h3>
        <form method="get" style="display: flex; align-items: flex-start; gap: 15px; flex-wrap: wrap;">
            <div style="flex-grow: 1; max-width: 300px;">
                <label style="display: block; margin-bottom: 5px;">By Class:</label>
                <select name="filter_class"  style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    <option value="">All Classes</option>
                    <?php foreach($classes_list as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($filter_class == $c['id']) ? 'selected' : ''; ?>><?php echo h($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex-grow: 1; max-width: 300px;">
                <label style="display: block; margin-bottom: 5px;">By Admission No:</label>
                <input type="text" name="filter_adm" value="<?php echo h($filter_adm); ?>" style="width: 100%; padding: 8px; margin-bottom: 10px;">
            </div>
            <button type="submit" style="padding: 8px 15px; margin-top: 20px;">Filter</button>
        </form>

    </div>

    <h3>Existing Students</h3>
    <table>
        <thead>
            <tr><th>Admission No</th><th>Name</th><th>Class</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while($s = $students->fetch_assoc()): ?>
            <tr>
                <td><?php echo h($s['admission_number']); ?></td>
                <td><?php echo h($s['full_name']); ?></td>
                <td><?php echo h($s['class_name']); ?></td>
                <td><a href="?delete=<?php echo $s['id']; ?>" onclick="return confirm('Are you sure?');" style="color: #d32f2f;">Delete</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>