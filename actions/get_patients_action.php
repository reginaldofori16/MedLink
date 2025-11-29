<?php
/**
 * Get Patients Action
 * Fetches all patients from the database for admin dashboard
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
    
    // Fetch all patients
    $sql = "SELECT 
                patient_id,
                full_name,
                email,
                phone,
                country,
                city,
                user_role,
                status,
                registered_date,
                last_login,
                created_at
            FROM patients 
            ORDER BY registered_date DESC";
    
    $patients = $db->db_fetch_all($sql);
    
    if ($patients === false) {
        throw new Exception('Failed to fetch patients from database.');
    }
    
    // Get prescription counts for each patient
    $prescriptionCounts = [];
    $countSql = "SELECT patient_id, COUNT(*) as count FROM prescriptions GROUP BY patient_id";
    $counts = $db->db_fetch_all($countSql);
    if ($counts && is_array($counts)) {
        foreach ($counts as $count) {
            $prescriptionCounts[(int)$count['patient_id']] = (int)$count['count'];
        }
    }
    
    // Format the data for frontend
    $formattedPatients = array_map(function($patient) use ($prescriptionCounts) {
        $patientId = (int)$patient['patient_id'];
        return [
            'id' => 'PT-' . str_pad($patientId, 5, '0', STR_PAD_LEFT),
            'patient_id' => $patientId,
            'name' => $patient['full_name'],
            'email' => $patient['email'],
            'phone' => $patient['phone'],
            'country' => $patient['country'],
            'city' => $patient['city'],
            'user_role' => (int)$patient['user_role'],
            'status' => ucfirst($patient['status']),
            'registered' => date('Y-m-d', strtotime($patient['registered_date'])),
            'last_login' => $patient['last_login'] ? date('Y-m-d H:i', strtotime($patient['last_login'])) : 'Never',
            'prescriptions' => isset($prescriptionCounts[$patientId]) ? $prescriptionCounts[$patientId] : 0
        ];
    }, $patients);
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'patients' => $formattedPatients
    ]);
    
} catch (Exception $e) {
    error_log("Get patients action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching patients.'
    ]);
}
?>

