<!-- filepath: f:\wampp\www\phatdan2569\logout.php -->
<?php
session_start();
session_destroy();
header('Location: login.php');
exit;
?>