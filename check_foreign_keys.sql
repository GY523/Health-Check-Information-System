-- Check foreign key names first
SHOW CREATE TABLE loans;

-- Or use this query to find the exact foreign key name:
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = 'loans' 
AND TABLE_SCHEMA = 'server_loaning_system'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Then use the correct constraint name in the ALTER statement:
-- ALTER TABLE loans DROP FOREIGN KEY [actual_constraint_name];