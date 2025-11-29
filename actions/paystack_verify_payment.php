<?php
/**
 * Verify Paystack Payment
 * Called after customer returns from Paystack payment page
 */

session_start();

// Include necessary files
require_once('../settings/paystack_config.php');
require_once('../includes/db_connection.php');

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Please log in to continue'
    ]);
    exit();
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['reference'])) {
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Transaction reference is required'
    ]);
    exit();
}

$reference = $data['reference'];
$patient_id = intval($_SESSION['patient_id']);

// Verify transaction with Paystack
$response = paystack_verify_transaction($reference);

// Log verification attempt
paystack_log('Payment verification attempted', [
    'reference' => $reference,
    'patient_id' => $patient_id
]);

// Check if verification was successful
if (!isset($response['status']) || $response['status'] !== true) {
    $error_message = isset($response['message']) ? $response['message'] : 'Payment verification failed';
    
    paystack_log('Payment verification failed', [
        'reference' => $reference,
        'error' => $error_message
    ]);
    
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => $error_message
    ]);
    exit();
}

// Get transaction data
$transaction = $response['data'];

// Validate payment status
if ($transaction['status'] !== 'success') {
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Payment was not successful. Status: ' . $transaction['status']
    ]);
    exit();
}

// Get prescription ID from metadata
$prescription_id = null;
if (isset($transaction['metadata']['prescription_id'])) {
    $prescription_id = intval($transaction['metadata']['prescription_id']);
} else {
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Invalid transaction metadata'
    ]);
    exit();
}

// Get transaction details
$amount_paid = paystack_pesewas_to_amount($transaction['amount']);
$currency = $transaction['currency'];
$payment_channel = $transaction['channel']; // card, bank, ussd, mobile_money
$authorization_code = isset($transaction['authorization']['authorization_code']) 
    ? $transaction['authorization']['authorization_code'] 
    : null;
$customer_email = $transaction['customer']['email'];

// Start database transaction
$conn->begin_transaction();

try {
    // 1. Get prescription details
    $stmt = $conn->prepare("
        SELECT p.prescription_id, p.patient_id, p.total_amount, p.status,
               pat.full_name, pat.email,
               h.name as hospital_name
        FROM prescriptions p
        JOIN patients pat ON p.patient_id = pat.patient_id
        JOIN hospitals h ON p.hospital_id = h.hospital_id
        WHERE p.prescription_id = ? AND p.patient_id = ?
    ");
    $stmt->bind_param("ii", $prescription_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Prescription not found');
    }
    
    $prescription = $result->fetch_assoc();
    
    // 2. Validate amount paid matches prescription total (allow 1 pesewa difference)
    if (abs($prescription['total_amount'] - $amount_paid) > 0.01) {
        throw new Exception('Amount mismatch. Expected: GHS ' . $prescription['total_amount'] . ', Paid: GHS ' . $amount_paid);
    }
    
    // 3. Check if payment already exists for this transaction
    $stmt = $conn->prepare("SELECT payment_id FROM payments WHERE transaction_ref = ?");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Payment already recorded
        $payment_record = $result->fetch_assoc();
        $payment_id = $payment_record['payment_id'];
    } else {
        // 4. Insert payment record
        $stmt = $conn->prepare("
            INSERT INTO payments (
                prescription_id, 
                patient_id, 
                amount, 
                currency, 
                payment_method, 
                transaction_ref, 
                authorization_code, 
                payment_channel, 
                payment_status,
                payment_date
            ) VALUES (?, ?, ?, ?, 'paystack', ?, ?, ?, 'success', NOW())
        ");
        
        $stmt->bind_param(
            "iidssss",
            $prescription_id,
            $patient_id,
            $amount_paid,
            $currency,
            $reference,
            $authorization_code,
            $payment_channel
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to record payment');
        }
        
        $payment_id = $conn->insert_id;
    }
    
    // 5. Update prescription status
    $stmt = $conn->prepare("
        UPDATE prescriptions 
        SET status = 'Payment received', 
            payment_id = ?,
            last_updated = NOW()
        WHERE prescription_id = ?
    ");
    $stmt->bind_param("ii", $payment_id, $prescription_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update prescription status');
    }
    
    // 6. Add timeline entry
    $timeline_text = "Payment received via " . ucfirst($payment_channel) . " - GHS " . number_format($amount_paid, 2);
    $stmt = $conn->prepare("
        INSERT INTO prescription_timeline (prescription_id, status_text, timestamp)
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param("is", $prescription_id, $timeline_text);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Log successful payment
    paystack_log('Payment verified and recorded successfully', [
        'reference' => $reference,
        'prescription_id' => $prescription_id,
        'amount' => $amount_paid,
        'payment_id' => $payment_id
    ]);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'verified' => true,
        'message' => 'Payment verified successfully!',
        'data' => [
            'payment_id' => $payment_id,
            'prescription_id' => $prescription_id,
            'amount' => $amount_paid,
            'currency' => $currency,
            'transaction_ref' => $reference,
            'payment_method' => ucfirst($payment_channel),
            'customer_name' => $prescription['full_name'],
            'customer_email' => $customer_email,
            'hospital_name' => $prescription['hospital_name'],
            'payment_date' => date('F j, Y')
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    paystack_log('Payment verification error', [
        'reference' => $reference,
        'error' => $e->getMessage()
    ]);
    
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Verification error: ' . $e->getMessage()
    ]);
}

?>

