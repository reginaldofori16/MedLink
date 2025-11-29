-- Add price column to prescription_medicines table
-- This allows pharmacies to set individual medicine prices

USE medlink_db;

-- Add price column for individual medicines
ALTER TABLE prescription_medicines 
ADD COLUMN price DECIMAL(10,2) NULL DEFAULT NULL 
COMMENT 'Price set by pharmacy for this medicine' 
AFTER duration;

-- Verify the column was added
DESCRIBE prescription_medicines;

