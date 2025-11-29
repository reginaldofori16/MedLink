<?php
/**
 * Update Prescription Status Action (Pharmacy)
 * Allows pharmacies to update prescription status and add timeline entries
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

// Check if user is logged in and is a pharmacy
if (!is_logged_in() || get_user_type() !== 'pharmacy') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Pharmacy login required.'
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
$timelineText = isset($input['timeline_text']) ? trim($input['timeline_text']) : null;
$medicinePrices = isset($input['medicine_prices']) ? $input['medicine_prices'] : null;
$totalAmount = isset($input['total_amount']) ? (float)$input['total_amount'] : null;

// Validate status value - pharmacies can only update statuses from "Sent to pharmacies" onwards
$allowed_statuses = [
    'Sent to pharmacies',
    'Pharmacy reviewing',
    'Awaiting patient payment',
    'Payment received',
    'Ready for pickup',
    'Ready for delivery',
    'Dispensed',
    'On hold'
];

if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status. Pharmacies can only update statuses from "Sent to pharmacies" onwards.'
    ]);
    exit;
}

try {
    $pharmacyId = isset($_SESSION['pharmacy_id']) ? (int)$_SESSION['pharmacy_id'] : null;
    if (!$pharmacyId) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Pharmacy session not found. Please login again.'
        ]);
        exit;
    }
    
    $db = new db_connection();
    
    // Start transaction
    $db->db_query('START TRANSACTION');
    
    // Check if prescription exists and has been sent to pharmacies
    $check_sql = "SELECT prescription_id, status FROM prescriptions WHERE prescription_id = $prescriptionId";
    $prescription = $db->db_fetch_one($check_sql);
    
    if (!$prescription) {
        throw new Exception('Prescription not found.');
    }
    
    // Verify prescription has been sent to pharmacies (pharmacies can only update from this point)
    $currentStatus = $prescription['status'];
    $pharmacyStatuses = ['Sent to pharmacies', 'Pharmacy reviewing', 'Awaiting patient payment', 'Payment received', 'Ready for pickup', 'Ready for delivery', 'Dispensed', 'On hold'];
    
    if (!in_array($currentStatus, $pharmacyStatuses) && !in_array($status, $pharmacyStatuses)) {
        throw new Exception('Access denied. This prescription has not been sent to pharmacies yet.');
    }
    
    // Update prescription status
    $status_escaped = $db->db_escape_string($status);
    
    $update_sql = "UPDATE prescriptions SET 
                    status = '$status_escaped',
                    last_updated = NOW()";
    
    // Update pharmacy_id if status is being set to a pharmacy status for the first time
    if (in_array($status, ['Pharmacy reviewing', 'Awaiting patient payment', 'Payment received', 'Ready for pickup', 'Ready for delivery', 'Dispensed', 'On hold'])) {
        $update_sql .= ", pharmacy_id = $pharmacyId";
    }
    
    // Update total_amount if provided (when pharmacy sets prices)
    if ($totalAmount !== null && $totalAmount > 0) {
        $update_sql .= ", total_amount = $totalAmount";
    }
    
    $update_sql .= " WHERE prescription_id = $prescriptionId";
    
    if (!$db->db_query($update_sql)) {
        throw new Exception('Failed to update prescription status: ' . $db->db_error());
    }
    
    // Update individual medicine prices if provided
    if ($medicinePrices && is_array($medicinePrices)) {
        foreach ($medicinePrices as $medicinePrice) {
            $medicineId = isset($medicinePrice['medicine_id']) ? $db->db_escape_string($medicinePrice['medicine_id']) : null;
            $medicineName = isset($medicinePrice['medicine_name']) ? $db->db_escape_string($medicinePrice['medicine_name']) : null;
            $price = isset($medicinePrice['price']) ? (float)$medicinePrice['price'] : 0;
            
            if ($medicineId && $price > 0) {
                // Update medicine price by medicine_id (if it's a prescription_medicine_id)
                if (is_numeric($medicineId)) {
                    $update_medicine_sql = "UPDATE prescription_medicines 
                                           SET price = $price 
                                           WHERE prescription_medicine_id = $medicineId 
                                           AND prescription_id = $prescriptionId";
                } else {
                    // If medicine_id is not numeric, update by name (fallback for index-based IDs)
                    $update_medicine_sql = "UPDATE prescription_medicines 
                                           SET price = $price 
                                           WHERE prescription_id = $prescriptionId 
                                           AND medicine_name = '$medicineName'
                                           LIMIT 1";
                }
                
                if (!$db->db_query($update_medicine_sql)) {
                    error_log("Warning: Failed to update medicine price for medicine: $medicineName");
                    // Don't throw exception, continue with other updates
                }
            }
        }
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
    
    error_log("Update prescription status (pharmacy) error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating prescription status: ' . $e->getMessage()
    ]);
}
?>

