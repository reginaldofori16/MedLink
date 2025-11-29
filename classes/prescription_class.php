<?php
/**
 * Prescription Class (Model)
 * Uses database connection class and contains prescription methods
 */

require_once __DIR__ . '/../settings/db_class.php';
require_once __DIR__ . '/../settings/core.php';

class PrescriptionClass {
    private $db;
    
    public function __construct() {
        $this->db = new db_connection();
    }
    
    /**
     * Generate unique prescription code
     * @return string - Prescription code like RX-2025-014
     */
    private function generatePrescriptionCode() {
        $year = date('Y');
        
        // Get the last prescription ID for this year
        $sql = "SELECT prescription_id FROM prescriptions WHERE YEAR(created_at) = $year ORDER BY prescription_id DESC LIMIT 1";
        $result = $this->db->db_query($sql);
        
        if ($result && $this->db->db_count() > 0) {
            $lastPrescription = $this->db->db_fetch_one($sql);
            $nextNumber = (int)$lastPrescription['prescription_id'] + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'RX-' . $year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Check if prescription code already exists
     * @param string $prescriptionCode
     * @return bool
     */
    private function prescriptionCodeExists($prescriptionCode) {
        $code = $this->db->db_escape_string($prescriptionCode);
        $sql = "SELECT prescription_id FROM prescriptions WHERE prescription_code = '$code'";
        
        if ($this->db->db_query($sql)) {
            return $this->db->db_count() > 0;
        }
        
        return false;
    }
    
    /**
     * Add a new prescription
     * @param array $args - Contains: patient_id, hospital_id, prescription_code, doctor_name, visit_date, medicines[], prescription_image_path (optional)
     * @return array - ['status' => 'success'|'error', 'message' => string, 'prescription_id' => int|null]
     */
    public function add($args) {
        // Validate required fields
        if (empty($args['patient_id']) || empty($args['hospital_id'])) {
            return [
                'status' => 'error',
                'message' => 'Patient ID and Hospital ID are required.'
            ];
        }
        
        if (empty($args['prescription_code'])) {
            return [
                'status' => 'error',
                'message' => 'Prescription ID is required.'
            ];
        }
        
        if (empty($args['doctor_name']) || empty($args['visit_date'])) {
            return [
                'status' => 'error',
                'message' => 'Doctor name and visit date are required.'
            ];
        }
        
        if (empty($args['medicines']) || !is_array($args['medicines']) || count($args['medicines']) === 0) {
            return [
                'status' => 'error',
                'message' => 'At least one medicine is required.'
            ];
        }
        
        // Validate prescription code format and uniqueness
        $prescriptionCode = trim($args['prescription_code']);
        if ($this->prescriptionCodeExists($prescriptionCode)) {
            return [
                'status' => 'error',
                'message' => 'This Prescription ID already exists. Please use a different ID.'
            ];
        }
        
        try {
            // Start transaction
            $this->db->db_query('START TRANSACTION');
            
            // Use provided prescription code
            $prescriptionCodeEscaped = $this->db->db_escape_string($prescriptionCode);
            
            // Prepare data
            $patientId = (int)$args['patient_id'];
            $hospitalId = (int)$args['hospital_id'];
            $doctorName = $this->db->db_escape_string(trim($args['doctor_name']));
            $visitDate = $this->db->db_escape_string($args['visit_date']);
            $imagePath = isset($args['prescription_image_path']) ? "'" . $this->db->db_escape_string($args['prescription_image_path']) . "'" : 'NULL';
            
            // Insert prescription
            $sql = "INSERT INTO prescriptions (
                        patient_id,
                        hospital_id,
                        prescription_code,
                        doctor_name,
                        visit_date,
                        prescription_image_path,
                        status
                    ) VALUES (
                        $patientId,
                        $hospitalId,
                        '$prescriptionCodeEscaped',
                        '$doctorName',
                        '$visitDate',
                        $imagePath,
                        'Submitted by patient'
                    )";
            
            if (!$this->db->db_query($sql)) {
                throw new Exception('Failed to insert prescription: ' . $this->db->db_error());
            }
            
            $prescriptionId = $this->db->db_last_id();
            
            // Insert medicines
            foreach ($args['medicines'] as $medicine) {
                $medicineName = $this->db->db_escape_string(trim($medicine['name']));
                $dosage = $this->db->db_escape_string(trim($medicine['dosage']));
                $frequency = $this->db->db_escape_string(trim($medicine['frequency']));
                $duration = $this->db->db_escape_string(trim($medicine['duration']));
                
                $medSql = "INSERT INTO prescription_medicines (
                            prescription_id,
                            medicine_name,
                            dosage,
                            frequency,
                            duration
                        ) VALUES (
                            $prescriptionId,
                            '$medicineName',
                            '$dosage',
                            '$frequency',
                            '$duration'
                        )";
                
                if (!$this->db->db_query($medSql)) {
                    throw new Exception('Failed to insert medicine: ' . $this->db->db_error());
                }
            }
            
            // Add initial timeline entry
            $timelineSql = "INSERT INTO prescription_timeline (
                            prescription_id,
                            status_text
                        ) VALUES (
                            $prescriptionId,
                            'Submitted to hospital'
                        )";
            
            if (!$this->db->db_query($timelineSql)) {
                throw new Exception('Failed to insert timeline entry: ' . $this->db->db_error());
            }
            
            // Commit transaction
            $this->db->db_query('COMMIT');
            
            return [
                'status' => 'success',
                'message' => 'Prescription submitted successfully!',
                'prescription_id' => $prescriptionId,
                'prescription_code' => $prescriptionCode
            ];
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->db_query('ROLLBACK');
            error_log("Prescription add error: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Failed to submit prescription. Please try again.'
            ];
        }
    }
    
    /**
     * Get prescription by ID
     * @param int $prescriptionId
     * @return array|null
     */
    public function getPrescriptionById($prescriptionId) {
        $prescriptionId = (int)$prescriptionId;
        
        $sql = "SELECT * FROM prescriptions WHERE prescription_id = $prescriptionId";
        return $this->db->db_fetch_one($sql);
    }
}

?>

