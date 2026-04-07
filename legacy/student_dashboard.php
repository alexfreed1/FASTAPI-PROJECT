<?php
require 'config.php';

// Prevent caching to ensure real-time updates
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['student'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student']['id'];
$student_name = $_SESSION['student']['full_name'];
$admission_number = isset($_SESSION['student']['admission_number']) ? $_SESSION['student']['admission_number'] : '';
$class_id = isset($_SESSION['student']['class_id']) ? $_SESSION['student']['class_id'] : 0;

// Refresh class_id from DB to ensure it's up to date
$stmt_class = $conn->prepare("SELECT class_id FROM students WHERE id = ?");
$stmt_class->bind_param("i", $student_id);
$stmt_class->execute();
$res_class = $stmt_class->get_result();
if($res_class->num_rows > 0) {
    $class_id = $res_class->fetch_assoc()['class_id'];
}

// Calculate attendance per unit
$query = "SELECT u.code as unit_code,
                 u.name as unit_name,
                 COUNT(a.id) as total_records,
                 SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as attended,
                 MAX(a.attendance_date) as last_update
          FROM class_units cu
          JOIN units u ON cu.unit_id = u.id
          LEFT JOIN attendance a ON a.unit_id = u.id AND a.student_id = ?
          WHERE cu.class_id = ?
          GROUP BY u.id, u.code, u.name";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $student_id, $class_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data for summary and display
$attendance_data = [];
$total_attended_sum = 0;
$total_records_sum = 0;

while($row = $result->fetch_assoc()){
    $attendance_data[] = $row;
    $total_attended_sum += $row['attended'];
    $total_records_sum += $row['total_records'];
}
$overall_percentage = ($total_records_sum > 0) ? round(($total_attended_sum / $total_records_sum) * 100, 1) : 0;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; color: #444; }
        .navbar { background: linear-gradient(135deg, #1e5a9f 0%, #2e75b6 100%); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar a { color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; background: rgba(255,255,255,0.2); transition: background 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        /* Summary Cards */
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-right: 20px; color: white; }
        .bg-blue { background: linear-gradient(135deg, #3498db, #2980b9); }
        .bg-green { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .bg-purple { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
        .stat-info h3 { margin: 0; font-size: 14px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        .stat-info p { margin: 5px 0 0; font-size: 28px; font-weight: 700; color: #333; }
        
        /* Dashboard Card */
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 0; overflow: hidden; margin-bottom: 30px; }
        .card-header { padding: 25px 30px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .card-header h2 { color: #333; font-size: 20px; margin: 0; font-weight: 700; }
        .card-header .badge { background: #e3f2fd; color: #1e5a9f; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        
        /* Table Styling */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f8f9fa; color: #666; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; padding: 18px 25px; text-align: left; border-bottom: 2px solid #eee; }
        td { padding: 20px 25px; border-bottom: 1px solid #f0f0f0; color: #555; vertical-align: middle; font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background-color: #fcfcfc; }
        
        .unit-code { font-weight: 700; color: #1e5a9f; background: #eef4fb; padding: 5px 10px; border-radius: 6px; font-size: 13px; display: inline-block; }
        .unit-name { font-weight: 500; color: #333; }
        
        /* Progress Bar */
        .progress-container { display: flex; align-items: center; gap: 15px; width: 100%; max-width: 200px; }
        .progress-bg { flex: 1; height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }
        .progress-text { font-weight: 700; font-size: 13px; min-width: 40px; text-align: right; }
        
        .status-high { background: linear-gradient(90deg, #2ecc71, #27ae60); color: #27ae60; }
        .status-med { background: linear-gradient(90deg, #f1c40f, #f39c12); color: #f39c12; }
        .status-low { background: linear-gradient(90deg, #e74c3c, #c0392b); color: #c0392b; }
        
        .text-high { color: #27ae60; }
        .text-med { color: #f39c12; }
        .text-low { color: #c0392b; }
        
        .last-update { font-size: 12px; color: #888; display: flex; align-items: center; gap: 5px; }
        
        .empty-state { text-align: center; padding: 40px; color: #777; }
        .empty-state i { font-size: 48px; color: #ddd; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="navbar">
    <div style="display: flex; align-items: center;">
        <img src="assets/THIKATTILOGO.jpg" alt="Logo" style="height: 50px; margin-right: 15px; border-radius: 50%; border: 2px solid white;">
        <div>
            <h1 style="margin: 0; font-size: 18px; line-height: 1.2; font-weight: 600;">THIKA TECHNICAL TRAINING INSTITUTE</h1>
            <p style="margin: 0; font-size: 14px; opacity: 0.9;">Student Portal</p>
        </div>
    </div>
    <div>
        <span style="margin-right: 15px;">Welcome, <?php echo htmlspecialchars($student_name); ?> (<?php echo htmlspecialchars($admission_number); ?>)</span>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <!-- Summary Stats -->
    <div class="summary-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fas fa-book"></i></div>
            <div class="stat-info">
                <h3>Total Units</h3>
                <p><?php echo count($attendance_data); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3>Classes Attended</h3>
                <p><?php echo $total_attended_sum; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-purple"><i class="fas fa-chart-pie"></i></div>
            <div class="stat-info">
                <h3>Avg. Attendance</h3>
                <p><?php echo $overall_percentage; ?>%</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Attendance Dashboard</h2>
            <span class="badge"><i class="fas fa-calendar-alt"></i> <?php echo date('F Y'); ?></span>
        </div>

        <?php if (count($attendance_data) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Unit Code</th>
                            <th>Unit Name</th>
                            <th style="text-align: center;">Classes Attended</th>
                            <th style="text-align: center;">Total Recorded</th>
                            <th>Attendance %</th>
                            <th>Last Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($attendance_data as $row): 
                            $percentage = ($row['total_records'] > 0) ? round(($row['attended'] / $row['total_records']) * 100, 1) : 0;
                            $statusClass = ($percentage < 75) ? 'status-low' : (($percentage < 90) ? 'status-med' : 'status-high');
                            $textClass = ($percentage < 75) ? 'text-low' : (($percentage < 90) ? 'text-med' : 'text-high');
                        ?>
                        <tr>
                            <td><span class="unit-code"><?php echo htmlspecialchars($row['unit_code']); ?></span></td>
                            <td class="unit-name"><?php echo htmlspecialchars($row['unit_name']); ?></td>
                            <td style="text-align: center; font-weight: bold;"><?php echo $row['attended']; ?></td>
                            <td style="text-align: center; color: #888;"><?php echo $row['total_records']; ?></td>
                            <td>
                                <div class="progress-container">
                                    <div class="progress-bg"><div class="progress-fill <?php echo $statusClass; ?>" style="width: <?php echo $percentage; ?>%"></div></div>
                                    <span class="progress-text <?php echo $textClass; ?>"><?php echo $percentage; ?>%</span>
                                </div>
                            </td>
                            <td><div class="last-update"><i class="far fa-clock"></i> <?php echo $row['last_update'] ? date('d M, H:i', strtotime($row['last_update'])) : '-'; ?></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>No attendance records found for your account yet.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="index.php" style="color: #1e5a9f; text-decoration: none;">&larr; Back to Home</a>
    </div>
</div>

</body>
</html>