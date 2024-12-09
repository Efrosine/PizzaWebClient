<?php
// logout.php - Halaman untuk logout dan hapus session

require_once('../includes/config.php');

// Hapus session
session_destroy();

// Redirect ke halaman login
header('Location: login.php');
exit();
?>