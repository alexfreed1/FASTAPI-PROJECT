<?php
require '../config.php';
if(empty($_SESSION['admin'])){ header('Location: login.php'); exit; }

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$unit_id = isset($_GET['unit_id']) ? (int)$_GET['unit_id'] : 0;
$week = isset($_GET['week']) ? (int)$_GET['week'] : 0;
$lesson = isset($_GET['lesson']) ? $conn->real_escape_string($_GET['lesson']) : '';

$classes = $conn->query("SELECT * FROM classes ORDER BY name");
$units = $conn->query("SELECT * FROM units ORDER BY code");

$attendance = [];
if($class_id && $unit_id && $week && $lesson){
    $sql = "SELECT a.*, s.admission_number, s.full_name 
            FROM attendance a 
            JOIN students s ON a.student_id = s.id 
            WHERE a.unit_id = $unit_id 
            AND a.week = $week 
            AND a.lesson = '$lesson' 
            AND s.class_id = $class_id
            ORDER BY s.admission_number";
    $res = $conn->query($sql);
    if($res){
        while($row = $res->fetch_assoc()){
            $attendance[] = $row;
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Attendance</title>
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
    .present { color: #28a745; font-weight: bold; }
    .absent { color: #dc3545; font-weight: bold; }
    .btn-download { display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; margin-bottom: 15px; }
    .btn-download:hover { background-color: #218838; }
  </style>
</head>
<body>
<div class="header">
  <h2><i class="fas fa-clipboard-list"></i> View Attendance</h2>
  <a href="welcome.php">Back to Dashboard</a>
</div>

<div class="container">
    <div class="form-section">
        <h3>Filter Attendance</h3>
        <form method="get">
            <label>Class:</label>
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php while($c = $classes->fetch_assoc()) echo "<option value='{$c['id']}' ".($class_id==$c['id']?'selected':'').">".h($c['name'])."</option>"; ?>
            </select>
            
            <label>Unit:</label>
            <select name="unit_id" required>
                <option value="">Select Unit</option>
                <?php while($u = $units->fetch_assoc()) echo "<option value='{$u['id']}' ".($unit_id==$u['unit_id']?'selected':'').">".h($u['code'].' - '.$u['name'])."</option>"; ?>
            </select>
            
            <label>Week:</label>
            <select name="week" required>
                <option value="">Select Week</option>
                <?php for($i=1; $i<=52; $i++) echo "<option value='$i' ".($week==$i?'selected':'').">Week $i</option>"; ?>
            </select>
            
            <label>Lesson:</label>
            <select name="lesson" required>
                <option value="">Select Lesson</option>
                <option value="L1" <?php echo $lesson=='L1'?'selected':''; ?>>L1</option>
                <option value="L2" <?php echo $lesson=='L2'?'selected':''; ?>>L2</option>
                <option value="L3" <?php echo $lesson=='L3'?'selected':''; ?>>L3</option>
                <option value="L4" <?php echo $lesson=='L4'?'selected':''; ?>>L4</option>
            </select>
            
            <button type="submit">View Records</button>
        </form>
    </div>

    <?php if($class_id && $unit_id): ?>
    <h3>Attendance Records</h3>
    <a href="download_attendance_pdf.php?class_id=<?php echo $class_id; ?>&unit_id=<?php echo $unit_id; ?>&week=<?php echo $week; ?>&lesson=<?php echo urlencode($lesson); ?>" class="btn-download" target="_blank">Download PDF</a>
    <?php if(empty($attendance)): ?>
        <p>No records found for this selection.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Admission No</th><th>Name</th><th>Status</th><th>Date Recorded</th></tr>
            </thead>
            <tbody>
                <?php foreach($attendance as $r): ?>
                <tr>
                    <td><?php echo h($r['admission_number']); ?></td>
                    <td><?php echo h($r['full_name']); ?></td>
                    <td><?php echo ($r['status']=='Present' ? '<span class="present">Present</span>' : '<span class="absent">Absent</span>'); ?></td>
                    <td><?php echo h($r['attendance_date']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>