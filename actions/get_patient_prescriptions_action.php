<?php
/**
 * Get Patient Prescriptions Action
 * Fetches all prescriptions for the logged-in patient
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

// Check if user is logged in and is a patient
if (!is_logged_in() || get_user_type() !== 'patient') {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login as a patient to view prescriptions.'
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
    
    // Fetch all prescriptions for this patient with hospital name and pharmacy name
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
                p.pharmacy_id,
                p.total_amount,
                p.submitted_date,
                p.last_updated,
                h.name as hospital_name,
                ph.name as pharmacy_name,
                ph.location as pharmacy_location,
                ph.contact as pharmacy_contact
            FROM prescriptions p
            INNER JOIN hospitals h ON p.hospital_id = h.hospital_id
            LEFT JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            WHERE p.patient_id = $patientId
            ORDER BY p.submitted_date DESC";
    
    $prescriptions = $db->db_fetch_all($sql);
    
    if ($prescriptions === false) {
        throw new Exception('Failed to fetch prescriptions from database.');
    }
    
    // Format prescriptions with medicines and timeline
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
        
        // Fetch medicines for this prescription with prices
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
                    'id' => (int)$med['prescription_medicine_id'],
                    'name' => $med['medicine_name'],
                    'dosage' => $med['dosage'],
                    'frequency' => $med['frequency'],
                    'duration' => $med['duration'],
                    'price' => $med['price'] ? (float)$med['price'] : null
                ];
            }
        }
        
        // Fetch timeline for this prescription
        $timelineSql = "SELECT 
                        status_text,
                        timestamp
                    FROM prescription_timeline
                    WHERE prescription_id = $prescriptionId
                    ORDER BY timestamp ASC";
        
        $timeline = $db->db_fetch_all($timelineSql);
        $formattedTimeline = [];
        
        if ($timeline && is_array($timeline)) {
            foreach ($timeline as $entry) {
                $formattedTimeline[] = [
                    'time' => date('Y-m-d H:i', strtotime($entry['timestamp'])),
                    'text' => $entry['status_text']
                ];
            }
        }
        
        // Format prescription
        $formattedPrescriptions[] = [
            'id' => $prescription['prescription_code'],
            'prescription_id' => $prescriptionId,
            'hospital' => $prescription['hospital_name'],
            'hospital_id' => (int)$prescription['hospital_id'],
            'status' => $prescription['status'],
            'submittedDate' => date('Y-m-d', strtotime($prescription['submitted_date'])),
            'lastUpdated' => date('Y-m-d', strtotime($prescription['last_updated'])),
            'medicines' => $formattedMedicines,
            'timeline' => $formattedTimeline,
            'clarificationMessage' => $prescription['clarification_message'],
            'clarificationResponse' => $prescription['clarification_response'],
            'totalAmount' => $prescription['total_amount'] ? (float)$prescription['total_amount'] : null,
            'pharmacy' => $prescription['pharmacy_name'] ? [
                'name' => $prescription['pharmacy_name'],
                'location' => $prescription['pharmacy_location'],
                'contact' => $prescription['pharmacy_contact']
            ] : null,
            'pharmacy_id' => $prescription['pharmacy_id'] ? (int)$prescription['pharmacy_id'] : null
        ];
    }
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'prescriptions' => $formattedPrescriptions
    ]);
    
} catch (Exception $e) {
    error_log("Get patient prescriptions action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching prescriptions.'
    ]);
}
?>

