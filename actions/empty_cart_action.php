<?php
/**
 * Empty Cart Action
 * Removes all items from cart
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

$patient_id = intval($_SESSION['patient_id']);

// Empty cart
try {
    $result = empty_cart_ctr($patient_id);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Cart emptied successfully',
            'cart_count' => 0
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to empty cart'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    ]);
}

?>

