<?php
/**
 * Active Loans Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice JOIN queries with multiple tables
 * - Learn date calculations (overdue detection)
 * - Implement search and filtering
 * - Handle status updates
 * - Display relational data in tables
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer(); // Both admin and engineer can view active loans

require_once '../config/db_config.php';
require_once '../includes/layout.php';

// ============================================
// HANDLE SEARCH/FILTER
// ============================================

$search_company = $_GET['search_company'] ?? '';
$overdue_only = isset($_GET['overdue_only']);

// ============================================
// BUILD QUERY WITH JOINS
// ============================================

$query = "SELECT 
    l.loan_id,
    l.loan_start_date,
    l.expected_return_date,
    l.loan_purpose,
    l.customer_company,
    l.customer_email,
    l.admin_notes,
    l.created_at,
    a.asset_id,
    a.asset_type,
    a.manufacturer,
    a.model,
    a.serial_number,
    u.full_name as created_by_name,
    DATEDIFF(l.expected_return_date, CURDATE()) as days_until_due,
    CASE 
        WHEN l.expected_return_date < CURDATE() THEN 1 
        ELSE 0 
    END as is_overdue
FROM loans l
JOIN assets a ON l.asset_id = a.asset_id
JOIN users u ON l.created_by_user_id = u.user_id
WHERE l.status = 'Active'";

$params = [];
$types = "";

// Add search condition
if (!empty($search_company)) {
    $query .= " AND l.customer_company LIKE ?";
    $search_param = "%$search_company%";
    $params[] = $search_param;
    $types .= "s";
}

// Add overdue filter
if ($overdue_only) {
    $query .= " AND l.expected_return_date < CURDATE()";
}

$query .= " ORDER BY l.expected_return_date ASC, l.created_at DESC";

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
<?php echo renderPageHeader('Active Loans', 'arrow-left-right', 
    '<a href="loan_record.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Record New Loan
    </a>'
); ?>

<!-- Search and Filter -->
<?php
$filterForm = '
<form method="GET" class="row g-3">
    <div class="col-md-6">
        <label for="search_company" class="form-label">Search by Company</label>
        <input type="text" class="form-control" id="search_company" name="search_company" 
               placeholder="Company name"
               value="' . htmlspecialchars($search_company) . '">
    </div>
    <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="overdue_only" name="overdue_only" 
                   ' . ($overdue_only ? 'checked' : '') . '>
            <label class="form-check-label" for="overdue_only">
                Show overdue only
            </label>
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <div class="d-grid">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search"></i> Filter
            </button>
        </div>
    </div>
</form>';

if (!empty($search_company) || $overdue_only) {
    $filterForm .= '<div class="mt-2">
        <a href="loans_active.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle"></i> Clear Filters
        </a>
    </div>';
}

echo renderCard('Search & Filter', $filterForm);
?>

<!-- Active Loans Table -->
<?php
$tableContent = '';
if ($result->num_rows > 0) {
    $tableContent = '<div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Loan ID</th>
                    <th>Customer Company</th>
                    <th>Asset</th>
                    <th>Start Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Purpose</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($loan = $result->fetch_assoc()) {
        $row_class = '';
        if ($loan['is_overdue']) {
            $row_class = 'table-danger';
        } elseif ($loan['days_until_due'] <= 3 && $loan['days_until_due'] >= 0) {
            $row_class = 'table-warning';
        }
        
        $tableContent .= '<tr class="' . $row_class . '">
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
            <td>' . date('M j, Y', strtotime($loan['loan_start_date'])) . '</td>
            <td>
                ' . date('M j, Y', strtotime($loan['expected_return_date'])) . '<br>';
        
        if ($loan['is_overdue']) {
            $tableContent .= '<span class="badge bg-danger">
                <i class="bi bi-exclamation-triangle"></i> 
                ' . abs($loan['days_until_due']) . ' days overdue
            </span>';
        } elseif ($loan['days_until_due'] <= 3) {
            $tableContent .= '<span class="badge bg-warning text-dark">
                <i class="bi bi-clock"></i> 
                Due in ' . $loan['days_until_due'] . ' days
            </span>';
        } else {
            $tableContent .= '<span class="badge bg-success">
                ' . $loan['days_until_due'] . ' days left
            </span>';
        }
        
        $tableContent .= '</td>
            <td><span class="badge bg-info">Active</span></td>
            <td><small>' . htmlspecialchars(substr($loan['loan_purpose'], 0, 50)) . (strlen($loan['loan_purpose']) > 50 ? '...' : '') . '</small></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="loan_view.php?id=' . $loan['loan_id'] . '" 
                       class="btn btn-outline-info" title="View Details">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="loan_return.php?id=' . $loan['loan_id'] . '" 
                       class="btn btn-outline-success" title="Process Return">
                        <i class="bi bi-arrow-return-left"></i>
                    </a>
                    <a href="loan_cancel.php?id=' . $loan['loan_id'] . '" 
                       class="btn btn-outline-danger" title="Cancel Loan">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </td>
        </tr>';
    }
    
    $tableContent .= '</tbody></table></div>';
} else {
    $tableContent = '<div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h4 class="text-muted">No Active Loans Found</h4>
        <p class="text-muted">';
    
    if (!empty($search_company) || $overdue_only) {
        $tableContent .= 'No loans match your search criteria. <a href="loans_active.php">Clear filters</a> to see all active loans.';
    } else {
        $tableContent .= 'No assets are currently on loan. <a href="loan_record.php">Record a new loan</a> to get started.';
    }
    
    $tableContent .= '</p></div>';
}

echo renderCard('Active Loans (' . $result->num_rows . ' loans)', $tableContent, 'table');
?>

<?php
$content = ob_get_clean();

$additionalCSS = '<style>
.table-danger { background-color: #fff2f2; }
.table-warning { background-color: #fff8e1; }
</style>';

echo renderLayout('Active Loans', $content, 'loans_active', $additionalCSS);
?>

<!-- 
============================================
WHAT YOU'VE LEARNED
============================================

✅ JOIN queries with multiple tables (loans + assets + users)
✅ Date calculations (DATEDIFF, overdue detection)
✅ Conditional styling (overdue rows highlighted)
✅ Search and filtering with dynamic queries
✅ Professional table layout with status badges
✅ Responsive design with Bootstrap
✅ Template system integration

============================================
TESTING INSTRUCTIONS
============================================

1. Make sure you have recorded at least one loan
2. Visit loans_active.php
3. Should see table with active loans
4. Try searching by company name
5. Try "Show overdue only" filter
6. Check that overdue loans are highlighted in red
7. Check that due-soon loans are highlighted in yellow

============================================
NEXT STEPS
============================================

After testing this page:
1. Next: Create loan_return.php (process returns)
2. Then: Create loans_history.php (search all loans)
3. Finally: Create loan_view.php (detailed view)

You're building a complete loan tracking system!
-->