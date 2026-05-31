<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) exit("Unauthorized");

$user_id = $_SESSION['user_id'];

// DELETE EVERYTHING RELATED TO USER
$tables = [
    "tbl_alerts",
    "tbl_documents",
    "tbl_maintenance_tasks",
    "tbl_repair_logs",
    "tbl_inventory_items",
    "api_keys"
];

foreach ($tables as $tbl) {
    $pdo->prepare("DELETE FROM $tbl WHERE user_id=? OR item_id IN(SELECT item_id FROM tbl_inventory_items WHERE user_id=?)")
        ->execute([$user_id, $user_id]);
}

// Finally delete user
$pdo->prepare("DELETE FROM tbl_users WHERE user_id=?")->execute([$user_id]);

session_destroy();

header("Location: auth/login.php");
exit;
?>
