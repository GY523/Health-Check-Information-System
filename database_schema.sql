-- ============================================
-- Server Loaning System - Simplified Database Schema
-- Database: server_loaning_system
-- ============================================

CREATE DATABASE IF NOT EXISTS server_loaning_system;
USE server_loaning_system;

-- ============================================
-- Table 1: users
-- ============================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    department VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table 2: assets
-- ============================================
CREATE TABLE assets (
    asset_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_type ENUM('Server', 'Security Appliance', 'Network Device', 'Other') NOT NULL,
    manufacturer VARCHAR(100),
    model VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100) UNIQUE,
    specifications TEXT,
    status ENUM('Available', 'On Loan', 'Maintenance', 'Retired') DEFAULT 'Available',
    location VARCHAR(200),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table 3: loans
-- ============================================
CREATE TABLE loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    borrower_id INT NOT NULL,
    approver_id INT,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_date DATETIME,
    loan_start_date DATE NOT NULL,
    expected_return_date DATE NOT NULL,
    actual_return_date DATE,
    loan_purpose TEXT,
    status ENUM('Pending', 'Approved', 'Rejected', 'Active', 'Returned', 'Overdue') DEFAULT 'Pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE,
    FOREIGN KEY (borrower_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Indexes for better performance
-- ============================================
CREATE INDEX idx_asset_status ON assets(status);
CREATE INDEX idx_loan_status ON loans(status);
CREATE INDEX idx_loan_borrower ON loans(borrower_id);
CREATE INDEX idx_loan_asset ON loans(asset_id);

-- ============================================
-- Insert default admin user
-- Password: admin123 (hashed with PASSWORD() - change in production!)
-- ============================================
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', MD5('admin123'), 'System Administrator', 'admin@company.com', 'admin');

-- ============================================
-- Sample data for testing (optional)
-- ============================================

-- Sample users
INSERT INTO users (username, password, full_name, email, department, role) VALUES
('john.doe', MD5('password123'), 'John Doe', 'john.doe@company.com', 'IT Department', 'user'),
('jane.smith', MD5('password123'), 'Jane Smith', 'jane.smith@company.com', 'Security Team', 'user');

-- Sample assets
INSERT INTO assets (asset_type, manufacturer, model, serial_number, specifications, status, location) VALUES
('Server', 'Dell', 'PowerEdge R740', 'SN001234567', 'Intel Xeon, 64GB RAM, 2TB SSD', 'Available', 'Server Room A'),
('Server', 'HP', 'ProLiant DL380', 'SN987654321', 'Intel Xeon, 128GB RAM, 4TB HDD', 'Available', 'Server Room A'),
('Security Appliance', 'Cisco', 'ASA 5516-X', 'SN456789123', 'Firewall, 8 Ports, 1Gbps', 'Available', 'Network Lab'),
('Network Device', 'Juniper', 'EX4300-48T', 'SN789123456', '48-Port Switch, 10G Uplinks', 'On Loan', 'Storage Room');

-- Sample loan (for testing)
INSERT INTO loans (asset_id, borrower_id, approver_id, loan_start_date, expected_return_date, loan_purpose, status, approval_date) VALUES
(4, 2, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Network testing project', 'Active', NOW());

-- Update asset status to match loan
UPDATE assets SET status = 'On Loan' WHERE asset_id = 4;
