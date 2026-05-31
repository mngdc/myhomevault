<?php
require_once __DIR__ . '/../includes/db_connect.php';

// --- CONFIGURATION ---
$days = 30;
$today = new DateTime();
$threshold = $today->modify("+$days days")->format('Y-m-d');

// --- FETCH EXPIRING WARRANTIES ---
$sql = "
    SELECT item_id, user_id, item_name, warranty_expiration 
    FROM tbl_inventory_items 
    WHERE warranty_expiration IS NOT NULL 
      AND warranty_expiration <= ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$threshold]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- SETUP LOGGING ---
$logDir = __DIR__ . '/../assets/uploads/system_logs/';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
$logfile = $logDir . 'alerts_log.txt';

$count = 0;
foreach ($items as $item) {
    $message = "Warranty expiring for item '{$item['item_name']}' (ID {$item['item_id']}) on {$item['warranty_expiration']}";

    // Check if alert already exists
    $check = $pdo->prepare("
        SELECT alert_id FROM tbl_alerts 
        WHERE item_id = ? AND alert_type = 'Warranty' AND is_read = 0
    ");
    $check->execute([$item['item_id']]);
    $exists = $check->fetchColumn();

    if (!$exists) {
        $ins = $pdo->prepare("
            INSERT INTO tbl_alerts (user_id, item_id, alert_type, message)
            VALUES (?, ?, 'Warranty', ?)
        ");
        $ins->execute([$item['user_id'], $item['item_id'], $message]);
        $count++;

        file_put_contents(
            $logfile,
            date('Y-m-d H:i:s') . " - " . $message . PHP_EOL,
            FILE_APPEND
        );
    }
}

// --- DISPLAY RESULT IN UI ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Warranty - MyHomeVault</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/checkWarranty.css">
    <link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

    <style>
        /* Mobile responsive styles - only layout changes */
        @media (max-width: 768px) {
            /* Container adjustments */
            .container {
                padding-left: 15px !important;
                padding-right: 15px !important;
                max-width: 100% !important;
                margin-top: 1.5rem !important;
            }
            
            .col-md-8 {
                width: 100% !important;
                max-width: 100% !important;
            }
            
            /* Card adjustments */
            .card {
                margin-bottom: 1rem !important;
                border-radius: 8px !important;
                overflow: hidden !important;
            }
            
            .card.p-4 {
                padding: 1.25rem !important;
            }
            
            .card.p-3 {
                padding: 1rem !important;
            }
            
            /* Typography - only size adjustments for readability */
            h3 {
                font-size: 1.4rem !important;
                line-height: 1.3 !important;
                margin-bottom: 0.75rem !important;
            }
            
            h5 {
                font-size: 1.1rem !important;
                margin-bottom: 0.75rem !important;
            }
            
            .lead {
                font-size: 1rem !important;
                line-height: 1.4 !important;
                margin-bottom: 0.75rem !important;
            }
            
            /* Button adjustments */
            .btn {
                padding: 0.625rem 1.5rem !important;
                font-size: 0.95rem !important;
                border-radius: 6px !important;
                display: inline-block !important;
                width: auto !important;
                min-width: 200px !important;
            }
            
            .mt-3 {
                margin-top: 1rem !important;
            }
            
            .mt-4 {
                margin-top: 1.5rem !important;
            }
            
            .mt-5 {
                margin-top: 2rem !important;
            }
            
            .mb-3 {
                margin-bottom: 0.75rem !important;
            }
            
            /* List items adjustments */
            .list-group-item {
                padding: 0.875rem 0.75rem !important;
                font-size: 0.95rem !important;
                flex-wrap: wrap !important;
                min-height: 60px !important;
                align-items: center !important;
            }
            
            /* Badge adjustments */
            .badge {
                font-size: 0.8rem !important;
                padding: 0.35em 0.65em !important;
                margin-top: 0.25rem !important;
                border-radius: 4px !important;
                display: inline-block !important;
                white-space: nowrap !important;
            }
            
            /* Text alignment for very small screens */
            @media (max-width: 400px) {
                .list-group-item {
                    flex-direction: column !important;
                    align-items: flex-start !important;
                    text-align: left !important;
                }
                
                .list-group-item .badge {
                    margin-top: 0.5rem !important;
                    align-self: flex-start !important;
                }
            }
            
            /* Empty state text */
            .text-muted {
                font-size: 0.95rem !important;
                padding: 1rem 0 !important;
                margin-top: 0.5rem !important;
            }
        }
        
        /* Desktop styles remain exactly the same */
        @media (min-width: 769px) {
            /* All desktop styles are from original bootstrap */
        }
    </style>
</head>
<body class="bg">
<div class="container mt-5 col-md-8">
    <div class="card p-4 shadow-sm text-center">
        <h3 class="mb-3">🔍 Warranty Check Completed</h3>
        <p class="lead">
            <strong><?= $count ?></strong> new warranty alert<?= $count == 1 ? '' : 's' ?> added.
        </p>
        <a href="../dashboard.php" class="btn btn-primary mt-3 return">Return to Dashboard</a>
    </div>

    <div class="card mt-4 p-3 shadow-sm">
        <h5>🧾 Items Checked</h5>
        <?php if ($items): ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($items as $it): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($it['item_name']) ?>
                        <span class="badge bg-warning text-dark">
                            Expires <?= htmlspecialchars($it['warranty_expiration']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted mt-2">No items with expiring warranties in the next <?= $days ?> days.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    // Mobile touch improvements
    document.addEventListener('DOMContentLoaded', function() {
        // Make sure content fits on mobile
        function adjustForMobile() {
            if (window.innerWidth <= 768) {
                // Ensure long item names wrap properly
                document.querySelectorAll('.list-group-item').forEach(item => {
                    const text = item.querySelector('span:not(.badge)');
                    if (text) {
                        text.style.maxWidth = 'calc(100% - 100px)';
                        text.style.overflow = 'hidden';
                        text.style.textOverflow = 'ellipsis';
                        text.style.whiteSpace = 'nowrap';
                    }
                });
                
                // Center the button container
                const card = document.querySelector('.card.text-center');
                if (card) {
                    card.style.display = 'flex';
                    card.style.flexDirection = 'column';
                    card.style.alignItems = 'center';
                }
            }
        }
        
        window.addEventListener('resize', adjustForMobile);
        adjustForMobile();
    });
</script>
</body>
</html>
