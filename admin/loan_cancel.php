<?php
/**
 * Cancel Loan Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice UPDATE queries for status changes
 * - Handle loan cancellation business logic
 * - Learn confirmation workflows
 * - Implement asset status restoration
 * - Practice transaction management
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can cancel loans

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
// FETCH LOAN DATA
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
// HANDLE CANCELLATION
// ============================================

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_cancel'])) {
    
    $cancellation_reason = trim($_POST['cancellation_reason']);
    
    if (empty($cancellation_reason)) {
        $error_message = "Cancellation reason is required.";
    } else {
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update loan status to 'Cancelled'
            $update_loan = $conn->prepare("UPDATE loans SET 
                status = 'Cancelled', 
                return_notes = ?,
                updated_at = NOW() 
                WHERE loan_id = ?");
            
            $update_loan->bind_param("si", $cancellation_reason, $loan_id);
            
            if (!$update_loan->execute()) {
                throw new Exception("Error cancelling loan: " . $update_loan->error);
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
            
            $success_message = "Loan cancelled successfully! Asset is now available.";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
}

// ============================================
// PREPARE CONTENT FOR TEMPLATE
// ============================================

ob_start();
?>

<!-- Page Header -->
<?php echo renderPageHeader('Cancel Loan #' . $loan['loan_id'], 'x-circle', 
    '<a href="loans_active.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Active Loans
    </a>'
); ?>

<!-- Success/Error Messages -->
<?php echo renderAlert($success_message, 'success'); ?>
<?php echo renderAlert($error_message, 'danger'); ?>

<div class="row">
    <!-- Cancellation Form -->
    <div class="col-md-8">
        <?php if (empty($success_message)): ?>
        <?php
        $formContent = '
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Warning:</strong> Cancelling this loan will make the asset available for new loans. This action cannot be undone.
        </div>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="cancellation_reason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="4" 
                          placeholder="Please provide a reason for cancelling this loan..." required></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <div>
                    <span class="text-danger">*</span> Required field
                </div>
                <div>
                    <a href="loans_active.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button type="submit" name="confirm_cancel" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Cancel Loan
                    </button>
                </div>
            </div>
        </form>';
        
        echo renderCard('Cancel Loan', $formContent, 'x-circle');
        ?>
        <?php else: ?>
        <?php
        $successContent = '
        <div class="text-center py-4">
            <i class="bi bi-check-circle-fill display-1 text-success"></i>
            <h4 class="text-success mt-3">Loan Cancelled Successfully!</h4>
            <p class="text-muted">The loan has been cancelled and the asset is now available for new loans.</p>
            <div class="mt-4">
                <a href="loans_active.php" class="btn btn-primary me-2">
                    <i class="bi bi-arrow-left-right"></i> View Active Loans
                </a>
                <a href="assets_list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-server"></i> View Assets
                </a>
            </div>
        </div>';
        
        echo renderCard('Cancellation Complete', $successContent, 'check-circle');
        ?>
        <?php endif; ?>
    </div>
    
    <!-- Loan Details -->
    <div class="col-md-4">
        <?php
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
                <td><strong>Start Date:</strong></td>
                <td>' . date('M j, Y', strtotime($loan['loan_start_date'])) . '</td>
            </tr>
            <tr>
                <td><strong>Expected Return:</strong></td>
                <td>' . date('M j, Y', strtotime($loan['expected_return_date'])) . '</td>
            </tr>
            <tr>
                <td><strong>Purpose:</strong></td>
                <td><small>' . htmlspecialchars($loan['loan_purpose']) . '</small></td>
            </tr>
            <tr>
                <td><strong>Created By:</strong></td>
                <td>' . htmlspecialchars($loan['created_by_name']) . '</td>
            </tr>
        </table>';
        
        echo renderCard('Loan Details', $detailsContent, 'info-circle');
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderLayout('Cancel Loan', $content, 'loans_active');
?>

<!-- 
============================================
WHAT YOU'VE LEARNED
============================================

✅ Status change workflows (Active → Cancelled)
✅ Transaction management for data consistency
✅ Confirmation forms with validation
✅ Asset status restoration logic
✅ Professional cancellation interface
✅ Warning messages and user guidance

============================================
TESTING INSTRUCTIONS
============================================

1. Go to Active Loans page
2. Click "Cancel" button on any active loan
3. Should see cancellation form with loan details
4. Try submitting without reason - should show error
5. Fill in cancellation reason and submit
6. Should show success message
7. Check Active Loans - loan should be gone
8. Check Assets List - asset should be "Available"

============================================
BUSINESS LOGIC
============================================

When loan is cancelled:
1. Loan status changes to 'Cancelled'
2. Asset status changes back to 'Available'
3. Cancellation reason stored in return_notes
4. Asset becomes available for new loans

This provides flexibility for handling cancelled requests!
-->