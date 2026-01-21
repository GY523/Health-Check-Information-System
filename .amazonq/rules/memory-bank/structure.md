# Health Check Information System - Project Structure

## Directory Organization

### Core Application Structure
```
Health Check Information System/
├── admin/                    # Main application functionality
│   ├── dashboard.php         # System dashboard with metrics
│   ├── loan_record.php       # Record new loans
│   ├── loans_active.php      # View active loans
│   ├── assets_list.php       # Asset management interface
│   ├── asset_add.php         # Add new assets
│   ├── asset_edit.php        # Edit existing assets
│   └── asset_delete.php      # Delete assets (with validation)
│
├── auth/                     # Authentication system
│   ├── login.php             # User login interface
│   ├── logout.php            # Session termination
│   └── check_auth.php        # Authentication validation
│
├── config/                   # Configuration files
│   └── db_config.php         # Database connection settings
│
├── includes/                 # Shared components
│   └── layout.php            # Template system foundation
│
├── user/                     # Future user functionality
│
├── development/              # Development resources
│   ├── database_schema.sql   # Database structure
│   ├── DEVELOPMENT_CONTEXT.md # Project requirements log
│   └── project_structure.md  # Architecture documentation
│
└── index.php                 # Application entry point
```

## Core Components & Relationships

### Authentication Layer
- **check_auth.php**: Central authentication validation
- **Role-based access**: Admin and Engineer roles with equal permissions
- **Session management**: Secure session handling with proper cleanup

### Database Layer
- **db_config.php**: MySQLi connection with UTF-8 support
- **Connection management**: Proper error handling and character encoding
- **Security**: Prepared statements for SQL injection prevention

### Application Layer
- **Admin module**: All primary functionality accessible to both admin and engineer roles
- **Template system**: Centralized layout management through includes/layout.php
- **Business logic**: Embedded within individual pages with proper validation

### Data Model Relationships
```
users (admin/engineer) → loans (created_by_user_id)
assets (servers/appliances) → loans (asset_id)
loans → customers (company tracking)
```

## Architectural Patterns

### Authentication Pattern
- Session-based authentication with role validation
- Centralized auth checking through require_once includes
- Consistent redirect logic for unauthorized access

### Database Access Pattern
- Direct MySQLi usage with prepared statements
- Connection established once in db_config.php
- Proper error handling and connection cleanup

### UI/UX Pattern
- Bootstrap-based responsive design
- Consistent navigation structure across all pages
- Template system for shared layout components

### Business Logic Pattern
- Page-level business logic with form processing
- Validation at both client and server side
- Status-based workflow management (Active/Returned/Cancelled)

## Navigation Structure
Standardized across all pages:
1. Dashboard (metrics and overview)
2. Record New Loan (loan creation)
3. Active Loans (current loan tracking)
4. Loan History (historical records)
5. ─── (visual separator)
6. View Assets (asset listing)
7. Add Asset (asset creation)

## Security Architecture
- Role-based access control (RBAC)
- SQL injection prevention through prepared statements
- Session security with proper timeout handling
- Input validation and sanitization

## Template System
- **layout.php**: Centralized template management
- **Consistent styling**: Bootstrap integration
- **Modular design**: Reusable components for headers, navigation, and footers
- **Responsive design**: Mobile-friendly interface

## Data Flow Architecture
1. **Request Processing**: Authentication → Authorization → Business Logic
2. **Database Operations**: Connection → Query → Result Processing → Cleanup
3. **Response Generation**: Data Preparation → Template Rendering → Output

## Scalability Considerations
- Template system foundation for future MVC implementation
- Modular component structure
- Separation of concerns between authentication, business logic, and presentation
- Database schema designed for extensibility