<?php
/**
 * Register Hospital Action
 * Receives data from the hospital registration form,
 * invokes the relevant function from the hospital controller,
 * and returns a message to the caller
 */

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Only POST requests are accepted.'
    ]);
    exit;
}

// Include core functions (starts session automatically)
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/hospital_controller.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data received.');
    }
    
    // Create controller instance
    $hospitalController = new HospitalController();
    
    // Call register method
    $result = $hospitalController->register_hospital_ctr($data);
    
    // Return JSON response
    http_response_code($result['status'] === 'success' ? 200 : 400);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Register hospital action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>

