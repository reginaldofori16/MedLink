<?php
/**
 * Pharmacy Controller
 * Creates an instance of the pharmacy class and runs the methods
 */

require_once __DIR__ . '/../classes/pharmacy_class.php';

class PharmacyController {
    private $pharmacyClass;
    
    public function __construct() {
        $this->pharmacyClass = new PharmacyClass();
    }
    
    /**
     * Register pharmacy controller method
     * @param array $kwargs - Form data from registration form
     * @return array - Response with status and message
     */
    public function register_pharmacy_ctr($kwargs) {
        // Validate and sanitize input data
        $data = [
            'name' => trim($kwargs['name'] ?? ''),
            'government_id' => trim($kwargs['government_id'] ?? ''),
            'contact' => trim($kwargs['contact'] ?? ''),
            'location' => trim($kwargs['location'] ?? ''),
            'password' => $kwargs['password'] ?? ''
        ];
        
        // Call the pharmacy class add method
        return $this->pharmacyClass->add($data);
    }
    
    /**
     * Edit pharmacy controller method (for future use)
     * @param array $kwargs
     * @return array
     */
    public function edit_pharmacy_ctr($kwargs) {
        return $this->pharmacyClass->edit($kwargs);
    }
    
    /**
     * Delete pharmacy controller method (for future use)
     * @param int $pharmacy_id
     * @return array
     */
    public function delete_pharmacy_ctr($pharmacy_id) {
        return $this->pharmacyClass->delete($pharmacy_id);
    }
    
    /**
     * Login pharmacy controller method
     * @param array $kwargs - Form data from login form (government_id, password)
     * @return array - Response with status, message, and pharmacy data
     */
    public function login_pharmacy_ctr($kwargs) {
        // Validate and sanitize input data
        $data = [
            'government_id' => trim($kwargs['government_id'] ?? ''),
            'password' => $kwargs['password'] ?? ''
        ];
        
        // Call the pharmacy class get method (login method)
        return $this->pharmacyClass->get($data);
    }
}
?>

