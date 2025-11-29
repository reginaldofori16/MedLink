<?php
/**
 * Get Pharmacy Prescriptions Action
 * Fetches all prescriptions that have been sent to pharmacies
 * Pharmacies can see all prescriptions with status "Sent to pharmacies" or later
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

// Check if user is logged in and is a pharmacy
if (!is_logged_in() || get_user_type() !== 'pharmacy') {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login as a pharmacy to view prescriptions.'
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
    
    // Fetch prescriptions that have been sent to pharmacies
    // Status should be "Sent to pharmacies" or any later status in the workflow
    $sql = "SELECT 
                p.prescription_id,
                p.prescription_code,
                p.patient_id,
                p.hospital_id,
                p.pharmacy_id,
                p.doctor_name,
                p.visit_date,
                p.prescription_image_path,
                p.status,
                p.clarification_message,
                p.total_amount,
                p.submitted_date,
                p.last_updated,
                pt.full_name as patient_name,
                h.name as hospital_name,
                h.government_id as hospital_code
            FROM prescriptions p
            INNER JOIN patients pt ON p.patient_id = pt.patient_id
            INNER JOIN hospitals h ON p.hospital_id = h.hospital_id
            WHERE p.status IN (
                'Sent to pharmacies',
                'Pharmacy reviewing',
                'Awaiting patient payment',
                'Payment received',
                'Ready for pickup',
                'Ready for delivery',
                'Dispensed',
                'On hold'
            )
            ORDER BY p.last_updated DESC";
    
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
                    prescription_medicine_id,
                    medicine_name,
                    dosage,
                    frequency,
                    duration,
                    price
                FROM prescription_medicines
                WHERE prescription_id = $prescriptionId
                ORDER BY prescription_medicine_id ASC";
        
        $medicines = $db->db_fetch_all($medSql);
        $formattedMedicines = [];
        
        if ($medicines && is_array($medicines)) {
            foreach ($medicines as $med) {
                $formattedMedicines[] = [
                    'medicine_id' => $med['prescription_medicine_id'],
                    'name' => $med['medicine_name'],
                    'dosage' => $med['dosage'],
                    'frequency' => $med['frequency'],
                    'duration' => $med['duration'],
                    'price' => $med['price'] ? (float)$med['price'] : null
                ];
            }
        }
        
        // Format prescription
        $formattedPrescriptions[] = [
            'id' => $prescription['prescription_code'],
            'prescription_id' => $prescriptionId,
            'patient' => $prescription['patient_name'],
            'patientId' => 'PT-' . str_pad($prescription['patient_id'], 5, '0', STR_PAD_LEFT),
            'hospital' => $prescription['hospital_name'],
            'hospitalCode' => $prescription['hospital_code'],
            'dateReceived' => date('Y-m-d', strtotime($prescription['last_updated'])), // Use last_updated as date received
            'status' => $prescription['status'],
            'hospitalNotes' => $prescription['clarification_message'] || 'No additional notes from hospital',
            'warnings' => '', // Can be added later if needed in database
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
    error_log("Get pharmacy prescriptions action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching prescriptions.'
    ]);
}
?>

