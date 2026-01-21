<?php
/**
 * Loan View Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice detailed data display with JOINs
 * - Learn comprehensive information presentation
 * - Implement action buttons based on loan status
 * - Practice professional detail page layout
 * - Handle different loan states and conditions
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can view loan details

require_once '../config/db_config.php';
require_once '../includes/layout.php';

// ============================================
// GET LOAN ID FROM URL
// ============================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: loans_history.php');
    exit();
}

$loan_id = (int)$_GET['id'];

// ============================================
// FETCH COMPLETE LOAN DATA
// ============================================

$stmt = $conn->prepare("SELECT 
    l.*,
    a.asset_type,
    a.manufacturer,
    a.model,
    a.serial_number,
    a.specifications,
    a.location,
    u.full_name as created_by_name,
    DATEDIFF(l.expected_return_date, CURDATE()) as days_until_due,
    CASE 
        WHEN l.status = 'Active' AND l.expected_return_date < CURDATE() THEN 1 
        ELSE 0 
    END as is_overdue
FROM loans l
JOIN assets a ON l.asset_id = a.asset_id
JOIN users u ON l.created_by_user_id = u.user_id
WHERE l.loan_id = ?");

$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: loans_history.php');
    exit();
}

$loan = $result->fetch_assoc();
$stmt->close();

// ============================================
// PREPARE CONTENT FOR TEMPLATE
// ============================================

ob_start();
?>

<!-- Page Header -->
<?php 
$backUrl = $loan['status'] === 'Active' ? 'loans_active.php' : 'loans_history.php';
$backText = $loan['status'] === 'Active' ? 'Back to Active Loans' : 'Back to Loan History';

echo renderPageHeader('Loan Details #' . $loan['loan_id'], 'eye', 
    '<a href="' . $backUrl . '" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> ' . $backText . '
    </a>'
); 
?>

<div class="row">
    <!-- Main Loan Information -->
    <div class="col-md-8">
        
        <!-- Loan Overview -->
        <?php
        $status_class = match($loan['status']) {
            'Active' => $loan['is_overdue'] ? 'bg-danger' : 'bg-info',
            'Returned' => 'bg-success',
            'Cancelled' => 'bg-secondary',
            default => 'bg-secondary'
        };
        
        $overviewContent = '
        <div class="row">
            <div class="col-md-6">
                <h5>Customer Information</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Company:</strong></td>
                        <td>' . htmlspecialchars($loan['customer_company']) . '</td>
                    </tr>';
        
        if (!empty($loan['customer_email'])) {
            $overviewContent .= '
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><a href="mailto:' . htmlspecialchars($loan['customer_email']) . '">' . htmlspecialchars($loan['customer_email']) . '</a></td>
                    </tr>';
        }
        
        $overviewContent .= '
                </table>
            </div>
            <div class="col-md-6">
                <h5>Loan Status</h5>
                <p><span class="badge ' . $status_class . ' fs-6">' . htmlspecialchars($loan['status']);
        
        if ($loan['status'] === 'Active' && $loan['is_overdue']) {
            $overviewContent .= ' (Overdue)';
        }
        
        $overviewContent .= '</span></p>';
        
        if ($loan['status'] === 'Active') {
            if ($loan['is_overdue']) {
                $overviewContent .= '<p class="text-danger"><i class="bi bi-exclamation-triangle"></i> 
                    <strong>' . abs($loan['days_until_due']) . ' days overdue</strong></p>';
            } else {
                $overviewContent .= '<p class="text-success"><i class="bi bi-clock"></i> 
                    <strong>' . $loan['days_until_due'] . ' days remaining</strong></p>';
            }
        }
        
        $overviewContent .= '
            </div>
        </div>';
        
        echo renderCard('Loan Overview', $overviewContent, 'info-circle');
        ?>
        
        <!-- Loan Timeline -->
        <?php
        $timelineContent = '
        <div class="row">
            <div class="col-md-4">
                <h6><i class="bi bi-calendar-plus"></i> Loan Start</h6>
                <p><strong>' . date('F j, Y', strtotime($loan['loan_start_date'])) . '</strong></p>
            </div>
            <div class="col-md-4">
                <h6><i class="bi bi-calendar-event"></i> Expected Return</h6>
                <p><strong>' . date('F j, Y', strtotime($loan['expected_return_date'])) . '</strong></p>
            </div>
            <div class="col-md-4">
                <h6><i class="bi bi-calendar-check"></i> Actual Return</h6>';
        
        if ($loan['status'] === 'Returned' && !empty($loan['actual_return_date'])) {
            $timelineContent .= '<p><strong>' . date('F j, Y', strtotime($loan['actual_return_date'])) . '</strong></p>';
        } else {
            $timelineContent .= '<p class="text-muted">Not returned yet</p>';
        }
        
        $timelineContent .= '
            </div>
        </div>';
        
        echo renderCard('Loan Timeline', $timelineContent, 'calendar');
        ?>
        
        <!-- Loan Purpose & Notes -->
        <?php
        $notesContent = '
        <div class="mb-3">
            <h6>Loan Purpose</h6>
            <p>' . nl2br(htmlspecialchars($loan['loan_purpose'])) . '</p>
        </div>';
        
        if (!empty($loan['admin_notes'])) {
            $notesContent .= '
            <div class="mb-3">
                <h6>Internal Notes</h6>
                <p class="text-muted">' . nl2br(htmlspecialchars($loan['admin_notes'])) . '</p>
            </div>';
        }
        
        if (!empty($loan['return_notes'])) {
            $notesContent .= '
            <div class="mb-3">
                <h6>Return Notes</h6>
                <p class="text-muted">' . nl2br(htmlspecialchars($loan['return_notes'])) . '</p>
            </div>';
        }
        
        echo renderCard('Purpose & Notes', $notesContent, 'chat-text');
        ?>
        
    </div>
    
    <!-- Sidebar Information -->
    <div class="col-md-4">
        
        <!-- Asset Details -->
        <?php
        $assetContent = '
        <div class="text-center mb-3">
            <span class="badge bg-secondary fs-6">' . htmlspecialchars($loan['asset_type']) . '</span>
        </div>
        <table class="table table-sm">
            <tr>
                <td><strong>Manufacturer:</strong></td>
                <td>' . htmlspecialchars($loan['manufacturer']) . '</td>
            </tr>
            <tr>
                <td><strong>Model:</strong></td>
                <td>' . htmlspecialchars($loan['model']) . '</td>
            </tr>
            <tr>
                <td><strong>Serial Number:</strong></td>
                <td><code>' . htmlspecialchars($loan['serial_number']) . '</code></td>
            </tr>
            <tr>
                <td><strong>Location:</strong></td>
                <td>' . htmlspecialchars($loan['location'] ?? 'Not specified') . '</td>
            </tr>
        </table>';
        
        if (!empty($loan['specifications'])) {
            $assetContent .= '
            <div class="mt-3">
                <strong>Specifications:</strong><br>
                <small class="text-muted">' . nl2br(htmlspecialchars($loan['specifications'])) . '</small>
            </div>';
        }
        
        $assetActions = '<a href="assets_list.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-server"></i> View All Assets
        </a>';
        
        echo renderCard('Asset Information', $assetContent, 'server', $assetActions);
        ?>
        
        <!-- Loan Actions -->
        <?php
        $actionsContent = '';
        
        if ($loan['status'] === 'Active') {
            $actionsContent = '
            <div class="d-grid gap-2">
                <a href="loan_return.php?id=' . $loan['loan_id'] . '" class="btn btn-success">
                    <i class="bi bi-arrow-return-left"></i> Process Return
                </a>
                <a href="loan_cancel.php?id=' . $loan['loan_id'] . '" class="btn btn-outline-danger">
                    <i class="bi bi-x-circle"></i> Cancel Loan
                </a>
            </div>';
        } else {
            $actionsContent = '
            <div class="text-center py-3">
                <p class="text-muted">This loan has been ' . strtolower($loan['status']) . '.</p>
                <a href="loan_record.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Record New Loan
                </a>
            </div>';
        }
        
        echo renderCard('Actions', $actionsContent, 'gear');
        ?>
        
        <!-- Loan Metadata -->
        <?php
        $metadataContent = '
        <table class="table table-sm">
            <tr>
                <td><strong>Loan ID:</strong></td>
                <td>#' . $loan['loan_id'] . '</td>
            </tr>
            <tr>
                <td><strong>Created By:</strong></td>
                <td>' . htmlspecialchars($loan['created_by_name']) . '</td>
            </tr>
            <tr>
                <td><strong>Created On:</strong></td>
                <td>' . date('M j, Y g:i A', strtotime($loan['created_at'])) . '</td>
            </tr>
            <tr>
                <td><strong>Last Updated:</strong></td>
                <td>' . date('M j, Y g:i A', strtotime($loan['updated_at'])) . '</td>
            </tr>
        </table>';
        
        echo renderCard('Loan Information', $metadataContent, 'info');
        ?>
        
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderLayout('Loan Details', $content, $loan['status'] === 'Active' ? 'loans_active' : 'loans_history');
?>

<!-- 
============================================
WHAT YOU'VE LEARNED
============================================

✅ Comprehensive data display with JOINs
✅ Professional detail page layout design
✅ Conditional content based on loan status
✅ Timeline and status visualization
✅ Action buttons based on current state
✅ Responsive sidebar information layout
✅ Email links and interactive elements

============================================
TESTING INSTRUCTIONS
============================================

1. Go to Active Loans or Loan History
2. Click "View Details" (eye icon) on any loan
3. Should see comprehensive loan information
4. Check that overdue loans show warning
5. Test action buttons (Process Return for active loans)
6. Verify all information displays correctly
7. Test navigation back to appropriate list

============================================
SYSTEM COMPLETE
============================================

Congratulations! You've built a complete loan management system:
✅ Asset management (CRUD)
✅ Loan recording and tracking
✅ Return processing
✅ History and search
✅ Detailed views
✅ Professional UI with template system

Ready for final testing and deployment!
-->