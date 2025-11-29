<?php
/**
 * Login Hospital Action
 * Receives data from the hospital login form,
 * invokes the relevant function from the hospital controller,
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
    
    // Call login method
    $result = $hospitalController->login_hospital_ctr($data);
    
    // If login successful, set session variables
    if ($result['status'] === 'success' && isset($result['hospital'])) {
        $hospital = $result['hospital'];
        
        // Set session variables
        $_SESSION['hospital_id'] = (int)$hospital['hospital_id'];
        $_SESSION['user_id'] = (int)$hospital['hospital_id']; // Generic user ID
        $_SESSION['user_name'] = $hospital['name'];
        $_SESSION['user_email'] = $hospital['contact'] ?? '';
        $_SESSION['user_type'] = 'hospital';
        
        // Optional: Set additional session variables
        $_SESSION['government_id'] = $hospital['government_id'] ?? '';
        
        // Add redirect URL to response (relative to view/login.php)
        $result['redirect_url'] = 'hospital.php';
        
        // Log successful login
        error_log("Hospital login successful: Hospital ID " . $hospital['hospital_id'] . " - Government ID: " . $hospital['government_id']);
    }
    
    // Return JSON response
    http_response_code($result['status'] === 'success' ? 200 : 401);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Login hospital action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>

