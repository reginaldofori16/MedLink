<?php
/**
 * Get Recent Activity Action
 * Fetches recent activity from prescription timeline for admin dashboard
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
    
    // Fetch recent timeline entries with prescription and user info
    $sql = "SELECT 
                pt.timestamp,
                pt.status_text,
                p.prescription_code,
                pat.full_name as patient_name,
                h.name as hospital_name,
                ph.name as pharmacy_name
            FROM prescription_timeline pt
            INNER JOIN prescriptions p ON pt.prescription_id = p.prescription_id
            INNER JOIN patients pat ON p.patient_id = pat.patient_id
            INNER JOIN hospitals h ON p.hospital_id = h.hospital_id
            LEFT JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
            ORDER BY pt.timestamp DESC
            LIMIT 20";
    
    $activities = $db->db_fetch_all($sql);
    
    if ($activities === false) {
        throw new Exception('Failed to fetch recent activity from database.');
    }
    
    // Format activities with relative time
    $formattedActivities = [];
    foreach ($activities ?: [] as $activity) {
        $timestamp = strtotime($activity['timestamp']);
        $now = time();
        $diff = $now - $timestamp;
        
        // Calculate relative time
        if ($diff < 60) {
            $timeAgo = $diff . ' sec ago';
        } elseif ($diff < 3600) {
            $timeAgo = floor($diff / 60) . ' min ago';
        } elseif ($diff < 86400) {
            $timeAgo = floor($diff / 3600) . ' hour' . (floor($diff / 3600) > 1 ? 's' : '') . ' ago';
        } else {
            $timeAgo = floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';
        }
        
        // Build activity text
        $activityText = $activity['status_text'];
        if ($activity['prescription_code']) {
            $activityText .= ' (Prescription: ' . $activity['prescription_code'] . ')';
        }
        
        $formattedActivities[] = [
            'time' => $timeAgo,
            'text' => $activityText,
            'timestamp' => date('Y-m-d H:i', $timestamp)
        ];
    }
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'activities' => $formattedActivities
    ]);
    
} catch (Exception $e) {
    error_log("Get recent activity action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching recent activity.'
    ]);
}
?>

