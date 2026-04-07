<?php
// Database Configuration
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'attendancesystem';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Database connection error: ' . $conn->connect_error);
}

session_start();

function h($s){ 
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
