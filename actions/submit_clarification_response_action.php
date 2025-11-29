<?php
/**
 * Submit Clarification Response Action
 * Allows patients to respond to hospital clarification requests
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

// Check if user is logged in and is a patient
if (!is_logged_in() || get_user_type() !== 'patient') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Patient login required.'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['prescription_id']) || !isset($input['clarification_response'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields: prescription_id and clarification_response are required.'
    ]);
    exit;
}

$prescriptionId = (int)$input['prescription_id'];
$clarificationResponse = trim($input['clarification_response']);

// Validate response is not empty
if (empty($clarificationResponse)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Clarification response cannot be empty.'
    ]);
    exit;
}

try {
    $patientId = isset($_SESSION['patient_id']) ? (int)$_SESSION['patient_id'] : null;
    if (!$patientId) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Patient session not found. Please login again.'
        ]);
        exit;
    }
    
    $db = new db_connection();
    
    // Start transaction
    $db->db_query('START TRANSACTION');
    
    // Check if prescription exists and belongs to this patient
    $check_sql = "SELECT prescription_id, patient_id, status FROM prescriptions WHERE prescription_id = $prescriptionId";
    $prescription = $db->db_fetch_one($check_sql);
    
    if (!$prescription) {
        throw new Exception('Prescription not found.');
    }
    
    if ((int)$prescription['patient_id'] !== $patientId) {
        throw new Exception('Access denied. This prescription does not belong to you.');
    }
    
    // Check if prescription is in "Clarification requested" status
    if ($prescription['status'] !== 'Clarification requested') {
        throw new Exception('This prescription is not awaiting clarification response.');
    }
    
    // Update prescription with clarification response and change status
    $response_escaped = $db->db_escape_string($clarificationResponse);
    
    $update_sql = "UPDATE prescriptions SET 
                    status = 'Waiting for hospital',
                    clarification_response = '$response_escaped',
                    last_updated = NOW()
                   WHERE prescription_id = $prescriptionId";
    
    if (!$db->db_query($update_sql)) {
        throw new Exception('Failed to submit clarification response: ' . $db->db_error());
    }
    
    // Add timeline entry
    $timeline_text = "Patient responded to clarification request";
    $timeline_escaped = $db->db_escape_string($timeline_text);
    
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
        'message' => 'Clarification response submitted successfully.',
        'prescription_id' => $prescriptionId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    if (isset($db)) {
        $db->db_query('ROLLBACK');
    }
    
    error_log("Submit clarification response error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while submitting your response: ' . $e->getMessage()
    ]);
}
?>

