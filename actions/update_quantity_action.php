<?php
/**
 * Update Quantity Action
 * Processes cart quantity update requests
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
        'message' => 'Please log in to manage cart'
    ]);
    exit();
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['cart_id']) || !isset($data['quantity'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit();
}

$cart_id = intval($data['cart_id']);
$quantity = intval($data['quantity']);
$patient_id = intval($_SESSION['patient_id']);

// Validate quantity
if ($quantity < 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Quantity must be at least 1'
    ]);
    exit();
}

// Update quantity
try {
    $result = update_cart_item_ctr($cart_id, $quantity);
    
    if ($result) {
        // Get updated cart total
        $cart_total = get_cart_total_ctr($patient_id);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Quantity updated',
            'cart_total' => $cart_total
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update quantity'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    ]);
}

?>

