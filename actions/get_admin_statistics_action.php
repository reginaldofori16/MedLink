<?php
/**
 * Get Admin Statistics Action
 * Fetches statistics for admin dashboard
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
    
    // Get total prescriptions count
    $totalPrescriptions = $db->db_fetch_one("SELECT COUNT(*) as count FROM prescriptions");
    $totalPrescriptionsCount = $totalPrescriptions ? (int)$totalPrescriptions['count'] : 0;
    
    // Get active prescriptions (not dispensed)
    $activePrescriptions = $db->db_fetch_one("SELECT COUNT(*) as count FROM prescriptions WHERE status != 'Dispensed'");
    $activePrescriptionsCount = $activePrescriptions ? (int)$activePrescriptions['count'] : 0;
    
    // Get total hospitals count
    $totalHospitals = $db->db_fetch_one("SELECT COUNT(*) as count FROM hospitals");
    $totalHospitalsCount = $totalHospitals ? (int)$totalHospitals['count'] : 0;
    
    // Get total pharmacies count
    $totalPharmacies = $db->db_fetch_one("SELECT COUNT(*) as count FROM pharmacies");
    $totalPharmaciesCount = $totalPharmacies ? (int)$totalPharmacies['count'] : 0;
    
    // Get total patients count
    $totalPatients = $db->db_fetch_one("SELECT COUNT(*) as count FROM patients");
    $totalPatientsCount = $totalPatients ? (int)$totalPatients['count'] : 0;
    
    // Get total revenue (sum of all paid prescriptions)
    $totalRevenue = $db->db_fetch_one("SELECT SUM(total_amount) as total FROM prescriptions WHERE status IN ('Payment received', 'Ready for pickup', 'Ready for delivery', 'Dispensed') AND total_amount IS NOT NULL");
    $totalRevenueAmount = $totalRevenue && $totalRevenue['total'] ? (float)$totalRevenue['total'] : 0.00;
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'statistics' => [
            'totalPrescriptions' => $totalPrescriptionsCount,
            'activePrescriptions' => $activePrescriptionsCount,
            'totalHospitals' => $totalHospitalsCount,
            'totalPharmacies' => $totalPharmaciesCount,
            'totalPatients' => $totalPatientsCount,
            'totalRevenue' => $totalRevenueAmount
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get admin statistics action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching statistics.'
    ]);
}
?>

