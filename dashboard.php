<?php
session_start();
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) header("Location: auth/login.php");

$user_id = $_SESSION['user_id'];

// fetch user row (may have different column names)
$u = $pdo->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
$u->execute([$user_id]);
$user = $u->fetch(PDO::FETCH_ASSOC) ?: [];

// helper to safely read user columns with fallbacks
function user_get($userRow, $preferredKeys, $fallback = '') {
    foreach ((array)$preferredKeys as $k) {
        if (isset($userRow[$k]) && $userRow[$k] !== null && $userRow[$k] !== '') return $userRow[$k];
    }
    return $fallback;
}

$display_name = user_get($user, ['name','username','user_name'], 'User');
$display_email = user_get($user, ['email'], '');
$display_phone = user_get($user, ['phone','contact_phone'], '');
$display_address = user_get($user, ['address'], '');
$avatar_path = user_get($user, ['avatar_path','avatar','photo_path'], 'assets/default_avatar.png');

// Summary counts (user-scoped)
$inventoryCount = $pdo->prepare("SELECT COUNT(*) FROM tbl_inventory_items WHERE user_id = ?");
$inventoryCount->execute([$user_id]);
$inventoryCount = $inventoryCount->fetchColumn();

$taskCount = $pdo->prepare("SELECT COUNT(*) FROM tbl_maintenance_tasks WHERE user_id = ?");
$taskCount->execute([$user_id]);
$taskCount = $taskCount->fetchColumn();

$alertCount = $pdo->prepare("SELECT COUNT(*) FROM tbl_alerts WHERE user_id = ?");
$alertCount->execute([$user_id]);
$alertCount = $alertCount->fetchColumn();

// upcoming & recent alerts (same as before)
$upcoming = $pdo->prepare("
    SELECT task_description, due_date 
    FROM tbl_maintenance_tasks 
    WHERE user_id = ? 
    AND status = 'Pending' 
    AND due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY due_date ASC
");
$upcoming->execute([$user_id]);
$upcomingTasks = $upcoming->fetchAll(PDO::FETCH_ASSOC);

$recent = $pdo->prepare("
    SELECT alert_type, message, alert_date 
    FROM tbl_alerts 
    WHERE user_id = ? 
    ORDER BY alert_date DESC 
    LIMIT 5
");
$recent->execute([$user_id]);
$recentAlerts = $recent->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard - MyHomeVault</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<link rel="icon" type="image/png" href="assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

<style>
.user-avatar { width:45px; height:45px; border-radius:50%; object-fit:cover; cursor:pointer; }

/* Mobile responsive styles */
@media (max-width: 768px) {
    /* Navigation */
    .nav-header-bg {
        padding: 0.5rem !important;
        flex-wrap: wrap;
    }
    
    .nav-buttons-desktop {
        display: none !important;
    }
    
    .mobile-menu-btn {
        display: block !important;
        font-size: 1.5rem;
    }
    
    .mobile-nav {
        width: 100%;
        display: none;
        background: var(--nav-bg, #f8f9fa);
        border-top: 1px solid #dee2e6;
        padding: 1rem 0;
    }
    
    .mobile-nav.active {
        display: block;
    }
    
    .mobile-nav .btn {
        width: 100%;
        margin-bottom: 0.5rem;
        text-align: left;
        padding: 0.75rem;
    }
    
    /* Logo adjustments */
    .logo-text {
        font-size: 1rem !important;
    }
    
    .nav-header-bg img {
        width: 30px !important;
        height: 30px !important;
    }
    
    /* Container adjustments */
    .container {
        padding-left: 10px !important;
        padding-right: 10px !important;
        max-width: 100% !important;
    }
    
    .maincontainertop {
        margin-top: 0.5rem !important;
    }
    
    /* Welcome section */
    .welcome-section {
        padding: 1rem 0;
        margin-bottom: 1rem;
        text-align: center;
        color: #ffffffff;
    }
    
    .welcome-title {
        font-size: 1.1rem;
        font-weight: 400;
        margin-bottom: 0.25rem;
        color: #ffffffff;
    }
    
    .welcome-section h4 {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #ffffffff;
    }
    
    /* Card grid adjustments */
    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
        display: flex;
        justify-content: center;
        
    }
    
    .col-md-4, .col-md-6 {
        padding-left: 5px !important;
        padding-right: 5px !important;
        margin-bottom: 10px !important;
        display: flex;
        justify-content: center;
    }
    
    .col-12 {
        padding-left: 0 !important;
        padding-right: 0 !important;
        display: flex;
        justify-content: center;
    }
    
    /* Stats cards  */
    .stats-card {
        text-align: center;
        transition: transform 0.2s ease;
        padding: 1.5rem 1rem !important;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        background: white;
        width: 100%;
        max-width: 300px;
        margin: 0.5rem;
    }
    
    .stats-card h2 {
        font-size: 2.5rem !important;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #2d3748;
        line-height: 1;
    }
    
    .stats-card h5 {
        font-size: 0.9rem !important;
        color: #718096;
        font-weight: 500;
        margin-bottom: 0;
    }
    
    /* Regular cards  */
    .card:not(.stats-card) {
        margin-bottom: 10px !important;
        padding: 1.25rem 1rem !important;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        background: white;
        width: 100%;
    }
    

    .row .col-md-6 {
        display: flex;
        justify-content: center;
    }
    
    .col-md-6 .card {
        width: 100%;
        max-width: 100%;
        min-height: 300px; 
    }
    
   
    .list-group-item {
        padding: 0.875rem 0.5rem !important;
        font-size: 0.9rem !important;
    }
    
   
    .list-group-item .text-truncate {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        max-width: 100% !important;
        display: block !important;
        -webkit-line-clamp: unset !important;
        -webkit-box-orient: unset !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
    }
    
    .list-group-item .d-flex {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    
    .list-group-item .flex-grow-1 {
        width: 100% !important;
        margin-bottom: 0.5rem !important;
    }
    
    .list-group-item small.text-muted {
        width: 100% !important;
        text-align: left !important;
        font-size: 0.8rem !important;
        color: #6c757d !important;
        padding-top: 0.25rem !important;
        border-top: 1px solid #f1f3f5 !important;
        margin-top: 0.5rem !important;
    }
    
   
    .list-group-item strong.d-block {
        font-size: 0.95rem !important;
        margin-bottom: 0.25rem !important;
        color: #495057 !important;
    }
    
    .list-group-item span.d-block {
        font-size: 0.9rem !important;
        line-height: 1.4 !important;
        color: #6c757d !important;
    }
    
    
    h4 {
        font-size: 1.25rem !important;
    }
    
    h5 {
        font-size: 1rem !important;
    }
    
    h2 {
        font-size: 1.75rem !important;
    }
    
    
    .modal-dialog {
        margin: 10px !important;
        max-width: calc(100% - 20px) !important;
    }
    
    
    .btn {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.9rem !important;
    }
    
    
    .badge {
        font-size: 0.75rem !important;
        padding: 0.25em 0.5em !important;
        white-space: nowrap;
    }
    
    
    .list-group-item.d-flex.justify-content-between {
        flex-direction: row !important;
        align-items: center !important;
    }
    
    .list-group-item.d-flex .text-truncate {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 65% !important;
    }
    
    /* Intro section - hide on mobile */
    .intro {
        display: none !important;
    }
    
    /* Check warranty button */
    .check-warranty {
        width: 100%;
        margin-top: 15px;
        padding: 0.75rem !important;
        border-radius: 10px;
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        color: #4a5568;
        font-weight: 500;
    }
    
    /* Section titles in cards */
    .card h5 {
        color: #2d3748;
        font-weight: 600;
        font-size: 1.1rem;
        padding-bottom: 0.75rem;
        margin-bottom: 0.75rem;
        border-bottom: 2px solid #edf2f7;
    }
    
    /* Empty state text */
    .text-muted {
        padding: 1rem 0;
        text-align: center;
        font-size: 0.95rem;
    }
}

/* Desktop styles */
@media (min-width: 769px) {
    .mobile-menu-btn, .mobile-nav {
        display: none !important;
    }
    
    .nav-buttons-desktop {
        display: flex !important;
    }
    
    
    .welcome-section {
        display: none !important;
    }
    
    /* Reset mobile centering for desktop */
    .row {
        margin-left: -0.75rem !important;
        margin-right: -0.75rem !important;
    }
    
    .col-md-4, .col-md-6 {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        display: block;
    }
    
    .stats-card {
        padding: 1.5rem 1rem !important;
        border-radius: 12px;
        max-width: none;
        margin: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .card:not(.stats-card) {
        width: auto;
    }
    
    /* Desktop alert card layout */
    .list-group-item .d-flex {
        flex-direction: row !important;
        align-items: flex-start !important;
    }
    
    .list-group-item .flex-grow-1 {
        width: auto !important;
        margin-bottom: 0 !important;
    }
    
    .list-group-item small.text-muted {
        width: auto !important;
        text-align: right !important;
        border-top: none !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    /* Desktop text truncation */
    .list-group-item .text-truncate {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 300px !important;
    }
}

/* Common responsive adjustments */
.mobile-menu-btn {
    background: none;
    border: none;
    color: #6c757d;
    padding: 0.5rem;
    display: none;
}

/* Ensure cards are touch-friendly on mobile */
.card {
    transition: transform 0.2s;
}

.card:active {
    transform: scale(0.98);
}


.check-warranty {
    width: 100%;
    margin-top: 10px;
}


.alert-content {
    flex: 1;
    min-width: 0;
}

.alert-date {
    flex-shrink: 0;
    margin-left: 0.5rem;
}


@media (max-width: 400px) {
    .list-group-item {
        max-height: none !important;
        overflow-y: visible !important;
    }
}
</style>
</head>

<body class="bg">

<!-- Mobile-friendly Navigation -->
<div class="d-flex justify-content-between align-items-center py-2 nav-header-bg"> 
    <div class="d-flex align-items-center">
        <img 
            src="assets/MyHomeVault Assets/LOGO/TRANSPARENT/mhvLOGO_T_MEDIUM.png" 
            alt="MyHomeVault Logo"
            class="me-2" 
            style="width: 40px; height: 40px;"
        >
        <h2 class="h4 mb-0 logo-text">MyHomeVault</h2> 
    </div>

    <!-- Desktop Navigation -->
    <div class="d-flex align-items-center nav-buttons-desktop">
        <a href="inventory/list.php" class="btn btn-outline-primary me-2"><i class="bi bi-box"></i> <span class="d-none d-md-inline">Inventory</span></a>
        <a href="maintenance/list.php" class="btn btn-outline-success me-2"><i class="bi bi-tools"></i> <span class="d-none d-md-inline">Maintenance</span></a>
        <a href="alerts/list.php" class="btn btn-outline-danger me-2"><i class="bi bi-bell"></i> <span class="d-none d-md-inline">Alerts</span></a>
        <a href="maintenance/ai_troubleshooter.php" class="btn btn-outline-dark me-2"><i class="bi bi-robot"></i> <span class="d-none d-md-inline">AI</span></a>
        <button class="btn btn-outline-dark me-2" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="bi bi-person-circle"></i> <span class="d-none d-md-inline">Profile</span></button>
        <a href="auth/logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span></a>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="bi bi-list"></i>
    </button>
</div>

<!-- Mobile Navigation Menu -->
<div class="mobile-nav" id="mobileNav">
    <div class="container">
        <a href="inventory/list.php" class="btn btn-outline-primary d-flex align-items-center">
            <i class="bi bi-box me-2"></i> Inventory
        </a>
        <a href="maintenance/list.php" class="btn btn-outline-success d-flex align-items-center">
            <i class="bi bi-tools me-2"></i> Maintenance
        </a>
        <a href="alerts/list.php" class="btn btn-outline-danger d-flex align-items-center">
            <i class="bi bi-bell me-2"></i> Alerts
        </a>
        <a href="maintenance/ai_troubleshooter.php" class="btn btn-outline-dark d-flex align-items-center">
            <i class="bi bi-robot me-2"></i> AI Troubleshooter
        </a>
        <button class="btn btn-outline-dark d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#profileModal">
            <i class="bi bi-person-circle me-2"></i> Profile
        </button>
        <a href="auth/logout.php" class="btn btn-danger d-flex align-items-center">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
    </div>
</div>

<div class="container mt-4">
    <div class="welcome-section">
        <h4>Hello, <?= htmlspecialchars($display_name) ?>! 👋</h4>
        <div class="welcome-title">Welcome Back to My Home Vault,</div>
    </div>

    <!-- top bar (desktop only) -->
    <div class="maincontainertop">
        <div class="intro d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4>Hello, <?= htmlspecialchars($display_name) ?>! 👋</h4>
                <p class="text-white">Welcome back to MyHomeVault</p>
            </div>
        </div>

        
        <div class="row mt-4">
            <div class="col-12 col-md-4 mb-3">
                <div class="card stats-card text-center shadow-sm h-100">
                
                    <h2 class="mb-2"><?= $inventoryCount ?></h2>
                    <h5 class="mb-0"><i class="bi bi-box-seam me-1"></i> Inventory</h5>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card stats-card text-center shadow-sm h-100">
                
                    <h2 class="mb-2"><?= $taskCount ?></h2>
                    <h5 class="mb-0"><i class="bi bi-tools me-1"></i> Maintenance</h5>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card stats-card text-center shadow-sm h-100">
                    
                    <h2 class="mb-2"><?= $alertCount ?></h2>
                    <h5 class="mb-0"><i class="bi bi-bell me-1"></i> Alerts</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- upcoming & recent alerts - also centered -->
    <div class="row">
        <div class="col-12 col-md-6 mb-3">
            <div class="card shadow-sm p-3 h-100">
                <h5 class="d-flex align-items-center"><i class="bi bi-tools me-2"></i> Upcoming Maintenance (Next 7 Days)</h5>
                <?php if ($upcomingTasks): ?>
                    <ul class="list-group list-group-flush mt-2">
                        <?php foreach ($upcomingTasks as $t): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-truncate me-2 alert-content"><?= htmlspecialchars($t['task_description']) ?></span>
                                <span class="badge bg-warning text-nowrap alert-date"><?= htmlspecialchars($t['due_date']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mt-2">No upcoming maintenance.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-md-6 mb-3">
            <div class="card shadow-sm p-3 h-100">
                <h5 class="d-flex align-items-center"><i class="bi bi-bell me-2"></i> Recent Alerts</h5>
                <?php if ($recentAlerts): ?>
                    <ul class="list-group list-group-flush mt-2">
                        <?php foreach ($recentAlerts as $a): ?>
                            <li class="list-group-item py-2">
                                <div class="d-flex justify-content-between align-items-start flex-wrap">
                                    <div class="flex-grow-1 me-2 alert-content">
                                        <strong class="d-block"><?= htmlspecialchars(ucfirst($a['alert_type'])) ?>:</strong>
                                        
                                        <span class="d-block"><?= htmlspecialchars($a['message']) ?></span>
                                    </div>
                                    <small class="text-muted text-nowrap alert-date"><?= htmlspecialchars($a['alert_date']) ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mt-2">No recent alerts.</p>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="alerts/check_warranty.php" class="btn btn-outline-secondary btn-sm check-warranty">
                        <i class="bi bi-search"></i> Check Warranty Expirations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- profile modal -->
<div class="modal fade" id="profileModal">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="update_profile.php" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Your Profile</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="text-center mb-3">
                    <img src="<?= htmlspecialchars($avatar_path) ?>" class="user-avatar mb-2" style="width:80px;height:80px;border-radius:50%;" onerror="this.src='../assets/default_avatar.png'">
                    <input type="file" name="avatar" class="form-control mt-2" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" value="<?= htmlspecialchars(user_get($user, ['name','username','user_name'], '')) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input name="email" class="form-control" value="<?= htmlspecialchars($display_email) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control" value="<?= htmlspecialchars($display_phone) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($display_address) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password (optional)</label>
                    <input type="password" name="new_pass" class="form-control" placeholder="Leave blank to keep current">
                </div>
            </div>

            <div class="modal-footer d-flex flex-wrap">
                <button type="button" class="btn btn-outline-danger delete-account flex-fill m-1" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete Account</button>
                <button class="btn btn-secondary custom-btn-text flex-fill m-1" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary save-changes flex-fill m-1">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- delete modal -->
<div class="modal fade" id="deleteModal">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="delete_account.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete your account and all data? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary flex-fill m-1" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger flex-fill m-1">Delete Account</button>
            </div>
        </form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileNav = document.getElementById('mobileNav');
    
    if (mobileMenuBtn && mobileNav) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenuBtn.contains(event.target) && !mobileNav.contains(event.target)) {
                mobileNav.classList.remove('active');
            }
        });
        
        // Close mobile menu when a link is clicked
        mobileNav.querySelectorAll('a, button').forEach(item => {
            item.addEventListener('click', () => {
                mobileNav.classList.remove('active');
            });
        });
    }
    
    // Add touch feedback to cards
    document.querySelectorAll('.card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        card.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Ensure alert messages show full text on mobile
    function ensureFullMessageDisplay() {
        if (window.innerWidth <= 768) {
            
            document.querySelectorAll('.list-group-item .d-block').forEach(el => {
                el.style.whiteSpace = 'normal';
                el.style.overflow = 'visible';
                el.style.textOverflow = 'clip';
                el.style.display = 'block';
                el.style.webkitLineClamp = 'unset';
                el.style.webkitBoxOrient = 'unset';
            });
        }
    }
    
    window.addEventListener('resize', ensureFullMessageDisplay);
    ensureFullMessageDisplay();
    
    // Adjust modal padding on mobile
    function adjustModalPadding() {
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.modal-content').forEach(modal => {
                modal.style.padding = '10px';
            });
        }
    }
    
    window.addEventListener('resize', adjustModalPadding);
    adjustModalPadding();
});
</script>
</body>
</html>