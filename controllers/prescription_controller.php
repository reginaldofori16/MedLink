<?php
/**
 * Prescription Controller
 * Creates an instance of the prescription class and runs the methods
 */

require_once __DIR__ . '/../classes/prescription_class.php';

class PrescriptionController {
    private $prescriptionClass;
    
    public function __construct() {
        $this->prescriptionClass = new PrescriptionClass();
    }
    
    /**
     * Submit prescription controller method
     * @param array $kwargs - Form data from prescription submission form
     * @return array - Response with status and message
     */
    public function submit_prescription_ctr($kwargs) {
        // Call the prescription class add method
        return $this->prescriptionClass->add($kwargs);
    }
    
    /**
     * Get prescription by ID controller method
     * @param int $prescriptionId
     * @return array
     */
    public function get_prescription_ctr($prescriptionId) {
        $prescription = $this->prescriptionClass->getPrescriptionById($prescriptionId);
        
        if ($prescription) {
            return [
                'status' => 'success',
                'prescription' => $prescription
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Prescription not found.'
            ];
        }
    }
}

?>

