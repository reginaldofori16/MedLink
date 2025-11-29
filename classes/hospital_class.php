<?php
/**
 * Hospital Class (Model)
 * Uses database connection class and contains hospital methods
 */

require_once __DIR__ . '/../settings/db_class.php';
require_once __DIR__ . '/../settings/core.php';

class HospitalClass {
    private $db;
    
    public function __construct() {
        $this->db = new db_connection();
    }
    
    /**
     * Check if government ID already exists
     * @param string $government_id
     * @return bool
     */
    public function governmentIdExists($government_id) {
        // Escape government_id for security
        $government_id = $this->db->db_escape_string($government_id);
        
        // Query to check if government_id exists
        $sql = "SELECT hospital_id FROM hospitals WHERE government_id = '$government_id'";
        
        if ($this->db->db_query($sql)) {
            return $this->db->db_count() > 0;
        }
        
        return false;
    }
    
    /**
     * Add a new hospital
     * @param array $args - Contains: name, government_id, contact, password
     * @return array - ['status' => 'success'|'error', 'message' => string, 'hospital_id' => int|null]
     */
    public function add($args) {
        // Validate required fields
        $required = ['name', 'government_id', 'contact', 'password'];
        foreach ($required as $field) {
            if (empty($args[$field])) {
                return [
                    'status' => 'error',
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                ];
            }
        }
        
        // Validate name length
        if (strlen($args['name']) > 255) {
            return [
                'status' => 'error',
                'message' => 'Hospital name must be 255 characters or less.'
            ];
        }
        
        // Validate government_id length
        if (strlen($args['government_id']) > 100) {
            return [
                'status' => 'error',
                'message' => 'Government ID must be 100 characters or less.'
            ];
        }
        
        // Check if government_id already exists
        if ($this->governmentIdExists($args['government_id'])) {
            return [
                'status' => 'error',
                'message' => 'Government Hospital ID already registered. Please use a different ID or login.'
            ];
        }
        
        // Validate contact length
        if (strlen($args['contact']) > 255) {
            return [
                'status' => 'error',
                'message' => 'Contact information must be 255 characters or less.'
            ];
        }
        
        // Hash password
        $password_hash = password_hash($args['password'], PASSWORD_DEFAULT);
        
        // Escape all input data for security
        $name = $this->db->db_escape_string($args['name']);
        $government_id = $this->db->db_escape_string($args['government_id']);
        $contact = $this->db->db_escape_string($args['contact']);
        $password_hash_escaped = $this->db->db_escape_string($password_hash);
        
        // Build INSERT query
        $sql = "INSERT INTO hospitals (
            name, 
            government_id, 
            contact, 
            password_hash,
            status
        ) VALUES (
            '$name',
            '$government_id',
            '$contact',
            '$password_hash_escaped',
            'pending'
        )";
        
        // Execute query
        if ($this->db->db_query($sql)) {
            $hospital_id = $this->db->db_last_id();
            return [
                'status' => 'success',
                'message' => 'Hospital registration successful! Your account is pending approval.',
                'hospital_id' => $hospital_id
            ];
        } else {
            error_log("Hospital add error: " . $this->db->db_error());
            return [
                'status' => 'error',
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }
    
    /**
     * Edit hospital information
     * @param array $args
     * @return array
     */
    public function edit($args) {
        // Implementation for editing hospital (for future use)
        return ['status' => 'error', 'message' => 'Edit functionality not yet implemented'];
    }
    
    /**
     * Delete hospital
     * @param int $hospital_id
     * @return array
     */
    public function delete($hospital_id) {
        // Implementation for deleting hospital (for future use)
        return ['status' => 'error', 'message' => 'Delete functionality not yet implemented'];
    }
    
    /**
     * Get hospital by government ID
     * @param string $government_id
     * @return array|null
     */
    public function getHospitalByGovernmentId($government_id) {
        // Escape government_id for security
        $government_id = $this->db->db_escape_string($government_id);
        
        // Query to get hospital by government_id
        $sql = "SELECT * FROM hospitals WHERE government_id = '$government_id'";
        
        // db_fetch_one already calls db_query internally
        return $this->db->db_fetch_one($sql);
    }
    
    /**
     * Login hospital - get hospital by government ID and verify password
     * @param array $args - Contains: government_id, password
     * @return array - ['status' => 'success'|'error', 'message' => string, 'hospital' => array|null]
     */
    public function get($args) {
        // Validate required fields
        if (empty($args['government_id']) || empty($args['password'])) {
            return [
                'status' => 'error',
                'message' => 'Government ID and password are required.',
                'hospital' => null
            ];
        }
        
        // Get hospital by government ID
        $hospital = $this->getHospitalByGovernmentId($args['government_id']);
        
        // Check if hospital exists
        if (!$hospital) {
            return [
                'status' => 'error',
                'message' => 'Invalid Government ID or password.',
                'hospital' => null
            ];
        }
        
        // Check if account is active
        if (isset($hospital['status']) && $hospital['status'] !== 'active') {
            return [
                'status' => 'error',
                'message' => 'Your account is not active. Please contact support.',
                'hospital' => null
            ];
        }
        
        // Verify password
        if (!password_verify($args['password'], $hospital['password_hash'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid Government ID or password.',
                'hospital' => null
            ];
        }
        
        // Update last login timestamp
        $hospital_id = (int)$hospital['hospital_id'];
        $update_sql = "UPDATE hospitals SET last_login = NOW() WHERE hospital_id = $hospital_id";
        $this->db->db_query($update_sql);
        
        // Return success with hospital data (excluding password hash)
        unset($hospital['password_hash']);
        
        return [
            'status' => 'success',
            'message' => 'Login successful!',
            'hospital' => $hospital
        ];
    }
}
?>

