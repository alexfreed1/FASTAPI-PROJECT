<?php
require '../config.php';
if(empty($_SESSION['admin'])){ header('Location: login.php'); exit; }

// Handle Add Assignment
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $class_id = (int)$_POST['class_id'];
    $unit_id = (int)$_POST['unit_id'];
    $trainer_id = (int)$_POST['trainer_id'];

    if($class_id && $unit_id && $trainer_id) {
        // Check if already assigned
        $chk = $conn->query("SELECT id FROM class_units WHERE class_id=$class_id AND unit_id=$unit_id AND trainer_id=$trainer_id");
        if($chk->num_rows > 0){
            $error = "This assignment already exists.";
        } else {
            $conn->query("INSERT INTO class_units (class_id, unit_id, trainer_id) VALUES ($class_id, $unit_id, $trainer_id)");
        }
    } else {
        $error = "Please select Class, Unit, and Trainer.";
    }
}

// Handle CSV Import
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
    if($_FILES['csv_file']['error'] == 0){
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Format: Class Name, Unit Code, Trainer Username
            if(count($data) >= 3) {
                $className = $conn->real_escape_string(trim($data[0]));
                $unitCode = $conn->real_escape_string(trim($data[1]));
                $trainerUser = $conn->real_escape_string(trim($data[2]));

                $cRes = $conn->query("SELECT id FROM classes WHERE name='$className'");
                $uRes = $conn->query("SELECT id FROM units WHERE code='$unitCode'");
                $tRes = $conn->query("SELECT id FROM trainers WHERE username='$trainerUser'");

                if($cRes && $cRes->num_rows > 0 && $uRes && $uRes->num_rows > 0 && $tRes && $tRes->num_rows > 0){
                    $cid = $cRes->fetch_assoc()['id'];
                    $uid = $uRes->fetch_assoc()['id'];
                    $tid = $tRes->fetch_assoc()['id'];
                    
                    $chk = $conn->query("SELECT id FROM class_units WHERE class_id=$cid AND unit_id=$uid AND trainer_id=$tid");
                    if($chk->num_rows == 0){
                        $conn->query("INSERT INTO class_units (class_id, unit_id, trainer_id) VALUES ($cid, $uid, $tid)");
                        $count++;
                    }
                }
            }
        }
        fclose($handle);
        $success = "Imported $count assignments successfully.";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM class_units WHERE id=$id");
    header("Location: assign_units.php"); exit;
}

// Fetch Data for Dropdowns
$classes_res = $conn->query("SELECT * FROM classes ORDER BY name");
$classes = []; while($row = $classes_res->fetch_assoc()) $classes[] = $row;

$units_res = $conn->query("SELECT * FROM units ORDER BY code");
$units = []; while($row = $units_res->fetch_assoc()) $units[] = $row;

$trainers_res = $conn->query("SELECT * FROM trainers ORDER BY name");
$trainers = []; while($row = $trainers_res->fetch_assoc()) $trainers[] = $row;

// Filter Logic
$filter_class = isset($_GET['filter_class']) ? (int)$_GET['filter_class'] : 0;
$filter_trainer = isset($_GET['filter_trainer']) ? (int)$_GET['filter_trainer'] : 0;
$where = [];
if($filter_class) $where[] = "cu.class_id = $filter_class";
if($filter_trainer) $where[] = "cu.trainer_id = $filter_trainer";
$where_sql = count($where) ? "WHERE ".implode(' AND ', $where) : "";

// Fetch Existing Assignments
$assignments = $conn->query("SELECT cu.id, c.name as class_name, u.code, u.name as unit_name, t.name as trainer_name 
                             FROM class_units cu 
                             JOIN classes c ON cu.class_id = c.id 
                             JOIN units u ON cu.unit_id = u.id 
                             JOIN trainers t ON cu.trainer_id = t.id 
                             $where_sql
                             ORDER BY c.name, u.code");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Assign Units</title>
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
    select { width: 100%; max-width: 400px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; display: block; }
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
  <h2><i class="fas fa-book-open"></i> Assign Units to Classes & Trainers</h2>
  <a href="welcome.php">Back to Dashboard</a>
</div>

<div class="container">
    <?php if(isset($error)) echo '<div class="error">'.h($error).'</div>'; ?>
    <?php if(isset($success)) echo '<div class="success">'.h($success).'</div>'; ?>

    <div class="form-section">
        <h3>New Assignment</h3>
        <form method="post">
            <label>Class:</label>
            <select name="class_id" required><option value="">Select Class</option><?php foreach($classes as $c) echo "<option value='{$c['id']}'>".h($c['name'])."</option>"; ?></select>
            <label>Unit:</label>
            <select name="unit_id" required><option value="">Select Unit</option><?php foreach($units as $u) echo "<option value='{$u['id']}'>".h($u['code']." - ".$u['name'])."</option>"; ?></select>
            <label>Trainer:</label>
            <select name="trainer_id" required><option value="">Select Trainer</option><?php foreach($trainers as $t) echo "<option value='{$t['id']}'>".h($t['name'])."</option>"; ?></select>
            <button type="submit" name="assign">Assign Unit</button>
        </form>
    </div>

    <div class="form-section">
        <h3>Import Assignments (CSV)</h3>
        <form method="post" enctype="multipart/form-data">
            <p style="margin-bottom: 10px; color: #666;"><strong>Format:</strong> Class Name, Unit Code, Trainer Username</p>
            <input type="file" name="csv_file" required accept=".csv">
            <button type="submit" name="import_csv">Import CSV</button>
        </form>
    </div>

    <div class="form-section" style="background-color: #e3f2fd;">
        <h3>Filter Assignments</h3>
        <form method="get" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex-grow: 1; max-width: 300px;">
                <label>By Class:</label>
                <select name="filter_class" style="margin-bottom: 0;"><option value="">All Classes</option><?php foreach($classes as $c) echo "<option value='{$c['id']}' ".($filter_class==$c['id']?'selected':'').">".h($c['name'])."</option>"; ?></select>
            </div>
            <div style="flex-grow: 1; max-width: 300px;">
                <label>By Trainer:</label>
                <select name="filter_trainer" style="margin-bottom: 0;"><option value="">All Trainers</option><?php foreach($trainers as $t) echo "<option value='{$t['id']}' ".($filter_trainer==$t['id']?'selected':'').">".h($t['name'])."</option>"; ?></select>
            </div>
            <button type="submit" style="height: 42px;">Filter</button>
        </form>
    </div>

    <h3>Current Assignments</h3>
    <table>
        <thead><tr><th>Class</th><th>Unit</th><th>Trainer</th><th>Action</th></tr></thead>
        <tbody>
            <?php while($row = $assignments->fetch_assoc()): ?>
            <tr><td><?php echo h($row['class_name']); ?></td><td><?php echo h($row['code']." - ".$row['unit_name']); ?></td><td><?php echo h($row['trainer_name']); ?></td><td><a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Remove this assignment?');" style="color: #d32f2f;">Remove</a></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>