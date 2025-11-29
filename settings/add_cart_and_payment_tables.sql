-- ============================================
-- MEDLINK CART AND PAYMENT SYSTEM
-- Based on Week 9 Activity Requirements
-- ============================================

-- Cart Table (stores items patient wants to order)
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL COMMENT 'Patient who owns this cart',
    prescription_id INT NOT NULL COMMENT 'Prescription being ordered',
    prescription_medicine_id INT NOT NULL COMMENT 'Specific medicine from prescription',
    quantity INT DEFAULT 1 COMMENT 'Quantity of this medicine',
    price DECIMAL(10, 2) NOT NULL COMMENT 'Price per unit at time of adding to cart',
    added_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE,
    FOREIGN KEY (prescription_medicine_id) REFERENCES prescription_medicines(prescription_medicine_id) ON DELETE CASCADE,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_prescription_id (prescription_id),
    INDEX idx_prescription_medicine_id (prescription_medicine_id),
    INDEX idx_added_date (added_date),
    
    -- Prevent duplicate entries: same patient + prescription + medicine
    UNIQUE KEY unique_cart_item (patient_id, prescription_id, prescription_medicine_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Shopping cart for prescription medicines';


-- Prescription Orders Table (completed orders)
CREATE TABLE IF NOT EXISTS prescription_orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL COMMENT 'Patient who placed the order',
    prescription_id INT NOT NULL COMMENT 'Original prescription',
    pharmacy_id INT NULL COMMENT 'Pharmacy fulfilling the order',
    order_reference VARCHAR(100) UNIQUE NOT NULL COMMENT 'Unique order reference like ORD-2025-001',
    order_status ENUM(
        'pending_payment',
        'payment_confirmed',
        'processing',
        'ready_for_pickup',
        'ready_for_delivery',
        'completed',
        'cancelled',
        'refunded'
    ) DEFAULT 'pending_payment',
    subtotal DECIMAL(10, 2) NOT NULL COMMENT 'Subtotal before tax',
    tax_amount DECIMAL(10, 2) NOT NULL COMMENT 'Tax amount',
    total_amount DECIMAL(10, 2) NOT NULL COMMENT 'Total order amount',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    delivery_address TEXT NULL COMMENT 'Delivery address if applicable',
    notes TEXT NULL COMMENT 'Special instructions or notes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE SET NULL,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_prescription_id (prescription_id),
    INDEX idx_pharmacy_id (pharmacy_id),
    INDEX idx_order_reference (order_reference),
    INDEX idx_order_status (order_status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Completed prescription orders';


-- Order Details Table (individual medicines in each order)
CREATE TABLE IF NOT EXISTS order_details (
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL COMMENT 'Order this detail belongs to',
    prescription_medicine_id INT NOT NULL COMMENT 'Medicine from prescription',
    medicine_name VARCHAR(255) NOT NULL COMMENT 'Medicine name (snapshot)',
    dosage VARCHAR(100) NOT NULL COMMENT 'Dosage (snapshot)',
    frequency VARCHAR(100) NOT NULL COMMENT 'Frequency (snapshot)',
    duration VARCHAR(100) NOT NULL COMMENT 'Duration (snapshot)',
    quantity INT NOT NULL COMMENT 'Quantity ordered',
    unit_price DECIMAL(10, 2) NOT NULL COMMENT 'Price per unit at time of order',
    subtotal DECIMAL(10, 2) NOT NULL COMMENT 'Quantity × Unit Price',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES prescription_orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (prescription_medicine_id) REFERENCES prescription_medicines(prescription_medicine_id) ON DELETE CASCADE,
    
    INDEX idx_order_id (order_id),
    INDEX idx_prescription_medicine_id (prescription_medicine_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual items in each order';


-- Prescription Payment Table (payment records)
CREATE TABLE IF NOT EXISTS prescription_payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL COMMENT 'Order that was paid for',
    patient_id INT NOT NULL COMMENT 'Patient who made the payment',
    amount DECIMAL(10, 2) NOT NULL COMMENT 'Payment amount in GHS',
    currency VARCHAR(10) DEFAULT 'GHS' COMMENT 'Currency code',
    payment_method VARCHAR(50) NOT NULL COMMENT 'Payment method: paystack, cash, etc',
    transaction_reference VARCHAR(100) UNIQUE NOT NULL COMMENT 'Paystack transaction reference',
    authorization_code VARCHAR(100) NULL COMMENT 'Paystack authorization code',
    payment_channel VARCHAR(50) NULL COMMENT 'card, mobile_money, bank, ussd',
    card_type VARCHAR(50) NULL COMMENT 'visa, mastercard, verve, etc',
    payment_status ENUM('pending', 'success', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    paystack_response TEXT NULL COMMENT 'Full Paystack response JSON',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES prescription_orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    
    INDEX idx_order_id (order_id),
    INDEX idx_patient_id (patient_id),
    INDEX idx_transaction_reference (transaction_reference),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Payment records for prescription orders';


-- Add order_id reference to prescriptions table (optional but useful)
ALTER TABLE prescriptions 
ADD COLUMN IF NOT EXISTS order_id INT NULL COMMENT 'Reference to completed order' AFTER total_amount;

-- Add foreign key constraint
ALTER TABLE prescriptions 
ADD CONSTRAINT fk_prescription_order 
FOREIGN KEY (order_id) REFERENCES prescription_orders(order_id) ON DELETE SET NULL;

-- Add index
CREATE INDEX IF NOT EXISTS idx_order_id ON prescriptions(order_id);


-- ============================================
-- SUMMARY OF TABLES
-- ============================================
-- 1. cart: Stores items patient wants to order (like shopping cart)
-- 2. prescription_orders: Completed orders after payment
-- 3. order_details: Individual medicines in each order (normalized)
-- 4. prescription_payment: Payment records from Paystack
--
-- FLOW:
-- Patient views prescription → Adds to cart → Reviews cart → 
-- → Proceeds to checkout → Pays via Paystack → 
-- → Creates order + order_details + payment → Empties cart
-- ============================================

