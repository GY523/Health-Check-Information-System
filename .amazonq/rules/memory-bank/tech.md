# Health Check Information System - Technology Stack

## Programming Languages & Versions

### Primary Technologies
- **PHP**: Server-side scripting language (compatible with PHP 7.4+)
- **MySQL**: Database management system (MySQL 5.7+ or MariaDB 10.2+)
- **HTML5**: Markup language for web structure
- **CSS3**: Styling with Bootstrap framework integration
- **JavaScript**: Client-side scripting for enhanced user experience

### Database Technology
- **MySQLi Extension**: MySQL Improved extension for database connectivity
- **Character Encoding**: UTF-8 (utf8mb4) for international character support
- **Connection Method**: Object-oriented MySQLi with prepared statements

## Development Environment

### XAMPP Stack Requirements
- **Apache**: Web server (version 2.4+)
- **MySQL**: Database server (version 5.7+)
- **PHP**: Server-side processing (version 7.4+)
- **phpMyAdmin**: Database administration interface

### Database Configuration
```php
// Database connection settings
DB_HOST: 'localhost'
DB_USER: 'root' 
DB_PASS: '' (empty for XAMPP default)
DB_NAME: 'server_loaning_system'
```

## Build System & Dependencies

### Frontend Dependencies
- **Bootstrap 5**: CSS framework for responsive design
- **Bootstrap Icons**: Icon library for UI elements
- **jQuery**: JavaScript library for DOM manipulation (if needed)

### PHP Extensions Required
- **MySQLi**: Database connectivity
- **Session**: Session management
- **Filter**: Input validation and sanitization
- **JSON**: Data serialization (for future API development)

### Development Tools
- **phpMyAdmin**: Database schema management
- **XAMPP Control Panel**: Local server management
- **Git**: Version control system
- **Text Editor/IDE**: PHP development environment

## Database Schema

### Core Tables
```sql
users: User authentication and role management
assets: Physical server and appliance inventory
loans: Loan transaction records
```

### Key Relationships
- Foreign key constraints between users, assets, and loans
- Referential integrity enforcement
- Cascade delete prevention for active loans

## Development Commands

### Database Setup
```bash
# Import database schema
mysql -u root -p server_loaning_system < database_schema.sql

# Or use phpMyAdmin import feature
# Navigate to: http://localhost/phpmyadmin
```

### Local Development Server
```bash
# Start XAMPP services
# Use XAMPP Control Panel to start Apache and MySQL

# Access application
# http://localhost/Health Check Information System/
```

### Testing Commands
```php
// Test database connection
php test_connection.php

// Verify authentication system
// Login with admin credentials through web interface
```

## Configuration Files

### Database Configuration (config/db_config.php)
- MySQLi connection setup
- Error reporting configuration (development mode)
- Character encoding settings
- Connection validation and error handling

### Authentication Configuration (auth/check_auth.php)
- Session validation logic
- Role-based access control
- Redirect handling for unauthorized access

## Security Configuration

### PHP Security Settings
```php
// Development error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Production settings (comment out error reporting)
// Set proper session security in production
```

### Database Security
- Prepared statements for SQL injection prevention
- Input validation and sanitization
- Proper connection cleanup and resource management

## Performance Considerations

### Database Optimization
- Indexed columns for frequently queried fields
- Efficient query design with proper JOIN usage
- Connection pooling through persistent connections (if needed)

### Frontend Optimization
- Bootstrap CDN for faster loading
- Minimal custom CSS and JavaScript
- Responsive design for mobile compatibility

## Deployment Requirements

### Production Environment
- **Web Server**: Apache 2.4+ or Nginx
- **PHP**: Version 7.4+ with MySQLi extension
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **SSL Certificate**: HTTPS for secure authentication

### Environment Variables
- Database credentials (separate from code)
- Session security settings
- Error reporting configuration (disabled in production)

## Development Workflow

### Local Setup Process
1. Install XAMPP with Apache, MySQL, and PHP
2. Create project directory in htdocs
3. Import database schema through phpMyAdmin
4. Configure database connection in config/db_config.php
5. Test authentication system with admin login

### Code Organization Standards
- PHP files use .php extension
- Database queries use prepared statements
- HTML output properly escaped with htmlspecialchars()
- Session management follows PHP best practices