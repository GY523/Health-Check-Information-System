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
requireAdmin(); // Only admins can add assets

require_once '../config/db_config.php';

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Asset - Server Loaning System</title>
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
                        <a href="asset_add.php" class="list-group-item list-group-item-action active">
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
                    <h2><i class="bi bi-plus-circle"></i> Add New Asset</h2>
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

                <!-- Add Asset Form -->
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
                                        <option value="Server" <?php echo (isset($asset_type) && $asset_type === 'Server') ? 'selected' : ''; ?>>Server</option>
                                        <option value="Security Appliance" <?php echo (isset($asset_type) && $asset_type === 'Security Appliance') ? 'selected' : ''; ?>>Security Appliance</option>
                                        <option value="Network Device" <?php echo (isset($asset_type) && $asset_type === 'Network Device') ? 'selected' : ''; ?>>Network Device</option>
                                        <option value="Other" <?php echo (isset($asset_type) && $asset_type === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>

                                <!-- Manufacturer -->
                                <div class="col-md-6 mb-3">
                                    <label for="manufacturer" class="form-label">Manufacturer</label>
                                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                                           placeholder="e.g., Dell, HP, Cisco"
                                           value="<?php echo isset($manufacturer) ? htmlspecialchars($manufacturer) : ''; ?>">
                                </div>

                                <!-- Model -->
                                <div class="col-md-6 mb-3">
                                    <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="model" name="model" 
                                           placeholder="e.g., PowerEdge R740, ASA 5516-X"
                                           value="<?php echo isset($model) ? htmlspecialchars($model) : ''; ?>" required>
                                </div>

                                <!-- Serial Number -->
                                <div class="col-md-6 mb-3">
                                    <label for="serial_number" class="form-label">Serial Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="serial_number" name="serial_number" 
                                           placeholder="Unique serial number"
                                           value="<?php echo isset($serial_number) ? htmlspecialchars($serial_number) : ''; ?>" required>
                                    <div class="form-text">Must be unique across all assets</div>
                                </div>

                                <!-- Location -->
                                <div class="col-md-6 mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           placeholder="e.g., Server Room A, Storage Room"
                                           value="<?php echo isset($location) ? htmlspecialchars($location) : ''; ?>">
                                </div>

                                <!-- Specifications -->
                                <div class="col-md-12 mb-3">
                                    <label for="specifications" class="form-label">Specifications</label>
                                    <textarea class="form-control" id="specifications" name="specifications" rows="3"
                                              placeholder="e.g., Intel Xeon, 64GB RAM, 2TB SSD, 10Gbps NIC"><?php echo isset($specifications) ? htmlspecialchars($specifications) : ''; ?></textarea>
                                    <div class="form-text">Include CPU, RAM, storage, network specs, etc.</div>
                                </div>

                                <!-- Notes -->
                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"
                                              placeholder="Additional information, warranty details, etc."><?php echo isset($notes) ? htmlspecialchars($notes) : ''; ?></textarea>
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
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-hide success messages -->
    <script>
        // Auto-hide success alerts after 5 seconds
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