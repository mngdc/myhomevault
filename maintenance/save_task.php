<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/frequency_parser.php'; // <--- ADD THIS

if (!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");
$user_id = $_SESSION['user_id'];

$item_name_free = trim($_POST['item_name_free'] ?? '');
$description = trim($_POST['description'] ?? '');
$due_date = $_POST['due_date'] ?: null;

// USE CUSTOM FREQUENCY IF TYPED
$freq_custom = trim($_POST['frequency_custom'] ?? '');
$frequency = $freq_custom !== '' ? $freq_custom : ($_POST['frequency_select'] ?? 'none');

// 1) match item by name
$item_id = null;
if ($item_name_free !== '') {
    $stmt = $pdo->prepare("SELECT item_id FROM tbl_inventory_items WHERE user_id = ? AND item_name = ? LIMIT 1");
    $stmt->execute([$user_id, $item_name_free]);
    $found = $stmt->fetchColumn();
    if ($found) $item_id = $found;
}

// 2) If custom frequency → calculate next due
if ($freq_custom !== '') {
    $parsed = parse_custom_frequency($freq_custom);
    if ($parsed !== null) {
        $due_date = $parsed;
        $frequency = $freq_custom; // keep exact text
    }
}

// 3) Save task
$stmt = $pdo->prepare("
    INSERT INTO tbl_maintenance_tasks (user_id, item_id, task_description, due_date, frequency, status)
    VALUES (?, ?, ?, ?, ?, 'Pending')
");
$stmt->execute([$user_id, $item_id, $description, $due_date, $frequency]);

header("Location: list.php");
exit;
?>
