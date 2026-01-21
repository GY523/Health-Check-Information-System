<?php
/**
 * Delete Asset Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice DELETE queries
 * - Implement safety checks before deletion
 * - Handle foreign key constraints
 * - Learn about cascading deletes
 * - Practice confirmation workflows
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can delete assets

require_once '../config/db_config.php';
require_once '../includes/layout.php';

// ============================================
// GET ASSET ID FROM URL
// ============================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: assets_list.php');
    exit();
}

$asset_id = (int)$_GET['id'];

// ============================================
// FETCH ASSET DATA
// ============================================

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
// CHECK FOR ACTIVE LOANS
// ============================================
// LEARNING NOTE: We should not delete assets that have active loans

$loan_check_stmt = $conn->prepare("SELECT COUNT(*) as loan_count FROM loans WHERE asset_id = ? AND status IN ('Pending', 'Approved', 'Active')");
$loan_check_stmt->bind_param("i", $asset_id);
$loan_check_stmt->execute();
$loan_result = $loan_check_stmt->get_result();
$loan_count = $loan_result->fetch_assoc()['loan_count'];
$loan_check_stmt->close();

$can_delete = ($loan_count == 0);
$error_message = '';
$success_message = '';

// ============================================
// HANDLE DELETE CONFIRMATION
// ============================================

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    
    if (!$can_delete) {
        $error_message = "Cannot delete asset with active loans.";
    } else {
        
        // ============================================
        // DELETE ASSET
        // ============================================
        // LEARNING NOTE: DELETE query with WHERE condition
        
        $delete_stmt = $conn->prepare("DELETE FROM assets WHERE asset_id = ?");
        $delete_stmt->bind_param("i", $asset_id);
        
        if ($delete_stmt->execute()) {
            $delete_stmt->close();
            
            // Redirect with success message
            header('Location: assets_list.php?deleted=1');
            exit();
            
        } else {
            $error_message = "Error deleting asset: " . $conn->error;
        }
        
        $delete_stmt->close();
    }
}

// ============================================
// PREPARE CONTENT FOR TEMPLATE
// ============================================

ob_start();
?>

<!-- Page Header -->
<?php echo renderPageHeader('Delete Asset #' . $asset['asset_id'], 'trash', 
    '<a href="assets_list.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Assets List
    </a>'
); ?>

<!-- Error Messages -->
<?php echo renderAlert($error_message, 'danger'); ?>

<!-- Delete Confirmation -->
<div class="row">
    <div class="col-md-8">
        
        <?php if (!$can_delete): ?>
        <!-- Cannot Delete Warning -->
        <?php
        $warningContent = '
        <p><strong>This asset cannot be deleted because it has active loans.</strong></p>
        <p>Active/Pending loans: <span class="badge bg-warning text-dark">' . $loan_count . '</span></p>
        <p>To delete this asset, you must first:</p>
        <ul>
            <li>Complete or cancel all pending loan requests</li>
            <li>Process returns for all active loans</li>
            <li>Ensure no loans are in "Approved" status</li>
        </ul>
        <div class="mt-3">
            <a href="assets_list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Assets List
            </a>
            <a href="loans_active.php" class="btn btn-warning">
                <i class="bi bi-eye"></i> View Active Loans
            </a>
        </div>';
        
        echo '<div class="card border-warning">';
        echo '<div class="card-header bg-warning text-dark">';
        echo '<h5><i class="bi bi-exclamation-triangle"></i> Cannot Delete Asset</h5>';
        echo '</div>';
        echo '<div class="card-body">' . $warningContent . '</div>';
        echo '</div>';
        ?>
        
        <?php else: ?>
        <!-- Delete Confirmation Form -->
        <?php
        $confirmContent = '
        <div class="alert alert-warning">
            <strong>Warning:</strong> This action cannot be undone. The asset will be permanently removed from the system.
        </div>
        
        <p><strong>Are you sure you want to delete this asset?</strong></p>
        
        <form method="POST" action="">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="assets_list.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
                <div>
                    <button type="submit" name="confirm_delete" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Yes, Delete Asset
                    </button>
                </div>
            </div>
        </form>';
        
        echo '<div class="card border-danger">';
        echo '<div class="card-header bg-danger text-white">';
        echo '<h5><i class="bi bi-exclamation-triangle"></i> Confirm Asset Deletion</h5>';
        echo '</div>';
        echo '<div class="card-body">' . $confirmContent . '</div>';
        echo '</div>';
        ?>
        <?php endif; ?>

    </div>
    
    <!-- Asset Details -->
    <div class="col-md-4">
        <?php
        $status_class = '';
        switch($asset['status']) {
            case 'Available': $status_class = 'bg-success'; break;
            case 'On Loan': $status_class = 'bg-warning text-dark'; break;
            case 'Maintenance': $status_class = 'bg-info'; break;
            case 'Retired': $status_class = 'bg-danger'; break;
            default: $status_class = 'bg-secondary';
        }
        
        $detailsContent = '
        <table class="table table-sm">
            <tr>
                <td><strong>ID:</strong></td>
                <td>' . $asset['asset_id'] . '</td>
            </tr>
            <tr>
                <td><strong>Type:</strong></td>
                <td>
                    <span class="badge bg-secondary">
                        ' . htmlspecialchars($asset['asset_type']) . '
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Manufacturer:</strong></td>
                <td>' . htmlspecialchars($asset['manufacturer'] ?? '') . '</td>
            </tr>
            <tr>
                <td><strong>Model:</strong></td>
                <td>' . htmlspecialchars($asset['model']) . '</td>
            </tr>
            <tr>
                <td><strong>Serial:</strong></td>
                <td><code>' . htmlspecialchars($asset['serial_number']) . '</code></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>
                    <span class="badge ' . $status_class . '">
                        ' . htmlspecialchars($asset['status']) . '
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Location:</strong></td>
                <td>' . htmlspecialchars($asset['location'] ?? '') . '</td>
            </tr>
            <tr>
                <td><strong>Created:</strong></td>
                <td>' . date('Y-m-d', strtotime($asset['created_at'])) . '</td>
            </tr>
        </table>';
        
        if (!empty($asset['specifications'])) {
            $detailsContent .= '
            <div class="mt-3">
                <strong>Specifications:</strong><br>
                <small class="text-muted">' . nl2br(htmlspecialchars($asset['specifications'])) . '</small>
            </div>';
        }
        
        if (!empty($asset['notes'])) {
            $detailsContent .= '
            <div class="mt-3">
                <strong>Notes:</strong><br>
                <small class="text-muted">' . nl2br(htmlspecialchars($asset['notes'])) . '</small>
            </div>';
        }
        
        echo renderCard('Asset Details', $detailsContent, 'info-circle');
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderLayout('Delete Asset', $content, 'assets_list');
?>

<!-- 
============================================
LEARNING EXERCISE #1
============================================
TODO: Add soft delete functionality
Instead of permanently deleting, mark as deleted

HINT:
1. Add 'deleted_at' column to assets table
2. UPDATE assets SET deleted_at = NOW() instead of DELETE
3. Modify all SELECT queries to exclude deleted assets
4. Add "restore" functionality for admins

============================================
LEARNING EXERCISE #2
============================================
TODO: Add deletion logging
Track who deleted what and when

HINT:
1. Create deletion_log table
2. Before DELETE, INSERT log entry
3. Store: asset_id, deleted_by, deleted_at, asset_data (JSON)
4. Create admin page to view deletion history

============================================
WHAT YOU'VE LEARNED
============================================

✅ DELETE queries with WHERE conditions
✅ Safety checks before deletion
✅ Foreign key constraint handling
✅ Confirmation workflows
✅ Business logic validation
✅ User-friendly error messages
✅ Professional deletion interface

============================================
TESTING INSTRUCTIONS
============================================

1. Go to assets list
2. Click delete button (trash icon) on an available asset
3. Should see confirmation page with asset details
4. Click "Yes, Delete Asset" - should redirect to list with success
5. Try deleting an asset with active loans - should show warning
6. Asset should be removed from database

============================================
CONGRATULATIONS!
============================================

You've completed all CRUD operations:
✅ CREATE - Add assets
✅ READ - View/search assets  
✅ UPDATE - Edit assets
✅ DELETE - Remove assets

Next: Build the loan management system!
-->