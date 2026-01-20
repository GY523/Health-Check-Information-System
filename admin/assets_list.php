<?php
/**
 * Assets List Page
 * 
 * LEARNING OBJECTIVES:
 * - Practice SELECT queries
 * - Display database data in HTML tables
 * - Use PHP loops with database results
 * - Implement search/filter functionality
 * - Learn about SQL WHERE clauses
 */

require_once '../auth/check_auth.php';
requireAdmin(); // Only admins can manage assets

require_once '../config/db_config.php';

// ============================================
// HANDLE SEARCH/FILTER
// ============================================
// LEARNING NOTE: We'll add search functionality to filter assets

$search_term = '';
$status_filter = '';
$type_filter = '';

if (isset($_GET['search'])) {
    $search_term = trim($_GET['search']);
}
if (isset($_GET['status'])) {
    $status_filter = $_GET['status'];
}
if (isset($_GET['type'])) {
    $type_filter = $_GET['type'];
}

// ============================================
// BUILD QUERY WITH FILTERS
// ============================================
// LEARNING NOTE: We build the SQL query dynamically based on filters

$query = "SELECT * FROM assets WHERE 1=1";
$params = [];
$types = "";

// Add search condition
if (!empty($search_term)) {
    $query .= " AND (model LIKE ? OR manufacturer LIKE ? OR serial_number LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Add type filter
if (!empty($type_filter)) {
    $query .= " AND asset_type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

// ============================================
// EXECUTE QUERY
// ============================================
// LEARNING NOTE: We use prepared statements even for SELECT queries

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
    <title>Assets List - Server Loaning System</title>
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
                        <a href="assets_list.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-server"></i> View Assets
                        </a>
                        <a href="asset_add.php" class="list-group-item list-group-item-action">
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
                    <h2><i class="bi bi-server"></i> Assets Management</h2>
                    <a href="asset_add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Asset
                    </a>
                </div>

                <!-- Success Message for Deletion -->
                <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> Asset deleted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Search and Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Model, manufacturer, or serial number"
                                       value="<?php echo htmlspecialchars($search_term); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="Available" <?php echo $status_filter === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="On Loan" <?php echo $status_filter === 'On Loan' ? 'selected' : ''; ?>>On Loan</option>
                                    <option value="Maintenance" <?php echo $status_filter === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="Retired" <?php echo $status_filter === 'Retired' ? 'selected' : ''; ?>>Retired</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">All Types</option>
                                    <option value="Server" <?php echo $type_filter === 'Server' ? 'selected' : ''; ?>>Server</option>
                                    <option value="Security Appliance" <?php echo $type_filter === 'Security Appliance' ? 'selected' : ''; ?>>Security Appliance</option>
                                    <option value="Network Device" <?php echo $type_filter === 'Network Device' ? 'selected' : ''; ?>>Network Device</option>
                                    <option value="Other" <?php echo $type_filter === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                        <?php if (!empty($search_term) || !empty($status_filter) || !empty($type_filter)): ?>
                        <div class="mt-2">
                            <a href="assets_list.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear Filters
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Assets Table -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-table"></i> Assets List (<?php echo $result->num_rows; ?> items)</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Manufacturer</th>
                                        <th>Model</th>
                                        <th>Serial Number</th>
                                        <th>Status</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($asset = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $asset['asset_id']; ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($asset['asset_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($asset['manufacturer']); ?></td>
                                        <td><?php echo htmlspecialchars($asset['model']); ?></td>
                                        <td>
                                            <code><?php echo htmlspecialchars($asset['serial_number']); ?></code>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch($asset['status']) {
                                                case 'Available': $status_class = 'bg-success'; break;
                                                case 'On Loan': $status_class = 'bg-warning text-dark'; break;
                                                case 'Maintenance': $status_class = 'bg-info'; break;
                                                case 'Retired': $status_class = 'bg-danger'; break;
                                                default: $status_class = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($asset['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($asset['location']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="asset_edit.php?id=<?php echo $asset['asset_id']; ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="asset_view.php?id=<?php echo $asset['asset_id']; ?>" 
                                                   class="btn btn-outline-info" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="confirmDelete(<?php echo $asset['asset_id']; ?>)" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
                            <h4 class="text-muted">No Assets Found</h4>
                            <p class="text-muted">
                                <?php if (!empty($search_term) || !empty($status_filter) || !empty($type_filter)): ?>
                                    No assets match your search criteria. <a href="assets_list.php">Clear filters</a> to see all assets.
                                <?php else: ?>
                                    You haven't added any assets yet. <a href="asset_add.php">Add your first asset</a>.
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
    <script>
        function confirmDelete(assetId) {
            if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
                window.location.href = 'asset_delete.php?id=' + assetId;
            }
        }
    </script>
</body>
</html>

<!-- 
============================================
LEARNING EXERCISE #1
============================================
TODO: Add sorting functionality
Allow users to sort by different columns (click column headers)

HINT:
1. Add ?sort=column_name&order=asc/desc to URL
2. Modify ORDER BY clause based on parameters
3. Add sorting arrows to column headers
4. Remember current sort when filtering

============================================
LEARNING EXERCISE #2
============================================
TODO: Add pagination
Show only 10-20 items per page with navigation

HINT:
1. Add LIMIT and OFFSET to query
2. Count total records for pagination info
3. Add page navigation buttons
4. Preserve filters when changing pages

============================================
WHAT YOU'VE LEARNED
============================================

✅ SELECT queries with WHERE conditions
✅ Dynamic query building with user input
✅ Prepared statements with multiple parameters
✅ PHP loops with database results (while loop)
✅ HTML table generation from database
✅ Search and filter functionality
✅ Status badges and conditional styling
✅ Professional table layout with Bootstrap

============================================
TESTING INSTRUCTIONS
============================================

1. Login as admin
2. Click "View Assets" in sidebar
3. Should see table with sample assets from database
4. Try searching for "Dell" or "Server"
5. Try filtering by status "Available"
6. Try filtering by type "Server"
7. Click "Clear Filters" to reset

============================================
NEXT STEPS
============================================

After testing this page:
1. Next: Create asset_add.php (CREATE operation)
2. Then: Create asset_edit.php (UPDATE operation)
3. Finally: Create asset_delete.php (DELETE operation)

You're learning the fundamental CRUD operations!
-->