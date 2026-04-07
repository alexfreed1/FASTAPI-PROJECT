<?php
require '../config.php';
if(empty($_SESSION['admin'])){ header('Location: login.php'); exit; }
session_destroy();
header('Location: index.php');
exit;
?>
