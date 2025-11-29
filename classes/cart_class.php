<?php
/**
 * Cart Class for MedLink
 * Handles all cart-related database operations
 * Based on Week 9 Activity requirements
 */

require_once(__DIR__ . '/../settings/db_class.php');

class CartClass extends db_connection {
    
    /**
     * Add a product (medicine) to cart
     * If already exists, increment quantity instead of duplicating
     * @param int $patient_id - Patient ID
     * @param int $prescription_id - Prescription ID
     * @param int $prescription_medicine_id - Medicine ID
     * @param int $quantity - Quantity to add
     * @param float $price - Price per unit
     * @return boolean - true on success, false on failure
     */
    public function addToCart($patient_id, $prescription_id, $prescription_medicine_id, $quantity = 1, $price) {
        // First check if item already exists in cart
        $check_sql = "SELECT cart_id, quantity FROM cart 
                      WHERE patient_id = ? 
                      AND prescription_id = ? 
                      AND prescription_medicine_id = ?";
        
        $stmt = $this->db_conn()->prepare($check_sql);
        $stmt->bind_param("iii", $patient_id, $prescription_id, $prescription_medicine_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Item exists - update quantity
            $row = $result->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;
            
            $update_sql = "UPDATE cart 
                          SET quantity = ?, price = ?
                          WHERE cart_id = ?";
            
            $update_stmt = $this->db_conn()->prepare($update_sql);
            $update_stmt->bind_param("idi", $new_quantity, $price, $row['cart_id']);
            return $update_stmt->execute();
        } else {
            // Item doesn't exist - insert new
            $insert_sql = "INSERT INTO cart 
                          (patient_id, prescription_id, prescription_medicine_id, quantity, price) 
                          VALUES (?, ?, ?, ?, ?)";
            
            $insert_stmt = $this->db_conn()->prepare($insert_sql);
            $insert_stmt->bind_param("iiiid", $patient_id, $prescription_id, $prescription_medicine_id, $quantity, $price);
            return $insert_stmt->execute();
        }
    }
    
    /**
     * Update quantity of item in cart
     * @param int $cart_id - Cart item ID
     * @param int $quantity - New quantity
     * @return boolean - true on success, false on failure
     */
    public function updateCartQuantity($cart_id, $quantity) {
        $sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $quantity, $cart_id);
        return $stmt->execute();
    }
    
    /**
     * Remove an item from cart
     * @param int $cart_id - Cart item ID
     * @return boolean - true on success, false on failure
     */
    public function removeFromCart($cart_id) {
        $sql = "DELETE FROM cart WHERE cart_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $cart_id);
        return $stmt->execute();
    }
    
    /**
     * Get all cart items for a user
     * @param int $patient_id - Patient ID
     * @return array - Array of cart items with medicine details
     */
    public function getUserCart($patient_id) {
        $sql = "SELECT 
                    c.cart_id,
                    c.prescription_id,
                    c.prescription_medicine_id,
                    c.quantity,
                    c.price,
                    c.added_date,
                    pm.medicine_name,
                    pm.dosage,
                    pm.frequency,
                    pm.duration,
                    p.prescription_code,
                    p.pharmacy_id,
                    ph.name as pharmacy_name,
                    h.name as hospital_name
                FROM cart c
                INNER JOIN prescription_medicines pm ON c.prescription_medicine_id = pm.prescription_medicine_id
                INNER JOIN prescriptions p ON c.prescription_id = p.prescription_id
                LEFT JOIN pharmacies ph ON p.pharmacy_id = ph.pharmacy_id
                LEFT JOIN hospitals h ON p.hospital_id = h.hospital_id
                WHERE c.patient_id = ?
                ORDER BY c.added_date DESC";
        
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cart_items = [];
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
        }
        
        return $cart_items;
    }
    
    /**
     * Empty entire cart for a user
     * @param int $patient_id - Patient ID
     * @return boolean - true on success, false on failure
     */
    public function emptyCart($patient_id) {
        $sql = "DELETE FROM cart WHERE patient_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        return $stmt->execute();
    }
    
    /**
     * Check if a product already exists in cart
     * @param int $patient_id - Patient ID
     * @param int $prescription_id - Prescription ID
     * @param int $prescription_medicine_id - Medicine ID
     * @return array|boolean - Cart item array if exists, false if not
     */
    public function checkProductInCart($patient_id, $prescription_id, $prescription_medicine_id) {
        $sql = "SELECT * FROM cart 
                WHERE patient_id = ? 
                AND prescription_id = ? 
                AND prescription_medicine_id = ?";
        
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("iii", $patient_id, $prescription_id, $prescription_medicine_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Get cart count for a patient
     * @param int $patient_id - Patient ID
     * @return int - Number of items in cart
     */
    public function getCartCount($patient_id) {
        $sql = "SELECT COUNT(*) as count FROM cart WHERE patient_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }
    
    /**
     * Get cart total for a patient
     * @param int $patient_id - Patient ID
     * @return float - Total cart value
     */
    public function getCartTotal($patient_id) {
        $sql = "SELECT SUM(quantity * price) as total FROM cart WHERE patient_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (float)$row['total'];
    }
}

?>

