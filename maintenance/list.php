<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- Handle alert messages ---
$successMsg = '';
$errorMsg = '';
if (isset($_GET['completed'])) {
    $successMsg = "Task marked as completed.";
} elseif (isset($_GET['deleted'])) {
    $successMsg = "Task deleted successfully.";
} elseif (isset($_GET['error'])) {
    $errorMsg = "An error occurred while processing your request.";
}

// --- Fetch all tasks ---
$sql = "
SELECT 
    mt.*, 
    ii.item_name 
FROM tbl_maintenance_tasks mt 
LEFT JOIN tbl_inventory_items ii ON mt.item_id = ii.item_id 
WHERE mt.user_id = ?
ORDER BY mt.status = 'Pending' DESC, mt.due_date IS NULL, mt.due_date ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Maintenance Tasks - MyHomeVault</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/listMaintenance.css">
<link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

<style>
@media (max-width: 768px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    .list-group-item {
        padding: 12px 8px;
        flex-direction: column;
        align-items: stretch !important;
    }
    
    .list-group-item > div:first-child {
        margin-bottom: 10px;
        width: 100%;
    }
    
    .btn-group {
        width: 100%;
        justify-content: flex-end;
    }
    
    .btn-group .btn {
        padding: 6px 12px;
    }
    
    .inventory-header-container h3 {
        font-size: 1.3rem;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-3 {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-3 > div:first-child {
        margin-bottom: 15px;
        width: 100%;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-3 > div:last-child {
        width: 100%;
        display: flex;
        gap: 10px;
    }
    
    .btn {
        flex: 1;
        font-size: 0.9rem;
        padding: 8px 10px;
    }
    

    .list-group-item > div:first-child > div {
        margin-bottom: 5px;
    }
    
    /* Adjust badge size */
    .badge {
        font-size: 0.8rem;
        padding: 4px 8px;
    }
}

/* Tablet adjustments */
@media (max-width: 992px) and (min-width: 769px) {
    .container {
        max-width: 95%;
    }
    
    .list-group-item {
        padding: 15px;
    }
}

/* Extra small devices */
@media (max-width: 576px) {
    .container {
        padding-left: 5px;
        padding-right: 5px;
    }
    
    .btn {
        font-size: 0.85rem;
    }
    
    .btn i {
        margin-right: 3px;
    }
    
    .list-group-item {
        padding: 10px 5px;
    }
    
    .list-group-item strong {
        font-size: 0.95rem;
        display: block;
        margin-bottom: 5px;
    }
    
    .list-group-item small {
        font-size: 0.8rem;
    }
}

.list-group-item {
    transition: all 0.3s ease;
}

.btn {
    min-height: 38px;
}

.bg-light.text-muted {
    opacity: 0.8;
}
</style>
</head>
<body class="bg">
<div class="container mt-4 mt-md-5">
    <!-- Header Section -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12 col-md-8 mb-3 mb-md-0">
            <div class="inventory-header-container">
                <h3 class="mb-0"><i class="bi bi-tools me-2"></i>Maintenance Tasks</h3>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="d-flex flex-column flex-md-row gap-2">
                <a href="add_task.php" class="btn btn-success flex-fill">
                    <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Add Task</span>
                    <span class="d-inline d-md-none">Add</span>
                </a>
                <a href="../dashboard.php" class="btn btn-secondary custom-btn-text flex-fill">
                    <i class="bi bi-house-door"></i> <span class="d-none d-md-inline">Dashboard</span>
                    <span class="d-inline d-md-none">Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    <!--  Feedback Alerts -->
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

    <!-- Tasks List -->
    <?php if ($tasks): ?>
        <div class="row">
            <div class="col-12">
                <div class="list-group shadow-sm">
                    <?php foreach($tasks as $t): ?>
                        <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center
                            <?= $t['status'] === 'Completed' ? 'bg-light text-muted' : ''; ?>">
                            <!-- Task Info - Left Side -->
                            <div class="flex-grow-1 mb-2 mb-md-0">
                                <strong class="d-block mb-1"><?= htmlspecialchars($t['task_description']); ?></strong>
                                
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <small class="text-muted">
                                        <?= $t['item_name'] ? 'Item: ' . htmlspecialchars($t['item_name']) : 'General Task'; ?>
                                    </small>
                                    <span class="d-none d-md-inline">•</span>
                                    <small class="text-muted">
                                        Due: <?= $t['due_date'] ?: '—'; ?>
                                    </small>
                                </div>
                                
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <small class="text-muted">
                                        Frequency: <?= ucfirst(htmlspecialchars($t['frequency'])); ?>
                                    </small>
                                    <span class="badge <?= $t['status'] === 'Completed' ? 'bg-success' : 'bg-warning text-dark'; ?> ms-md-2">
                                        <?= htmlspecialchars($t['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons - Right Side -->
                            <div class="d-flex justify-content-end mt-2 mt-md-0">
                                <div class="btn-group" role="group">
                                    <?php if ($t['status'] === 'Pending'): ?>
                                        <form method="POST" action="complete_task.php" class="me-1">
                                            <input type="hidden" name="task_id" value="<?= $t['task_id']; ?>">
                                            <button class="btn btn-sm btn-outline-success" title="Mark Done">
                                                <i class="bi bi-check-circle icon-complete"></i>
                                                <span class="d-none d-md-inline ms-1">Done</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" action="delete_task.php" onsubmit="return confirm('Delete this task?');">
                                        <input type="hidden" name="task_id" value="<?= $t['task_id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger" title="Delete Task">
                                            <i class="bi bi-trash"></i>
                                            <span class="d-none d-md-inline ms-1">Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center py-4">
                    <i class="bi bi-info-circle me-2"></i>No maintenance tasks found.
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
