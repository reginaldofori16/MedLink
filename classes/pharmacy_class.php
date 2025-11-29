<?php
/**
 * Pharmacy Class (Model)
 * Uses database connection class and contains pharmacy methods
 */

require_once __DIR__ . '/../settings/db_class.php';
require_once __DIR__ . '/../settings/core.php';

class PharmacyClass {
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
        $sql = "SELECT pharmacy_id FROM pharmacies WHERE government_id = '$government_id'";
        
        if ($this->db->db_query($sql)) {
            return $this->db->db_count() > 0;
        }
        
        return false;
    }
    
    /**
     * Add a new pharmacy
     * @param array $args - Contains: name, government_id, contact, location, password
     * @return array - ['status' => 'success'|'error', 'message' => string, 'pharmacy_id' => int|null]
     */
    public function add($args) {
        // Validate required fields
        $required = ['name', 'government_id', 'contact', 'location', 'password'];
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
                'message' => 'Pharmacy name must be 255 characters or less.'
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
                'message' => 'Government Pharmacy ID already registered. Please use a different ID or login.'
            ];
        }
        
        // Validate contact length
        if (strlen($args['contact']) > 255) {
            return [
                'status' => 'error',
                'message' => 'Contact information must be 255 characters or less.'
            ];
        }
        
        // Validate location length
        if (strlen($args['location']) > 255) {
            return [
                'status' => 'error',
                'message' => 'Location must be 255 characters or less.'
            ];
        }
        
        // Hash password
        $password_hash = password_hash($args['password'], PASSWORD_DEFAULT);
        
        // Escape all input data for security
        $name = $this->db->db_escape_string($args['name']);
        $government_id = $this->db->db_escape_string($args['government_id']);
        $contact = $this->db->db_escape_string($args['contact']);
        $location = $this->db->db_escape_string($args['location']);
        $password_hash_escaped = $this->db->db_escape_string($password_hash);
        
        // Build INSERT query
        $sql = "INSERT INTO pharmacies (
            name, 
            government_id, 
            contact, 
            location,
            password_hash,
            status
        ) VALUES (
            '$name',
            '$government_id',
            '$contact',
            '$location',
            '$password_hash_escaped',
            'pending'
        )";
        
        // Execute query
        if ($this->db->db_query($sql)) {
            $pharmacy_id = $this->db->db_last_id();
            return [
                'status' => 'success',
                'message' => 'Pharmacy registration successful! Your account is pending approval.',
                'pharmacy_id' => $pharmacy_id
            ];
        } else {
            error_log("Pharmacy add error: " . $this->db->db_error());
            return [
                'status' => 'error',
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }
    
    /**
     * Edit pharmacy information
     * @param array $args
     * @return array
     */
    public function edit($args) {
        // Implementation for editing pharmacy (for future use)
        return ['status' => 'error', 'message' => 'Edit functionality not yet implemented'];
    }
    
    /**
     * Delete pharmacy
     * @param int $pharmacy_id
     * @return array
     */
    public function delete($pharmacy_id) {
        // Implementation for deleting pharmacy (for future use)
        return ['status' => 'error', 'message' => 'Delete functionality not yet implemented'];
    }
    
    /**
     * Get pharmacy by government ID
     * @param string $government_id
     * @return array|null
     */
    public function getPharmacyByGovernmentId($government_id) {
        // Escape government_id for security
        $government_id = $this->db->db_escape_string($government_id);
        
        // Query to get pharmacy by government_id
        $sql = "SELECT * FROM pharmacies WHERE government_id = '$government_id'";
        
        // db_fetch_one already calls db_query internally
        return $this->db->db_fetch_one($sql);
    }
    
    /**
     * Login pharmacy - get pharmacy by government ID and verify password
     * @param array $args - Contains: government_id, password
     * @return array - ['status' => 'success'|'error', 'message' => string, 'pharmacy' => array|null]
     */
    public function get($args) {
        // Validate required fields
        if (empty($args['government_id']) || empty($args['password'])) {
            return [
                'status' => 'error',
                'message' => 'Government ID and password are required.',
                'pharmacy' => null
            ];
        }
        
        // Get pharmacy by government ID
        $pharmacy = $this->getPharmacyByGovernmentId($args['government_id']);
        
        // Check if pharmacy exists
        if (!$pharmacy) {
            return [
                'status' => 'error',
                'message' => 'Invalid Government ID or password.',
                'pharmacy' => null
            ];
        }
        
        // Check if account is active
        if (isset($pharmacy['status']) && $pharmacy['status'] !== 'active') {
            return [
                'status' => 'error',
                'message' => 'Your account is not active. Please contact support.',
                'pharmacy' => null
            ];
        }
        
        // Verify password
        if (!password_verify($args['password'], $pharmacy['password_hash'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid Government ID or password.',
                'pharmacy' => null
            ];
        }
        
        // Update last login timestamp
        $pharmacy_id = (int)$pharmacy['pharmacy_id'];
        $update_sql = "UPDATE pharmacies SET last_login = NOW() WHERE pharmacy_id = $pharmacy_id";
        $this->db->db_query($update_sql);
        
        // Return success with pharmacy data (excluding password hash)
        unset($pharmacy['password_hash']);
        
        return [
            'status' => 'success',
            'message' => 'Login successful!',
            'pharmacy' => $pharmacy
        ];
    }
}
?>

