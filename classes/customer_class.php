<?php
/**
 * Customer Class (Model)
 * Uses database connection class and contains customer methods
 */

require_once __DIR__ . '/../settings/db_class.php';
require_once __DIR__ . '/../settings/core.php';

class CustomerClass {
    private $db;
    
    public function __construct() {
        $this->db = new db_connection();
    }
    
    /**
     * Check if email already exists
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        // Escape email for security
        $email = $this->db->db_escape_string($email);
        
        // Query to check if email exists
        $sql = "SELECT patient_id FROM patients WHERE email = '$email'";
        
        if ($this->db->db_query($sql)) {
            return $this->db->db_count() > 0;
        }
        
        return false;
    }
    
    /**
     * Add a new customer (patient)
     * @param array $args - Contains: full_name, email, phone, password, country, city
     * @return array - ['status' => 'success'|'error', 'message' => string, 'patient_id' => int|null]
     */
    public function add($args) {
        // Validate required fields
        $required = ['full_name', 'email', 'phone', 'password', 'country', 'city'];
        foreach ($required as $field) {
            if (empty($args[$field])) {
                return [
                    'status' => 'error',
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                ];
            }
        }
        
        // Check email format
        if (!filter_var($args['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'error',
                'message' => 'Invalid email format.'
            ];
        }
        
        // Check if email already exists
        if ($this->emailExists($args['email'])) {
            return [
                'status' => 'error',
                'message' => 'Email already registered. Please use a different email or login.'
            ];
        }
        
        // Validate phone number (Ghana format: +233XXXXXXXXX or 0XXXXXXXXX)
        $phone = preg_replace('/\s+/', '', $args['phone']);
        if (!preg_match('/^(\+233|0)[0-9]{9}$/', $phone)) {
            return [
                'status' => 'error',
                'message' => 'Invalid phone number format. Use Ghana format: +233XXXXXXXXX or 0XXXXXXXXX'
            ];
        }
        
        // Validate name length
        if (strlen($args['full_name']) > 255) {
            return [
                'status' => 'error',
                'message' => 'Full name must be 255 characters or less.'
            ];
        }
        
        // Validate country and city length
        if (strlen($args['country']) > 100) {
            return [
                'status' => 'error',
                'message' => 'Country name must be 100 characters or less.'
            ];
        }
        
        if (strlen($args['city']) > 100) {
            return [
                'status' => 'error',
                'message' => 'City name must be 100 characters or less.'
            ];
        }
        
        // Hash password
        $password_hash = password_hash($args['password'], PASSWORD_DEFAULT);
        
        // Set user role (default 2 for customer)
        $user_role = isset($args['user_role']) ? (int)$args['user_role'] : 2;
        
        // Escape all input data for security
        $full_name = $this->db->db_escape_string($args['full_name']);
        $email = $this->db->db_escape_string(strtolower(trim($args['email'])));
        $phone = $this->db->db_escape_string($phone);
        $country = $this->db->db_escape_string($args['country']);
        $city = $this->db->db_escape_string($args['city']);
        $password_hash_escaped = $this->db->db_escape_string($password_hash);
        
        // Build INSERT query
        $sql = "INSERT INTO patients (
            full_name, 
            email, 
            phone, 
            country, 
            city, 
            password_hash, 
            user_role,
            status
        ) VALUES (
            '$full_name',
            '$email',
            '$phone',
            '$country',
            '$city',
            '$password_hash_escaped',
            $user_role,
            'active'
        )";
        
        // Execute query
        if ($this->db->db_query($sql)) {
            $patient_id = $this->db->db_last_id();
            return [
                'status' => 'success',
                'message' => 'Registration successful! You can now login.',
                'patient_id' => $patient_id
            ];
        } else {
            error_log("Customer add error: " . $this->db->db_error());
            return [
                'status' => 'error',
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }
    
    /**
     * Edit customer information
     * @param array $args
     * @return array
     */
    public function edit($args) {
        // Implementation for editing customer (for future use)
        // This will be implemented in later labs
        return ['status' => 'error', 'message' => 'Edit functionality not yet implemented'];
    }
    
    /**
     * Delete customer
     * @param int $patient_id
     * @return array
     */
    public function delete($patient_id) {
        // Implementation for deleting customer (for future use)
        // This will be implemented in later labs
        return ['status' => 'error', 'message' => 'Delete functionality not yet implemented'];
    }
    
    /**
     * Get customer by email
     * @param string $email
     * @return array|null
     */
    public function getCustomerByEmail($email) {
        // Escape email for security
        $email = $this->db->db_escape_string($email);
        
        // Query to get customer by email
        $sql = "SELECT * FROM patients WHERE email = '$email'";
        
        // db_fetch_one already calls db_query internally
        return $this->db->db_fetch_one($sql);
    }
    
    /**
     * Login customer - get customer by email and verify password
     * @param array $args - Contains: email, password
     * @return array - ['status' => 'success'|'error', 'message' => string, 'customer' => array|null]
     */
    public function get($args) {
        // Validate required fields
        if (empty($args['email']) || empty($args['password'])) {
            return [
                'status' => 'error',
                'message' => 'Email and password are required.',
                'customer' => null
            ];
        }
        
        // Validate email format
        $email = trim(strtolower($args['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'error',
                'message' => 'Invalid email format.',
                'customer' => null
            ];
        }
        
        // Get customer by email
        $customer = $this->getCustomerByEmail($email);
        
        // Check if customer exists
        if (!$customer) {
            return [
                'status' => 'error',
                'message' => 'Invalid email or password.',
                'customer' => null
            ];
        }
        
        // Check if account is active
        if (isset($customer['status']) && $customer['status'] !== 'active') {
            return [
                'status' => 'error',
                'message' => 'Your account is not active. Please contact support.',
                'customer' => null
            ];
        }
        
        // Verify password
        if (!password_verify($args['password'], $customer['password_hash'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid email or password.',
                'customer' => null
            ];
        }
        
        // Update last login timestamp
        $patient_id = (int)$customer['patient_id'];
        $update_sql = "UPDATE patients SET last_login = NOW() WHERE patient_id = $patient_id";
        $this->db->db_query($update_sql);
        
        // Return success with customer data (excluding password hash)
        unset($customer['password_hash']);
        
        return [
            'status' => 'success',
            'message' => 'Login successful!',
            'customer' => $customer
        ];
    }
}
?>
