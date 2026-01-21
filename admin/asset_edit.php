<?php
/**
 * Edit Asset Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice UPDATE queries
 * - Learn to pre-populate forms with existing data
 * - Handle URL parameters (GET)
 * - Understand record validation (does asset exist?)
 * - Practice form security with existing records
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can edit assets

require_once '../config/db_config.php';
require_once '../includes/layout.php';

$success_message = '';
$error_message = '';
$asset = null;

// ============================================
// GET ASSET ID FROM URL
// ============================================
// LEARNING NOTE: We get the asset ID from the URL parameter ?id=123

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: assets_list.php');
    exit();
}

$asset_id = (int)$_GET['id'];

// ============================================
// FETCH EXISTING ASSET DATA
// ============================================
// LEARNING NOTE: We need to get current data to pre-populate the form

$stmt = $conn->prepare("SELECT * FROM assets WHERE asset_id = ?");
$stmt->bind_param("i", $asset_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Asset not found
    header('Location: assets_list.php');
    exit();
}

$asset = $result->fetch_assoc();
$stmt->close();

// ============================================
// HANDLE FORM SUBMISSION (UPDATE)
// ============================================
// LEARNING NOTE: Check if form was submitted to update the asset

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get and sanitize form data
    $asset_type = trim($_POST['asset_type']);
    $manufacturer = trim($_POST['manufacturer']);
    $model = trim($_POST['model']);
    $serial_number = trim($_POST['serial_number']);
    $specifications = trim($_POST['specifications']);
    $status = trim($_POST['status']);
    $location = trim($_POST['location']);
    $notes = trim($_POST['notes']);
    
    // ============================================
    // VALIDATION
    // ============================================
    
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
        // Check if serial number exists for OTHER assets (not this one)
        $check_stmt = $conn->prepare("SELECT asset_id FROM assets WHERE serial_number = ? AND asset_id != ?");
        $check_stmt->bind_param("si", $serial_number, $asset_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Serial number already exists for another asset.";
        }
        $check_stmt->close();
    }
    
    // ============================================
    // UPDATE DATABASE
    // ============================================
    
    if (empty($errors)) {
        
        $stmt = $conn->prepare("UPDATE assets SET asset_type = ?, manufacturer = ?, model = ?, serial_number = ?, specifications = ?, status = ?, location = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE asset_id = ?");
        
        $stmt->bind_param("ssssssssi", $asset_type, $manufacturer, $model, $serial_number, $specifications, $status, $location, $notes, $asset_id);
        
        if ($stmt->execute()) {
            $success_message = "Asset updated successfully!";
            
            // Update the $asset array with new data for display
            $asset['asset_type'] = $asset_type;
            $asset['manufacturer'] = $manufacturer;
            $asset['model'] = $model;
            $asset['serial_number'] = $serial_number;
            $asset['specifications'] = $specifications;
            $asset['status'] = $status;
            $asset['location'] = $location;
            $asset['notes'] = $notes;
            
        } else {
            $error_message = "Error updating asset: " . $conn->error;
        }
        
        $stmt->close();
        
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// ============================================
// PREPARE CONTENT FOR TEMPLATE
// ============================================

ob_start();
?>

<!-- Page Header -->
<?php echo renderPageHeader('Edit Asset #' . $asset['asset_id'], 'pencil', 
    '<a href="assets_list.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Assets List
    </a>'
); ?>

<!-- Success/Error Messages -->
<?php echo renderAlert($success_message, 'success'); ?>
<?php echo renderAlert($error_message, 'danger'); ?>

<!-- Edit Asset Form -->
<?php
$formContent = '
<form method="POST" action="">
    <div class="row">
        
        <!-- Asset Type -->
        <div class="col-md-6 mb-3">
            <label for="asset_type" class="form-label">Asset Type <span class="text-danger">*</span></label>
            <select class="form-select" id="asset_type" name="asset_type" required>
                <option value="">Select Asset Type</option>
                <option value="Server" ' . ($asset['asset_type'] === 'Server' ? 'selected' : '') . '>Server</option>
                <option value="Security Appliance" ' . ($asset['asset_type'] === 'Security Appliance' ? 'selected' : '') . '>Security Appliance</option>
                <option value="Network Device" ' . ($asset['asset_type'] === 'Network Device' ? 'selected' : '') . '>Network Device</option>
                <option value="Other" ' . ($asset['asset_type'] === 'Other' ? 'selected' : '') . '>Other</option>
            </select>
        </div>

        <!-- Status -->
        <div class="col-md-6 mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" id="status" name="status" required>
                <option value="Available" ' . ($asset['status'] === 'Available' ? 'selected' : '') . '>Available</option>
                <option value="On Loan" ' . ($asset['status'] === 'On Loan' ? 'selected' : '') . '>On Loan</option>
                <option value="Maintenance" ' . ($asset['status'] === 'Maintenance' ? 'selected' : '') . '>Maintenance</option>
                <option value="Retired" ' . ($asset['status'] === 'Retired' ? 'selected' : '') . '>Retired</option>
            </select>
        </div>

        <!-- Manufacturer -->
        <div class="col-md-6 mb-3">
            <label for="manufacturer" class="form-label">Manufacturer</label>
            <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                   placeholder="e.g., Dell, HP, Cisco"
                   value="' . htmlspecialchars($asset['manufacturer'] ?? '') . '">
        </div>

        <!-- Model -->
        <div class="col-md-6 mb-3">
            <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="model" name="model" 
                   placeholder="e.g., PowerEdge R740, ASA 5516-X"
                   value="' . htmlspecialchars($asset['model'] ?? '') . '" required>
        </div>

        <!-- Serial Number -->
        <div class="col-md-6 mb-3">
            <label for="serial_number" class="form-label">Serial Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="serial_number" name="serial_number" 
                   placeholder="Unique serial number"
                   value="' . htmlspecialchars($asset['serial_number'] ?? '') . '" required>
            <div class="form-text">Must be unique across all assets</div>
        </div>

        <!-- Location -->
        <div class="col-md-6 mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" 
                   placeholder="e.g., Server Room A, Storage Room"
                   value="' . htmlspecialchars($asset['location'] ?? '') . '">
        </div>

        <!-- Specifications -->
        <div class="col-md-12 mb-3">
            <label for="specifications" class="form-label">Specifications</label>
            <textarea class="form-control" id="specifications" name="specifications" rows="3"
                      placeholder="e.g., Intel Xeon, 64GB RAM, 2TB SSD, 10Gbps NIC">' . htmlspecialchars($asset['specifications'] ?? '') . '</textarea>
            <div class="form-text">Include CPU, RAM, storage, network specs, etc.</div>
        </div>

        <!-- Notes -->
        <div class="col-md-12 mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"
                      placeholder="Additional information, warranty details, etc.">' . htmlspecialchars($asset['notes'] ?? '') . '</textarea>
        </div>

    </div>

    <!-- Asset Info -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Asset Information</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Asset ID:</small><br>
                            <strong>' . $asset['asset_id'] . '</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Created:</small><br>
                            <strong>' . date('Y-m-d H:i', strtotime($asset['created_at'])) . '</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Last Updated:</small><br>
                            <strong>' . date('Y-m-d H:i', strtotime($asset['updated_at'])) . '</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row mt-3">
        <div class="col-12">
            <hr>
            <div class="d-flex justify-content-between">
                <div>
                    <span class="text-danger">*</span> Required fields
                </div>
                <div>
                    <a href="assets_list.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update Asset
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
    setTimeout(function() {
        var alerts = document.querySelectorAll(".alert-success");
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>';

echo renderLayout('Edit Asset', $content, 'assets_list', '', $additionalJS);
?>

<!-- 
============================================
LEARNING EXERCISE #1
============================================
TODO: Add change tracking
Show what fields were modified and when

HINT:
1. Store original values in hidden fields
2. Compare original vs new values on submit
3. Log changes to an audit table
4. Display "Last modified by" information

============================================
LEARNING EXERCISE #2
============================================
TODO: Add confirmation for status changes
Warn user when changing status from/to certain values

HINT:
1. Add JavaScript to detect status changes
2. Show confirmation dialog for critical changes
3. Explain implications (e.g., "Retiring will make asset unavailable")

============================================
WHAT YOU'VE LEARNED
============================================

✅ UPDATE queries with WHERE conditions
✅ Form pre-population with existing data
✅ URL parameter handling ($_GET['id'])
✅ Record existence validation
✅ Duplicate checking excluding current record
✅ Displaying asset metadata (created/updated dates)
✅ Professional edit form layout

============================================
TESTING INSTRUCTIONS
============================================

1. Go to assets list
2. Click edit button (pencil icon) on any asset
3. Should see form pre-filled with current data
4. Modify some fields and submit
5. Should show success message
6. Verify changes in assets list
7. Try changing serial number to existing one - should show error

============================================
NEXT STEPS
============================================

After testing this page:
1. Next: Create asset_delete.php (DELETE operation)
2. Then: Complete CRUD operations
3. Move to loan management system

You're almost done with asset management!
-->