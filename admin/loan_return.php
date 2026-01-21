<?php
/**
 * Loan Return Processing Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice UPDATE queries with multiple tables
 * - Handle business logic (return processing)
 * - Learn transaction management
 * - Implement status workflow transitions
 * - Practice form validation with existing data
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can process returns

require_once '../config/db_config.php';
require_once '../includes/layout.php';

$success_message = '';
$error_message = '';
$loan = null;

// ============================================
// GET LOAN ID FROM URL
// ============================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: loans_active.php');
    exit();
}

$loan_id = (int)$_GET['id'];

// ============================================
// FETCH LOAN DATA WITH JOINS
// ============================================

$stmt = $conn->prepare("SELECT 
    l.*,
    a.asset_type,
    a.manufacturer,
    a.model,
    a.serial_number,
    u.full_name as created_by_name
FROM loans l
JOIN assets a ON l.asset_id = a.asset_id
JOIN users u ON l.created_by_user_id = u.user_id
WHERE l.loan_id = ? AND l.status = 'Active'");

$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: loans_active.php');
    exit();
}

$loan = $result->fetch_assoc();
$stmt->close();

// ============================================
// HANDLE RETURN PROCESSING
// ============================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $return_date = $_POST['return_date'];
    $return_notes = trim($_POST['return_notes']);
    
    // Validation
    $errors = [];
    
    if (empty($return_date)) {
        $errors[] = "Return date is required.";
    }
    
    // Validate return date is not in future
    if (!empty($return_date) && strtotime($return_date) > time()) {
        $errors[] = "Return date cannot be in the future.";
    }
    
    if (empty($errors)) {
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update loan status to 'Returned'
            $update_loan = $conn->prepare("UPDATE loans SET 
                status = 'Returned', 
                actual_return_date = ?, 
                return_notes = ?,
                updated_at = NOW() 
                WHERE loan_id = ?");
            
            $update_loan->bind_param("ssi", $return_date, $return_notes, $loan_id);
            
            if (!$update_loan->execute()) {
                throw new Exception("Error updating loan: " . $update_loan->error);
            }
            $update_loan->close();
            
            // Update asset status back to 'Available'
            $update_asset = $conn->prepare("UPDATE assets SET 
                status = 'Available', 
                updated_at = NOW() 
                WHERE asset_id = ?");
            
            $update_asset->bind_param("i", $loan['asset_id']);
            
            if (!$update_asset->execute()) {
                throw new Exception("Error updating asset: " . $update_asset->error);
            }
            $update_asset->close();
            
            // Commit transaction
            $conn->commit();
            
            $success_message = "Loan return processed successfully! Asset is now available.";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = $e->getMessage();
        }
        
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
<?php echo renderPageHeader('Process Return - Loan #' . $loan['loan_id'], 'arrow-return-left', 
    '<a href="loans_active.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Active Loans
    </a>'
); ?>

<!-- Success/Error Messages -->
<?php echo renderAlert($success_message, 'success'); ?>
<?php echo renderAlert($error_message, 'danger'); ?>

<div class="row">
    <!-- Return Form -->
    <div class="col-md-8">
        <?php if (empty($success_message)): ?>
        <?php
        $formContent = '
        <form method="POST" action="">
            <div class="row">
                
                <!-- Return Date -->
                <div class="col-md-12 mb-3">
                    <label for="return_date" class="form-label">Return Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="return_date" name="return_date" 
                           value="' . date('Y-m-d') . '" required>
                </div>

                <!-- Return Notes -->
                <div class="col-md-12 mb-3">
                    <label for="return_notes" class="form-label">Return Notes</label>
                    <textarea class="form-control" id="return_notes" name="return_notes" rows="4"
                              placeholder="Any issues, damage, or notes about the return condition..."></textarea>
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
                            <a href="loans_active.php" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Process Return
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>';
        
        echo renderCard('Return Processing', $formContent, 'arrow-return-left');
        ?>
        <?php else: ?>
        <?php
        $successContent = '
        <div class="text-center py-4">
            <i class="bi bi-check-circle-fill display-1 text-success"></i>
            <h4 class="text-success mt-3">Return Processed Successfully!</h4>
            <p class="text-muted">The asset has been returned and is now available for new loans.</p>
            <div class="mt-4">
                <a href="loans_active.php" class="btn btn-primary me-2">
                    <i class="bi bi-arrow-left-right"></i> View Active Loans
                </a>
                <a href="assets_list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-server"></i> View Assets
                </a>
            </div>
        </div>';
        
        echo renderCard('Return Complete', $successContent, 'check-circle');
        ?>
        <?php endif; ?>
    </div>
    
    <!-- Loan Details -->
    <div class="col-md-4">
        <?php
        $is_overdue = strtotime($loan['expected_return_date']) < time();
        $days_diff = floor((time() - strtotime($loan['expected_return_date'])) / (60 * 60 * 24));
        
        $detailsContent = '
        <table class="table table-sm">
            <tr>
                <td><strong>Loan ID:</strong></td>
                <td>#' . $loan['loan_id'] . '</td>
            </tr>
            <tr>
                <td><strong>Customer:</strong></td>
                <td>' . htmlspecialchars($loan['customer_company']) . '</td>
            </tr>
            <tr>
                <td><strong>Asset:</strong></td>
                <td>
                    <span class="badge bg-secondary">' . htmlspecialchars($loan['asset_type']) . '</span><br>
                    <strong>' . htmlspecialchars($loan['manufacturer'] . ' ' . $loan['model']) . '</strong><br>
                    <small class="text-muted">SN: ' . htmlspecialchars($loan['serial_number']) . '</small>
                </td>
            </tr>
            <tr>
                <td><strong>Loan Start:</strong></td>
                <td>' . date('M j, Y', strtotime($loan['loan_start_date'])) . '</td>
            </tr>
            <tr>
                <td><strong>Expected Return:</strong></td>
                <td>
                    ' . date('M j, Y', strtotime($loan['expected_return_date'])) . '<br>';
        
        if ($is_overdue) {
            $detailsContent .= '<span class="badge bg-danger">
                <i class="bi bi-exclamation-triangle"></i> ' . $days_diff . ' days overdue
            </span>';
        } else {
            $detailsContent .= '<span class="badge bg-success">On time</span>';
        }
        
        $detailsContent .= '
                </td>
            </tr>
            <tr>
                <td><strong>Purpose:</strong></td>
                <td><small>' . htmlspecialchars($loan['loan_purpose']) . '</small></td>
            </tr>
            <tr>
                <td><strong>Recorded By:</strong></td>
                <td>' . htmlspecialchars($loan['created_by_name']) . '</td>
            </tr>
        </table>';
        
        if (!empty($loan['admin_notes'])) {
            $detailsContent .= '
            <div class="mt-3">
                <strong>Internal Notes:</strong><br>
                <small class="text-muted">' . nl2br(htmlspecialchars($loan['admin_notes'])) . '</small>
            </div>';
        }
        
        echo renderCard('Loan Details', $detailsContent, 'info-circle');
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = '
<script>
    // Set maximum date to today
    document.getElementById("return_date").max = new Date().toISOString().split("T")[0];
</script>';

echo renderLayout('Process Return', $content, 'loans_active', '', $additionalJS);
?>

<!-- 
============================================
WHAT YOU'VE LEARNED
============================================

✅ UPDATE queries with multiple tables
✅ Transaction management (begin, commit, rollback)
✅ Business logic implementation (status transitions)
✅ Form validation with existing data
✅ JOIN queries for related data display
✅ Date validation and constraints
✅ Professional return processing workflow

============================================
TESTING INSTRUCTIONS
============================================

1. Go to Active Loans page
2. Click "Process Return" button on any active loan
3. Should see return form with loan details
4. Fill out return information:
   - Return date (today or earlier)
   - Condition (dropdown selection)
   - Notes (optional)
5. Submit - should show success message
6. Check Active Loans - loan should be gone
7. Check Assets List - asset should be "Available"

============================================
NEXT STEPS
============================================

After testing this page:
1. Next: Create loans_history.php (search all loans)
2. Then: Create loan_view.php (detailed loan view)
3. Finally: System polish and testing

You're completing the loan lifecycle management!
-->