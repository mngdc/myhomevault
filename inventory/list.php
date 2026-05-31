<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");
$user_id = $_SESSION['user_id'];

// --- Handle alert messages from delete_item.php ---
$successMsg = '';
$errorMsg = '';

if (isset($_GET['deleted'])) {
    $successMsg = "Item deleted successfully.";
} elseif (isset($_GET['error']) && $_GET['error'] === 'item_not_found') {
    $errorMsg = "The item you tried to delete was not found or already removed.";
}

// --- Fetch all inventory items for this user ---
$stmt = $pdo->prepare("SELECT * FROM tbl_inventory_items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Inventory List - MyHomeVault</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/listInventory.css">

    <link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">


<!DOCTYPE html>
<html>
<head>
<title>My Inventory - MyHomeVault</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

<style>
/* Common Styles - NO BACKGROUND CHANGES */
body.bg {
    /* Keep original background */
}

.container {
    padding-left: 15px;
    padding-right: 15px;
    max-width: 100%;
}

/* Tablet View (769px - 992px) */
@media (min-width: 769px) and (max-width: 992px) {
    .container {
        max-width: 95%;
        padding-left: 20px;
        padding-right: 20px;
    }
    
    /* Table adjustments - FIX: Show table on tablet */
    .table-responsive {
        display: block !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table {
        display: table !important;
        min-width: 700px;
    }
    
    .mobile-inventory-list {
        display: none !important;
    }
    
    .table th, .table td {
        padding: 12px 8px;
        font-size: 0.9rem;
        white-space: nowrap;
    }
    
    .table th:first-child,
    .table td:first-child {
        padding-left: 15px;
    }
    
    .table th:last-child,
    .table td:last-child {
        padding-right: 15px;
    }
    
    /* Image in table */
    .img-thumbnail {
        width: 60px !important;
        height: 60px !important;
        object-fit: cover;
    }
    
    /* Button adjustments */
    .btn-group .btn {
        padding: 6px 10px;
        font-size: 0.85rem;
        min-width: 40px;
    }
    
    /* Badge adjustments */
    .badge {
        font-size: 0.8rem;
        padding: 0.35em 0.65em;
    }
    
    /* Header adjustments */
    .inventory-header-container h3 {
        font-size: 1.6rem;
    }
    
    .d-flex.flex-column.flex-md-row {
        gap: 10px;
    }
    
    .btn {
        padding: 10px 15px;
        font-size: 0.95rem;
    }
    
    /* Alert adjustments */
    .alert {
        font-size: 0.95rem;
        padding: 12px 15px;
    }
}

/* Mobile View (up to 768px) */
@media (max-width: 768px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
        margin-top: 1rem;
    }
    
    /* Hide table on mobile */
    .table-responsive {
        display: none !important;
    }
    
    /* Show mobile cards */
    .mobile-inventory-list {
        display: block !important;
    }
    
    /* Header adjustments */
    .row.mb-3.mb-md-4 {
        margin-bottom: 1.5rem !important;
    }
    
    .col-12.col-md-6.mb-3.mb-md-0 {
        margin-bottom: 1rem !important;
    }
    
    .inventory-header-container {
        width: 100%;
        text-align: center;
    }
    
    .inventory-header-container h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: #2d3748;
    }
    
    /* Button container */
    .d-flex.flex-column.flex-md-row.gap-2 {
        gap: 10px;
        width: 100%;
    }
    
    .btn {
        flex: 1;
        padding: 12px 10px;
        font-size: 0.9rem;
        min-height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    
    .btn i {
        font-size: 1.1em;
    }
    
    /* Alert adjustments */
    .alert {
        margin: 0 0 15px 0;
        font-size: 0.9rem;
        padding: 12px 15px;
        border-radius: 8px;
    }
    
    .alert .btn-close {
        padding: 0.75rem;
    }
    
    /* Mobile cards */
    .mobile-inventory-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .mobile-inventory-card:active {
        transform: scale(0.98);
    }
    
    /* Card header */
    .card-header {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
        gap: 15px;
    }
    
    .card-image {
        width: 70px;
        height: 70px;
        flex-shrink: 0;
    }
    
    .card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 6px;
    }
    
    .card-image-placeholder {
        width: 70px;
        height: 70px;
        background: #6c757d;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
    }
    
    .card-title-wrapper {
        flex: 1;
        min-width: 0;
    }
    
    .card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        line-height: 1.3;
        word-break: break-word;
        margin-bottom: 4px;
    }
    
    .card-category {
        color: #666;
        font-size: 0.9rem;
        margin-top: 2px;
    }
    
    /* Card details grid */
    .card-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 15px;
    }
    
    .card-detail {
        font-size: 0.85rem;
    }
    
    .detail-label {
        display: block;
        color: #666;
        font-size: 0.8rem;
        margin-bottom: 2px;
    }
    
    .detail-value {
        display: block;
        color: #333;
        font-weight: 500;
        word-break: break-word;
    }
    
    /* Warranty status */
    .warranty-expired {
        color: #dc3545 !important;
        font-weight: 600;
    }
    
    .warranty-warning {
        color: #ffc107 !important;
        font-weight: 600;
    }
    
    /* Card actions */
    .card-actions {
        display: flex;
        gap: 8px;
        justify-content: center;
        border-top: 1px solid #eee;
        padding-top: 15px;
    }
    
    .card-actions .btn {
        flex: 1;
        padding: 8px 5px;
        font-size: 0.8rem;
        min-height: 38px;
    }
    
    /* Empty state */
    .alert-info {
        text-align: center;
        padding: 2rem 1rem !important;
    }
    
    .alert-info i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        display: block;
    }
}

/* Extra small devices (up to 576px) */
@media (max-width: 576px) {
    .container {
        padding-left: 5px;
        padding-right: 5px;
    }
    
    .mobile-inventory-card {
        padding: 12px;
    }
    
    .card-details {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .card-header {
        gap: 12px;
    }
    
    .card-title {
        font-size: 1rem;
    }
    
    .card-actions .btn {
        padding: 8px 5px;
        font-size: 0.75rem;
    }
    
    .btn {
        padding: 10px 8px;
        font-size: 0.85rem;
    }
    
    .inventory-header-container h3 {
        font-size: 1.4rem;
    }
}

/* Desktop (993px and above) */
@media (min-width: 993px) {
    .container {
        max-width: 1200px;
    }
    
    .mobile-inventory-list {
        display: none !important;
    }
    
    .table-responsive {
        display: block !important;
    }
    
    .table {
        display: table !important;
    }
}

/* Common responsive fixes - NO DESIGN CHANGES */
.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Ensure images maintain aspect ratio */
.img-thumbnail {
    object-fit: cover;
    border-radius: 6px;
}

/* Button group spacing */
.btn-group {
    gap: 2px;
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.mobile-inventory-card {
    animation: fadeIn 0.3s ease-out;
}

/* Touch improvements */
* {
    -webkit-tap-highlight-color: transparent;
}

.btn, .mobile-inventory-card {
    cursor: pointer;
}

/* Prevent text selection on mobile */
@media (max-width: 768px) {
    .card-title, .detail-value {
        user-select: text;
    }
}

/* Scrollbar styling for tablet */
@media (min-width: 769px) and (max-width: 992px) {
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
}

/* FIX: Tablet view showing empty */
@media (min-width: 769px) {
    .d-none.d-md-block {
        display: block !important;
    }
    
    .d-block.d-md-none {
        display: none !important;
    }
}
</style>
</head>
<body class="bg">
<div class="container mt-4 mt-md-5">
    <!-- Header Section -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <div class="inventory-header-container">
                <h3 class="mb-0"><i class="bi bi-box-seam d-none d-md-inline me-2"></i>My Inventory List</h3>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="d-flex flex-column flex-md-row gap-2">
                <a href="add_item.php" class="btn btn-primary flex-fill">
                    <i class="bi bi-plus-circle"></i> 
                    <span class="d-none d-md-inline">Add New Item</span>
                    <span class="d-inline d-md-none">Add New Item</span>
                </a>
                <a href="../dashboard.php" class="btn btn-secondary custom-btn-text flex-fill">
                    <i class="bi bi-house-door"></i>
                    <span class="d-none d-md-inline">Dashboard</span>
                    <span class="d-inline d-md-none">Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    <!--  Display Success or Error Messages -->
    <div class="row">
        <div class="col-12">
            <?php if ($successMsg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($successMsg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($errorMsg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($errorMsg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Desktop & Tablet Table View (769px and above) -->
    <?php if (count($items) > 0): ?>
        <div class="table-responsive d-none d-md-block">
            <table class="table table-striped table-hover align-middle shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Purchase Date</th>
                        <th>Warranty Expiration</th>
                        <th>Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="width: 100px;">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="../<?= htmlspecialchars($item['image_path']); ?>" class="img-thumbnail" style="width:80px; height:80px; object-fit:cover;">
                            <?php else: ?>
                                <div class="bg-secondary text-white text-center rounded d-flex align-items-center justify-content-center" style="width:80px; height:80px;">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['item_name']); ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($item['category']); ?></span></td>
                        <td><?= htmlspecialchars($item['purchase_date']); ?></td>
                        <td>
                            <?php 
                                $warranty = $item['warranty_expiration'];
                                if ($warranty) {
                                    $expiring = (strtotime($warranty) - time()) / (60*60*24);
                                    if ($expiring < 0) echo "<span class='badge bg-danger'>Expired</span>";
                                    elseif ($expiring <= 30) echo "<span class='badge bg-warning text-dark'>" . htmlspecialchars($warranty) . "</span>";
                                    else echo "<span class='badge bg-success'>" . htmlspecialchars($warranty) . "</span>";
                                } else {
                                    echo "<span class='text-muted'>-</span>";
                                }
                            ?>
                        </td>
                        <td><strong>₱<?= number_format($item['estimated_value'], 2); ?></strong></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="view_item.php?id=<?= $item['item_id']; ?>" class="btn btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit_item.php?id=<?= $item['item_id']; ?>" class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete_item.php?id=<?= $item['item_id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to permanently delete this item and its related files?');"
                                   title="Delete">
                                   <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards View (768px and below) -->
        <div class="mobile-inventory-list d-block d-md-none">
            <?php foreach ($items as $item): ?>
                <div class="mobile-inventory-card">
                    <div class="card-header">
                        <div class="card-image">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="../<?= htmlspecialchars($item['image_path']); ?>" alt="<?= htmlspecialchars($item['item_name']); ?>">
                            <?php else: ?>
                                <div class="card-image-placeholder">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-title-wrapper">
                            <h4 class="card-title"><?= htmlspecialchars($item['item_name']); ?></h4>
                            <div class="card-category"><?= htmlspecialchars($item['category']); ?></div>
                        </div>
                    </div>
                    
                    <div class="card-details">
                        <div class="card-detail">
                            <span class="detail-label">Purchase Date</span>
                            <span class="detail-value"><?= htmlspecialchars($item['purchase_date']); ?></span>
                        </div>
                        <div class="card-detail">
                            <span class="detail-label">Warranty</span>
                            <span class="detail-value 
                                <?php 
                                    $warranty = $item['warranty_expiration'];
                                    if ($warranty) {
                                        $expiring = (strtotime($warranty) - time()) / (60*60*24);
                                        if ($expiring < 0) echo 'warranty-expired';
                                        elseif ($expiring <= 30) echo 'warranty-warning';
                                    }
                                ?>">
                                <?= $warranty ? htmlspecialchars($warranty) : '-'; ?>
                            </span>
                        </div>
                        <div class="card-detail">
                            <span class="detail-label">Value</span>
                            <span class="detail-value">₱<?= number_format($item['estimated_value'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <a href="view_item.php?id=<?= $item['item_id']; ?>" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i>
                            <span class="d-none d-sm-inline">View</span>
                        </a>
                        <a href="edit_item.php?id=<?= $item['item_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                            <span class="d-none d-sm-inline">Edit</span>
                        </a>
                        <a href="delete_item.php?id=<?= $item['item_id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Delete this item?');">
                            <i class="bi bi-trash"></i>
                            <span class="d-none d-sm-inline">Delete</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center py-4">
                    <i class="bi bi-box-seam me-2"></i>No items found. Add your first item!
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    // Touch feedback for cards
    if (window.innerWidth <= 768) {
        const cards = document.querySelectorAll('.mobile-inventory-card');
        cards.forEach(card => {
            card.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            card.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
            });
        });
    }
    
    // Tablet-specific adjustments
    if (window.innerWidth >= 769 && window.innerWidth <= 992) {
        
        const tableResponsive = document.querySelector('.table-responsive');
        if (tableResponsive) {
            tableResponsive.style.display = 'block';
            tableResponsive.style.overflowX = 'auto';
        }
        
        const table = document.querySelector('.table');
        if (table) {
            table.style.display = 'table';
            table.style.minWidth = '700px';
        }
    }
    
    // Handle warranty date tooltips
    const warrantyElements = document.querySelectorAll('.warranty-warning, .warranty-expired, .badge.bg-danger, .badge.bg-warning');
    warrantyElements.forEach(el => {
        const text = el.textContent.trim();
        if (text !== '-' && text !== 'Expired') {
            try {
                const date = new Date(text);
                if (!isNaN(date.getTime())) {
                    const now = new Date();
                    const diffTime = date - now;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (el.classList.contains('warranty-expired') || el.classList.contains('bg-danger')) {
                        el.title = 'Expired ' + Math.abs(diffDays) + ' days ago';
                    } else if (el.classList.contains('warranty-warning') || el.classList.contains('bg-warning')) {
                        el.title = 'Expires in ' + diffDays + ' days';
                    }
                }
            } catch (e) {
                // Date parsing failed, skip tooltip
            }
        }
    });
});
</script>
</body>
</html>
