<?php
/**
 * Remove from Cart Action
 * Processes Remove from Cart requests
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
if (!isset($data['cart_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing cart item ID'
    ]);
    exit();
}

$cart_id = intval($data['cart_id']);
$patient_id = intval($_SESSION['patient_id']);

// Remove from cart
try {
    $result = remove_from_cart_ctr($cart_id);
    
    if ($result) {
        // Get updated cart count
        $cart_count = get_cart_count_ctr($patient_id);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Item removed from cart',
            'cart_count' => $cart_count
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to remove item from cart'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    ]);
}

?>

