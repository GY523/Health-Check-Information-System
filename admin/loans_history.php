<?php
/**
 * Loan History Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice complex search queries with multiple filters
 * - Learn date range filtering
 * - Implement pagination for large datasets
 * - Handle different loan statuses display
 * - Practice advanced JOIN queries
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can view loan history

require_once '../config/db_config.php';
require_once '../includes/layout.php';

// ============================================
// HANDLE SEARCH/FILTER PARAMETERS
// ============================================

$search_company = $_GET['search_company'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// ============================================
// BUILD DYNAMIC QUERY
// ============================================

$query = "SELECT 
    l.loan_id,
    l.loan_start_date,
    l.expected_return_date,
    l.actual_return_date,
    l.loan_purpose,
    l.customer_company,
    l.customer_email,
    l.status,
    l.created_at,
    a.asset_type,
    a.manufacturer,
    a.model,
    a.serial_number,
    u.full_name as created_by_name,
    CASE 
        WHEN l.status = 'Active' AND l.expected_return_date < CURDATE() THEN 1 
        ELSE 0 
    END as is_overdue
FROM loans l
JOIN assets a ON l.asset_id = a.asset_id
JOIN users u ON l.created_by_user_id = u.user_id
WHERE 1=1";

$params = [];
$types = "";

// Add search conditions
if (!empty($search_company)) {
    $query .= " AND l.customer_company LIKE ?";
    $params[] = "%$search_company%";
    $types .= "s";
}

if (!empty($status_filter)) {
    $query .= " AND l.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_from)) {
    $query .= " AND l.loan_start_date >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $query .= " AND l.loan_start_date <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$query .= " ORDER BY l.created_at DESC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

// ============================================
// PREPARE CONTENT FOR TEMPLATE
// ============================================

ob_start();
?>

<!-- Page Header -->
<?php echo renderPageHeader('Loan History', 'clock-history', 
    '<a href="loan_record.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Record New Loan
    </a>'
); ?>

<!-- Search and Filter -->
<?php
$filterForm = '
<form method="GET" class="row g-3">
    <div class="col-md-3">
        <label for="search_company" class="form-label">Company</label>
        <input type="text" class="form-control" name="search_company" 
               placeholder="Company name"
               value="' . htmlspecialchars($search_company) . '">
    </div>
    <div class="col-md-2">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="Active"' . ($status_filter === 'Active' ? ' selected' : '') . '>Active</option>
            <option value="Returned"' . ($status_filter === 'Returned' ? ' selected' : '') . '>Returned</option>
            <option value="Cancelled"' . ($status_filter === 'Cancelled' ? ' selected' : '') . '>Cancelled</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="date_from" class="form-label">From Date</label>
        <input type="date" class="form-control" name="date_from" 
               value="' . htmlspecialchars($date_from) . '">
    </div>
    <div class="col-md-2">
        <label for="date_to" class="form-label">To Date</label>
        <input type="date" class="form-control" name="date_to" 
               value="' . htmlspecialchars($date_to) . '">
    </div>
    <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <div class="d-grid">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search"></i> Search
            </button>
        </div>
    </div>
</form>';

if (!empty($search_company) || !empty($status_filter) || !empty($date_from) || !empty($date_to)) {
    $filterForm .= '<div class="mt-2">
        <a href="loans_history.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle"></i> Clear Filters
        </a>
    </div>';
}

echo renderCard('Search & Filter', $filterForm);
?>

<!-- Loan History Table -->
<?php
$tableContent = '';
if ($result->num_rows > 0) {
    $tableContent = '<div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Loan ID</th>
                    <th>Customer</th>
                    <th>Asset</th>
                    <th>Loan Period</th>
                    <th>Status</th>
                    <th>Purpose</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($loan = $result->fetch_assoc()) {
        $status_class = match($loan['status']) {
            'Active' => $loan['is_overdue'] ? 'bg-danger' : 'bg-info',
            'Returned' => 'bg-success',
            'Cancelled' => 'bg-secondary',
            default => 'bg-secondary'
        };
        
        $tableContent .= '<tr>
            <td><strong>#' . $loan['loan_id'] . '</strong></td>
            <td>
                <strong>' . htmlspecialchars($loan['customer_company']) . '</strong>';
        
        if (!empty($loan['customer_email'])) {
            $tableContent .= '<br><small class="text-muted">
                <i class="bi bi-envelope"></i> ' . htmlspecialchars($loan['customer_email']) . '
            </small>';
        }
        
        $tableContent .= '</td>
            <td>
                <span class="badge bg-secondary">' . htmlspecialchars($loan['asset_type']) . '</span><br>
                <strong>' . htmlspecialchars($loan['manufacturer'] . ' ' . $loan['model']) . '</strong><br>
                <small class="text-muted">SN: ' . htmlspecialchars($loan['serial_number']) . '</small>
            </td>
            <td>
                <strong>Start:</strong> ' . date('M j, Y', strtotime($loan['loan_start_date'])) . '<br>
                <strong>Expected:</strong> ' . date('M j, Y', strtotime($loan['expected_return_date'])) . '<br>';
        
        if ($loan['status'] === 'Returned' && !empty($loan['actual_return_date'])) {
            $tableContent .= '<strong>Returned:</strong> ' . date('M j, Y', strtotime($loan['actual_return_date']));
        }
        
        $tableContent .= '</td>
            <td>
                <span class="badge ' . $status_class . '">' . htmlspecialchars($loan['status']);
        
        if ($loan['status'] === 'Active' && $loan['is_overdue']) {
            $tableContent .= ' (Overdue)';
        }
        
        $tableContent .= '</span>
            </td>
            <td><small>' . htmlspecialchars(substr($loan['loan_purpose'], 0, 40)) . (strlen($loan['loan_purpose']) > 40 ? '...' : '') . '</small></td>
            <td>' . htmlspecialchars($loan['created_by_name']) . '<br>
                <small class="text-muted">' . date('M j, Y', strtotime($loan['created_at'])) . '</small>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="loan_view.php?id=' . $loan['loan_id'] . '" 
                       class="btn btn-outline-info" title="View Details">
                        <i class="bi bi-eye"></i>
                    </a>';
        
        if ($loan['status'] === 'Active') {
            $tableContent .= '<a href="loan_return.php?id=' . $loan['loan_id'] . '" 
                       class="btn btn-outline-success" title="Process Return">
                        <i class="bi bi-arrow-return-left"></i>
                    </a>
                    <a href="loan_cancel.php?id=' . $loan['loan_id'] . '" 
                       class="btn btn-outline-danger" title="Cancel Loan">
                        <i class="bi bi-x-circle"></i>
                    </a>';
        }
        
        $tableContent .= '</div>
            </td>
        </tr>';
    }
    
    $tableContent .= '</tbody></table></div>';
} else {
    $tableContent = '<div class="text-center py-5">
        <i class="bi bi-clock-history display-1 text-muted"></i>
        <h4 class="text-muted">No Loans Found</h4>
        <p class="text-muted">';
    
    if (!empty($search_company) || !empty($status_filter) || !empty($date_from) || !empty($date_to)) {
        $tableContent .= 'No loans match your search criteria. <a href="loans_history.php">Clear filters</a> to see all loans.';
    } else {
        $tableContent .= 'No loans have been recorded yet. <a href="loan_record.php">Record the first loan</a> to get started.';
    }
    
    $tableContent .= '</p></div>';
}

echo renderCard('Loan History (' . $result->num_rows . ' loans)', $tableContent, 'table');
?>

<?php
$content = ob_get_clean();
echo renderLayout('Loan History', $content, 'loans_history');
?>

<!-- 
============================================
WHAT YOU'VE LEARNED
============================================

✅ Complex search queries with multiple filters
✅ Date range filtering with SQL
✅ Dynamic query building with parameters
✅ Status-based conditional styling
✅ Advanced JOIN queries with multiple tables
✅ Professional search interface design
✅ Data presentation with badges and formatting

============================================
TESTING INSTRUCTIONS
============================================

1. Go to Loan History page
2. Should see all loans (active, returned, cancelled)
3. Try searching by company name
4. Try filtering by status (Active, Returned, Cancelled)
5. Try date range filtering
6. Test combinations of filters
7. Check that overdue active loans show as red
8. Test "Clear Filters" functionality

============================================
NEXT STEPS
============================================

After testing this page:
1. Next: Create loan_view.php (detailed loan view)
2. Then: System polish and final testing
3. Finally: Deployment preparation

You're almost done with the complete system!
-->