<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$alert_id = intval($_GET['id'] ?? 0);

if ($alert_id > 0) {
    $stmt = $pdo->prepare("UPDATE tbl_alerts SET is_read = 1 WHERE alert_id = ? AND user_id = ?");
    $stmt->execute([$alert_id, $user_id]);
}

header("Location: list.php");
exit;
?>
