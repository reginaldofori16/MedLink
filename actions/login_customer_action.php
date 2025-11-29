<?php
/**
 * Login Customer Action
 * Receives data from the customer login form,
 * invokes the relevant function from the customer controller,
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
require_once __DIR__ . '/../controllers/customer_controller.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data received.');
    }
    
    // Create controller instance
    $customerController = new CustomerController();
    
    // Call login method
    $result = $customerController->login_customer_ctr($data);
    
    // If login successful, set session variables
    if ($result['status'] === 'success' && isset($result['customer'])) {
        $customer = $result['customer'];
        
        // Set session variables
        $_SESSION['patient_id'] = (int)$customer['patient_id'];
        $_SESSION['user_id'] = (int)$customer['patient_id']; // Generic user ID
        $userRole = (int)($customer['user_role'] ?? 2);
        $_SESSION['user_role'] = $userRole;
        $_SESSION['user_name'] = $customer['full_name'];
        $_SESSION['user_email'] = $customer['email'];
        
        // Check if user is an admin (role = 1)
        if ($userRole === 1) {
            $_SESSION['user_type'] = 'admin';
            $_SESSION['admin_id'] = (int)$customer['patient_id']; // Set admin_id for admin check
            // Redirect to admin dashboard
            $result['redirect_url'] = 'admin.php';
        } else {
            $_SESSION['user_type'] = 'patient';
            // Redirect to patient dashboard
            $result['redirect_url'] = 'patients.php';
        }
        
        // Optional: Set additional session variables
        $_SESSION['user_phone'] = $customer['phone'] ?? '';
        $_SESSION['user_country'] = $customer['country'] ?? '';
        $_SESSION['user_city'] = $customer['city'] ?? '';
        
        // Log successful login
        error_log("Customer login successful: Patient ID " . $customer['patient_id'] . " - Email: " . $customer['email']);
    }
    
    // Return JSON response
    http_response_code($result['status'] === 'success' ? 200 : 401);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Login customer action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again later.'
    ]);
}
?>

