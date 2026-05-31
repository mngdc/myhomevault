<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }
$user_id = $_SESSION['user_id'];

// fetch user's items
$stmt = $pdo->prepare("SELECT item_id, item_name FROM tbl_inventory_items WHERE user_id = ? ORDER BY item_name");
$stmt->execute([$user_id]);
$item_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// prefill from GET
$prefill_desc = $_GET['description'] ?? '';
$prefill_due = $_GET['due_date'] ?? '';
$prefill_freq = $_GET['frequency'] ?? 'none';
$prefill_item_name = $_GET['item_name'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Task - MyHomeVault</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/addTask.css">
<link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

<style>
/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
        margin-top: 15px;
    }
    
    .card {
        padding: 20px 15px !important;
        margin-bottom: 20px;
    }
    
    h4 {
        font-size: 1.4rem;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .form-label {
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    .form-control, .form-select {
        font-size: 1rem;
        padding: 10px 12px;
    }
    
    .input-group {
        flex-direction: column;
    }
    
    .input-group .form-select,
    .input-group .form-control {
        width: 100% !important;
        margin-bottom: 10px;
        border-radius: 0.375rem !important;
    }
    
    .input-group .form-select {
        border-bottom-left-radius: 0.375rem !important;
        border-bottom-right-radius: 0.375rem !important;
        border-top-right-radius: 0.375rem !important;
    }
    
    .input-group .form-control {
        border-top-left-radius: 0.375rem !important;
        border-top-right-radius: 0.375rem !important;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 10px !important;
    }
    
    .btn {
        width: 100%;
        padding: 12px;
        font-size: 1rem;
        margin-bottom: 5px;
    }
    
    .btn br {
        display: none;
    }
    
    .back, .back-ai {
        font-size: 0.95rem;
    }
    
    .text-muted.mb-2.d-block {
        font-size: 0.85rem;
        line-height: 1.4;
        margin-bottom: 15px !important;
    }
}

/* Tablet adjustments */
@media (min-width: 769px) and (max-width: 992px) {
    .container {
        max-width: 80%;
    }
    
    .card {
        padding: 25px !important;
    }
    
    .d-flex.gap-2 {
        gap: 15px !important;
    }
}

/* Large mobile devices */
@media (min-width: 577px) and (max-width: 768px) {
    .container {
        max-width: 90%;
    }
}

/* Extra small devices */
@media (max-width: 576px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    .card {
        padding: 15px 10px !important;
    }
    
    h4 {
        font-size: 1.3rem;
    }
    
    .btn {
        padding: 10px;
        font-size: 0.95rem;
    }
    
    textarea.form-control {
        min-height: 100px;
    }
}

/* Make form elements touch-friendly */
.form-control, .form-select, .btn {
    min-height: 44px; /* Minimum touch target size */
}

/* Improve spacing */
.mb-2 {
    margin-bottom: 1rem !important;
}

.mb-3 {
    margin-bottom: 1.5rem !important;
}

/* Adjust input group for better mobile display */
@media (max-width: 768px) {
    .input-group > :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
        margin-left: 0;
        border-top-left-radius: 0.375rem;
    }
}

/* Ensure date input is properly sized on mobile */
input[type="date"] {
    -webkit-appearance: none;
    min-height: 44px;
}

/* Card styling for better mobile appearance */
.card {
    border-radius: 10px;
    border: 1px solid #e0e0e0;
}

/* Responsive text in buttons */
@media (max-width: 768px) {
    .btn span {
        display: inline !important;
    }
    
    .btn i + span {
        margin-left: 5px;
    }
}

/* Add some icons to buttons for better mobile UX */
@media (max-width: 768px) {
    .add::before {
        content: "✓ ";
        font-weight: bold;
    }
    
    .back::before {
        content: "← ";
    }
    
    .back-ai::before {
        content: "🤖 ";
    }
}
</style>
</head>
<body class="bg">
<div class="container mt-4 mt-md-5 col-12 col-md-8 col-lg-6">
  <div class="card p-4 p-md-5 shadow">
    <h4 class="mb-4">📝 Add Maintenance Task</h4>
    <form method="POST" action="save_task.php">
      <!-- Associated Item -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Associated Item</label>
        <input list="itemsList" name="item_name_free" class="form-control" 
               value="<?= htmlspecialchars($prefill_item_name) ?>"
               placeholder="Type or select an item">
        <datalist id="itemsList">
          <?php foreach ($item_list as $it): ?>
            <option value="<?= htmlspecialchars($it['item_name']) ?>">
          <?php endforeach; ?>
        </datalist>
        <small class="text-muted mt-1 d-block">Type a product name or select from suggestions</small>
      </div>

      <!-- Task Description -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Task Description</label>
        <textarea name="description" class="form-control" 
                  placeholder="Describe the maintenance task..." 
                  rows="4" required><?= htmlspecialchars($prefill_desc) ?></textarea>
      </div>

      <!-- Due Date -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Due Date</label>
        <input type="date" name="due_date" class="form-control" 
               value="<?= htmlspecialchars($prefill_due) ?>">
      </div>

      <!-- Frequency -->
      <div class="mb-4">
        <label class="form-label fw-semibold">Frequency</label>
        <div class="row g-2">
          <div class="col-12 col-md-6">
            <select name="frequency" class="form-select h-100">
              <option value="none" <?= $prefill_freq==='none' ? 'selected' : '' ?>>One-time</option>
              <option value="monthly" <?= $prefill_freq==='monthly' ? 'selected' : '' ?>>Monthly</option>
              <option value="quarterly" <?= $prefill_freq==='quarterly' ? 'selected' : '' ?>>Quarterly</option>
              <option value="yearly" <?= $prefill_freq==='yearly' ? 'selected' : '' ?>>Yearly</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <input type="text" name="frequency_custom" class="form-control h-100"
                   placeholder="Custom frequency (optional)">
          </div>
        </div>
        <small class="text-muted mt-2 d-block">Choose a preset frequency or enter a custom one</small>
      </div>

      <!-- Action Buttons -->
      <div class="row g-3 mt-4">
        <div class="col-12 col-md-4">
          <button type="submit" class="btn btn-success w-100 add">
            <span class="d-none d-md-inline">Add Task</span>
            <span class="d-inline d-md-none">Add</span>
          </button>
        </div>
        <div class="col-12 col-md-4">
          <a href="../dashboard.php" class="btn btn-secondary w-100 back">
            <span class="d-none d-md-inline">Back to Dashboard</span>
            <span class="d-inline d-md-none"></span>
          </a>
        </div>
        <div class="col-12 col-md-4">
          <a href="../maintenance/ai_troubleshooter.php" class="btn btn-outline-secondary w-100 back-ai">
            <span class="d-none d-md-inline">AI Troubleshooter</span>
            <span class="d-inline d-md-none">AI Help</span>
          </a>
        </div>
      </div>
      
      <!-- Cancel Link for Mobile -->
      <div class="text-center mt-4 d-block d-md-none">
        <a href="javascript:history.back()" class="text-muted text-decoration-none">
          ← Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Enhance mobile experience
document.addEventListener('DOMContentLoaded', function() {
    // Make date input easier to use on mobile
    const dateInput = document.querySelector('input[type="date"]');
    if (dateInput && /Mobile|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        // Set minimum date to today for mobile users
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        // Add a clear button for mobile
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'btn btn-sm btn-outline-secondary mt-2';
        clearBtn.textContent = 'Clear Date';
        clearBtn.style.fontSize = '0.8rem';
        clearBtn.addEventListener('click', function() {
            dateInput.value = '';
        });
        dateInput.parentNode.appendChild(clearBtn);
    }
    
    // Auto-focus on description field on desktop
    if (!/Mobile|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        document.querySelector('textarea[name="description"]').focus();
    }
});
</script>
</body>
</html>
