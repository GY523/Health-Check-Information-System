<?php
/**
 * Record New Loan Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice INSERT with multiple tables
 * - Handle date validation
 * - Learn business logic (asset availability)
 * - Understand transaction concepts
 * - Practice form handling with dropdowns
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can record loans

require_once '../config/db_config.php';

$success_message = '';
$error_message = '';

// ============================================
// HANDLE FORM SUBMISSION
// ============================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get and sanitize form data
    $asset_id = (int)$_POST['asset_id'];
    $customer_company = trim($_POST['customer_company']);
    $customer_email = trim($_POST['customer_email']);
    $loan_purpose = trim($_POST['loan_purpose']);
    $loan_start_date = $_POST['loan_start_date'];
    $expected_return_date = $_POST['expected_return_date'];
    $internal_notes = trim($_POST['internal_notes']);
    
    // ============================================
    // VALIDATION
    // ============================================
    
    $errors = [];
    
    if (empty($asset_id)) {
        $errors[] = "Please select an asset.";
    }
    
    if (empty($customer_company)) {
        $errors[] = "Customer company is required.";
    }
    
    if (empty($loan_purpose)) {
        $errors[] = "Loan purpose is required.";
    }
    
    if (empty($loan_start_date)) {
        $errors[] = "Loan start date is required.";
    }
    
    if (empty($expected_return_date)) {
        $errors[] = "Expected return date is required.";
    }
    
    // Validate dates
    if (!empty($loan_start_date) && !empty($expected_return_date)) {
        if (strtotime($expected_return_date) <= strtotime($loan_start_date)) {
            $errors[] = "Return date must be after start date.";
        }
    }
    
    // Check if asset is available
    if (!empty($asset_id)) {
        $asset_check = $conn->prepare("SELECT status FROM assets WHERE asset_id = ?");
        $asset_check->bind_param("i", $asset_id);
        $asset_check->execute();
        $asset_result = $asset_check->get_result();
        
        if ($asset_result->num_rows == 0) {
            $errors[] = "Selected asset not found.";
        } else {
            $asset_data = $asset_result->fetch_assoc();
            if ($asset_data['status'] !== 'Available') {
                $errors[] = "Selected asset is not available (Status: " . $asset_data['status'] . ").";
            }
        }
        $asset_check->close();
    }
    
    // ============================================
    // INSERT LOAN RECORD
    // ============================================
    
    if (empty($errors)) {
        
        // Start transaction for data consistency
        $conn->begin_transaction();
        
        try {
            // Insert loan record
            $stmt = $conn->prepare("INSERT INTO loans (asset_id, created_by_user_id, request_date, loan_start_date, expected_return_date, loan_purpose, customer_company, customer_email, status, admin_notes) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, 'Active', ?)");
            
            $stmt->bind_param("iissssss", $asset_id, getCurrentUserId(), $loan_start_date, $expected_return_date, $loan_purpose, $customer_company, $customer_email, $internal_notes);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating loan record: " . $stmt->error);
            }
            
            $loan_id = $conn->insert_id;
            $stmt->close();
            
            // Update asset status to 'On Loan'
            $update_asset = $conn->prepare("UPDATE assets SET status = 'On Loan', updated_at = NOW() WHERE asset_id = ?");
            $update_asset->bind_param("i", $asset_id);
            
            if (!$update_asset->execute()) {
                throw new Exception("Error updating asset status: " . $update_asset->error);
            }
            $update_asset->close();
            
            // Commit transaction
            $conn->commit();
            
            $success_message = "Loan recorded successfully! Loan ID: " . $loan_id;
            
            // Clear form data
            $asset_id = $customer_company = $customer_email = $loan_purpose = $loan_start_date = $expected_return_date = $internal_notes = '';
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error_message = $e->getMessage();
        }
        
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// ============================================
// GET AVAILABLE ASSETS FOR DROPDOWN
// ============================================

$available_assets_query = "SELECT asset_id, asset_type, manufacturer, model, serial_number FROM assets WHERE status = 'Available' ORDER BY asset_type, manufacturer, model";
$available_assets_result = $conn->query($available_assets_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record New Loan - Server Loaning System</title>
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
                    Welcome, <?php echo getCurrentUserName(); ?> (<?php echo ucfirst($_SESSION['role']); ?>)
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
                        <h5><i class="bi bi-list"></i> Menu</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="dashboard.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="loan_record.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-plus-circle"></i> Record New Loan
                        </a>
                        <a href="loans_active.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-left-right"></i> Active Loans
                        </a>
                        <a href="loans_history.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-clock-history"></i> Loan History
                        </a>
                        <hr>
                        <a href="assets_list.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-server"></i> View Assets
                        </a>
                        <a href="asset_add.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle"></i> Add Asset
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-plus-circle"></i> Record New Loan</h2>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
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

                <!-- Record Loan Form -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-form"></i> Loan Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                
                                <!-- Asset Selection -->
                                <div class="col-md-12 mb-3">
                                    <label for="asset_id" class="form-label">Select Asset <span class="text-danger">*</span></label>
                                    <select class="form-select" id="asset_id" name="asset_id" required>
                                        <option value="">Choose an available asset...</option>
                                        <?php while ($asset = $available_assets_result->fetch_assoc()): ?>
                                        <option value="<?php echo $asset['asset_id']; ?>" 
                                                <?php echo (isset($asset_id) && $asset_id == $asset['asset_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($asset['asset_type'] . ' - ' . $asset['manufacturer'] . ' ' . $asset['model'] . ' (SN: ' . $asset['serial_number'] . ')'); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <div class="form-text">Only available assets are shown</div>
                                </div>

                                <!-- Customer Company -->
                                <div class="col-md-6 mb-3">
                                    <label for="customer_company" class="form-label">Customer Company <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="customer_company" name="customer_company" 
                                           placeholder="e.g., ABC Corporation"
                                           value="<?php echo isset($customer_company) ? htmlspecialchars($customer_company) : ''; ?>" required>
                                </div>

                                <!-- Customer Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="customer_email" class="form-label">Customer Email</label>
                                    <input type="email" class="form-control" id="customer_email" name="customer_email" 
                                           placeholder="presales@company.com"
                                           value="<?php echo isset($customer_email) ? htmlspecialchars($customer_email) : ''; ?>">
                                </div>

                                <!-- Loan Purpose -->
                                <div class="col-md-12 mb-3">
                                    <label for="loan_purpose" class="form-label">Loan Purpose <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="loan_purpose" name="loan_purpose" rows="2" 
                                              placeholder="e.g., PoC demonstration for new client, Product evaluation, Demo setup"
                                              required><?php echo isset($loan_purpose) ? htmlspecialchars($loan_purpose) : ''; ?></textarea>
                                </div>

                                <!-- Loan Start Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="loan_start_date" class="form-label">Loan Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="loan_start_date" name="loan_start_date" 
                                           value="<?php echo isset($loan_start_date) ? $loan_start_date : date('Y-m-d'); ?>" required>
                                </div>

                                <!-- Expected Return Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="expected_return_date" class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="expected_return_date" name="expected_return_date" 
                                           value="<?php echo isset($expected_return_date) ? $expected_return_date : date('Y-m-d', strtotime('+30 days')); ?>" required>
                                </div>

                                <!-- Internal Notes -->
                                <div class="col-md-12 mb-3">
                                    <label for="internal_notes" class="form-label">Internal Notes</label>
                                    <textarea class="form-control" id="internal_notes" name="internal_notes" rows="3"
                                              placeholder="Internal tracking notes, special instructions, etc."><?php echo isset($internal_notes) ? htmlspecialchars($internal_notes) : ''; ?></textarea>
                                    <div class="form-text">For internal use only - not visible to customer</div>
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
                                                <i class="bi bi-check-circle"></i> Record Loan
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
        
        // Set minimum date to today for date inputs
        document.getElementById('loan_start_date').min = new Date().toISOString().split('T')[0];
        document.getElementById('expected_return_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>

<!-- 
============================================
WHAT YOU'VE LEARNED
============================================

✅ INSERT with multiple related tables
✅ Database transactions (begin, commit, rollback)
✅ Business logic validation (asset availability)
✅ Date validation and handling
✅ Dropdown population from database
✅ Form data persistence after errors
✅ Professional loan recording interface

============================================
TESTING INSTRUCTIONS
============================================

1. Make sure you've run the database schema updates
2. Login as admin or engineer
3. Click "Record New Loan" in sidebar
4. Fill out form with test data:
   - Select an available asset
   - Company: "Test Corp"
   - Email: "test@testcorp.com"
   - Purpose: "PoC demonstration"
   - Dates: Today to 30 days from now
5. Submit - should show success message
6. Check assets list - asset should be "On Loan"
7. Try selecting unavailable asset - should show error

============================================
NEXT STEPS
============================================

After testing this page:
1. Next: Create loans_active.php (view active loans)
2. Then: Create loans_history.php (search loan history)
3. Finally: Create loan return processing

You're building the complete loan workflow!
-->