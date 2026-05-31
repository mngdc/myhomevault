<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$item_name          = trim($_POST['name'] ?? '');
$category           = trim($_POST['category'] ?? '');
$description        = trim($_POST['description'] ?? '');
$purchase_date      = $_POST['purchase_date'] ?: null;
$purchase_price     = $_POST['purchase_price'] ?: null;
$serial_number      = trim($_POST['serial_number'] ?? '');
$model_number       = trim($_POST['model_number'] ?? '');
$warranty_exp       = $_POST['warranty_expiration'] ?: null;
$estimated_value    = $_POST['estimated_value'] ?: null;
$location           = trim($_POST['location'] ?? '');

$upload_dir = __DIR__ . '/../assets/uploads/' . $user_id . '/items/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$image_path = null;

if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $filename = time().'_'.basename($_FILES['image']['name']);
    $target = $upload_dir.$filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $image_path = "assets/uploads/{$user_id}/items/{$filename}";
    }
}

$stmt = $pdo->prepare("
    INSERT INTO tbl_inventory_items
    (user_id, item_name, category, description, purchase_date, purchase_price,
     serial_number, model_number, warranty_expiration, estimated_value, location, image_path)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $user_id, $item_name, $category, $description, $purchase_date, $purchase_price,
    $serial_number, $model_number, $warranty_exp, $estimated_value, $location, $image_path
]);

$item_id = $pdo->lastInsertId();

if (!empty($_FILES['document']['name']) && is_uploaded_file($_FILES['document']['tmp_name'])) {
    $docname = time().'_'.basename($_FILES['document']['name']);
    $target = $upload_dir.$docname;
    if (move_uploaded_file($_FILES['document']['tmp_name'], $target)) {
        $path = "assets/uploads/{$user_id}/items/{$docname}";
        $pdo->prepare("INSERT INTO tbl_documents (item_id, file_name, file_path) VALUES (?, ?, ?)")
            ->execute([$item_id, $docname, $path]);
    }
}

/* ----------------------------------------------
   CLEAR AI SESSION DATA AFTER SAVING SUCCESSFULLY
---------------------------------------------- */
unset(
    $_SESSION['ai_item'],
    $_SESSION['ai_issue'],
    $_SESSION['ai_result'],
    $_SESSION['ai_mode'],
    $_SESSION['ai_last_formdata']
);

header("Location: list.php");
exit;
?>
