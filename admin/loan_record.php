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
require_once '../includes/layout.php';

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

// ============================================
// PREPARE CONTENT FOR TEMPLATE
// ============================================

ob_start();
?>

<!-- Page Header -->
<?php echo renderPageHeader('Record New Loan', 'plus-circle', 
    '<a href="dashboard.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>'
); ?>

<!-- Success/Error Messages -->
<?php echo renderAlert($success_message, 'success'); ?>
<?php echo renderAlert($error_message, 'danger'); ?>

<!-- Record Loan Form -->
<?php
$formContent = '
<form method="POST" action="">
    <div class="row">
        
        <!-- Asset Selection -->
        <div class="col-md-12 mb-3">
            <label for="asset_id" class="form-label">Select Asset <span class="text-danger">*</span></label>
            <select class="form-select" id="asset_id" name="asset_id" required>
                <option value="">Choose an available asset...</option>';

$available_assets_result->data_seek(0); // Reset result pointer
while ($asset = $available_assets_result->fetch_assoc()) {
    $selected = (isset($asset_id) && $asset_id == $asset['asset_id']) ? 'selected' : '';
    $formContent .= '<option value="' . $asset['asset_id'] . '" ' . $selected . '>';
    $formContent .= htmlspecialchars($asset['asset_type'] . ' - ' . $asset['manufacturer'] . ' ' . $asset['model'] . ' (SN: ' . $asset['serial_number'] . ')');
    $formContent .= '</option>';
}

$formContent .= '
            </select>
            <div class="form-text">Only available assets are shown</div>
        </div>

        <!-- Customer Company -->
        <div class="col-md-6 mb-3">
            <label for="customer_company" class="form-label">Customer Company <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="customer_company" name="customer_company" 
                   placeholder="e.g., ABC Corporation"
                   value="' . htmlspecialchars($customer_company ?? '') . '" required>
        </div>

        <!-- Customer Email -->
        <div class="col-md-6 mb-3">
            <label for="customer_email" class="form-label">Customer Email</label>
            <input type="email" class="form-control" id="customer_email" name="customer_email" 
                   placeholder="presales@company.com"
                   value="' . htmlspecialchars($customer_email ?? '') . '">
        </div>

        <!-- Loan Purpose -->
        <div class="col-md-12 mb-3">
            <label for="loan_purpose" class="form-label">Loan Purpose <span class="text-danger">*</span></label>
            <textarea class="form-control" id="loan_purpose" name="loan_purpose" rows="2" 
                      placeholder="e.g., PoC demonstration for new client, Product evaluation, Demo setup"
                      required>' . htmlspecialchars($loan_purpose ?? '') . '</textarea>
        </div>

        <!-- Loan Start Date -->
        <div class="col-md-6 mb-3">
            <label for="loan_start_date" class="form-label">Loan Start Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="loan_start_date" name="loan_start_date" 
                   value="' . ($loan_start_date ?? date('Y-m-d')) . '" required>
        </div>

        <!-- Expected Return Date -->
        <div class="col-md-6 mb-3">
            <label for="expected_return_date" class="form-label">Expected Return Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="expected_return_date" name="expected_return_date" 
                   value="' . ($expected_return_date ?? date('Y-m-d', strtotime('+30 days'))) . '" required>
        </div>

        <!-- Internal Notes -->
        <div class="col-md-12 mb-3">
            <label for="internal_notes" class="form-label">Internal Notes</label>
            <textarea class="form-control" id="internal_notes" name="internal_notes" rows="3"
                      placeholder="Internal tracking notes, special instructions, etc.">' . htmlspecialchars($internal_notes ?? '') . '</textarea>
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
</form>';

echo renderCard('Loan Information', $formContent, 'form');
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
    
    // Set minimum date to today for date inputs
    document.getElementById("loan_start_date").min = new Date().toISOString().split("T")[0];
    document.getElementById("expected_return_date").min = new Date().toISOString().split("T")[0];
</script>';

echo renderLayout('Record New Loan', $content, 'loan_record', '', $additionalJS);
?>

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
✅ Template system integration

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