# Health Check Information System

A comprehensive server loaning management system designed to track and manage the loaning of physical servers and security appliances for presales Proof of Concept (PoC) purposes.

## 🎯 Purpose

This system streamlines the workflow from email requests to asset tracking and return processing, providing centralized asset management and real-time status tracking for internal teams.

## ✨ Features

### Asset Management
- Complete CRUD operations for servers, security appliances, and network devices
- Asset categorization with detailed specifications
- Availability status tracking with business logic validation
- Search and filtering capabilities

### Loan Management
- Direct loan recording by admin and engineer staff
- Customer company and contact tracking
- Three-status workflow: Active, Returned, Cancelled
- Overdue detection and tracking
- Comprehensive loan history and search

### User Authentication & Roles
- Two-tier role system: Admin and Engineer (equal permissions)
- Session-based authentication with proper security measures
- Role-based dashboard customization

### Business Intelligence
- Dashboard with key metrics and overdue alerts
- Active loans monitoring with customer details
- Asset utilization tracking
- Advanced search and filtering across all entities

## 🛠️ Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+ / MariaDB 10.2+
- **Frontend:** Bootstrap 5.1.3, Bootstrap Icons 1.7.2
- **Architecture:** MVC-style template system
- **Security:** Prepared statements, input sanitization, session management

## 📋 Requirements

- **Web Server:** Apache 2.4+ or Nginx
- **PHP:** Version 7.4+ with MySQLi extension
- **Database:** MySQL 5.7+ or MariaDB 10.2+
- **Development:** XAMPP (for local development)

## 🚀 Installation

### Local Development Setup

1. **Install XAMPP**
   - Download and install XAMPP with Apache, MySQL, and PHP
   - Start Apache and MySQL services

2. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/health-check-information-system.git
   cd health-check-information-system
   ```

3. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create database: `server_loaning_system`
   - Import: `development/database_schema.sql`

4. **Configuration**
   - Copy project to XAMPP htdocs folder
   - Update database credentials in `config/db_config.php` if needed

5. **Access Application**
   - Navigate to: `http://localhost/health-check-information-system/`
   - Default login credentials in database schema

## 📁 Project Structure

```
Health Check Information System/
├── admin/                    # Main application functionality
│   ├── dashboard.php         # System dashboard with metrics
│   ├── loan_record.php       # Record new loans
│   ├── loans_active.php      # View active loans
│   ├── loans_history.php     # Loan history and search
│   ├── loan_view.php         # Detailed loan view
│   ├── loan_return.php       # Process loan returns
│   ├── loan_cancel.php       # Cancel active loans
│   ├── assets_list.php       # Asset management interface
│   ├── asset_add.php         # Add new assets
│   ├── asset_edit.php        # Edit existing assets
│   └── asset_delete.php      # Delete assets (with validation)
├── auth/                     # Authentication system
│   ├── login.php             # User login interface
│   ├── logout.php            # Session termination
│   └── check_auth.php        # Authentication validation
├── config/                   # Configuration files
│   └── db_config.php         # Database connection settings
├── includes/                 # Template system
│   └── layout.php            # Centralized layout management
├── development/              # Development resources
│   ├── database_schema.sql   # Database structure
│   └── *.md                  # Documentation files
└── index.php                 # Application entry point
```

## 🎮 Usage

### For Administrators/Engineers

1. **Login** with your credentials
2. **Dashboard** - View system overview and statistics
3. **Record Loans** - Create new loan records for customers
4. **Manage Assets** - Add, edit, or remove assets from inventory
5. **Track Active Loans** - Monitor current loans and overdue items
6. **Process Returns** - Handle asset returns and cancellations
7. **Search History** - Find past loans and generate reports

### Typical Workflow

1. **Email Request** → Admin/Engineer receives loan request
2. **Check Availability** → Verify asset availability in system
3. **Record Loan** → Create loan record with customer details
4. **Asset Assignment** → Asset status automatically updated to "On Loan"
5. **Return Processing** → Mark as returned when asset comes back
6. **Status Update** → Asset becomes available for new loans

## 🔧 Key Features

### Template System Architecture
- **70% code reduction** through centralized layout management
- **MVC-style separation** of business logic and presentation
- **Consistent UI/UX** across all pages
- **Single point of maintenance** for layout changes

### Security Features
- **Prepared statements** for SQL injection prevention
- **Input validation** and sanitization
- **Session management** with timeout handling
- **Role-based access control**

### Business Logic
- **Asset availability validation** prevents double-booking
- **Foreign key constraints** maintain data integrity
- **Transaction management** ensures data consistency
- **Overdue detection** with visual indicators

## 📊 Database Schema

### Core Tables
- **users** - Admin and Engineer authentication
- **assets** - Physical server and appliance inventory
- **loans** - Loan transaction records with customer tracking

### Key Relationships
- Foreign key constraints between users, assets, and loans
- Referential integrity enforcement
- Cascade delete prevention for active loans

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Check the documentation in the `development/` folder
- Review the learning comments in the code
- Create an issue in the GitHub repository

## 🎓 Educational Value

This project serves as a comprehensive learning resource for:
- PHP/MySQL development
- MVC architecture patterns
- Database design and relationships
- Security best practices
- Professional web application development

Each file contains detailed learning objectives, explanations, and testing instructions to help developers understand the concepts and implementation.

---

**Version:** 1.0.0  
**Status:** Stable Release  
**Last Updated:** 23/1/2026
