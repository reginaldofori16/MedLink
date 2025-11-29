<?php
/**
 * Get All Prescriptions Action
 * Fetches all prescriptions from the database for admin dashboard
 */

header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Only GET requests are accepted.'
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

try {
    $db = new db_connection();
    
    // Fetch all prescriptions with related data
    $sql = "SELECT 
                p.prescription_id,
                p.prescription_code,
                p.patient_id,
                p.hospital_id,
                p.pharmacy_id,
                p.status,
                p.total_amount,
                p.submitted_date,
                p.last_updated,
                pt.full_name as patient_name,
                h.name as hospital_name,
                ph.name as pharmacy_name
            FROM prescriptions p
            INNER JOIN patients pt ON p.patient_id = pt.patient_id
            INNER JOIN hospitals h ON p.hospital_id = h.hospital_id
            LEFT JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            ORDER BY p.submitted_date DESC";
    
    $prescriptions = $db->db_fetch_all($sql);
    
    if ($prescriptions === false) {
        throw new Exception('Failed to fetch prescriptions from database.');
    }
    
    // Format the data for frontend
    $formattedPrescriptions = array_map(function($prescription) {
        return [
            'id' => $prescription['prescription_code'],
            'prescription_id' => (int)$prescription['prescription_id'],
            'patient' => $prescription['patient_name'],
            'patientId' => 'PT-' . str_pad($prescription['patient_id'], 5, '0', STR_PAD_LEFT),
            'hospital' => $prescription['hospital_name'],
            'pharmacy' => $prescription['pharmacy_name'] ? $prescription['pharmacy_name'] : null,
            'status' => $prescription['status'],
            'date' => date('Y-m-d', strtotime($prescription['submitted_date'])),
            'amount' => $prescription['total_amount'] ? (float)$prescription['total_amount'] : 0.00
        ];
    }, $prescriptions ?: []);
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'prescriptions' => $formattedPrescriptions
    ]);
    
} catch (Exception $e) {
    error_log("Get all prescriptions action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching prescriptions.'
    ]);
}
?>

