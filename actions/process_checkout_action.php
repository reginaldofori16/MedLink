<?php
/**
 * Process Checkout Action
 * Handles the complete checkout process with Paystack integration
 * Based on Week 9 Activity requirements
 * 
 * Flow:
 * 1. Receive verified payment data from Paystack
 * 2. Generate unique order reference
 * 3. Create order in prescription_orders table
 * 4. Add order details from cart to order_details table
 * 5. Record payment in prescription_payment table
 * 6. Update prescription status
 * 7. Empty cart
 * 8. Return success response
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Capture any output
ob_start();

header('Content-Type: application/json');

// Include required files
try {
    require_once('../settings/core.php');
    require_once('../settings/paystack_config.php');
    require_once('../controllers/order_controller.php');
    require_once('../settings/db_class.php');
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load required files: ' . $e->getMessage()
    ]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Please log in to complete checkout'
    ]);
    exit();
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['transaction_reference']) || !isset($data['prescription_id'])) {
    ob_end_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required payment data'
    ]);
    exit();
}

$transaction_reference = $data['transaction_reference'];
$prescription_id = intval($data['prescription_id']);
$patient_id = intval($_SESSION['patient_id']);

try {
    // Create database connection
    $db = new db_connection();
    $conn = $db->db_conn();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Auto-create missing tables
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'order_details'");
    if (!$check || mysqli_num_rows($check) == 0) {
        $sql = "CREATE TABLE IF NOT EXISTS order_details (
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
        mysqli_query($conn, $sql);
    }
    
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'prescription_payment'");
    if (!$check || mysqli_num_rows($check) == 0) {
        $sql = "CREATE TABLE IF NOT EXISTS prescription_payment (
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
        mysqli_query($conn, $sql);
    }
    
    // Step 1: Verify payment with Paystack
    paystack_log('Starting checkout process', [
        'patient_id' => $patient_id,
        'transaction_reference' => $transaction_reference,
        'prescription_id' => $prescription_id
    ]);
    
    $payment_data = paystack_verify_transaction($transaction_reference);
    
    if (!isset($payment_data['status']) || $payment_data['status'] !== true) {
        throw new Exception('Payment verification failed');
    }
    
    if ($payment_data['data']['status'] !== 'success') {
        throw new Exception('Payment was not successful');
    }
    
    // Extract payment details
    $paystack_amount = $payment_data['data']['amount'] / 100; // Convert from pesewas to cedis
    $currency = $payment_data['data']['currency'];
    $authorization_code = isset($payment_data['data']['authorization']['authorization_code']) 
        ? $payment_data['data']['authorization']['authorization_code'] 
        : null;
    $payment_channel = isset($payment_data['data']['channel']) ? $payment_data['data']['channel'] : null;
    $card_type = isset($payment_data['data']['authorization']['card_type']) 
        ? $payment_data['data']['authorization']['card_type'] 
        : null;
    
    // Step 2: Get prescription medicines
    $medicines_sql = "SELECT pm.prescription_medicine_id, pm.medicine_name, pm.dosage, 
                             pm.frequency, pm.duration, pm.price,
                             p.patient_id
                      FROM prescription_medicines pm
                      INNER JOIN prescriptions p ON pm.prescription_id = p.prescription_id
                      WHERE pm.prescription_id = ? AND p.patient_id = ?";
    $stmt = $conn->prepare($medicines_sql);
    $stmt->bind_param("ii", $prescription_id, $patient_id);
    $stmt->execute();
    $medicines_result = $stmt->get_result();
    
    $medicines = [];
    while ($row = $medicines_result->fetch_assoc()) {
        $medicines[] = $row;
    }
    
    if (empty($medicines)) {
        throw new Exception('No medicines found for this prescription');
    }
    
    // Calculate totals from prescription medicines
    $subtotal = 0;
    foreach ($medicines as $medicine) {
        $price = isset($medicine['price']) ? floatval($medicine['price']) : 0;
        if ($price <= 0) {
            throw new Exception('Some medicines do not have prices set');
        }
        $subtotal += $price;
    }
    
    $tax_rate = 0.05; // 5% tax
    $tax_amount = $subtotal * $tax_rate;
    $total_amount = $subtotal + $tax_amount;
    
    // Verify amount matches payment
    if (abs($total_amount - $paystack_amount) > 0.01) {
        throw new Exception('Payment amount mismatch');
    }
    
    // Step 3: Get prescription details
    $prescription_sql = "SELECT pharmacy_id FROM prescriptions WHERE prescription_id = ?";
    $stmt = $conn->prepare($prescription_sql);
    $stmt->bind_param("i", $prescription_id);
    $stmt->execute();
    $prescription_result = $stmt->get_result();
    
    if ($prescription_result->num_rows === 0) {
        throw new Exception('Prescription not found');
    }
    
    $prescription = $prescription_result->fetch_assoc();
    $pharmacy_id = $prescription['pharmacy_id'];
    
    // Step 4: Start database transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Step 5: Generate order reference
        $order_reference = generate_order_reference_ctr();
        
        // Step 6: Create order
        $order_params = [
            'patient_id' => $patient_id,
            'prescription_id' => $prescription_id,
            'pharmacy_id' => $pharmacy_id,
            'order_reference' => $order_reference,
            'order_status' => 'payment_confirmed',
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'total_amount' => $total_amount,
            'delivery_address' => isset($data['delivery_address']) ? $data['delivery_address'] : null,
            'notes' => isset($data['notes']) ? $data['notes'] : null
        ];
        
        $order_id = create_order_ctr($order_params);
        
        if (!$order_id) {
            error_log("Order creation failed. Params: " . print_r($order_params, true));
            throw new Exception('Failed to create order in database');
        }
        
        paystack_log('Order created', ['order_id' => $order_id, 'order_reference' => $order_reference]);
        error_log("Order created successfully: ID = $order_id, Reference = $order_reference");
        
        // Step 7: Add order details (each medicine)
        foreach ($medicines as $medicine) {
            $detail_params = [
                'order_id' => $order_id,
                'prescription_medicine_id' => $medicine['prescription_medicine_id'],
                'medicine_name' => $medicine['medicine_name'],
                'dosage' => $medicine['dosage'],
                'frequency' => $medicine['frequency'],
                'duration' => $medicine['duration'],
                'quantity' => 1, // Prescriptions are quantity 1 per medicine
                'unit_price' => $medicine['price'],
                'subtotal' => $medicine['price']
            ];
            
            $detail_result = add_order_detail_ctr($detail_params);
            
            if (!$detail_result) {
                error_log("Failed to add order detail for medicine: " . $medicine['medicine_name']);
                throw new Exception('Failed to add order details for ' . $medicine['medicine_name']);
            }
            
            error_log("Added order detail for: " . $medicine['medicine_name']);
        }
        
        paystack_log('Order details added', ['medicine_count' => count($medicines)]);
        
        // Step 8: Record payment
        $payment_params = [
            'order_id' => $order_id,
            'patient_id' => $patient_id,
            'amount' => $total_amount,
            'currency' => $currency,
            'payment_method' => 'paystack',
            'transaction_reference' => $transaction_reference,
            'authorization_code' => $authorization_code,
            'payment_channel' => $payment_channel,
            'card_type' => $card_type,
            'payment_status' => 'success',
            'paystack_response' => json_encode($payment_data)
        ];
        
        $payment_id = record_payment_ctr($payment_params);
        
        if (!$payment_id) {
            error_log("Payment recording failed. Transaction ref: " . $transaction_reference);
            throw new Exception('Failed to record payment in database');
        }
        
        paystack_log('Payment recorded', ['payment_id' => $payment_id]);
        error_log("Payment recorded successfully: ID = $payment_id");
        
        // Step 9: Update prescription status
        $update_prescription_sql = "UPDATE prescriptions 
                                    SET status = 'Payment received', 
                                        order_id = ?,
                                        total_amount = ? 
                                    WHERE prescription_id = ?";
        $update_stmt = $conn->prepare($update_prescription_sql);
        $update_stmt->bind_param("idi", $order_id, $total_amount, $prescription_id);
        
        if (!$update_stmt->execute()) {
            error_log("Failed to update prescription status. Prescription ID: " . $prescription_id);
            throw new Exception('Failed to update prescription status');
        }
        
        error_log("Prescription status updated successfully");
        
        // Step 10: Add to prescription timeline
        $timeline_sql = "INSERT INTO prescription_timeline (prescription_id, status_text) 
                        VALUES (?, ?)";
        $timeline_stmt = $conn->prepare($timeline_sql);
        $timeline_text = "Payment received - Order {$order_reference} created";
        $timeline_stmt->bind_param("is", $prescription_id, $timeline_text);
        $timeline_stmt->execute();
        
        // Note: No need to empty cart since prescription medicines are stored in prescription_medicines table
        // The prescription status change to "Payment received" is sufficient
        
        // Commit transaction
        mysqli_commit($conn);
        
        paystack_log('Checkout completed successfully', [
            'order_id' => $order_id,
            'order_reference' => $order_reference,
            'amount' => $total_amount
        ]);
        
        // Clear any output buffer and return success response
        ob_end_clean();
        echo json_encode([
            'status' => 'success',
            'message' => 'Order completed successfully',
            'order_id' => $order_id,
            'order_reference' => $order_reference,
            'transaction_reference' => $transaction_reference,
            'amount' => $total_amount,
            'currency' => $currency,
            'payment_channel' => $payment_channel
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        throw $e;
    }
    
} catch (Exception $e) {
    // Clear any output and log error
    ob_end_clean();
    
    error_log("Checkout error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    paystack_log('Checkout failed', [
        'error' => $e->getMessage(),
        'patient_id' => isset($patient_id) ? $patient_id : 'unknown',
        'trace' => $e->getTraceAsString()
    ]);
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

?>

