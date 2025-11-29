<?php
/**
 * Get Admin Analytics Action
 * Fetches analytics data for admin dashboard (top hospitals, pharmacies, medicines)
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
    
    // Get top hospitals by prescription count
    $topHospitalsSql = "SELECT 
                            h.hospital_id,
                            h.name,
                            COUNT(p.prescription_id) as prescription_count
                        FROM hospitals h
                        LEFT JOIN prescriptions p ON h.hospital_id = p.hospital_id
                        GROUP BY h.hospital_id, h.name
                        ORDER BY prescription_count DESC
                        LIMIT 5";
    $topHospitals = $db->db_fetch_all($topHospitalsSql);
    $formattedHospitals = [];
    foreach ($topHospitals ?: [] as $hospital) {
        $formattedHospitals[] = [
            'name' => $hospital['name'],
            'prescriptions' => (int)$hospital['prescription_count']
        ];
    }
    
    // Get top pharmacies by prescription count
    $topPharmaciesSql = "SELECT 
                            ph.pharmacy_id,
                            ph.name,
                            COUNT(p.prescription_id) as order_count
                        FROM pharmacies ph
                        LEFT JOIN prescriptions p ON ph.pharmacy_id = p.pharmacy_id
                        GROUP BY ph.pharmacy_id, ph.name
                        ORDER BY order_count DESC
                        LIMIT 5";
    $topPharmacies = $db->db_fetch_all($topPharmaciesSql);
    $formattedPharmacies = [];
    foreach ($topPharmacies ?: [] as $pharmacy) {
        $formattedPharmacies[] = [
            'name' => $pharmacy['name'],
            'orders' => (int)$pharmacy['order_count']
        ];
    }
    
    // Get popular medicines by count
    $popularMedicinesSql = "SELECT 
                                medicine_name,
                                COUNT(*) as prescription_count
                            FROM prescription_medicines
                            GROUP BY medicine_name
                            ORDER BY prescription_count DESC
                            LIMIT 5";
    $popularMedicines = $db->db_fetch_all($popularMedicinesSql);
    $formattedMedicines = [];
    foreach ($popularMedicines ?: [] as $medicine) {
        $formattedMedicines[] = [
            'name' => $medicine['medicine_name'],
            'count' => (int)$medicine['prescription_count']
        ];
    }
    
    // Get prescription trends (last 30 days)
    $trendsSql = "SELECT 
                    DATE(submitted_date) as date,
                    COUNT(*) as count
                  FROM prescriptions
                  WHERE submitted_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY DATE(submitted_date)
                  ORDER BY date ASC";
    $trends = $db->db_fetch_all($trendsSql);
    $formattedTrends = [];
    foreach ($trends ?: [] as $trend) {
        $formattedTrends[] = [
            'date' => $trend['date'],
            'count' => (int)$trend['count']
        ];
    }
    
    // Get revenue trends (last 30 days)
    $revenueTrendsSql = "SELECT 
                            DATE(last_updated) as date,
                            SUM(total_amount) as revenue
                         FROM prescriptions
                         WHERE status IN ('Payment received', 'Ready for pickup', 'Ready for delivery', 'Dispensed')
                           AND total_amount IS NOT NULL
                           AND last_updated >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         GROUP BY DATE(last_updated)
                         ORDER BY date ASC";
    $revenueTrends = $db->db_fetch_all($revenueTrendsSql);
    $formattedRevenueTrends = [];
    $totalRevenue30Days = 0;
    foreach ($revenueTrends ?: [] as $trend) {
        $revenue = (float)$trend['revenue'];
        $totalRevenue30Days += $revenue;
        $formattedRevenueTrends[] = [
            'date' => $trend['date'],
            'revenue' => $revenue
        ];
    }
    
    // Get user growth (last 30 days) - patients
    $patientGrowthSql = "SELECT 
                            DATE(registered_date) as date,
                            COUNT(*) as count
                          FROM patients
                          WHERE registered_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                          GROUP BY DATE(registered_date)";
    $patientGrowth = $db->db_fetch_all($patientGrowthSql);
    
    // Get user growth - hospitals
    $hospitalGrowthSql = "SELECT 
                            DATE(registered_date) as date,
                            COUNT(*) as count
                          FROM hospitals
                          WHERE registered_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                          GROUP BY DATE(registered_date)";
    $hospitalGrowth = $db->db_fetch_all($hospitalGrowthSql);
    
    // Get user growth - pharmacies
    $pharmacyGrowthSql = "SELECT 
                            DATE(registered_date) as date,
                            COUNT(*) as count
                          FROM pharmacies
                          WHERE registered_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                          GROUP BY DATE(registered_date)";
    $pharmacyGrowth = $db->db_fetch_all($pharmacyGrowthSql);
    
    // Combine all user growth data
    $userGrowthMap = [];
    foreach (($patientGrowth ?: []) as $item) {
        $date = $item['date'];
        $userGrowthMap[$date] = ($userGrowthMap[$date] ?? 0) + (int)$item['count'];
    }
    foreach (($hospitalGrowth ?: []) as $item) {
        $date = $item['date'];
        $userGrowthMap[$date] = ($userGrowthMap[$date] ?? 0) + (int)$item['count'];
    }
    foreach (($pharmacyGrowth ?: []) as $item) {
        $date = $item['date'];
        $userGrowthMap[$date] = ($userGrowthMap[$date] ?? 0) + (int)$item['count'];
    }
    
    $userGrowth = [];
    foreach ($userGrowthMap as $date => $count) {
        $userGrowth[] = ['date' => $date, 'count' => $count];
    }
    usort($userGrowth, function($a, $b) {
        return strcmp($a['date'], $b['date']);
    });
    $formattedUserGrowth = [];
    foreach ($userGrowth ?: [] as $growth) {
        $formattedUserGrowth[] = [
            'date' => $growth['date'],
            'count' => (int)$growth['count']
        ];
    }
    
    // Get total users count
    $totalUsers = $db->db_fetch_one("SELECT 
                                        (SELECT COUNT(*) FROM patients) +
                                        (SELECT COUNT(*) FROM hospitals) +
                                        (SELECT COUNT(*) FROM pharmacies) as total");
    $totalUsersCount = $totalUsers ? (int)$totalUsers['total'] : 0;
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'analytics' => [
            'topHospitals' => $formattedHospitals,
            'topPharmacies' => $formattedPharmacies,
            'popularMedicines' => $formattedMedicines,
            'prescriptionTrends' => $formattedTrends,
            'revenueTrends' => $formattedRevenueTrends,
            'totalRevenue30Days' => $totalRevenue30Days,
            'userGrowth' => $formattedUserGrowth,
            'totalUsers' => $totalUsersCount
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get admin analytics action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching analytics.'
    ]);
}
?>

