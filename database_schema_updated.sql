-- ============================================
-- Updated Database Schema for Loan Tracking System
-- Simplified for Admin/Engineer workflow
-- ============================================

-- Update user roles (remove regular users)
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'engineer') DEFAULT 'engineer';

-- Update loan statuses (simplified)
ALTER TABLE loans MODIFY COLUMN status ENUM('Active', 'Returned', 'Cancelled') DEFAULT 'Active';

-- Add customer/company tracking to loans table (simplified)
ALTER TABLE loans 
ADD COLUMN customer_company VARCHAR(200) NOT NULL AFTER loan_purpose,
ADD COLUMN customer_email VARCHAR(200) AFTER customer_company,
ADD COLUMN loan_notes TEXT AFTER admin_notes;

-- Remove approval workflow columns (drop foreign key first)
ALTER TABLE loans DROP FOREIGN KEY loans_ibfk_3;
ALTER TABLE loans 
DROP COLUMN approver_id,
DROP COLUMN approval_date;

-- Rename columns for clarity
ALTER TABLE loans 
CHANGE COLUMN borrower_id created_by_user_id INT NOT NULL,
CHANGE COLUMN admin_notes internal_notes TEXT;

-- Add indexes for better performance
CREATE INDEX idx_customer_company ON loans(customer_company);
CREATE INDEX idx_loan_dates ON loans(loan_start_date, expected_return_date);

-- ============================================
-- Sample data for testing (optional)
-- ============================================

-- Add engineer user
INSERT INTO users (username, password, full_name, email, role) VALUES
('engineer1', MD5('password123'), 'John Engineer', 'john.engineer@company.com', 'engineer');

-- Update existing sample loan with customer info
UPDATE loans SET 
    customer_company = 'ABC Corporation', 
    customer_email = 'presales@abccorp.com',
    loan_purpose = 'PoC demonstration for new client',
    status = 'Active'
WHERE loan_id = 1;

-- ============================================
-- Updated table structure summary
-- ============================================

/*
LOANS TABLE (Updated):
- loan_id (PK)
- asset_id (FK to assets)
- created_by_user_id (FK to users - admin/engineer who recorded the loan)
- request_date (when loan was recorded in system)
- loan_start_date (when asset was actually loaned out)
- expected_return_date (when it should be returned)
- actual_return_date (when it was actually returned)
- loan_purpose (why they need it - PoC, demo, etc.)
- customer_company (client company name - main search field)
- customer_email (contact email)
- status (Active, Returned, Cancelled)
- internal_notes (admin/engineer notes)
- loan_notes (additional tracking notes)
- created_at, updated_at (timestamps)

WORKFLOW:
1. Email received → Admin/Engineer logs loan with company name
2. Asset assigned → Status = Active
3. Customer uses asset → Track with notes
4. Asset returned → Status = Returned, actual_return_date set
5. If cancelled → Status = Cancelled

SEARCH: Primarily by customer_company for finding loan history
*/