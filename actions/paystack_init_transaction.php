<?php
/**
 * Initialize Paystack Transaction
 * Called when patient clicks "Pay with Paystack" button
 */

// Include necessary files
require_once('../settings/core.php');
require_once('../settings/paystack_config.php');
require_once('../settings/db_class.php');

// Set JSON header
header('Content-Type: application/json');

// Create database connection
$db = new db_connection();
$conn = $db->db_conn();

if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please log in to continue'
    ]);
    exit();
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['email']) || !isset($data['prescription_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit();
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$prescription_id = intval($data['prescription_id']);
$patient_id = intval($_SESSION['patient_id']);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email address'
    ]);
    exit();
}

try {
    // Verify prescription exists and belongs to patient
    $prescription_check_sql = "SELECT prescription_id, total_amount, status 
                               FROM prescriptions 
                               WHERE prescription_id = ? AND patient_id = ?";
    $stmt = $conn->prepare($prescription_check_sql);
    $stmt->bind_param("ii", $prescription_id, $patient_id);
    $stmt->execute();
    $prescription_result = $stmt->get_result();
    
    if ($prescription_result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Prescription not found or does not belong to you'
        ]);
        exit();
    }
    
    $prescription_data = $prescription_result->fetch_assoc();
    
    // Get prescription medicines with prices
    $medicines_sql = "SELECT prescription_medicine_id, medicine_name, dosage, frequency, 
                             duration, price 
                      FROM prescription_medicines 
                      WHERE prescription_id = ?";
    $stmt = $conn->prepare($medicines_sql);
    $stmt->bind_param("i", $prescription_id);
    $stmt->execute();
    $medicines_result = $stmt->get_result();
    
    $medicines = [];
    while ($row = $medicines_result->fetch_assoc()) {
        $medicines[] = $row;
    }
    
    if (empty($medicines)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No medicines found for this prescription'
        ]);
        exit();
    }
    
    // Calculate total amount from medicines
    $subtotal = 0;
    foreach ($medicines as $medicine) {
        $price = isset($medicine['price']) ? floatval($medicine['price']) : 0;
        if ($price <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Some medicines do not have prices set'
            ]);
            exit();
        }
        $subtotal += $price;
    }
    
    $tax_rate = 0.05; // 5% tax
    $tax_amount = $subtotal * $tax_rate;
    $total_amount = $subtotal + $tax_amount;
    
    if ($total_amount <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid amount'
        ]);
        exit();
    }
    
    // Generate transaction reference
    $reference = paystack_generate_reference($patient_id);
    
    // Prepare metadata
    $metadata = [
        'prescription_id' => $prescription_id,
        'patient_id' => $patient_id,
        'medicine_count' => count($medicines),
        'custom_fields' => [
            [
                'display_name' => 'Prescription ID',
                'variable_name' => 'prescription_id',
                'value' => $prescription_id
            ],
            [
                'display_name' => 'Patient ID',
                'variable_name' => 'patient_id',
                'value' => $patient_id
            ]
        ]
    ];
    
    // Initialize transaction with Paystack
    $response = paystack_initialize_transaction($email, $total_amount, $reference, $metadata);
    
    // Log the transaction
    paystack_log('Transaction initialized', [
        'reference' => $reference,
        'email' => $email,
        'amount' => $total_amount,
        'prescription_id' => $prescription_id,
        'medicines' => count($medicines)
    ]);
    
    // Check response
    if (isset($response['status']) && $response['status'] === true) {
        // Success - return authorization URL
        echo json_encode([
            'status' => 'success',
            'authorization_url' => $response['data']['authorization_url'],
            'access_code' => $response['data']['access_code'],
            'reference' => $reference,
            'message' => 'Redirecting to payment gateway...'
        ]);
    } else {
        // Error from Paystack
        $error_message = isset($response['message']) ? $response['message'] : 'Payment initialization failed';
        
        paystack_log('Transaction initialization failed', [
            'error' => $error_message,
            'response' => $response
        ]);
        
        echo json_encode([
            'status' => 'error',
            'message' => $error_message
        ]);
    }
    
} catch (Exception $e) {
    paystack_log('Transaction initialization error', [
        'error' => $e->getMessage()
    ]);
    
    echo json_encode([
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    ]);
}

?>
