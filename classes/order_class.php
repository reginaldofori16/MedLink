<?php
/**
 * Order Class for MedLink
 * Handles all order-related database operations
 * Based on Week 9 Activity requirements
 */

require_once(__DIR__ . '/../settings/db_class.php');

class OrderClass extends db_connection {
    
    /**
     * Create a new order
     * @param array $params - Order parameters (patient_id, prescription_id, pharmacy_id, order_reference, subtotal, tax_amount, total_amount, etc.)
     * @return int|boolean - Order ID on success, false on failure
     */
    public function createOrder($params) {
        $sql = "INSERT INTO prescription_orders 
                (patient_id, prescription_id, pharmacy_id, order_reference, order_status, 
                 subtotal, tax_amount, total_amount, delivery_address, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $conn = $this->db_conn();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Order prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param(
            "iiissiddss", 
            $params['patient_id'],
            $params['prescription_id'],
            $params['pharmacy_id'],
            $params['order_reference'],
            $params['order_status'],
            $params['subtotal'],
            $params['tax_amount'],
            $params['total_amount'],
            $params['delivery_address'],
            $params['notes']
        );
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            error_log("Order created successfully with ID: " . $order_id);
            return $order_id;
        } else {
            error_log("Order execute failed: " . $stmt->error);
            return false;
        }
    }
    
    /**
     * Add order details (individual medicines)
     * @param array $params - Order detail parameters (order_id, prescription_medicine_id, medicine_name, dosage, frequency, duration, quantity, unit_price, subtotal)
     * @return boolean - true on success, false on failure
     */
    public function addOrderDetail($params) {
        $sql = "INSERT INTO order_details 
                (order_id, prescription_medicine_id, medicine_name, dosage, frequency, 
                 duration, quantity, unit_price, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $conn = $this->db_conn();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Order detail prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param(
            "iissssids", 
            $params['order_id'],
            $params['prescription_medicine_id'],
            $params['medicine_name'],
            $params['dosage'],
            $params['frequency'],
            $params['duration'],
            $params['quantity'],
            $params['unit_price'],
            $params['subtotal']
        );
        
        if ($stmt->execute()) {
            error_log("Order detail added for medicine: " . $params['medicine_name']);
            return true;
        } else {
            error_log("Order detail execute failed: " . $stmt->error);
            return false;
        }
    }
    
    /**
     * Record payment
     * @param array $params - Payment parameters (order_id, patient_id, amount, currency, payment_method, transaction_reference, authorization_code, payment_channel, card_type, payment_status, paystack_response)
     * @return int|boolean - Payment ID on success, false on failure
     */
    public function recordPayment($params) {
        $sql = "INSERT INTO prescription_payment 
                (order_id, patient_id, amount, currency, payment_method, 
                 transaction_reference, authorization_code, payment_channel, 
                 card_type, payment_status, paystack_response) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $conn = $this->db_conn();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Payment prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param(
            "iidssssssss", 
            $params['order_id'],
            $params['patient_id'],
            $params['amount'],
            $params['currency'],
            $params['payment_method'],
            $params['transaction_reference'],
            $params['authorization_code'],
            $params['payment_channel'],
            $params['card_type'],
            $params['payment_status'],
            $params['paystack_response']
        );
        
        if ($stmt->execute()) {
            $payment_id = $conn->insert_id;
            error_log("Payment recorded successfully with ID: " . $payment_id);
            return $payment_id;
        } else {
            error_log("Payment execute failed: " . $stmt->error);
            return false;
        }
    }
    
    /**
     * Get all orders for a patient
     * @param int $patient_id - Patient ID
     * @return array - Array of orders
     */
    public function getPatientOrders($patient_id) {
        $sql = "SELECT 
                    po.*,
                    p.prescription_code,
                    ph.name as pharmacy_name,
                    pay.payment_status,
                    pay.payment_method,
                    pay.payment_channel
                FROM prescription_orders po
                INNER JOIN prescriptions p ON po.prescription_id = p.prescription_id
                LEFT JOIN pharmacies ph ON po.pharmacy_id = ph.pharmacy_id
                LEFT JOIN prescription_payment pay ON po.order_id = pay.order_id
                WHERE po.patient_id = ?
                ORDER BY po.order_date DESC";
        
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    /**
     * Get order by ID
     * @param int $order_id - Order ID
     * @return array|boolean - Order details or false
     */
    public function getOrderById($order_id) {
        $sql = "SELECT 
                    po.*,
                    p.prescription_code,
                    ph.name as pharmacy_name,
                    pat.full_name as patient_name,
                    pat.email as patient_email,
                    pay.payment_status,
                    pay.transaction_reference
                FROM prescription_orders po
                INNER JOIN prescriptions p ON po.prescription_id = p.prescription_id
                INNER JOIN patients pat ON po.patient_id = pat.patient_id
                LEFT JOIN pharmacies ph ON po.pharmacy_id = ph.pharmacy_id
                LEFT JOIN prescription_payment pay ON po.order_id = pay.order_id
                WHERE po.order_id = ?";
        
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Get order details (medicines) for an order
     * @param int $order_id - Order ID
     * @return array - Array of order details
     */
    public function getOrderDetails($order_id) {
        $sql = "SELECT * FROM order_details WHERE order_id = ? ORDER BY order_detail_id";
        
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        
        return $details;
    }
    
    /**
     * Update order status
     * @param int $order_id - Order ID
     * @param string $status - New status
     * @return boolean - true on success, false on failure
     */
    public function updateOrderStatus($order_id, $status) {
        $sql = "UPDATE prescription_orders SET order_status = ? WHERE order_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);
        return $stmt->execute();
    }
    
    /**
     * Generate unique order reference
     * @return string - Unique order reference
     */
    public function generateOrderReference() {
        $prefix = "ORD";
        $year = date('Y');
        
        // Get last order reference for this year
        $sql = "SELECT order_reference FROM prescription_orders 
                WHERE order_reference LIKE ? 
                ORDER BY order_id DESC LIMIT 1";
        
        $pattern = "{$prefix}-{$year}-%";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Extract number from last reference (e.g., ORD-2025-014 → 14)
            preg_match('/(\d+)$/', $row['order_reference'], $matches);
            $next_number = intval($matches[1]) + 1;
        } else {
            $next_number = 1;
        }
        
        // Format: ORD-2025-001
        return sprintf("%s-%s-%03d", $prefix, $year, $next_number);
    }
}

?>

