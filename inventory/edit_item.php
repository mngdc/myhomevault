<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");
$user_id = $_SESSION['user_id'];

$item_id = intval($_GET['id'] ?? 0);
if ($item_id <= 0) {
    header('Location: list.php');
    exit;
}

// Fetch item
$stmt = $pdo->prepare("SELECT * FROM tbl_inventory_items WHERE item_id = ? AND user_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    echo "<div class='alert alert-danger text-center mt-4'>Item not found.</div>";
    exit;
}

// Optional: fetch document
$docStmt = $pdo->prepare("SELECT * FROM tbl_documents WHERE item_id = ?");
$docStmt->execute([$item_id]);
$doc = $docStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Item - MyHomeVault</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/editItemInventory.css">

<link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png"> 

</head>
<body class="bg">
<div class="container mt-4 col-md-6">
    <div class="card p-4 shadow">
        <h4>Edit Inventory Item</h4>
        <form method="POST" action="update_item.php" enctype="multipart/form-data">
            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">

            <input type="text" name="name" class="form-control mb-2" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
            <input type="text" name="category" class="form-control mb-2" value="<?php echo htmlspecialchars($item['category']); ?>">
            <textarea name="description" class="form-control mb-2"><?php echo htmlspecialchars($item['description']); ?></textarea>

            <label>Purchase Date:</label>
            <input type="date" name="purchase_date" class="form-control mb-2" value="<?php echo htmlspecialchars($item['purchase_date']); ?>">

            <label>Warranty Expiration:</label>
            <input type="date" name="warranty_expiration" class="form-control mb-2" value="<?php echo htmlspecialchars($item['warranty_expiration']); ?>">

            <input type="number" step="0.01" name="estimated_value" class="form-control mb-2" value="<?php echo htmlspecialchars($item['estimated_value']); ?>">
            <input type="text" name="serial_number" class="form-control mb-2" value="<?php echo htmlspecialchars($item['serial_number']); ?>">
            <input type="text" name="model_number" class="form-control mb-2" value="<?php echo htmlspecialchars($item['model_number']); ?>">
            <input type="text" name="location" class="form-control mb-2" value="<?php echo htmlspecialchars($item['location']); ?>">
            <input type="number" step="0.01" name="purchase_price" class="form-control mb-2" value="<?php echo htmlspecialchars($item['purchase_price']); ?>">

            <?php if (!empty($item['image_path'])): ?>
                <p>Current Image:<br><img src="../<?php echo htmlspecialchars($item['image_path']); ?>" width="120"></p>
            <?php endif; ?>
            <label>Replace Image (optional):</label>
            <input type="file" name="image" class="form-control mb-2">

            <?php if ($doc): ?>
                <p>Current Document: <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($doc['file_name']); ?></a></p>
            <?php endif; ?>
            <label>Replace Document (optional):</label>
            <input type="file" name="document" class="form-control mb-3">

            <button class="btn btn-success w-100">Save Changes</button>
        </form>
    </div>
</div>
</body>
</html>
