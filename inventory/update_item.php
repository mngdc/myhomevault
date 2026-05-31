<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");
$user_id = $_SESSION['user_id'];

$item_id = intval($_POST['item_id'] ?? 0);
if ($item_id <= 0) {
    header('Location: list.php');
    exit;
}

// Collect updated fields
$fields = [
    'item_name' => $_POST['name'] ?? '',
    'category' => $_POST['category'] ?? '',
    'description' => $_POST['description'] ?? null,
    'purchase_date' => $_POST['purchase_date'] ?: null,
    'warranty_expiration' => $_POST['warranty_expiration'] ?: null,
    'estimated_value' => $_POST['estimated_value'] ?: null,
    'serial_number' => $_POST['serial_number'] ?? '',
    'model_number' => $_POST['model_number'] ?? '',
    'location' => $_POST['location'] ?? '',
    'purchase_price' => $_POST['purchase_price'] ?: null
];

// Keep only provided fields
$fields = array_filter($fields, fn($v) => $v !== '' && $v !== null);

// Handle image/document uploads
$upload_dir = __DIR__ . '/../assets/uploads/' . $user_id . '/items/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

if (!empty($_FILES['image']['name'])) {
    $image_name = time() . '_' . basename($_FILES['image']['name']);
    $target = $upload_dir . $image_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $fields['image_path'] = 'assets/uploads/' . $user_id . '/items/' . $image_name;
    }
}

if (!empty($_FILES['document']['name'])) {
    $doc_name = time() . '_' . basename($_FILES['document']['name']);
    $target2 = $upload_dir . $doc_name;
    if (move_uploaded_file($_FILES['document']['tmp_name'], $target2)) {
        $document_path = 'assets/uploads/' . $user_id . '/items/' . $doc_name;
        $stmtDoc = $pdo->prepare("
            INSERT INTO tbl_documents (item_id, file_name, file_path)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE file_name = VALUES(file_name), file_path = VALUES(file_path)
        ");
        $stmtDoc->execute([$item_id, $doc_name, $document_path]);
    }
}

// Build SQL dynamically
if (!empty($fields)) {
    $setClause = implode(', ', array_map(fn($col) => "$col = ?", array_keys($fields)));
    $sql = "UPDATE tbl_inventory_items SET $setClause WHERE item_id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge(array_values($fields), [$item_id, $user_id]));
}

header("Location: view_item.php?id=$item_id");
exit;
?>
