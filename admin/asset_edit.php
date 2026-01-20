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
requireAdmin(); // Only admins can edit assets

require_once '../config/db_config.php';

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Asset - Server Loaning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-server"></i> Server Loaning System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo getCurrentUserName(); ?> (Admin)
                </span>
                <a class="nav-link" href="../auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-list"></i> Admin Menu</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="dashboard.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="assets_list.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-server"></i> View Assets
                        </a>
                        <a href="asset_add.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle"></i> Add Asset
                        </a>
                        <a href="pending_approvals.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-clock-history"></i> Pending Approvals
                        </a>
                        <a href="active_loans.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-left-right"></i> Active Loans
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-pencil"></i> Edit Asset #<?php echo $asset['asset_id']; ?></h2>
                    <a href="assets_list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Assets List
                    </a>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Edit Asset Form -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-form"></i> Asset Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                
                                <!-- Asset Type -->
                                <div class="col-md-6 mb-3">
                                    <label for="asset_type" class="form-label">Asset Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="asset_type" name="asset_type" required>
                                        <option value="">Select Asset Type</option>
                                        <option value="Server" <?php echo $asset['asset_type'] === 'Server' ? 'selected' : ''; ?>>Server</option>
                                        <option value="Security Appliance" <?php echo $asset['asset_type'] === 'Security Appliance' ? 'selected' : ''; ?>>Security Appliance</option>
                                        <option value="Network Device" <?php echo $asset['asset_type'] === 'Network Device' ? 'selected' : ''; ?>>Network Device</option>
                                        <option value="Other" <?php echo $asset['asset_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Available" <?php echo $asset['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="On Loan" <?php echo $asset['status'] === 'On Loan' ? 'selected' : ''; ?>>On Loan</option>
                                        <option value="Maintenance" <?php echo $asset['status'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        <option value="Retired" <?php echo $asset['status'] === 'Retired' ? 'selected' : ''; ?>>Retired</option>
                                    </select>
                                </div>

                                <!-- Manufacturer -->
                                <div class="col-md-6 mb-3">
                                    <label for="manufacturer" class="form-label">Manufacturer</label>
                                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                                           placeholder="e.g., Dell, HP, Cisco"
                                           value="<?php echo htmlspecialchars($asset['manufacturer'] ?? ''); ?>">
                                </div>

                                <!-- Model -->
                                <div class="col-md-6 mb-3">
                                    <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="model" name="model" 
                                           placeholder="e.g., PowerEdge R740, ASA 5516-X"
                                           value="<?php echo htmlspecialchars($asset['model'] ?? ''); ?>" required>
                                </div>

                                <!-- Serial Number -->
                                <div class="col-md-6 mb-3">
                                    <label for="serial_number" class="form-label">Serial Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="serial_number" name="serial_number" 
                                           placeholder="Unique serial number"
                                           value="<?php echo htmlspecialchars($asset['serial_number'] ?? ''); ?>" required>
                                    <div class="form-text">Must be unique across all assets</div>
                                </div>

                                <!-- Location -->
                                <div class="col-md-6 mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           placeholder="e.g., Server Room A, Storage Room"
                                           value="<?php echo htmlspecialchars($asset['location'] ?? ''); ?>">
                                </div>

                                <!-- Specifications -->
                                <div class="col-md-12 mb-3">
                                    <label for="specifications" class="form-label">Specifications</label>
                                    <textarea class="form-control" id="specifications" name="specifications" rows="3"
                                              placeholder="e.g., Intel Xeon, 64GB RAM, 2TB SSD, 10Gbps NIC"><?php echo htmlspecialchars($asset['specifications'] ?? ''); ?></textarea>
                                    <div class="form-text">Include CPU, RAM, storage, network specs, etc.</div>
                                </div>

                                <!-- Notes -->
                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"
                                              placeholder="Additional information, warranty details, etc."><?php echo htmlspecialchars($asset['notes'] ?? ''); ?></textarea>
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
                                                    <strong><?php echo $asset['asset_id']; ?></strong>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Created:</small><br>
                                                    <strong><?php echo date('Y-m-d H:i', strtotime($asset['created_at'])); ?></strong>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Last Updated:</small><br>
                                                    <strong><?php echo date('Y-m-d H:i', strtotime($asset['updated_at'])); ?></strong>
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
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-hide success messages -->
    <script>
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

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