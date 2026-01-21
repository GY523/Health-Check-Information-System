<?php
/**
 * Add Asset Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice INSERT queries
 * - Handle form validation
 * - Learn about form security (CSRF protection)
 * - Understand success/error message handling
 * - Practice redirects after form submission
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can add assets

require_once '../config/db_config.php';
require_once '../includes/layout.php';

$success_message = '';
$error_message = '';

// ============================================
// HANDLE FORM SUBMISSION
// ============================================
// LEARNING NOTE: Always check if form was submitted via POST

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get and sanitize form data
    $asset_type = trim($_POST['asset_type']);
    $manufacturer = trim($_POST['manufacturer']);
    $model = trim($_POST['model']);
    $serial_number = trim($_POST['serial_number']);
    $specifications = trim($_POST['specifications']);
    $location = trim($_POST['location']);
    $notes = trim($_POST['notes']);
    
    // ============================================
    // VALIDATION
    // ============================================
    // LEARNING NOTE: Always validate user input before database operations
    
    $errors = [];
    
    if (empty($asset_type)) {
        $errors[] = "Asset type is required.";
    }
    
    if (empty($model)) {
        $errors[] = "Model is required.";
    }
    
    if (empty($serial_number)) {
        $errors[] = "Serial number is required.";
    } else {
        // Check if serial number already exists
        $check_stmt = $conn->prepare("SELECT asset_id FROM assets WHERE serial_number = ?");
        $check_stmt->bind_param("s", $serial_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Serial number already exists. Please use a unique serial number.";
        }
        $check_stmt->close();
    }
    
    // ============================================
    // INSERT INTO DATABASE
    // ============================================
    // LEARNING NOTE: Only proceed if no validation errors
    
    if (empty($errors)) {
        
        $stmt = $conn->prepare("INSERT INTO assets (asset_type, manufacturer, model, serial_number, specifications, location, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Available')");
        
        $stmt->bind_param("sssssss", $asset_type, $manufacturer, $model, $serial_number, $specifications, $location, $notes);
        
        if ($stmt->execute()) {
            $new_asset_id = $conn->insert_id; // Get the ID of the newly inserted asset
            $success_message = "Asset added successfully! Asset ID: " . $new_asset_id;
            
            // Clear form data after successful submission
            $asset_type = $manufacturer = $model = $serial_number = $specifications = $location = $notes = '';
            
        } else {
            $error_message = "Error adding asset: " . $conn->error;
        }
        
        $stmt->close();
        
    } else {
        // Display validation errors
        $error_message = implode("<br>", $errors);
    }
}

// ============================================
// PREPARE CONTENT FOR TEMPLATE
// ============================================

ob_start();
?>

<!-- Page Header -->
<?php echo renderPageHeader('Add New Asset', 'plus-circle', 
    '<a href="assets_list.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Assets List
    </a>'
); ?>

<!-- Success/Error Messages -->
<?php echo renderAlert($success_message, 'success'); ?>
<?php echo renderAlert($error_message, 'danger'); ?>

<!-- Add Asset Form -->
<?php
$formContent = '
<form method="POST" action="">
    <div class="row">

                                
        <!-- Asset Type -->
        <div class="col-md-6 mb-3">
            <label for="asset_type" class="form-label">Asset Type <span class="text-danger">*</span></label>
            <select class="form-select" id="asset_type" name="asset_type" required>
                <option value="">Select Asset Type</option>
                <option value="Server" ' . ((isset($asset_type) && $asset_type === 'Server') ? 'selected' : '') . '>Server</option>
                <option value="Security Appliance" ' . ((isset($asset_type) && $asset_type === 'Security Appliance') ? 'selected' : '') . '>Security Appliance</option>
                <option value="Network Device" ' . ((isset($asset_type) && $asset_type === 'Network Device') ? 'selected' : '') . '>Network Device</option>
                <option value="Other" ' . ((isset($asset_type) && $asset_type === 'Other') ? 'selected' : '') . '>Other</option>
            </select>
        </div>

        <!-- Manufacturer -->
        <div class="col-md-6 mb-3">
            <label for="manufacturer" class="form-label">Manufacturer</label>
            <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                   placeholder="e.g., Dell, HP, Cisco"
                   value="' . htmlspecialchars($manufacturer ?? '') . '">
        </div>

        <!-- Model -->
        <div class="col-md-6 mb-3">
            <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="model" name="model" 
                   placeholder="e.g., PowerEdge R740, ASA 5516-X"
                   value="' . htmlspecialchars($model ?? '') . '" required>
        </div>

        <!-- Serial Number -->
        <div class="col-md-6 mb-3">
            <label for="serial_number" class="form-label">Serial Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="serial_number" name="serial_number" 
                   placeholder="Unique serial number"
                   value="' . htmlspecialchars($serial_number ?? '') . '" required>
            <div class="form-text">Must be unique across all assets</div>
        </div>

        <!-- Location -->
        <div class="col-md-6 mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" 
                   placeholder="e.g., Server Room A, Storage Room"
                   value="' . htmlspecialchars($location ?? '') . '">
        </div>

        <!-- Specifications -->
        <div class="col-md-12 mb-3">
            <label for="specifications" class="form-label">Specifications</label>
            <textarea class="form-control" id="specifications" name="specifications" rows="3"
                      placeholder="e.g., Intel Xeon, 64GB RAM, 2TB SSD, 10Gbps NIC">' . htmlspecialchars($specifications ?? '') . '</textarea>
            <div class="form-text">Include CPU, RAM, storage, network specs, etc.</div>
        </div>

        <!-- Notes -->
        <div class="col-md-12 mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"
                      placeholder="Additional information, warranty details, etc.">' . htmlspecialchars($notes ?? '') . '</textarea>
        </div>

    </div>

    <!-- Form Actions -->
    <div class="row">
        <div class="col-12">
            <hr>
            <div class="d-flex justify-content-between">
                <div>
                    <span class="text-danger">*</span> Required fields
                </div>
                <div>
                    <button type="reset" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-clockwise"></i> Reset Form
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Asset
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>';

echo renderCard('Asset Information', $formContent, 'form');
?>

<?php
$content = ob_get_clean();

$additionalJS = '
<script>
    // Auto-hide success alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll(".alert-success");
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>';

echo renderLayout('Add Asset', $content, 'asset_add', '', $additionalJS);
?>

<!-- 
============================================
LEARNING EXERCISE #1
============================================
TODO: Add client-side validation with JavaScript
Validate form before submission to improve user experience

HINT:
1. Add JavaScript validation for required fields
2. Check serial number format (alphanumeric)
3. Validate specifications length
4. Show validation messages in real-time

============================================
LEARNING EXERCISE #2
============================================
TODO: Add file upload for asset photos
Allow users to upload images of the assets

HINT:
1. Add <input type="file" accept="image/*"> to form
2. Create uploads/ directory
3. Validate file type and size
4. Store filename in database
5. Display images in asset list

============================================
WHAT YOU'VE LEARNED
============================================

✅ INSERT queries with prepared statements
✅ Form validation (server-side)
✅ Duplicate checking (serial number uniqueness)
✅ Success/error message handling
✅ Form data persistence (keeping values after errors)
✅ Bootstrap form styling and layout
✅ Auto-dismissing alerts with JavaScript

============================================
TESTING INSTRUCTIONS
============================================

1. Login as admin
2. Click "Add Asset" in sidebar or from assets list
3. Fill out the form with test data:
   - Type: Server
   - Manufacturer: Dell
   - Model: Test Server R123
   - Serial: TEST001
   - Location: Test Lab
4. Submit form - should show success message
5. Try submitting with duplicate serial number - should show error
6. Check assets list - new asset should appear

============================================
NEXT STEPS
============================================

After testing this page:
1. Next: Create asset_edit.php (UPDATE operation)
2. Then: Create asset_delete.php (DELETE operation)
3. Finally: Complete CRUD operations for assets

You're building the complete asset management system!
-->