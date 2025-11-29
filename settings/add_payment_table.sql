-- Add Payment Table for MedLink Paystack Integration
-- Run this in phpMyAdmin SQL tab

USE medlink_db;

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL COMMENT 'Prescription that was paid for',
    patient_id INT NOT NULL COMMENT 'Patient who made the payment',
    amount DECIMAL(10, 2) NOT NULL COMMENT 'Payment amount in GHS',
    currency VARCHAR(10) DEFAULT 'GHS' COMMENT 'Currency code',
    payment_method VARCHAR(50) NOT NULL COMMENT 'Payment method: paystack, cash, etc',
    transaction_ref VARCHAR(100) UNIQUE NOT NULL COMMENT 'Paystack transaction reference',
    authorization_code VARCHAR(100) NULL COMMENT 'Paystack authorization code',
    payment_channel VARCHAR(50) NULL COMMENT 'card, mobile_money, bank, etc',
    payment_status ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    
    INDEX idx_prescription_id (prescription_id),
    INDEX idx_patient_id (patient_id),
    INDEX idx_transaction_ref (transaction_ref),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add payment_id column to prescriptions table if it doesn't exist
ALTER TABLE prescriptions 
ADD COLUMN payment_id INT NULL COMMENT 'Reference to payment record' AFTER total_amount,
ADD FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE SET NULL;

-- Add index for payment_id
ALTER TABLE prescriptions ADD INDEX idx_payment_id (payment_id);

