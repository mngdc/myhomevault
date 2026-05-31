<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");
$user_id = $_SESSION['user_id'];

// Get item ID from URL
$item_id = intval($_GET['id'] ?? 0);
if ($item_id <= 0) {
    header('Location: list.php');
    exit;
}

// Fetch item details
$stmt = $pdo->prepare("SELECT * FROM tbl_inventory_items WHERE item_id = ? AND user_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "<div class='alert alert-danger text-center mt-4'>Item not found.</div>";
    exit;
}

// Fetch linked documents
$docs = $pdo->prepare("SELECT * FROM tbl_documents WHERE item_id = ?");
$docs->execute([$item_id]);
$documents = $docs->fetchAll(PDO::FETCH_ASSOC);

// Helper for safe display
function safe($val, $fallback = '—') {
    return htmlspecialchars($val ?: $fallback);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>View Item - MyHomeVault</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/viewItemInventory.css">
<link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

</head>
<body class="bg">
<div class="container mt-4 col-md-8">
    <div class="card p-4 shadow">
        <h4 class="mb-3">Item Details</h4>
        <div class="row">
            <div class="col-md-4">
                <?php
                $imgPath = '../' . $item['image_path'];
                if (!empty($item['image_path']) && file_exists(__DIR__ . '/../' . $item['image_path'])):
                ?>
                    <img src="<?= htmlspecialchars($imgPath) ?>" class="img-fluid rounded mb-3">
                <?php else: ?>
                    <div class="bg-secondary text-white text-center p-5 rounded">No Image</div>
                <?php endif; ?>
            </div>

            <div class="col-md-8">
                <h5><?= safe($item['item_name']); ?></h5>
                <p><strong>Category:</strong> <?= safe($item['category']); ?></p>
                <p><strong>Description:</strong> <?= safe($item['description']); ?></p>
                <p><strong>Location:</strong> <?= safe($item['location']); ?></p>
                <p><strong>Purchase Date:</strong> <?= safe($item['purchase_date']); ?></p>
                <p><strong>Warranty Expiration:</strong> <?= safe($item['warranty_expiration']); ?></p>
                <p><strong>Purchase Price:</strong> ₱<?= $item['purchase_price'] ? number_format($item['purchase_price'], 2) : '—'; ?></p>
                <p><strong>Estimated Value:</strong> ₱<?= $item['estimated_value'] ? number_format($item['estimated_value'], 2) : '—'; ?></p>
                <p><strong>Serial Number:</strong> <?= safe($item['serial_number']); ?></p>
                <p><strong>Model Number:</strong> <?= safe($item['model_number']); ?></p>
            </div>
        </div>

        <hr>
        <h5>Documents</h5>
        <?php if (count($documents) > 0): ?>
            <ul>
                <?php foreach ($documents as $doc): 
                    $path = '../' . $doc['file_path'];
                ?>
                    <li>
                        <?php if (file_exists(__DIR__ . '/../' . $doc['file_path'])): ?>
                            <a href="<?= htmlspecialchars($path); ?>" target="_blank">
                                <?= htmlspecialchars($doc['file_name']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted"><?= htmlspecialchars($doc['file_name']); ?> (missing)</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No documents uploaded.</p>
        <?php endif; ?>

        <hr>
        <div class="mt-3 d-flex gap-2">
            <a href="edit_item.php?id=<?= $item['item_id']; ?>" class="btn btn-warning">Edit Item</a>
            <a href="list.php" class="btn btn-secondary">Back to List</a>
            <a href="../maintenance/list.php?item_id=<?= $item['item_id']; ?>" class="btn btn-success">Maintenance</a>
            <a href="../alerts/list.php?item_id=<?= $item['item_id']; ?>" class="btn btn-danger">Alerts</a>
        </div>
    </div>
</div>
        <hr>
<div class="card p-4 shadow mt-4">
    <h5 class="mb-3">Repair History</h5>

    <?php
        $r = $pdo->prepare("SELECT * FROM tbl_repair_logs WHERE item_id = ? ORDER BY repair_date DESC");
        $r->execute([$item_id]);
        $repairs = $r->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (count($repairs) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Issue</th>
                    <th>Cost</th>
                    <th>Technician Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repairs as $rp): ?>
                    <tr>
                        <td><?= safe($rp['repair_date']); ?></td>
                        <td><?= safe($rp['issue_description']); ?></td>
                        <td>₱<?= $rp['cost'] ? number_format($rp['cost'],2) : '—'; ?></td>
                        <td><?= safe($rp['technician_notes']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No repair logs recorded yet.</p>
    <?php endif; ?>

    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addRepairModal">
        ➕ Log Repair
    </button>
</div>

<!-- Repair Modal -->
<div class="modal fade" id="addRepairModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="../repairs/save_repair.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Repair</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="item_id" value="<?= $item_id ?>">

                <label>Date</label>
                <input type="date" name="repair_date" class="form-control mb-2" required>

                <label>Issue Fixed</label>
                <input name="issue_description" class="form-control mb-2" required>

                <label>Cost</label>
                <input type="number" step="0.01" name="cost" class="form-control mb-2">

                <label>Technician Notes</label>
                <textarea name="technician_notes" class="form-control mb-2"></textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS (required for modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

