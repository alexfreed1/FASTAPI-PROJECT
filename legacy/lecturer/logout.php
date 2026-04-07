<?php
require '../config.php';
unset($_SESSION['trainer']); 
unset($_SESSION['selected_department']);
session_destroy();
header('Location: login.php'); 
exit;
?>
