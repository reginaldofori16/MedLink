<?php
/**
 * Payment Class
 * Handles payment-related database operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Payment extends db_connection {
    
    /**
     * Record a new payment
     */
    public function record_payment(
        $prescription_id,
        $patient_id,
        $amount,
        $currency,
        $payment_method,
        $transaction_ref,
        $authorization_code = null,
        $payment_channel = null,
        $payment_status = 'success'
    ) {
        $sql = "INSERT INTO payments (
            prescription_id,
            patient_id,
            amount,
            currency,
            payment_method,
            transaction_ref,
            authorization_code,
            payment_channel,
            payment_status,
            payment_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        return $this->db_query(
            $sql,
            [
                $prescription_id,
                $patient_id,
                $amount,
                $currency,
                $payment_method,
                $transaction_ref,
                $authorization_code,
                $payment_channel,
                $payment_status
            ]
        );
    }
    
    /**
     * Get payment by transaction reference
     */
    public function get_payment_by_reference($transaction_ref) {
        $sql = "SELECT * FROM payments WHERE transaction_ref = ?";
        return $this->db_fetch_one($sql, [$transaction_ref]);
    }
    
    /**
     * Get payment by prescription ID
     */
    public function get_payment_by_prescription($prescription_id) {
        $sql = "SELECT * FROM payments WHERE prescription_id = ? ORDER BY payment_date DESC";
        return $this->db_fetch_all($sql, [$prescription_id]);
    }
    
    /**
     * Get payment by patient ID
     */
    public function get_payments_by_patient($patient_id) {
        $sql = "SELECT p.*, pr.prescription_code, h.name as hospital_name
                FROM payments p
                LEFT JOIN prescriptions pr ON p.prescription_id = pr.prescription_id
                LEFT JOIN hospitals h ON pr.hospital_id = h.hospital_id
                WHERE p.patient_id = ?
                ORDER BY p.payment_date DESC";
        return $this->db_fetch_all($sql, [$patient_id]);
    }
    
    /**
     * Update payment status
     */
    public function update_payment_status($payment_id, $status) {
        $sql = "UPDATE payments SET payment_status = ?, updated_at = NOW() WHERE payment_id = ?";
        return $this->db_query($sql, [$status, $payment_id]);
    }
    
    /**
     * Get payment statistics
     */
    public function get_payment_statistics($start_date = null, $end_date = null) {
        if ($start_date && $end_date) {
            $sql = "SELECT 
                        COUNT(*) as total_payments,
                        SUM(amount) as total_amount,
                        AVG(amount) as average_amount,
                        payment_method,
                        payment_channel,
                        payment_status
                    FROM payments
                    WHERE payment_date BETWEEN ? AND ?
                    GROUP BY payment_method, payment_channel, payment_status";
            return $this->db_fetch_all($sql, [$start_date, $end_date]);
        } else {
            $sql = "SELECT 
                        COUNT(*) as total_payments,
                        SUM(amount) as total_amount,
                        AVG(amount) as average_amount,
                        payment_method,
                        payment_channel,
                        payment_status
                    FROM payments
                    GROUP BY payment_method, payment_channel, payment_status";
            return $this->db_fetch_all($sql);
        }
    }
    
    /**
     * Verify if payment exists for prescription
     */
    public function prescription_has_payment($prescription_id) {
        $sql = "SELECT COUNT(*) as count FROM payments WHERE prescription_id = ? AND payment_status = 'success'";
        $result = $this->db_fetch_one($sql, [$prescription_id]);
        return $result && $result['count'] > 0;
    }
}

?>

