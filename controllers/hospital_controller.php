<?php
/**
 * Hospital Controller
 * Creates an instance of the hospital class and runs the methods
 */

require_once __DIR__ . '/../classes/hospital_class.php';

class HospitalController {
    private $hospitalClass;
    
    public function __construct() {
        $this->hospitalClass = new HospitalClass();
    }
    
    /**
     * Register hospital controller method
     * @param array $kwargs - Form data from registration form
     * @return array - Response with status and message
     */
    public function register_hospital_ctr($kwargs) {
        // Validate and sanitize input data
        $data = [
            'name' => trim($kwargs['name'] ?? ''),
            'government_id' => trim($kwargs['government_id'] ?? ''),
            'contact' => trim($kwargs['contact'] ?? ''),
            'password' => $kwargs['password'] ?? ''
        ];
        
        // Call the hospital class add method
        return $this->hospitalClass->add($data);
    }
    
    /**
     * Edit hospital controller method (for future use)
     * @param array $kwargs
     * @return array
     */
    public function edit_hospital_ctr($kwargs) {
        return $this->hospitalClass->edit($kwargs);
    }
    
    /**
     * Delete hospital controller method (for future use)
     * @param int $hospital_id
     * @return array
     */
    public function delete_hospital_ctr($hospital_id) {
        return $this->hospitalClass->delete($hospital_id);
    }
    
    /**
     * Login hospital controller method
     * @param array $kwargs - Form data from login form (government_id, password)
     * @return array - Response with status, message, and hospital data
     */
    public function login_hospital_ctr($kwargs) {
        // Validate and sanitize input data
        $data = [
            'government_id' => trim($kwargs['government_id'] ?? ''),
            'password' => $kwargs['password'] ?? ''
        ];
        
        // Call the hospital class get method (login method)
        return $this->hospitalClass->get($data);
    }
}
?>

