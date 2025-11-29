<?php
/**
 * Get Cart Action
 * Retrieves user's cart items
 * Additional helper for cart.php
 */

header('Content-Type: application/json');

// Include required files
require_once('../settings/core.php');
require_once('../controllers/cart_controller.php');

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please log in to view cart'
    ]);
    exit();
}

$patient_id = intval($_SESSION['patient_id']);

// Get cart
try {
    $cart_items = get_user_cart_ctr($patient_id);
    $cart_count = get_cart_count_ctr($patient_id);
    $cart_total = get_cart_total_ctr($patient_id);
    
    echo json_encode([
        'status' => 'success',
        'cart_items' => $cart_items,
        'cart_count' => $cart_count,
        'cart_total' => $cart_total
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    ]);
}

?>

