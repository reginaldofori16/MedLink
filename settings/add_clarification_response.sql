-- Add clarification_response column to prescriptions table
-- This column stores the patient's response to hospital's clarification request

USE medlink_db;

ALTER TABLE prescriptions 
ADD COLUMN clarification_response TEXT NULL 
COMMENT 'Patient response to hospital clarification request' 
AFTER clarification_message;

-- Verify the column was added
DESCRIBE prescriptions;

