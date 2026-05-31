<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* ----------------------------------------------------
   RESTORE LAST AI VALUES
---------------------------------------------------- */
$saved_item   = $_SESSION['ai_item'] ?? '';
$saved_issue  = $_SESSION['ai_issue'] ?? '';
$saved_result = $_SESSION['ai_result'] ?? '';
$saved_mode   = $_SESSION['ai_mode'] ?? '';
$last_form    = $_SESSION['ai_last_formdata'] ?? null;

$item_name = $saved_item;
$issue     = $saved_issue;
$result    = $saved_result;
$mode      = $saved_mode;

/* ----------------------------------------------------
   IF USER SUBMITTED A NEW PROMPT
---------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $item_name = trim($_POST['item_name'] ?? '');
    $issue     = trim($_POST['issue'] ?? '');

    if ($issue === "") {
        $result = "⚠️ Please describe the issue first.";
    } else {

        // Build prompt
        $prompt = "You are a home maintenance AI.\n\n".
        "Item: {$item_name}\n".
        "Problem: {$issue}\n\n".
        "Provide:\n".
        "1. Troubleshooting.\n".
        "2. Safety warnings.\n".
        "3. A JSON block like:\n".
        "{ \"inventory\": {...}, \"maintenance\": {...} }\n";

        // Call AI connector
        $ch = curl_init("http://localhost/myhomevault/ai/ai_connector.php");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['prompt' => $prompt],
            CURLOPT_TIMEOUT => 30
        ]);

        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $raw === "") {
            $result = "⚠️ Could not reach AI. {$curlErr}";
        } else {
            $decoded = json_decode($raw, true);

            if (isset($decoded['response'])) {
                $mode = $decoded['mode'] ?? 'online';
                $reply = $decoded['response'];

                /* Extract JSON block */
                $json_block = null;
                $human = $reply;

                $pos = strrpos($reply, '{');
                if ($pos !== false) {
                    $maybe = substr($reply, $pos);
                    $test = json_decode($maybe, true);
                    if (json_last_error() === JSON_ERROR_NONE)
                    {
                        $json_block = $test;
                        $human = trim(substr($reply, 0, $pos));
                    }
                }

                $result = $human;
                $last_form = $json_block;

                // Save in session
                $_SESSION['ai_item']  = $item_name;
                $_SESSION['ai_issue'] = $issue;
                $_SESSION['ai_result'] = $result;
                $_SESSION['ai_mode'] = $mode;
                $_SESSION['ai_last_formdata'] = $json_block;

            } else {
                $result = "⚠️ Invalid AI response.";
            }
        }
    }
}

/* ----------------------------------------------------
   BUILD PREFILLS
---------------------------------------------------- */
/* ----------------------------------------------------
   BUILD PREFILLS
---------------------------------------------------- */
$inv = [
    'name'              => $saved_item,
    'category'          => '',
    'description'       => $issue,
    'purchase_date'     => '',
    'warranty_expiration'=> '',
    'estimated_value'   => '',
    'purchase_price'    => '',
    'serial_number'     => '',
    'model_number'      => '',
    'location'          => ''
];

$maint = [
    'description' => $issue,
    'item_name'   => $saved_item,
    'due_date'    => '',
    'frequency'   => 'none'
];

if (is_array($last_form)) {
    if (isset($last_form['inventory'])) {
        $inv = array_merge($inv, $last_form['inventory']);
    }
    if (isset($last_form['maintenance'])) {
        $m = $last_form['maintenance'];
        if (isset($m['due_in_days'])) {
            $maint['due_date'] = date('Y-m-d', strtotime("+{$m['due_in_days']} days"));
        }
        if (isset($m['description'])) $maint['description'] = $m['description'];
        if (isset($m['frequency']))   $maint['frequency']   = $m['frequency'];
        if (isset($m['item_name']))   $maint['item_name']   = $m['item_name'];
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<title>AI Troubleshooter</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/aiTroubleshooter.css">
<link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

<style>
/* Mobile Responsive Styles  */
@media (max-width: 768px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
        margin-top: 15px;
    }
    
    .card {
        padding: 20px 15px !important;
        margin-bottom: 20px;
        border-radius: 12px;
    }
    
    h3 {
        font-size: 1.6rem;
        margin-bottom: 25px;
        text-align: center;
        font-weight: 600;
    }
    
    /* Form elements */
    .form-label {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 8px;
        color: #333;
    }
    
    .form-control, .form-select {
        font-size: 1rem;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        border: 1px solid #ddd;
        height: auto;
        min-height: 48px;
    }
    
    /* Placeholder text */
    ::placeholder {
        font-size: 0.95rem;
        color: #888;
    }
    
    textarea.form-control {
        min-height: 150px;
        font-size: 1rem;
        line-height: 1.5;
        resize: vertical;
    }
    
    /* Buttons -  */
    .btn {
        width: 100%;
        padding: 14px 20px;
        font-size: 1.1rem;
        margin-bottom: 15px;
        min-height: 52px;
        font-weight: 600;
        border-radius: 10px;
        border: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4392 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
        color: white;
        margin-top: 10px;
    }
    
    .btn-outline-primary, .btn-outline-success, .btn-outline-secondary {
        border: 2px solid;
        font-weight: 500;
    }
    
    /* AI Response -  */
    .alert {
        margin: 25px 0;
        padding: 20px;
        font-size: 1rem;
        line-height: 1.6;
        border-radius: 10px;
        border: none;
        background: #f8f9fa;
    }
    
    .alert strong {
        font-size: 1.1rem;
        display: block;
        margin-bottom: 10px;
        color: #333;
    }
    
    /* Action buttons container */
    .nav-btns {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
        margin-top: 20px;
    }
    
    .nav-btn-fixed {
        width: 100%;
        margin-bottom: 10px;
        padding: 14px;
        font-size: 1rem;
        min-height: 50px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Modal adjustments */
    .modal-dialog {
        margin: 15px;
        max-width: calc(100% - 30px);
    }
    
    .modal-content {
        padding: 20px;
        border-radius: 12px;
    }
    
    .modal-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .modal-body label {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 6px;
        display: block;
        color: #333;
    }
    
    .modal-body .form-control {
        font-size: 1rem;
        margin-bottom: 15px;
    }
    
    .input-group {
        flex-direction: column;
        gap: 10px;
    }
    
    .input-group .form-select,
    .input-group .form-control {
        width: 100%;
        margin-bottom: 10px;
    }
}

/* Tablet adjustments (769px - 992px) */
@media (min-width: 769px) and (max-width: 992px) {
    .container {
        max-width: 95%;
        padding-left: 20px;
        padding-right: 20px;
    }
    
    .card {
        padding: 30px !important;
    }
    
    h3 {
        font-size: 1.8rem;
        margin-bottom: 30px;
    }
    
    .nav-btns {
        flex-direction: row !important;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px !important;
    }
    
    .nav-btn-fixed {
        flex: 0 0 calc(50% - 10px);
        margin: 5px;
        font-size: 0.95rem;
        min-width: 180px;
    }
    
    .alert {
        font-size: 1rem;
        padding: 25px;
    }
    
    .btn {
        padding: 12px 20px;
        font-size: 1.05rem;
    }
    
    .form-control, .form-select {
        font-size: 1rem;
        padding: 12px 15px;
    }
}

/* Desktop adjustments (above 992px) */
@media (min-width: 993px) {
    .container {
        max-width: 1000px;
    }
    
    h3 {
        font-size: 2rem;
    }
    
    .nav-btns {
        flex-direction: row !important;
        justify-content: center;
        gap: 15px !important;
    }
    
    .nav-btn-fixed {
        min-width: 200px;
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .container {
        padding-left: 12px;
        padding-right: 12px;
    }
    
    .card {
        padding: 18px 12px !important;
    }
    
    h3 {
        font-size: 1.5rem;
        margin-bottom: 20px;
    }
    

    .btn {
        padding: 13px 18px;
        font-size: 1.05rem;
        min-height: 50px;
    }
    
    .nav-btn-fixed {
        padding: 13px;
        font-size: 0.95rem;
        min-height: 48px;
    }
    
    .alert {
        padding: 18px;
        font-size: 0.98rem;
    }
    
    .alert strong {
        font-size: 1.05rem;
    }
    
    .form-label {
        font-size: 0.98rem;
    }
    
    .form-control, .form-select {
        font-size: 0.98rem;
        padding: 11px 14px;
    }
    
    textarea.form-control {
        font-size: 0.98rem;
        min-height: 140px;
    }
}

/* Very small devices (below 400px) */
@media (max-width: 400px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    h3 {
        font-size: 1.4rem;
    }
    
    .btn {
        font-size: 1rem;
        padding: 12px 15px;
    }
    
    .nav-btn-fixed {
        font-size: 0.92rem;
        padding: 12px;
    }
    
    
    .nav-btn-fixed {
        white-space: normal;
        line-height: 1.3;
        height: auto;
        min-height: 48px;
    }
}

.form-control, .form-select, .btn {
    min-height: 48px;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

p, .alert, textarea, .form-control {
    line-height: 1.6;
}

/* Modal styling for mobile */
@media (max-width: 768px) {
    .modal-header, .modal-footer {
        padding: 18px;
    }
    
    .modal-body {
        padding: 18px;
        font-size: 0.98rem;
    }
    
    .modal-footer .btn {
        font-size: 1rem;
        padding: 12px 18px;
    }
}

/* Grid adjustments for mobile forms */
@media (max-width: 768px) {
    .row.g-3 {
        margin-left: -5px;
        margin-right: -5px;
    }
    
    .row.g-3 > [class*="col-"] {
        padding-left: 5px;
        padding-right: 5px;
    }
}

/* Icon sizing */
.btn i {
    font-size: 1.1em;
    margin-right: 5px;
}

/* Loading state */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Focus states */
.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

/* Card shadow */
.card {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: none;
}

/* AI response specific styling */
.ai-response {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #667eea;
}


.heading-responsive, .text-responsive, .btn-responsive {
    
}


.mb-3 {
    margin-bottom: 1.2rem !important;
}

.mb-4 {
    margin-bottom: 1.8rem !important;
}
</style>
</head>
<body class="bg">

<div class="container mt-4 mt-md-5 col-12 col-md-10 col-lg-8">
<div class="card p-4 p-md-5 shadow">

    <h3 class="text-center mb-4">🤖 AI Troubleshooter</h3>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Item Name</label>
            <input type="text" name="item_name" class="form-control"
                   placeholder="Enter item name"
                   value="<?= htmlspecialchars($item_name) ?>">
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-semibold">Issue Description</label>
            <textarea name="issue" class="form-control"
                      placeholder="Describe the issue in detail..."
                      rows="4"><?= htmlspecialchars($issue) ?></textarea>
        </div>

        <button class="btn btn-primary w-100" id="aiSubmitBtn">
            <span class="d-none d-md-inline">Get AI Suggestion</span>
            <span class="d-inline d-md-none">Get AI Help</span>
        </button>
    </form>

    <?php if (!empty($result)): ?>
        <div class="alert alert-info mt-4 ai-response">
            <strong>AI Response (<?= strtoupper($mode) ?>):</strong><br>
            <div>
                <?= nl2br(htmlspecialchars($result)) ?>
            </div>
        </div>

        <div class="mt-3 d-flex flex-column justify-content-center align-items-center flex-wrap gap-2 nav-btns">
            <button class="btn btn-outline-primary nav-btn-fixed" data-bs-toggle="modal" data-bs-target="#invModal">
                <span class="d-none d-md-inline">➕ Add to Inventory</span>
                <span class="d-inline d-md-none">📦 Inventory</span>
            </button>
            <button class="btn btn-outline-success nav-btn-fixed" data-bs-toggle="modal" data-bs-target="#maintModal">
                <span class="d-none d-md-inline">🧰 Add to Maintenance</span>
                <span class="d-inline d-md-none">🔧 Maintenance</span>
            </button>

            <a class="btn btn-outline-secondary nav-btn-fixed"
               href="../inventory/add_item.php?name=<?= urlencode($inv['name']) ?>&description=<?= urlencode($inv['description']) ?>">
                <span class="d-none d-md-inline">Open Inventory Page</span>
                <span class="d-inline d-md-none">Open Inventory</span>
            </a>

            <a class="btn btn-outline-secondary nav-btn-fixed"
               href="../maintenance/add_task.php?description=<?= urlencode($maint['description']) ?>&item_name=<?= urlencode($maint['item_name']) ?>">
                <span class="d-none d-md-inline">Open Task Page</span>
                <span class="d-inline d-md-none">Open Tasks</span>
            </a>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="../dashboard.php" class="btn btn-secondary w-100">
            <span class="d-none d-md-inline">⬅ Back to Dashboard</span>
            <span class="d-inline d-md-none">← Dashboard</span>
        </a>
    </div>

</div>
</div>

<!-- Inventory Modal -->
<div class="modal fade" id="invModal">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="../inventory/save_item.php" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Item Name</label>
                    <input name="name" class="form-control" value="<?= htmlspecialchars($inv['name']) ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input name="category" class="form-control" value="<?= htmlspecialchars($inv['category']) ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($inv['description']) ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control" value="<?= $inv['purchase_date'] ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Warranty Expiration</label>
                        <input type="date" name="warranty_expiration" class="form-control" value="<?= $inv['warranty_expiration'] ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Estimated Value</label>
                        <input type="number" step="0.01" name="estimated_value" class="form-control" value="<?= htmlspecialchars($inv['estimated_value']) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Purchase Price</label>
                        <input type="number" step="0.01" name="purchase_price" class="form-control">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Serial Number</label>
                        <input name="serial_number" class="form-control" value="<?= htmlspecialchars($inv['serial_number']) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Model Number</label>
                        <input name="model_number" class="form-control" value="<?= htmlspecialchars($inv['model_number']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input name="location" class="form-control" value="<?= htmlspecialchars($inv['location']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Document</label>
                    <input type="file" name="document" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Maintenance Modal -->
<div class="modal fade" id="maintModal">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="../maintenance/save_task.php" class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Maintenance Task</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Associated Item</label>
                    <input name="item_name_free" list="userItems" class="form-control"
                           value="<?= htmlspecialchars($maint['item_name']) ?>">
                    <datalist id="userItems">
                        <?php
                        $q = $pdo->prepare("SELECT item_name FROM tbl_inventory_items WHERE user_id=? ORDER BY item_name");
                        $q->execute([$_SESSION['user_id']]);
                        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r)
                            echo "<option value=\"".htmlspecialchars($r['item_name'])."\">";
                        ?>
                    </datalist>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($maint['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="<?= $maint['due_date'] ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Frequency</label>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <select name="frequency_select" class="form-select">
                                <option value="none">One-time</option>
                                <option value="monthly" <?= $maint['frequency']=='monthly'?'selected':'' ?>>Monthly</option>
                                <option value="quarterly" <?= $maint['frequency']=='quarterly'?'selected':'' ?>>Quarterly</option>
                                <option value="yearly" <?= $maint['frequency']=='yearly'?'selected':'' ?>>Yearly</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <input type="text" name="frequency_custom" class="form-control" placeholder="Custom (optional)">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success">Save Task</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    
    const aiSubmitBtn = document.getElementById('aiSubmitBtn');
    if (aiSubmitBtn) {
        aiSubmitBtn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Analyzing...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 5000);
        });
    }
    
    // Auto-focus first input
    document.querySelector('input[name="item_name"]').focus();
    
    // Prevent modal from being too tall on mobile
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const modalBody = this.querySelector('.modal-body');
            if (modalBody && window.innerWidth <= 768) {
                const maxHeight = window.innerHeight * 0.6;
                modalBody.style.maxHeight = maxHeight + 'px';
                modalBody.style.overflowY = 'auto';
            }
        });
    });
    
    // Add clear buttons to date inputs on mobile
    if (window.innerWidth <= 768) {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block';
            wrapper.style.width = '100%';
            
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.innerHTML = '×';
            clearBtn.style.position = 'absolute';
            clearBtn.style.right = '10px';
            clearBtn.style.top = '50%';
            clearBtn.style.transform = 'translateY(-50%)';
            clearBtn.style.background = 'transparent';
            clearBtn.style.border = 'none';
            clearBtn.style.fontSize = '24px';
            clearBtn.style.color = '#666';
            clearBtn.style.cursor = 'pointer';
            clearBtn.style.padding = '0 15px';
            clearBtn.style.zIndex = '10';
            clearBtn.style.display = input.value ? 'block' : 'none';
            
            clearBtn.addEventListener('click', function() {
                input.value = '';
                this.style.display = 'none';
            });
            
            input.addEventListener('input', function() {
                clearBtn.style.display = this.value ? 'block' : 'none';
            });
            
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            wrapper.appendChild(clearBtn);
        });
    }
});
</script>
</body>
</html>