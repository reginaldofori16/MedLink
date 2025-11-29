<?php
/**
 * Get Hospital Prescriptions Action
 * Fetches all prescriptions for the logged-in hospital
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

// Check if user is logged in and is a hospital
if (!is_logged_in() || get_user_type() !== 'hospital') {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login as a hospital to view prescriptions.'
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
    
    // Fetch all prescriptions for this hospital with patient information
    $sql = "SELECT 
                p.prescription_id,
                p.prescription_code,
                p.patient_id,
                p.hospital_id,
                p.doctor_name,
                p.visit_date,
                p.prescription_image_path,
                p.status,
                p.clarification_message,
                p.clarification_response,
                p.submitted_date,
                p.last_updated,
                pt.full_name as patient_name
            FROM prescriptions p
            INNER JOIN patients pt ON p.patient_id = pt.patient_id
            WHERE p.hospital_id = $hospitalId
            ORDER BY p.submitted_date DESC";
    
    $prescriptions = $db->db_fetch_all($sql);
    
    if ($prescriptions === false) {
        throw new Exception('Failed to fetch prescriptions from database.');
    }
    
    // Format prescriptions with medicines
    $formattedPrescriptions = [];
    
    // Handle empty results
    if (!is_array($prescriptions) || count($prescriptions) === 0) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'prescriptions' => []
        ]);
        exit;
    }
    
    foreach ($prescriptions as $prescription) {
        $prescriptionId = (int)$prescription['prescription_id'];
        
        // Fetch medicines for this prescription
        $medSql = "SELECT 
                    medicine_name,
                    dosage,
                    frequency,
                    duration
                FROM prescription_medicines
                WHERE prescription_id = $prescriptionId
                ORDER BY prescription_medicine_id ASC";
        
        $medicines = $db->db_fetch_all($medSql);
        $formattedMedicines = [];
        
        if ($medicines && is_array($medicines)) {
            foreach ($medicines as $med) {
                $formattedMedicines[] = [
                    'name' => $med['medicine_name'],
                    'dosage' => $med['dosage'],
                    'frequency' => $med['frequency'],
                    'duration' => $med['duration']
                ];
            }
        }
        
        // Format prescription
        $formattedPrescriptions[] = [
            'id' => $prescription['prescription_code'],
            'prescription_id' => $prescriptionId,
            'patient' => $prescription['patient_name'],
            'patientId' => 'PT-' . str_pad($prescription['patient_id'], 5, '0', STR_PAD_LEFT),
            'date' => date('Y-m-d', strtotime($prescription['submitted_date'])),
            'status' => $prescription['status'],
            'notes' => $prescription['clarification_message'] || 'No additional notes',
            'warnings' => '', // Warnings can be added later if needed in database
            'clarification_message' => $prescription['clarification_message'],
            'clarification_response' => $prescription['clarification_response'],
            'medicines' => $formattedMedicines
        ];
    }
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'prescriptions' => $formattedPrescriptions
    ]);
    
} catch (Exception $e) {
    error_log("Get hospital prescriptions action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching prescriptions.'
    ]);
}
?>

