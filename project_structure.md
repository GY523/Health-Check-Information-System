# Server Loaning System - Project Structure

## Recommended Folder Organization

```
server_loaning_system/
│
├── config/
│   └── db_config.php          # Database connection (create first)
│
├── includes/
│   ├── header.php             # Common header HTML
│   ├── footer.php             # Common footer HTML
│   └── functions.php          # Reusable PHP functions
│
├── assets/
│   ├── css/
│   │   └── style.css          # Custom styles (if needed)
│   ├── js/
│   │   └── script.js          # Custom JavaScript (if needed)
│   └── images/
│       └── logo.png           # Logo and images
│
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── assets_list.php        # View all assets
│   ├── asset_add.php          # Add new asset
│   ├── asset_edit.php         # Edit asset
│   ├── asset_delete.php       # Delete asset
│   ├── pending_approvals.php  # Approve/reject loans
│   ├── active_loans.php       # View active loans
│   └── process_return.php     # Process asset returns
│
├── user/
│   ├── dashboard.php          # User dashboard
│   ├── browse_assets.php      # Browse available assets
│   ├── request_loan.php       # Create loan request
│   └── my_loans.php           # View my loan history
│
├── auth/
│   ├── login.php              # Login page
│   ├── logout.php             # Logout handler
│   └── check_auth.php         # Session validation
│
├── index.php                  # Landing page (redirects to login)
└── README.md                  # Project documentation
```

---

## Development Order (7-Day Plan)

### Day 1: Setup & Authentication
1. Create folder structure
2. Import database schema
3. Create `config/db_config.php`
4. Create `auth/login.php`
5. Create `auth/logout.php`
6. Create `auth/check_auth.php`
7. Test login system

### Day 2: Admin - Asset Management
1. Create `admin/dashboard.php`
2. Create `admin/assets_list.php`
3. Create `admin/asset_add.php`
4. Create `admin/asset_edit.php`
5. Test CRUD operations

### Day 3: User - Browse & Request
1. Create `user/dashboard.php`
2. Create `user/browse_assets.php`
3. Create `user/request_loan.php`
4. Test loan request flow

### Day 4: Admin - Loan Approval
1. Create `admin/pending_approvals.php`
2. Create approval/rejection logic
3. Update asset status automatically
4. Test approval workflow

### Day 5: Loan Tracking
1. Create `user/my_loans.php`
2. Create `admin/active_loans.php`
3. Create `admin/process_return.php`
4. Add overdue detection

### Day 6: Polish & Features
1. Add search/filter functionality
2. Improve UI/UX
3. Add validation and error messages
4. Create common header/footer

### Day 7: Testing & Deployment
1. Test all workflows end-to-end
2. Fix bugs
3. Add sample data
4. Document usage
5. Deploy to internal server

---

## File Naming Conventions

- **Lowercase with underscores**: `asset_add.php` (not `AssetAdd.php`)
- **Descriptive names**: `pending_approvals.php` (not `approvals.php`)
- **Action-based**: `process_return.php` (not `return.php`)

---

## Code Organization Pattern

Each page should follow this structure:

```php
<?php
// 1. Start session and check authentication
session_start();
require_once '../auth/check_auth.php';
require_once '../config/db_config.php';

// 2. Process form submissions (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form data
}

// 3. Fetch data from database (GET)
// Query database for display

// 4. HTML output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Page Title</title>
</head>
<body>
    <!-- Display data -->
</body>
</html>
```

---

## Next Steps

1. Create the folder structure above in your XAMPP htdocs folder
2. Import the database schema into phpMyAdmin
3. I'll guide you to create the first files with learning exercises

Ready to start coding? Let me know when you've created the folders!
