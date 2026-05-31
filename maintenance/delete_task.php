<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$task_id = intval($_POST['task_id'] ?? 0);

if ($task_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tbl_maintenance_tasks WHERE task_id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        header("Location: list.php?deleted=1");
        exit;
    } catch (Exception $e) {
        header("Location: list.php?error=1");
        exit;
    }
} else {
    header("Location: list.php?error=invalid");
    exit;
}
?>
