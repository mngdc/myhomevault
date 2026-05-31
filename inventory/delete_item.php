<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = intval($_GET['id'] ?? 0);

if ($item_id <= 0) {
    header("Location: list.php");
    exit;
}

// 1️⃣ Verify item ownership
$stmt = $pdo->prepare("SELECT * FROM tbl_inventory_items WHERE item_id = ? AND user_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: list.php?error=item_not_found");
    exit;
}

// 2️⃣ Delete related documents from both DB and file system
$docs = $pdo->prepare("SELECT * FROM tbl_documents WHERE item_id = ?");
$docs->execute([$item_id]);
$documents = $docs->fetchAll(PDO::FETCH_ASSOC);

foreach ($documents as $doc) {
    $filePath = __DIR__ . '/../' . $doc['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath); // delete file safely
    }
}

// Delete document records
$pdo->prepare("DELETE FROM tbl_documents WHERE item_id = ?")->execute([$item_id]);

// 3️⃣ Delete the image file if it exists
if (!empty($item['image_path'])) {
    $imageFullPath = __DIR__ . '/../' . $item['image_path'];
    if (file_exists($imageFullPath)) {
        unlink($imageFullPath);
    }
}

// 4️⃣ Optionally remove related alerts and maintenance tasks (uncomment if desired)
// $pdo->prepare("DELETE FROM tbl_alerts WHERE item_id = ?")->execute([$item_id]);
// $pdo->prepare("DELETE FROM tbl_maintenance_tasks WHERE item_id = ?")->execute([$item_id]);

// 5️⃣ Finally, delete the inventory item
$delete = $pdo->prepare("DELETE FROM tbl_inventory_items WHERE item_id = ? AND user_id = ?");
$delete->execute([$item_id, $user_id]);

header("Location: list.php?deleted=1");
exit;
?>
