<?php
/**
 * Login Pharmacy Action
 * Receives data from the pharmacy login form,
 * invokes the relevant function from the pharmacy controller,
 * sets session variables, and returns a message to the caller
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
require_once __DIR__ . '/../controllers/pharmacy_controller.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data received.');
    }
    
    // Create controller instance
    $pharmacyController = new PharmacyController();
    
    // Call login method
    $result = $pharmacyController->login_pharmacy_ctr($data);
    
    // If login successful, set session variables
    if ($result['status'] === 'success' && isset($result['pharmacy'])) {
        $pharmacy = $result['pharmacy'];
        
        // Set session variables
        $_SESSION['pharmacy_id'] = (int)$pharmacy['pharmacy_id'];
        $_SESSION['user_id'] = (int)$pharmacy['pharmacy_id']; // Generic user ID
        $_SESSION['user_name'] = $pharmacy['name'];
        $_SESSION['user_email'] = $pharmacy['contact'] ?? '';
        $_SESSION['user_type'] = 'pharmacy';
        
        // Optional: Set additional session variables
        $_SESSION['government_id'] = $pharmacy['government_id'] ?? '';
        $_SESSION['location'] = $pharmacy['location'] ?? '';
        
        // Add redirect URL to response (relative to view/login.php)
        $result['redirect_url'] = 'pharmacy.php';
        
        // Log successful login
        error_log("Pharmacy login successful: Pharmacy ID " . $pharmacy['pharmacy_id'] . " - Government ID: " . $pharmacy['government_id']);
    }
    
    // Return JSON response
    http_response_code($result['status'] === 'success' ? 200 : 401);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Login pharmacy action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>

