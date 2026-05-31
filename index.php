<?php
session_start();

// If already logged in, go straight to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Otherwise, go to login
header("Location: auth/login.php");
exit;
?>
