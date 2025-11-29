<?php
/**
 * Update Pharmacy Status Action
 * Allows admin to update pharmacy status to 'active' or 'suspended'
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
require_once __DIR__ . '/../settings/db_class.php';

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Admin privileges required.'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['pharmacy_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields: pharmacy_id and status are required.'
    ]);
    exit;
}

$pharmacy_id = (int)$input['pharmacy_id'];
$status = strtolower(trim($input['status']));

// Validate status value
$allowed_statuses = ['active', 'suspended'];
if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status. Allowed values are: ' . implode(', ', $allowed_statuses)
    ]);
    exit;
}

try {
    $db = new db_connection();
    
    // Check if pharmacy exists
    $check_sql = "SELECT pharmacy_id FROM pharmacies WHERE pharmacy_id = $pharmacy_id";
    $exists = $db->db_query($check_sql);
    
    if (!$exists || $db->db_count() === 0) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Pharmacy not found.'
        ]);
        exit;
    }
    
    // Update pharmacy status
    $status_escaped = $db->db_escape_string($status);
    $update_sql = "UPDATE pharmacies SET status = '$status_escaped', updated_at = NOW() WHERE pharmacy_id = $pharmacy_id";
    
    if ($db->db_query($update_sql)) {
        // Return success response
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Pharmacy status updated successfully.',
            'pharmacy_id' => $pharmacy_id,
            'new_status' => ucfirst($status)
        ]);
    } else {
        throw new Exception('Failed to update pharmacy status: ' . $db->db_error());
    }
    
} catch (Exception $e) {
    error_log("Update pharmacy status error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating pharmacy status.'
    ]);
}
?>

