<?php
session_start();
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");

$user_id = $_SESSION['user_id'];

// Fetch all alerts for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM tbl_alerts WHERE user_id = ? ORDER BY alert_date DESC");
$stmt->execute([$user_id]);
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Alerts - MyHomeVault</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/alertsNotif.css">
<link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

<style>
/* MyHomeVault Color Theme */
:root {
    --mhv-green-primary: #48bb78;     /* Primary green */
    --mhv-green-secondary: #38a169;   /* Darker green */
    --mhv-green-light: #c6f6d5;       /* Light green */
    --mhv-warning: #ed8936;           /* Orange - for warnings */
    --mhv-light: #f7fafc;             /* Light background */
    --mhv-dark: #2d3748;              /* Dark text */
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
        margin-top: 15px;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-3 {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 15px;
    }
    
    .inventory-header-container {
        width: 100%;
    }
    
    .inventory-header-container h3 {
        font-size: 1.5rem;
        margin-bottom: 0;
        text-align: center;
        color: var(--mhv-dark);
        font-weight: 600;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-3 > a {
        width: 100%;
        text-align: center;
        padding: 12px;
        font-size: 1rem;
        min-height: 44px;
    }
    
    /* Alert cards layout for mobile */
    .list-group-item {
        padding: 15px;
        border-radius: 10px !important;
        margin-bottom: 10px;
        border: 1px solid #e2e8f0;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    /* FIX: Unread alert background - NOT transparent */
    .list-group-item-warning {
        border-left: 4px solid var(--mhv-warning);
        background: rgba(237, 137, 54, 0.1) !important; /* Solid background */
        background-color: #fffaf0 !important; /* Fallback solid color */
        border: 1px solid #fed7aa;
    }
    
    .list-group-item .d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }
    
    .list-group-item .d-flex > div:first-child {
        width: 100%;
        margin-bottom: 15px;
    }
    
    .list-group-item .d-flex > div:last-child {
        width: 100%;
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    
    .btn-sm {
        flex: 1;
        padding: 10px 12px;
        font-size: 0.9rem;
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    /* BOTH BUTTONS IN GREEN THEME */
    /* View Item Button - Green theme */
    .btn-view-item {
        background: linear-gradient(135deg, var(--mhv-green-primary) 0%, var(--mhv-green-secondary) 100%);
        border: none;
        color: white;
    }
    
    .btn-view-item:hover, .btn-view-item:active {
        background: linear-gradient(135deg, #3d9e6f 0%, #2f855a 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(72, 187, 120, 0.3);
    }
    
    /* Mark Read Button - Green theme (slightly different shade) */
    .btn-mark-read {
        background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
        border: none;
        color: white;
    }
    
    .btn-mark-read:hover, .btn-mark-read:active {
        background: linear-gradient(135deg, #2f855a 0%, #276749 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(56, 161, 105, 0.3);
    }
    
    /* Back to Dashboard Button - Neutral theme */
    .btn-secondary.custom-btn-text {
        background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%);
        border: none;
        color: white;
    }
    
    .btn-secondary.custom-btn-text:hover {
        background: linear-gradient(135deg, #f2f2f3ff 0%, #ffffffff 100%);
        color: white;
        transform: translateY(-1px);
    }
    
    /* Alert text adjustments */
    .list-group-item strong {
        font-size: 1rem;
        display: block;
        margin-bottom: 5px;
        color: var(--mhv-dark);
    }
    
    .list-group-item span {
        color: #4a5568;
        line-height: 1.4;
        display: block;
        margin-bottom: 8px;
    }
    
    .list-group-item small {
        font-size: 0.85rem;
        color: #718096;
    }
}

/* Tablet adjustments */
@media (min-width: 769px) and (max-width: 992px) {
    .container {
        max-width: 90%;
    }
    
    .list-group-item {
        padding: 15px;
    }
    
    .btn-sm {
        padding: 8px 16px;
        font-size: 0.9rem;
    }
    
    /* FIX: Unread alert for tablet */
    .list-group-item-warning {
        background: rgba(237, 137, 54, 0.1) !important;
        background-color: #fffaf0 !important;
        border: 1px solid #fed7aa;
    }
    
    /* Green themed buttons for tablet */
    .btn-view-item, .btn-mark-read {
        padding: 8px 16px;
    }
}

/* Large mobile devices */
@media (min-width: 577px) and (max-width: 768px) {
    .container {
        max-width: 95%;
    }
}

/* Extra small devices */
@media (max-width: 576px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    .list-group-item {
        padding: 12px 8px;
    }
    
    /* FIX: Unread alert for small mobile */
    .list-group-item-warning {
        background: rgba(237, 137, 54, 0.1) !important;
        background-color: #fffaf0 !important;
        border: 1px solid #fed7aa;
    }
    
    .inventory-header-container h3 {
        font-size: 1.3rem;
    }
    
    .btn {
        padding: 10px;
        font-size: 0.95rem;
    }
    
    .btn-sm {
        padding: 8px 10px;
        font-size: 0.85rem;
    }
    
    .btn-view-item, .btn-mark-read {
        font-size: 0.85rem;
        padding: 8px 10px;
    }
}

/* Desktop styles */
@media (min-width: 769px) {
    /* FIX: Unread alert for desktop */
    .list-group-item-warning {
        background: rgba(237, 137, 54, 0.1) !important;
        background-color: #fffaf0 !important;
        border-left: 4px solid var(--mhv-warning);
        border: 1px solid #fed7aa;
    }
    
    /* Green themed buttons for desktop */
    .btn-view-item {
        background: linear-gradient(135deg, var(--mhv-green-primary) 0%, var(--mhv-green-secondary) 100%);
        border: none;
        color: white;
        padding: 8px 20px;
    }
    
    .btn-view-item:hover {
        background: linear-gradient(135deg, #3d9e6f 0%, #2f855a 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
    }
    
    .btn-mark-read {
        background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
        border: none;
        color: white;
        padding: 8px 20px;
    }
    
    .btn-mark-read:hover {
        background: linear-gradient(135deg, #2f855a 0%, #276749 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(56, 161, 105, 0.3);
    }
    
    .btn-secondary.custom-btn-text {
        background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%);
        border: none;
        color: white;
    }
    
    .btn-secondary.custom-btn-text:hover {
        background: linear-gradient(135deg, #f2f2f3ff 0%, #ffffffff 100%);
        color: white;
        transform: translateY(-1px);
    }
}

/* Make elements touch-friendly */
.btn, .btn-sm {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
}

/* Card styling for better mobile appearance */
.list-group {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

/* Alert styling */
.alert {
    margin: 15px 0;
    text-align: center;
    padding: 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f0fff4 0%, var(--mhv-green-light) 100%);
    border: 1px solid #9ae6b4;
    color: #22543d;
}

.alert-info i {
    color: var(--mhv-green-primary);
}

/* Responsive text */
@media (max-width: 768px) {
    .text-muted {
        font-size: 0.85rem;
    }
}

/* Animation for alerts */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.list-group-item {
    animation: fadeIn 0.3s ease-out;
}

/* Alert type styling */
.alert-warranty, .alert-maintenance, .alert-expired {
    /* Use solid backgrounds for all alert types */
}

/* Icon styling */
.bi {
    font-size: 1.1em;
}

/* Active state for buttons */
.btn:active {
    transform: translateY(1px) !important;
}

/* Empty state enhancement - Green theme */
.alert-info {
    background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
    border: 1px solid #9ae6b4;
    color: #22543d;
    font-weight: 500;
}

/* Read alert styling */
.list-group-item:not(.list-group-item-warning) {
    background: white;
    border: 1px solid #e2e8f0;
}
</style>
</head>
<body class="bg">
<div class="container mt-4 mt-md-5 col-12 col-md-10 col-lg-8">
    <!-- Header Section -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12 col-md-8 mb-3 mb-md-0">
            <div class="inventory-header-container">
                <h3 class="mb-0"><i class="bi bi-bell me-2"></i>Alerts & Notifications</h3>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <a href="../dashboard.php" class="btn btn-secondary w-100 custom-btn-text">
                <i class="bi bi-house-door me-2"></i>
                <span class="d-none d-md-inline">Dashboard</span>
                <span class="d-inline d-md-none">Dashboard</span>
            </a>
        </div>
    </div>

    <?php if ($alerts): ?>
        <div class="list-group shadow-sm">
        <?php foreach ($alerts as $a): ?>
            <div class="list-group-item <?= $a['is_read'] ? '' : 'list-group-item-warning'; ?>">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <!-- Alert Content -->
                    <div class="mb-3 mb-md-0">
                        <strong>
                            <i class="bi 
                                <?php 
                                    switch(strtolower($a['alert_type'])) {
                                        case 'warranty': echo 'bi-shield-check'; break;
                                        case 'maintenance': echo 'bi-tools'; break;
                                        case 'expired': echo 'bi-exclamation-triangle'; break;
                                        default: echo 'bi-bell';
                                    }
                                ?> 
                                me-2">
                            </i>
                            <?= htmlspecialchars(ucfirst($a['alert_type'])); ?>:
                        </strong>
                        <span><?= htmlspecialchars($a['message']); ?></span><br>
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            <?= htmlspecialchars($a['alert_date']); ?>
                        </small>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <?php if (!empty($a['item_id'])): ?>
                            <a href="../inventory/view_item.php?id=<?= $a['item_id']; ?>" class="btn btn-sm btn-view-item flex-fill">
                                <i class="bi bi-eye me-1 d-none d-md-inline"></i>
                                <span class="d-none d-md-inline">View Item</span>
                                <span class="d-inline d-md-none">View</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!$a['is_read']): ?>
                            <a href="mark_read.php?id=<?= $a['alert_id']; ?>" class="btn btn-sm btn-mark-read flex-fill">
                                <i class="bi bi-check-circle me-1 d-none d-md-inline"></i>
                                <span class="d-none d-md-inline">Mark Read</span>
                                <span class="d-inline d-md-none">Read</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-4">
            <i class="bi bi-bell-slash me-2"></i>No alerts found.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mobile enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Add touch feedback for green buttons
    const greenButtons = document.querySelectorAll('.btn-view-item, .btn-mark-read, .btn-secondary.custom-btn-text');
    
    greenButtons.forEach(btn => {
        btn.addEventListener('touchstart', function() {
            this.style.opacity = '0.9';
            this.style.transform = 'scale(0.98)';
        });
        
        btn.addEventListener('touchend', function() {
            this.style.opacity = '1';
            this.style.transform = 'scale(1)';
        });
        
        btn.addEventListener('touchcancel', function() {
            this.style.opacity = '1';
            this.style.transform = 'scale(1)';
        });
    });
    
    // Add swipe functionality for mobile alerts (optional)
    if (window.innerWidth <= 768) {
        const alertItems = document.querySelectorAll('.list-group-item-warning');
        alertItems.forEach(item => {
            let startX = 0;
            let isSwiping = false;
            
            item.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                isSwiping = true;
            });
            
            item.addEventListener('touchmove', (e) => {
                if (!isSwiping) return;
                const currentX = e.touches[0].clientX;
                const diff = startX - currentX;
                
                // If swiping left (for mark as read action)
                if (diff > 30) {
                    const markReadBtn = item.querySelector('.btn-mark-read');
                    if (markReadBtn) {
                        item.style.transform = `translateX(-${Math.min(diff, 60)}px)`;
                        item.style.transition = 'transform 0.2s ease';
                    }
                }
            });
            
            item.addEventListener('touchend', (e) => {
                if (!isSwiping) return;
                isSwiping = false;
                
                const markReadBtn = item.querySelector('.btn-mark-read');
                if (markReadBtn) {
                    item.style.transform = 'translateX(0)';
                    item.style.transition = 'transform 0.3s ease';
                    
                    // If swiped far enough, trigger mark as read
                    if (Math.abs(startX - e.changedTouches[0].clientX) > 100) {
                        if (confirm('Mark this alert as read?')) {
                            window.location.href = markReadBtn.getAttribute('href');
                        }
                    }
                }
            });
        });
    }
    
    // Visual feedback for alerts
    const alertItems = document.querySelectorAll('.list-group-item');
    alertItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Only trigger if not clicking on a button
            if (!e.target.closest('a')) {
                this.style.backgroundColor = '#f8fafc';
                setTimeout(() => {
                    this.style.backgroundColor = '';
                }, 300);
            }
        });
    });
});
</script>
</body>
</html>