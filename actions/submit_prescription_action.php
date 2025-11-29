<?php
/**
 * Submit Prescription Action
 * Handles prescription submission from patients, including file uploads
 */

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Only POST requests are accepted.'
    ]);
    exit;
}

// Include core functions (starts session automatically)
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/prescription_controller.php';

// Check if user is logged in and is a patient
if (!is_logged_in() || get_user_type() !== 'patient') {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login as a patient to submit prescriptions.'
    ]);
    exit;
}

try {
    // Get patient ID from session
    $patientId = isset($_SESSION['patient_id']) ? (int)$_SESSION['patient_id'] : null;
    
    if (!$patientId) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Patient session not found. Please login again.'
        ]);
        exit;
    }
    
    // Handle file upload if present (FormData)
    $imagePath = null;
    if (isset($_FILES['prescription_image']) && $_FILES['prescription_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/prescriptions/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $fileType = $_FILES['prescription_image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid file type. Only JPG, PNG, and PDF files are allowed.'
            ]);
            exit;
        }
        
        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($_FILES['prescription_image']['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'File size exceeds 5MB limit.'
            ]);
            exit;
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($_FILES['prescription_image']['name'], PATHINFO_EXTENSION);
        $filename = 'prescription_' . $patientId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['prescription_image']['tmp_name'], $uploadPath)) {
            $imagePath = 'uploads/prescriptions/' . $filename;
        } else {
            throw new Exception('Failed to upload prescription image.');
        }
    }
    
    // Get form data - check if it's FormData or JSON
    $input = [];
    
    // Check if data was sent as FormData (for file uploads)
    if (isset($_POST['hospital_id']) || isset($_POST['prescription_data'])) {
        // FormData was sent - get values from POST
        if (isset($_POST['hospital_id'])) {
            $input['prescription_code'] = $_POST['prescription_code'] ?? '';
            $input['hospital_id'] = $_POST['hospital_id'];
            $input['doctor_name'] = $_POST['doctor_name'] ?? '';
            $input['visit_date'] = $_POST['visit_date'] ?? '';
            
            if (isset($_POST['medicines'])) {
                $input['medicines'] = json_decode($_POST['medicines'], true);
            }
        } elseif (isset($_POST['prescription_data'])) {
            // Prescription data sent as JSON string
            $input = json_decode($_POST['prescription_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid prescription data format.');
            }
        }
    } else {
        // Try to get JSON from input stream
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        // Validate JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid data received.');
        }
    }
    
    // Validate required fields
    if (empty($input['prescription_code'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Prescription ID is required.'
        ]);
        exit;
    }
    
    if (empty($input['hospital_id'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Hospital selection is required.'
        ]);
        exit;
    }
    
    if (empty($input['doctor_name']) || empty($input['visit_date'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Doctor name and visit date are required.'
        ]);
        exit;
    }
    
    if (empty($input['medicines']) || !is_array($input['medicines']) || count($input['medicines']) === 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'At least one medicine is required.'
        ]);
        exit;
    }
    
    // Prepare data for controller
    $prescriptionData = [
        'patient_id' => $patientId,
        'prescription_code' => trim($input['prescription_code']),
        'hospital_id' => (int)$input['hospital_id'],
        'doctor_name' => trim($input['doctor_name']),
        'visit_date' => $input['visit_date'],
        'medicines' => $input['medicines']
    ];
    
    // Add image path if uploaded
    if ($imagePath) {
        $prescriptionData['prescription_image_path'] = $imagePath;
    }
    
    // Create controller instance
    $prescriptionController = new PrescriptionController();
    
    // Submit prescription
    $result = $prescriptionController->submit_prescription_ctr($prescriptionData);
    
    // Return JSON response
    http_response_code($result['status'] === 'success' ? 200 : 400);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Submit prescription action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while submitting the prescription. Please try again.'
    ]);
}
?>

