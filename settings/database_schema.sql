-- MedLink Database Schema
-- Supports: Hospitals, Pharmacies, Patients, and Prescriptions

-- Drop existing tables if they exist (for fresh installation)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS prescription_timeline;
DROP TABLE IF EXISTS prescription_medicines;
DROP TABLE IF EXISTS prescriptions;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS pharmacies;
DROP TABLE IF EXISTS hospitals;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- USERS TABLES
-- ============================================

-- Hospitals Table
CREATE TABLE hospitals (
    hospital_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Hospital Name',
    government_id VARCHAR(100) UNIQUE NOT NULL COMMENT 'Government Issued Hospital ID',
    contact VARCHAR(255) NOT NULL COMMENT 'Contact Information (Phone or Email)',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    status ENUM('pending', 'active', 'suspended', 'rejected') DEFAULT 'pending',
    registered_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_government_id (government_id),
    INDEX idx_status (status),
    INDEX idx_registered_date (registered_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pharmacies Table
CREATE TABLE pharmacies (
    pharmacy_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Pharmacy Name',
    government_id VARCHAR(100) UNIQUE NOT NULL COMMENT 'Government Issued Pharmacy ID',
    contact VARCHAR(255) NOT NULL COMMENT 'Contact Information (Phone or Email)',
    location VARCHAR(255) NOT NULL COMMENT 'Pharmacy Location',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    status ENUM('pending', 'active', 'suspended', 'rejected') DEFAULT 'pending',
    registered_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_government_id (government_id),
    INDEX idx_status (status),
    INDEX idx_location (location),
    INDEX idx_registered_date (registered_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Patients Table (Individuals)
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL COMMENT 'Full Name',
    email VARCHAR(255) UNIQUE NOT NULL COMMENT 'Email Address',
    phone VARCHAR(20) NOT NULL COMMENT 'Phone Number',
    country VARCHAR(100) NOT NULL COMMENT 'Country',
    city VARCHAR(100) NOT NULL COMMENT 'City',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    user_role INT DEFAULT 2 COMMENT '1 = admin, 2 = customer',
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    registered_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_status (status),
    INDEX idx_country (country),
    INDEX idx_city (city),
    INDEX idx_registered_date (registered_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PRESCRIPTIONS TABLES
-- ============================================

-- Prescriptions Table
CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL COMMENT 'Patient who submitted the prescription',
    hospital_id INT NOT NULL COMMENT 'Hospital the prescription was submitted to',
    prescription_code VARCHAR(50) UNIQUE NOT NULL COMMENT 'Prescription ID like RX-2025-014',
    doctor_name VARCHAR(255) NOT NULL COMMENT 'Doctor name',
    visit_date DATE NOT NULL COMMENT 'Date of hospital visit',
    prescription_image_path VARCHAR(500) NULL COMMENT 'Path to uploaded prescription image file',
    status ENUM(
        'Submitted by patient',
        'Hospital reviewing',
        'Clarification requested',
        'Waiting for hospital',
        'Sent to pharmacies',
        'Pharmacy reviewing',
        'Awaiting patient payment',
        'Payment received',
        'Ready for pickup',
        'Ready for delivery',
        'Dispensed',
        'On hold'
    ) DEFAULT 'Submitted by patient',
    clarification_message TEXT NULL COMMENT 'Clarification request message from hospital',
    clarification_response TEXT NULL COMMENT 'Patient response to hospital clarification request',
    pharmacy_id INT NULL COMMENT 'Pharmacy assigned to fulfill the prescription',
    total_amount DECIMAL(10, 2) NULL COMMENT 'Total cost of medicines',
    submitted_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE SET NULL,
    INDEX idx_patient_id (patient_id),
    INDEX idx_hospital_id (hospital_id),
    INDEX idx_pharmacy_id (pharmacy_id),
    INDEX idx_status (status),
    INDEX idx_prescription_code (prescription_code),
    INDEX idx_submitted_date (submitted_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prescription Medicines Table
CREATE TABLE prescription_medicines (
    prescription_medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL COMMENT 'Prescription this medicine belongs to',
    medicine_name VARCHAR(255) NOT NULL COMMENT 'Name of the medicine',
    dosage VARCHAR(100) NOT NULL COMMENT 'Medicine dosage (e.g., "1 capsule", "2 tablets")',
    frequency VARCHAR(100) NOT NULL COMMENT 'Frequency of intake (e.g., "3x daily", "Nightly")',
    duration VARCHAR(100) NOT NULL COMMENT 'Duration of treatment (e.g., "7 days", "30 days")',
    price DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Price set by pharmacy for this medicine',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE,
    INDEX idx_prescription_id (prescription_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prescription Timeline Table (for tracking status changes and history)
CREATE TABLE prescription_timeline (
    timeline_id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL COMMENT 'Prescription this timeline entry belongs to',
    status_text VARCHAR(500) NOT NULL COMMENT 'Description of what happened (e.g., "Submitted to hospital")',
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE,
    INDEX idx_prescription_id (prescription_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- END OF SCHEMA
-- ============================================
