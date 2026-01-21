-- Minimal database update - just add customer tracking
-- Run this in phpMyAdmin

-- Add customer company and email columns
ALTER TABLE loans 
ADD COLUMN customer_company VARCHAR(200) AFTER loan_purpose,
ADD COLUMN customer_email VARCHAR(200) AFTER customer_company;

-- Update loan statuses to simplified version
ALTER TABLE loans MODIFY COLUMN status ENUM('Active', 'Returned', 'Cancelled') DEFAULT 'Active';

-- Update user roles
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'engineer') DEFAULT 'engineer';

-- Add sample data
UPDATE loans SET 
    customer_company = 'ABC Corporation', 
    customer_email = 'presales@abccorp.com'
WHERE loan_id = 1;