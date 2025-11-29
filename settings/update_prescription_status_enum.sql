-- Update Prescription Status ENUM to include 'Confirmed by hospital'
-- Run this SQL script to update your existing database
-- This adds the missing status option that hospitals need

ALTER TABLE prescriptions 
MODIFY COLUMN status ENUM(
    'Submitted by patient',
    'Hospital reviewing',
    'Clarification requested',
    'Waiting for hospital',
    'Confirmed by hospital',
    'Rejected by hospital',
    'Sent to pharmacies',
    'Pharmacy reviewing',
    'Awaiting patient payment',
    'Payment received',
    'Ready for pickup',
    'Ready for delivery',
    'Dispensed',
    'On hold'
) DEFAULT 'Submitted by patient';



