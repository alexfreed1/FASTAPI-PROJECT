<?php
require '../config.php';
if(empty($_SESSION['admin'])){ header('Location: login.php'); exit; }

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$unit_id = isset($_GET['unit_id']) ? (int)$_GET['unit_id'] : 0;
$week = isset($_GET['week']) ? (int)$_GET['week'] : 0;
$lesson = isset($_GET['lesson']) ? $conn->real_escape_string($_GET['lesson']) : '';

if(!$class_id || !$unit_id || !$week || !$lesson){ echo 'Missing parameters.'; exit; }

$classR = $conn->query("SELECT * FROM classes WHERE id=$class_id");
$class = $classR->fetch_assoc();
$deptR = $conn->query("SELECT name FROM departments WHERE id=".$class['department_id']);
$dept = $deptR->fetch_assoc();
$unitR = $conn->query("SELECT * FROM units WHERE id=$unit_id");
$unit = $unitR->fetch_assoc();

// Fetch attendance
$sql = "SELECT a.*, s.admission_number, s.full_name 
        FROM attendance a 
        JOIN students s ON a.student_id = s.id 
        WHERE a.unit_id = $unit_id 
        AND a.week = $week 
        AND a.lesson = '$lesson' 
        AND s.class_id = $class_id
        ORDER BY s.admission_number";
$att = $conn->query($sql);

$attendance_records = [];
$attendanceDate = '-';
$trainer_name = '_______________';

if($att && $att->num_rows){
    while($r = $att->fetch_assoc()) $attendance_records[] = $r;
    if(!empty($attendance_records)) {
        $attendanceDate = $attendance_records[0]['attendance_date'];
        if(isset($attendance_records[0]['trainer_id'])){
            $tr = $conn->query("SELECT name FROM trainers WHERE id=".$attendance_records[0]['trainer_id']);
            if($tr && $tr->num_rows) $trainer_name = $tr->fetch_assoc()['name'];
        }
    }
}

$dateGen = date('d M Y, H:i');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Attendance PDF</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Arial', sans-serif; line-height: 1.6; }
    @media print {
      body { margin: 0; padding: 0; }
      .no-print { display: none; }
    }
    @page { size: A4; margin: 0.5in; }
    .print-container { max-width: 8.5in; margin: 0 auto; padding: 20px; }
    .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 15px; }
    .logo-img { height: 60px; margin-bottom: 10px; }
    .header h1 { font-size: 20px; margin-bottom: 5px; color: #333; }
    .header h2 { font-size: 14px; font-weight: bold; margin-bottom: 3px; color: #555; }
    .header h3 { font-size: 13px; color: #1e5a9f; margin-top: 8px; text-decoration: underline; }
    .info-bar { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; padding: 12px; background-color: #f3f4f6; border-radius: 6px; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    thead { background-color: #e9eef8; }
    th { padding: 10px; text-align: left; font-weight: bold; font-size: 12px; border: 1px solid #ccc; color: #333; }
    td { padding: 10px; font-size: 11px; border: 1px solid #ddd; }
    tbody tr:nth-child(even) { background-color: #f9f9f9; }
    .status-present { color: #28a745; font-weight: bold; }
    .status-absent { color: #e53935; font-weight: bold; }
    .no-records { text-align: center; padding: 20px; color: #999; }
    .buttons { margin-top: 20px; text-align: right; }
    .btn { padding: 10px 20px; margin-left: 10px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
    .btn-print { background-color: #1e5a9f; color: white; }
    .btn-back { background-color: #666; color: white; }
  </style>
</head>
<body>
  <div class="print-container">
    <div class="header">
      <img src="../assets/THIKATTILOGO.jpg" alt="Logo" class="logo-img">
      <h1>THIKA TECHNICAL TRAINING INSTITUTE</h1>
      <h2>Department of <?php echo h($dept['name']); ?></h2>
      <h3>UNIT ATTENDANCE REGISTER</h3>
    </div>

    <div class="info-bar">
      <div>
        <div><strong>CLASS:</strong> <?php echo h($class['name']); ?></div>
        <div><strong>WEEK:</strong> <?php echo h($week); ?></div>
        <div><strong>LESSON:</strong> <?php echo h($lesson); ?></div>
      </div>
      <div>
        <div><strong>UNIT:</strong> <?php echo h($unit['code'].' - '.$unit['name']); ?></div>
        <div><strong>DATE:</strong> <?php echo h($attendanceDate); ?></div>
        <div><strong>DATE GENERATED:</strong> <?php echo h($dateGen); ?></div>
      </div>
    </div> 

    <table>
      <thead>
        <tr>
          <th>ADM NO</th>
          <th>STUDENT NAME</th>
          <th>STATUS</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        if(!empty($attendance_records)){
          foreach($attendance_records as $r){
            $statusClass = $r['status'] == 'Present' ? 'status-present' : 'status-absent';
            $statusText = strtoupper($r['status']);
            echo '<tr>'; 
            echo '<td>'.h($r['admission_number']).'</td>';
            echo '<td>'.h($r['full_name']).'</td>';
            echo '<td class="'.$statusClass.'">'.$statusText.'</td>';
            echo '</tr>';
          }
        } else {
          echo '<tr><td colspan="3" class="no-records">No attendance records found</td></tr>';
        }
        ?>
      </tbody>
    </table>

    <div class="buttons no-print">
      <button class="btn btn-print" onclick="window.print()">Print / Save as PDF</button>
      <button class="btn btn-back" onclick="history.back()">Back</button>
    </div>
  </div> 
</body>
</html>