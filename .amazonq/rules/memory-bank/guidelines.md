# Health Check Information System - Development Guidelines

## Code Quality Standards

### PHP Coding Standards
- **File Structure**: All PHP files start with `<?php` opening tag and detailed docblock comments
- **Documentation**: Extensive inline comments with learning objectives and explanations
- **Error Handling**: Comprehensive error checking with user-friendly messages
- **Security**: Consistent use of prepared statements and input sanitization

### Naming Conventions
- **Files**: Lowercase with underscores (e.g., `asset_edit.php`, `check_auth.php`)
- **Variables**: Snake_case for variables (`$asset_id`, `$success_message`)
- **Functions**: CamelCase for functions (`getCurrentUserName()`, `requireAdminOrEngineer()`)
- **Constants**: UPPERCASE with underscores (`DB_HOST`, `DB_NAME`)

### Code Formatting Patterns
- **Indentation**: 4 spaces consistently used throughout codebase
- **Line Spacing**: Logical sections separated by blank lines with comment headers
- **Comment Blocks**: Structured comment sections using `// ============================================`
- **HTML Integration**: Clean separation between PHP logic and HTML presentation

## Authentication & Security Patterns

### Session Management
```php
// Standard session initialization pattern
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Session security with regeneration and timeout
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
```

### Access Control Pattern
- **Role-based functions**: `isAdmin()`, `isEngineer()`, `isAdminOrEngineer()`
- **Requirement functions**: `requireAdmin()`, `requireAdminOrEngineer()`
- **Consistent inclusion**: `require_once '../auth/check_auth.php'` at top of protected pages

### Input Sanitization
```php
// Consistent null coalescing and escaping pattern
value="<?php echo htmlspecialchars($asset['field'] ?? ''); ?>"

// Form data sanitization
$field = trim($_POST['field']);
```

## Database Interaction Patterns

### Connection Management
```php
// Standard MySQLi connection pattern
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
```

### Prepared Statement Pattern
```php
// Standard prepared statement with error handling
$stmt = $conn->prepare("SELECT * FROM table WHERE field = ?");
$stmt->bind_param("s", $value);
$stmt->execute();
$result = $stmt->get_result();
// Process results
$stmt->close();
```

### Database Constants Usage
- Use `define()` for database credentials that never change
- Constants preferred over variables for security and performance
- Consistent naming: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`

## Form Processing Architecture

### Standard Form Processing Flow
```php
// 1. Authentication and includes
require_once '../auth/check_auth.php';
require_once '../config/db_config.php';

// 2. Initialize variables
$success_message = '';
$error_message = '';

// 3. Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form data
}

// 4. Handle GET requests (data fetching)
// 5. HTML output
```

### Validation Patterns
- **Error Collection**: Use arrays to collect multiple validation errors
- **Required Field Validation**: Check for empty values with appropriate messages
- **Unique Constraint Validation**: Database checks for duplicate values
- **Business Logic Validation**: Prevent invalid operations (e.g., deleting assets with active loans)

## UI/UX Design Patterns

### Bootstrap Integration
- **CDN Usage**: Bootstrap 5.1.3 and Bootstrap Icons 1.7.2 from CDN
- **Responsive Design**: Container-fluid with row/col grid system
- **Component Usage**: Cards, alerts, forms, buttons with consistent styling

### Navigation Structure
```php
// Standardized sidebar menu across all pages
$menuItems = [
    'dashboard' => ['icon' => 'speedometer2', 'text' => 'Dashboard'],
    'loan_record' => ['icon' => 'plus-circle', 'text' => 'Record New Loan'],
    // ... consistent menu structure
];
```

### Alert Message Pattern
```php
// Consistent success/error message display
<?php if (!empty($success_message)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
```

## Template System Architecture

### Layout Function Pattern
```php
// Centralized layout rendering
function renderLayout($title, $content, $activeMenu = '', $additionalCSS = '', $additionalJS = '') {
    // Template logic with parameter injection
}
```

### Helper Functions
- **renderAlert()**: Standardized alert message generation
- **renderPageHeader()**: Consistent page header with icons and actions
- **renderCard()**: Reusable card component generation

## Error Handling Standards

### Development vs Production
```php
// Development error reporting (enabled in db_config.php)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
```

### User-Friendly Error Messages
- Database errors converted to user-friendly messages
- Validation errors collected and displayed clearly
- Success messages with auto-hide functionality

### Redirect Patterns
```php
// Standard redirect with exit
header('Location: target_page.php');
exit();

// Conditional redirects based on authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}
```

## Learning-Oriented Documentation

### Educational Comments
- **Learning Objectives**: Each file starts with clear learning goals
- **Inline Explanations**: Code sections explained with "LEARNING NOTE" comments
- **Exercise Sections**: TODO exercises for skill development
- **Testing Instructions**: Step-by-step testing guidance

### Code Examples
- **Best Practice Demonstrations**: Show correct implementation patterns
- **Security Explanations**: Explain why certain approaches are used
- **Alternative Approaches**: Mention different ways to solve problems

## Business Logic Patterns

### Role-Based Access
- **Unified Permissions**: Admin and Engineer roles have equal access
- **Function-Based Checks**: Use helper functions rather than direct session checks
- **Consistent Enforcement**: Apply access control at page level

### Status Management
- **Three-State Workflow**: Active, Returned, Cancelled for loans
- **Status Validation**: Prevent invalid status transitions
- **Business Rule Enforcement**: Implement constraints (e.g., can't delete assets with active loans)

### Data Integrity
- **Foreign Key Relationships**: Proper database relationships maintained
- **Cascade Prevention**: Prevent deletion of referenced records
- **Audit Trail**: Track creation and modification timestamps

## Performance Considerations

### Database Optimization
- **Prepared Statements**: Prevent SQL injection and improve performance
- **Connection Reuse**: Single connection per request through db_config.php
- **Proper Indexing**: Database schema designed with appropriate indexes

### Frontend Optimization
- **CDN Usage**: External resources loaded from CDN
- **Minimal Custom Assets**: Rely on Bootstrap for styling
- **Progressive Enhancement**: JavaScript used for enhancement, not core functionality