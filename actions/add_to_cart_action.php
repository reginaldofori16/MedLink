<?php
/**
 * Add to Cart Action
 * Processes Add to Cart requests
 * Based on Week 9 Activity requirements
 */

header('Content-Type: application/json');

// Include required files
require_once('../settings/core.php');
require_once('../controllers/cart_controller.php');

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please log in to add items to cart'
    ]);
    exit();
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['prescription_id']) || !isset($data['prescription_medicine_id']) || !isset($data['price'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit();
}

$patient_id = intval($_SESSION['patient_id']);
$prescription_id = intval($data['prescription_id']);
$prescription_medicine_id = intval($data['prescription_medicine_id']);
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;
$price = floatval($data['price']);

// Validate quantity
if ($quantity < 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid quantity'
    ]);
    exit();
}

// Validate price
if ($price <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid price'
    ]);
    exit();
}

// Add to cart
try {
    $result = add_to_cart_ctr($patient_id, $prescription_id, $prescription_medicine_id, $quantity, $price);
    
    if ($result) {
        // Get updated cart count
        $cart_count = get_cart_count_ctr($patient_id);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Item added to cart',
            'cart_count' => $cart_count
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add item to cart'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    ]);
}

?>

