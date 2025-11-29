<?php
/**
 * Get Prescription Timeline Action
 * Fetches timeline entries for a specific prescription
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
        'message' => 'Please login to view prescription timeline.'
    ]);
    exit;
}

// Validate prescription_id parameter
if (!isset($_GET['prescription_id']) || empty($_GET['prescription_id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameter: prescription_id'
    ]);
    exit;
}

$prescriptionId = (int)$_GET['prescription_id'];

try {
    $db = new db_connection();
    
    // Fetch timeline entries for this prescription
    $sql = "SELECT 
                timeline_id,
                prescription_id,
                status_text,
                DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i') as timestamp
            FROM prescription_timeline
            WHERE prescription_id = $prescriptionId
            ORDER BY timestamp ASC";
    
    $timeline = $db->db_fetch_all($sql);
    
    if ($timeline === false) {
        throw new Exception('Failed to fetch timeline from database.');
    }
    
    // Format timeline entries
    $formattedTimeline = [];
    
    if (is_array($timeline) && count($timeline) > 0) {
        foreach ($timeline as $entry) {
            $formattedTimeline[] = [
                'id' => (int)$entry['timeline_id'],
                'prescription_id' => (int)$entry['prescription_id'],
                'status_text' => $entry['status_text'],
                'timestamp' => $entry['timestamp']
            ];
        }
    }
    
    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'prescription_id' => $prescriptionId,
        'timeline' => $formattedTimeline
    ]);
    
} catch (Exception $e) {
    error_log("Get prescription timeline error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching timeline.'
    ]);
}
?>

