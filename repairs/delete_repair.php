<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$repair_id = intval($_GET['repair_id'] ?? 0);
$item_id = intval($_GET['item_id'] ?? 0);

if ($repair_id <= 0 || $item_id <= 0) {
    header("Location: ../inventory/view_item.php?id={$item_id}");
    exit;
}

// Ensure this repair belongs to a user's item
$stmt = $pdo->prepare("
    SELECT r.repair_id 
    FROM tbl_repair_logs r
    JOIN tbl_inventory_items i ON r.item_id = i.item_id
    WHERE r.repair_id = ? AND i.user_id = ?
");
$stmt->execute([$repair_id, $user_id]);
$repair = $stmt->fetch();

if ($repair) {
    $pdo->prepare("DELETE FROM tbl_repair_logs WHERE repair_id = ?")->execute([$repair_id]);
}

// Redirect back to the item view
header("Location: ../inventory/view_item.php?id={$item_id}");
exit;
?>
