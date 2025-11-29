<?php
/**
 * Update Prescription Status Action
 * Allows hospitals to update prescription status and add timeline entries
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

// Check if user is logged in and is a hospital
if (!is_logged_in() || get_user_type() !== 'hospital') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Hospital login required.'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['prescription_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields: prescription_id and status are required.'
    ]);
    exit;
}

$prescriptionId = (int)$input['prescription_id'];
$status = trim($input['status']);
$clarificationMessage = isset($input['clarification_message']) ? trim($input['clarification_message']) : null;
$timelineText = isset($input['timeline_text']) ? trim($input['timeline_text']) : null;

// Validate status value - must match database ENUM exactly
$allowed_statuses = [
    'Submitted by patient',
    'Hospital reviewing',
    'Clarification requested',
    'Waiting for hospital',
    'Confirmed by hospital',
    'Sent to pharmacies',
    'Pharmacy reviewing',
    'Awaiting patient payment',
    'Payment received',
    'Ready for pickup',
    'Ready for delivery',
    'Dispensed',
    'On hold',
    'Rejected'
];

if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status. Allowed values are: ' . implode(', ', $allowed_statuses)
    ]);
    exit;
}

try {
    $hospitalId = isset($_SESSION['hospital_id']) ? (int)$_SESSION['hospital_id'] : null;
    if (!$hospitalId) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Hospital session not found. Please login again.'
        ]);
        exit;
    }
    
    $db = new db_connection();
    
    // Start transaction
    $db->db_query('START TRANSACTION');
    
    // Check if prescription exists and belongs to this hospital
    $check_sql = "SELECT prescription_id, hospital_id FROM prescriptions WHERE prescription_id = $prescriptionId";
    $prescription = $db->db_fetch_one($check_sql);
    
    if (!$prescription) {
        throw new Exception('Prescription not found.');
    }
    
    if ((int)$prescription['hospital_id'] !== $hospitalId) {
        throw new Exception('Access denied. This prescription does not belong to your hospital.');
    }
    
    // Update prescription status
    $status_escaped = $db->db_escape_string($status);
    
    $update_sql = "UPDATE prescriptions SET 
                    status = '$status_escaped',
                    last_updated = NOW()";
    
    // Add clarification message if provided
    if ($status === 'Clarification requested' && $clarificationMessage) {
        $clarification_escaped = $db->db_escape_string($clarificationMessage);
        $update_sql .= ", clarification_message = '$clarification_escaped'";
    } elseif ($status !== 'Clarification requested') {
        // Clear clarification message if status is not clarification requested
        $update_sql .= ", clarification_message = NULL";
    }
    
    $update_sql .= " WHERE prescription_id = $prescriptionId";
    
    if (!$db->db_query($update_sql)) {
        throw new Exception('Failed to update prescription status: ' . $db->db_error());
    }
    
    // Add timeline entry
    $timelineTextFinal = $timelineText ? $timelineText : $status;
    $timeline_escaped = $db->db_escape_string($timelineTextFinal);
    
    $timeline_sql = "INSERT INTO prescription_timeline (
                        prescription_id,
                        status_text
                    ) VALUES (
                        $prescriptionId,
                        '$timeline_escaped'
                    )";
    
    if (!$db->db_query($timeline_sql)) {
        throw new Exception('Failed to add timeline entry: ' . $db->db_error());
    }
    
    // Commit transaction
    $db->db_query('COMMIT');
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Prescription status updated successfully.',
        'prescription_id' => $prescriptionId,
        'new_status' => $status
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    if (isset($db)) {
        $db->db_query('ROLLBACK');
    }
    
    error_log("Update prescription status error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating prescription status: ' . $e->getMessage()
    ]);
}
?>

