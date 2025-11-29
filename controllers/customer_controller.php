<?php
/**
 * Customer Controller
 * Creates an instance of the customer class and runs the methods
 */

require_once __DIR__ . '/../classes/customer_class.php';

class CustomerController {
    private $customerClass;
    
    public function __construct() {
        $this->customerClass = new CustomerClass();
    }
    
    /**
     * Register customer controller method
     * @param array $kwargs - Form data from registration form
     * @return array - Response with status and message
     */
    public function register_customer_ctr($kwargs) {
        // Validate and sanitize input data
        $data = [
            'full_name' => trim($kwargs['full_name'] ?? ''),
            'email' => trim(strtolower($kwargs['email'] ?? '')),
            'phone' => trim($kwargs['phone'] ?? ''),
            'password' => $kwargs['password'] ?? '',
            'country' => trim($kwargs['country'] ?? ''),
            'city' => trim($kwargs['city'] ?? ''),
            'user_role' => isset($kwargs['user_role']) ? (int)$kwargs['user_role'] : 2
        ];
        
        // Call the customer class add method
        return $this->customerClass->add($data);
    }
    
    /**
     * Edit customer controller method (for future use)
     * @param array $kwargs
     * @return array
     */
    public function edit_customer_ctr($kwargs) {
        return $this->customerClass->edit($kwargs);
    }
    
    /**
     * Delete customer controller method (for future use)
     * @param int $patient_id
     * @return array
     */
    public function delete_customer_ctr($patient_id) {
        return $this->customerClass->delete($patient_id);
    }
    
    /**
     * Login customer controller method
     * @param array $kwargs - Form data from login form (email, password)
     * @return array - Response with status, message, and customer data
     */
    public function login_customer_ctr($kwargs) {
        // Validate and sanitize input data
        $data = [
            'email' => trim(strtolower($kwargs['email'] ?? '')),
            'password' => $kwargs['password'] ?? ''
        ];
        
        // Call the customer class get method (login method)
        return $this->customerClass->get($data);
    }
}
?>

