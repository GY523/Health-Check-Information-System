# Database Schema Documentation
## Server Loaning System - Simplified Design

---

## Database Overview

**Database Name:** `server_loaning_system`  
**Tables:** 3 core tables  
**Relationships:** Simple foreign key relationships

---

## Entity Relationship Diagram (ERD)

```
┌─────────────────────┐
│      USERS          │
├─────────────────────┤
│ PK user_id          │
│    username         │
│    password         │
│    full_name        │
│    email            │
│    phone            │
│    department       │
│    role             │ (admin/user)
│    is_active        │
│    created_at       │
└──────────┬──────────┘
           │
           │ 1:N (borrower)
           │
┌──────────▼──────────┐         ┌─────────────────────┐
│      LOANS          │   N:1   │      ASSETS         │
├─────────────────────┤◄────────┤─────────────────────┤
│ PK loan_id          │         │ PK asset_id         │
│ FK asset_id         │         │    asset_type       │
│ FK borrower_id      │         │    manufacturer     │
│ FK approver_id      │         │    model            │
│    request_date     │         │    serial_number    │
│    approval_date    │         │    specifications   │
│    loan_start_date  │         │    status           │
│    expected_return  │         │    location         │
│    actual_return    │         │    notes            │
│    loan_purpose     │         │    created_at       │
│    status           │         │    updated_at       │
│    admin_notes      │         └─────────────────────┘
│    created_at       │
│    updated_at       │
└─────────────────────┘
```

---

## Table Details

### 1. USERS Table
**Purpose:** Store user accounts (both admins and regular users)

| Column | Type | Description |
|--------|------|-------------|
| user_id | INT (PK) | Auto-increment primary key |
| username | VARCHAR(50) | Unique login username |
| password | VARCHAR(255) | Hashed password (use MD5 or better) |
| full_name | VARCHAR(100) | User's full name |
| email | VARCHAR(100) | Contact email |
| phone | VARCHAR(20) | Contact phone |
| department | VARCHAR(100) | User's department/team |
| role | ENUM | 'admin' or 'user' |
| is_active | TINYINT(1) | 1=active, 0=inactive |
| created_at | TIMESTAMP | Account creation date |

**Key Points:**
- Admin can approve loans and manage system
- Regular users can only request loans
- Use MD5 for quick development (upgrade to bcrypt later)

---

### 2. ASSETS Table
**Purpose:** Store physical servers and security appliances inventory

| Column | Type | Description |
|--------|------|-------------|
| asset_id | INT (PK) | Auto-increment primary key |
| asset_type | ENUM | Server, Security Appliance, Network Device, Other |
| manufacturer | VARCHAR(100) | Brand (Dell, HP, Cisco, etc.) |
| model | VARCHAR(100) | Model number/name |
| serial_number | VARCHAR(100) | Unique serial number |
| specifications | TEXT | CPU, RAM, storage details |
| status | ENUM | Available, On Loan, Maintenance, Retired |
| location | VARCHAR(200) | Physical location |
| notes | TEXT | Additional information |
| created_at | TIMESTAMP | Record creation date |
| updated_at | TIMESTAMP | Last modification date |

**Key Points:**
- Status automatically updates when loan is created/returned
- Serial number should be unique
- Specifications stored as free text for flexibility

---

### 3. LOANS Table
**Purpose:** Track loan requests, approvals, and returns

| Column | Type | Description |
|--------|------|-------------|
| loan_id | INT (PK) | Auto-increment primary key |
| asset_id | INT (FK) | Links to assets table |
| borrower_id | INT (FK) | User who requested the loan |
| approver_id | INT (FK) | Admin who approved (NULL if pending) |
| request_date | TIMESTAMP | When request was submitted |
| approval_date | DATETIME | When admin approved (NULL if pending) |
| loan_start_date | DATE | When loan begins |
| expected_return_date | DATE | When asset should be returned |
| actual_return_date | DATE | When asset was actually returned (NULL if not returned) |
| loan_purpose | TEXT | Why user needs the asset |
| status | ENUM | Pending, Approved, Rejected, Active, Returned, Overdue |
| admin_notes | TEXT | Admin comments |
| created_at | TIMESTAMP | Record creation date |
| updated_at | TIMESTAMP | Last modification date |

**Key Points:**
- Status workflow: Pending → Approved → Active → Returned
- Can also be: Pending → Rejected (end)
- Overdue status set automatically if past expected_return_date
- One asset can have multiple loans over time (history)

---

## Status Workflows

### Asset Status Flow
```
Available → On Loan → Available
    ↓
Maintenance → Available
    ↓
Retired (end)
```

### Loan Status Flow
```
Pending → Approved → Active → Returned
   ↓
Rejected (end)

Active → Overdue (if past due date)
```

---

## Key Relationships

1. **Users → Loans (as Borrower)**
   - One user can have many loan requests
   - Relationship: 1:N

2. **Users → Loans (as Approver)**
   - One admin can approve many loans
   - Relationship: 1:N

3. **Assets → Loans**
   - One asset can have many loans (over time)
   - But only ONE active loan at a time
   - Relationship: 1:N

---

## Important Business Rules

### Asset Availability
- Asset status must be "Available" to create new loan
- When loan is approved and active, asset status → "On Loan"
- When loan is returned, asset status → "Available"

### Loan Approval
- Only admins can approve/reject loans
- Users can only create loan requests
- Approval sets approver_id and approval_date

### Overdue Detection
- Compare current date with expected_return_date
- If current_date > expected_return_date AND actual_return_date IS NULL
- Then status = "Overdue"

### Return Process
- Set actual_return_date to today
- Change loan status to "Returned"
- Change asset status back to "Available"

---

## Sample Queries

### Check Available Assets
```sql
SELECT * FROM assets WHERE status = 'Available' ORDER BY asset_type, model;
```

### Get Pending Approvals (for Admin)
```sql
SELECT l.*, a.model, u.full_name as borrower_name
FROM loans l
JOIN assets a ON l.asset_id = a.asset_id
JOIN users u ON l.borrower_id = u.user_id
WHERE l.status = 'Pending'
ORDER BY l.request_date;
```

### Get My Active Loans (for User)
```sql
SELECT l.*, a.model, a.serial_number
FROM loans l
JOIN assets a ON l.asset_id = a.asset_id
WHERE l.borrower_id = ? AND l.status IN ('Active', 'Overdue')
ORDER BY l.expected_return_date;
```

### Find Overdue Loans
```sql
SELECT l.*, a.model, u.full_name
FROM loans l
JOIN assets a ON l.asset_id = a.asset_id
JOIN users u ON l.borrower_id = u.user_id
WHERE l.status = 'Active' 
  AND l.expected_return_date < CURDATE()
  AND l.actual_return_date IS NULL;
```

### Get Asset Loan History
```sql
SELECT l.*, u.full_name as borrower_name
FROM loans l
JOIN users u ON l.borrower_id = u.user_id
WHERE l.asset_id = ?
ORDER BY l.request_date DESC;
```

---

## Installation Instructions

### Step 1: Start XAMPP
- Start Apache and MySQL

### Step 2: Open phpMyAdmin
- Go to http://localhost/phpmyadmin

### Step 3: Import Database
- Click "Import" tab
- Choose `database_schema.sql` file
- Click "Go"

### Step 4: Verify
- Database `server_loaning_system` should appear
- 3 tables created with sample data
- Default admin login: username=`admin`, password=`admin123`

---

## Security Notes (For Production)

⚠️ **Current Setup (Development Only):**
- Using MD5 for passwords (weak)
- Default admin password in plain text
- No input sanitization yet

✅ **Before Going Live:**
- Use `password_hash()` and `password_verify()` in PHP
- Change default admin password
- Add prepared statements to prevent SQL injection
- Add HTTPS
- Implement session security

---

## Next Steps

1. ✅ Database schema created
2. ⏭️ Create PHP connection file (db_config.php)
3. ⏭️ Build login system
4. ⏭️ Create CRUD pages for assets
5. ⏭️ Build loan request workflow
6. ⏭️ Create dashboard

---

**Schema Version:** 1.0  
**Last Updated:** 2024  
**Optimized for:** 7-day MVP development
