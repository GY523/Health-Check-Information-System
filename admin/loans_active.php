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
requireAdminOrEngineer();

require_once '../config/db_config.php';

// ============================================
// HANDLE SEARCH/FILTER
// ============================================

$search_company = '';
$overdue_only = false;

if (isset($_GET['search_company'])) {
    $search_company = trim($_GET['search_company']);
}
if (isset($_GET['overdue_only'])) {
    $overdue_only = true;
}

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Loans - Server Loaning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .overdue-row {
            background-color: #fff2f2;
        }
        .due-soon-row {
            background-color: #fff8e1;
        }
    </style>
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
                        <a href="loan_record.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle"></i> Record New Loan
                        </a>
                        <a href="loans_active.php" class="list-group-item list-group-item-action active">
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
                    <h2><i class="bi bi-arrow-left-right"></i> Active Loans</h2>
                    <a href="loan_record.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Record New Loan
                    </a>
                </div>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="search_company" class="form-label">Search by Company</label>
                                <input type="text" class="form-control" id="search_company" name="search_company" 
                                       placeholder="Company name"
                                       value="<?php echo htmlspecialchars($search_company); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="overdue_only" name="overdue_only" 
                                           <?php echo $overdue_only ? 'checked' : ''; ?>>
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
                        </form>
                        <?php if (!empty($search_company) || $overdue_only): ?>
                        <div class="mt-2">
                            <a href="loans_active.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear Filters
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Active Loans Table -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-table"></i> Active Loans (<?php echo $result->num_rows; ?> loans)</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
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
                                <tbody>
                                    <?php while ($loan = $result->fetch_assoc()): ?>
                                    <?php
                                    $row_class = '';
                                    if ($loan['is_overdue']) {
                                        $row_class = 'overdue-row';
                                    } elseif ($loan['days_until_due'] <= 3 && $loan['days_until_due'] >= 0) {
                                        $row_class = 'due-soon-row';
                                    }
                                    ?>
                                    <tr class="<?php echo $row_class; ?>">
                                        <td>
                                            <strong>#<?php echo $loan['loan_id']; ?></strong>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($loan['customer_company']); ?></strong>
                                            <?php if (!empty($loan['customer_email'])): ?>
                                            <br><small class="text-muted">
                                                <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($loan['customer_email']); ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($loan['asset_type']); ?></span><br>
                                            <strong><?php echo htmlspecialchars($loan['manufacturer'] . ' ' . $loan['model']); ?></strong><br>
                                            <small class="text-muted">SN: <?php echo htmlspecialchars($loan['serial_number']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($loan['loan_start_date'])); ?>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($loan['expected_return_date'])); ?>
                                            <br>
                                            <?php if ($loan['is_overdue']): ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-exclamation-triangle"></i> 
                                                    <?php echo abs($loan['days_until_due']); ?> days overdue
                                                </span>
                                            <?php elseif ($loan['days_until_due'] <= 3): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock"></i> 
                                                    Due in <?php echo $loan['days_until_due']; ?> days
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <?php echo $loan['days_until_due']; ?> days left
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">Active</span>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars(substr($loan['loan_purpose'], 0, 50)); ?><?php echo strlen($loan['loan_purpose']) > 50 ? '...' : ''; ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="loan_view.php?id=<?php echo $loan['loan_id']; ?>" 
                                                   class="btn btn-outline-info" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="loan_return.php?id=<?php echo $loan['loan_id']; ?>" 
                                                   class="btn btn-outline-success" title="Process Return">
                                                    <i class="bi bi-arrow-return-left"></i>
                                                </a>
                                                <a href="loan_edit.php?id=<?php echo $loan['loan_id']; ?>" 
                                                   class="btn btn-outline-primary" title="Edit Loan">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="text-muted">No Active Loans Found</h4>
                            <p class="text-muted">
                                <?php if (!empty($search_company) || $overdue_only): ?>
                                    No loans match your search criteria. <a href="loans_active.php">Clear filters</a> to see all active loans.
                                <?php else: ?>
                                    No assets are currently on loan. <a href="loan_record.php">Record a new loan</a> to get started.
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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