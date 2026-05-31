<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");

$item_id = intval($_POST['item_id']);
$issue = $_POST['issue_description'];
$repair_date = $_POST['repair_date'];
$cost = $_POST['cost'] ?: null;
$notes = $_POST['technician_notes'] ?? '';

$pdo->prepare("
    INSERT INTO tbl_repair_logs (item_id, issue_description, repair_date, cost, technician_notes)
    VALUES (?, ?, ?, ?, ?)
")->execute([$item_id, $issue, $repair_date, $cost, $notes]);

header("Location: ../inventory/view_item.php?id=$item_id");
exit;
?>
