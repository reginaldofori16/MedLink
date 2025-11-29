<?php
/**
 * Get Pharmacies Action
 * Fetches all pharmacies from the database for admin dashboard
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
    
    // Fetch all pharmacies
    $sql = "SELECT 
                pharmacy_id,
                name,
                government_id,
                contact,
                location,
                status,
                registered_date,
                last_login,
                created_at
            FROM pharmacies 
            ORDER BY registered_date DESC";
    
    $pharmacies = $db->db_fetch_all($sql);
    
    if ($pharmacies === false) {
        throw new Exception('Failed to fetch pharmacies from database.');
    }
    
    // Get order counts for each pharmacy
    $orderCounts = [];
    $countSql = "SELECT pharmacy_id, COUNT(*) as count FROM prescriptions WHERE pharmacy_id IS NOT NULL GROUP BY pharmacy_id";
    $counts = $db->db_fetch_all($countSql);
    if ($counts && is_array($counts)) {
        foreach ($counts as $count) {
            $orderCounts[(int)$count['pharmacy_id']] = (int)$count['count'];
        }
    }
    
    // Format the data for frontend
    $formattedPharmacies = array_map(function($pharmacy) use ($orderCounts) {
        $pharmacyId = (int)$pharmacy['pharmacy_id'];
        return [
            'id' => 'PH-' . str_pad($pharmacyId, 3, '0', STR_PAD_LEFT),
            'pharmacy_id' => $pharmacyId,
            'name' => $pharmacy['name'],
            'code' => $pharmacy['government_id'],
            'location' => $pharmacy['location'],
            'contact' => $pharmacy['contact'],
            'status' => ucfirst($pharmacy['status']),
            'registered' => date('Y-m-d', strtotime($pharmacy['registered_date'])),
            'last_login' => $pharmacy['last_login'] ? date('Y-m-d H:i', strtotime($pharmacy['last_login'])) : 'Never',
            'orders' => isset($orderCounts[$pharmacyId]) ? $orderCounts[$pharmacyId] : 0
        ];
    }, $pharmacies);
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'pharmacies' => $formattedPharmacies
    ]);
    
} catch (Exception $e) {
    error_log("Get pharmacies action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching pharmacies.'
    ]);
}
?>

