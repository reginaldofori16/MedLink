<?php
/**
 * Get Hospitals Action
 * Fetches hospitals from the database
 * - Admins: Gets all hospitals with full details
 * - Patients/Logged-in users: Gets only approved (active) hospitals for selection
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

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to access hospitals list.'
    ]);
    exit;
}

try {
    $db = new db_connection();
    $isAdmin = is_admin();
    $userType = get_user_type();
    
    // Determine what data to fetch based on user type
    if ($isAdmin) {
        // Admin: Fetch ALL hospitals with full details for admin dashboard
        $sql = "SELECT 
                    hospital_id,
                    name,
                    government_id,
                    contact,
                    status,
                    registered_date,
                    last_login,
                    created_at
                FROM hospitals 
                ORDER BY registered_date DESC";
        
        $hospitals = $db->db_fetch_all($sql);
        
        if ($hospitals === false) {
            throw new Exception('Failed to fetch hospitals from database.');
        }
        
        // Get prescription counts for each hospital
        $prescriptionCounts = [];
        $countSql = "SELECT hospital_id, COUNT(*) as count FROM prescriptions GROUP BY hospital_id";
        $counts = $db->db_fetch_all($countSql);
        if ($counts && is_array($counts)) {
            foreach ($counts as $count) {
                $prescriptionCounts[(int)$count['hospital_id']] = (int)$count['count'];
            }
        }
        
        // Format the data for admin dashboard
        $formattedHospitals = array_map(function($hospital) use ($prescriptionCounts) {
            $hospitalId = (int)$hospital['hospital_id'];
            return [
                'id' => 'HOS-' . str_pad($hospitalId, 3, '0', STR_PAD_LEFT),
                'hospital_id' => $hospitalId,
                'name' => $hospital['name'],
                'code' => $hospital['government_id'],
                'contact' => $hospital['contact'],
                'status' => ucfirst($hospital['status']),
                'registered' => date('Y-m-d', strtotime($hospital['registered_date'])),
                'last_login' => $hospital['last_login'] ? date('Y-m-d H:i', strtotime($hospital['last_login'])) : 'Never',
                'prescriptions' => isset($prescriptionCounts[$hospitalId]) ? $prescriptionCounts[$hospitalId] : 0
            ];
        }, $hospitals);
        
    } else {
        // Patient/Regular user: Fetch ONLY approved (active) hospitals for selection
        $sql = "SELECT 
                    hospital_id,
                    name,
                    government_id,
                    contact,
                    status,
                    registered_date
                FROM hospitals 
                WHERE status = 'active'
                ORDER BY name ASC";
        
        $hospitals = $db->db_fetch_all($sql);
        
        if ($hospitals === false) {
            throw new Exception('Failed to fetch hospitals from database.');
        }
        
        // Format the data for patient selection (simpler format)
        $formattedHospitals = array_map(function($hospital) {
            return [
                'hospital_id' => (int)$hospital['hospital_id'],
                'name' => $hospital['name'],
                'government_id' => $hospital['government_id']
            ];
        }, $hospitals);
    }
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'hospitals' => $formattedHospitals
    ]);
    
} catch (Exception $e) {
    error_log("Get hospitals action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching hospitals.'
    ]);
}
?>

