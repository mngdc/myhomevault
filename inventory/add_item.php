<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");

$prefill = [];
$prefill['name'] = $_GET['name'] ?? $_GET['item_name'] ?? ($_POST['suggested_item'] ?? '');
$prefill['category'] = $_GET['category'] ?? ($_POST['category'] ?? ''); 
$prefill['description'] = $_GET['description'] ?? ($_POST['description'] ?? '');
$prefill['purchase_date'] = $_GET['purchase_date'] ?? '';
$prefill['warranty_expiration'] = $_GET['warranty_expiration'] ?? '';
$prefill['estimated_value'] = $_GET['estimated_value'] ?? '';
$prefill['serial_number'] = $_GET['serial_number'] ?? '';
$prefill['model_number'] = $_GET['model_number'] ?? '';
$prefill['location'] = $_GET['location'] ?? '';
$prefill['purchase_price'] = $_GET['purchase_price'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Item - MyHomeVault</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/inventoryAddItem.css">
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
        margin-bottom: 15px;
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
    
    /* Make form elements touch-friendly */
    .form-control, .form-select, .btn {
        min-height: 44px;
    }
    
    /* Ensure date input is properly sized on mobile */
    input[type="date"] {
        -webkit-appearance: none;
        min-height: 44px;
    }
    
    /* File upload styling for mobile */
    input[type="file"] {
        padding: 8px;
    }
    
    /* Adjust spacing */
    .mb-2 {
        margin-bottom: 1rem !important;
    }
    
    .mb-3 {
        margin-bottom: 1.5rem !important;
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
}

/* Add icons to buttons for better mobile UX */
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

/* Remove number input spinners on mobile */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}
</style>
</head>
<body class="bg">
<div class="container mt-4 mt-md-5 col-12 col-md-8 col-lg-6">
  <div class="card p-4 p-md-5 shadow">
    <h4 class="mb-4">📦 Add New Inventory Item</h4>
    <form method="POST" action="save_item.php" enctype="multipart/form-data">
      <!-- Basic Information -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Item Name</label>
        <input type="text" name="name" class="form-control" 
               placeholder="Enter item name" 
               required 
               value="<?= htmlspecialchars($prefill['name']) ?>">
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-semibold">Category</label>
        <input type="text" name="category" class="form-control" 
               placeholder="Enter category"
               value="<?= htmlspecialchars($prefill['category']) ?>">
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control" 
                  placeholder="Describe the item..."
                  rows="4"><?= htmlspecialchars($prefill['description']) ?></textarea>
      </div>

      <!-- Dates -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Purchase Date</label>
        <input type="date" name="purchase_date" class="form-control" 
               value="<?= htmlspecialchars($prefill['purchase_date']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Warranty Expiration</label>
        <input type="date" name="warranty_expiration" class="form-control" 
               value="<?= htmlspecialchars($prefill['warranty_expiration']) ?>">
      </div>

      <!-- Numbers and Details -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Estimated Value</label>
        <input type="number" step="0.01" name="estimated_value" class="form-control" 
               placeholder="Enter estimated value"
               value="<?= htmlspecialchars($prefill['estimated_value']) ?>">
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-semibold">Serial Number</label>
        <input type="text" name="serial_number" class="form-control" 
               placeholder="Enter serial number"
               value="<?= htmlspecialchars($prefill['serial_number']) ?>">
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-semibold">Model Number</label>
        <input type="text" name="model_number" class="form-control" 
               placeholder="Enter model number"
               value="<?= htmlspecialchars($prefill['model_number']) ?>">
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-semibold">Location</label>
        <input type="text" name="location" class="form-control" 
               placeholder="Enter location"
               value="<?= htmlspecialchars($prefill['location']) ?>">
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-semibold">Purchase Price</label>
        <input type="number" step="0.01" name="purchase_price" class="form-control" 
               placeholder="Enter purchase price"
               value="<?= htmlspecialchars($prefill['purchase_price']) ?>">
      </div>

      <!-- File Uploads -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Upload Image</label>
        <input type="file" name="image" class="form-control" 
               accept="image/*">
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Upload Document</label>
        <input type="file" name="document" class="form-control" 
               accept=".pdf,.doc,.docx,.txt">
      </div>

      <!-- Action Buttons -->
      <div class="row g-3 mt-4">
        <div class="col-12 col-md-4">
          <button type="submit" class="btn btn-primary w-100 add">
            <span class="d-none d-md-inline">Add Item</span>
          </button>
        </div>
        <div class="col-12 col-md-4">
          <a href="../dashboard.php" class="btn btn-secondary w-100 back">
            <span class="d-none d-md-inline">Back to Dashboard</span>
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
    // Make date inputs easier to use on mobile
    const dateInputs = document.querySelectorAll('input[type="date"]');
    if (dateInputs.length > 0 && /Mobile|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        dateInputs.forEach(dateInput => {
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
        });
    }
    
    // Auto-focus on name field on desktop
    if (!/Mobile|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        document.querySelector('input[name="name"]').focus();
    }
});
</script>
</body>
</html>
