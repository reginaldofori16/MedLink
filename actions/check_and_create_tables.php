<?php
/**
 * Check and Create Missing Payment Tables
 * Run this once to ensure all tables exist
 */

require_once('../settings/db_class.php');

$db = new db_connection();
$conn = $db->db_conn();

$errors = [];
$success = [];

// Check and create order_details table
$check = mysqli_query($conn, "SHOW TABLES LIKE 'order_details'");
if (mysqli_num_rows($check) == 0) {
    $sql = "CREATE TABLE order_details (
        order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        prescription_medicine_id INT NOT NULL,
        medicine_name VARCHAR(255) NOT NULL,
        dosage VARCHAR(100) NOT NULL,
        frequency VARCHAR(100) NOT NULL,
        duration VARCHAR(100) NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        subtotal DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES prescription_orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (prescription_medicine_id) REFERENCES prescription_medicines(prescription_medicine_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($conn, $sql)) {
        $success[] = "✓ order_details table created";
    } else {
        $errors[] = "✗ Failed to create order_details: " . mysqli_error($conn);
    }
} else {
    $success[] = "✓ order_details table already exists";
}

// Check and create prescription_payment table
$check = mysqli_query($conn, "SHOW TABLES LIKE 'prescription_payment'");
if (mysqli_num_rows($check) == 0) {
    $sql = "CREATE TABLE prescription_payment (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        patient_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        currency VARCHAR(10) DEFAULT 'GHS',
        payment_method VARCHAR(50) NOT NULL,
        transaction_reference VARCHAR(100) UNIQUE NOT NULL,
        authorization_code VARCHAR(100) NULL,
        payment_channel VARCHAR(50) NULL,
        card_type VARCHAR(50) NULL,
        payment_status ENUM('pending', 'success', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
        payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        paystack_response TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES prescription_orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($conn, $sql)) {
        $success[] = "✓ prescription_payment table created";
    } else {
        $errors[] = "✗ Failed to create prescription_payment: " . mysqli_error($conn);
    }
} else {
    $success[] = "✓ prescription_payment table already exists";
}

// Output results
header('Content-Type: application/json');
echo json_encode([
    'success' => $success,
    'errors' => $errors,
    'ready' => empty($errors)
]);
?>

