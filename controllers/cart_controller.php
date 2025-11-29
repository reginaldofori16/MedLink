<?php
/**
 * Cart Controller for MedLink
 * Wraps cart_class methods for use by action scripts
 * Based on Week 9 Activity requirements
 */

require_once(__DIR__ . '/../classes/cart_class.php');

/**
 * Add item to cart
 * @param int $patient_id - Patient ID
 * @param int $prescription_id - Prescription ID
 * @param int $prescription_medicine_id - Medicine ID
 * @param int $quantity - Quantity
 * @param float $price - Price per unit
 * @return boolean - true on success, false on failure
 */
function add_to_cart_ctr($patient_id, $prescription_id, $prescription_medicine_id, $quantity, $price) {
    $cart = new CartClass();
    return $cart->addToCart($patient_id, $prescription_id, $prescription_medicine_id, $quantity, $price);
}

/**
 * Update cart item quantity
 * @param int $cart_id - Cart item ID
 * @param int $quantity - New quantity
 * @return boolean - true on success, false on failure
 */
function update_cart_item_ctr($cart_id, $quantity) {
    $cart = new CartClass();
    return $cart->updateCartQuantity($cart_id, $quantity);
}

/**
 * Remove item from cart
 * @param int $cart_id - Cart item ID
 * @return boolean - true on success, false on failure
 */
function remove_from_cart_ctr($cart_id) {
    $cart = new CartClass();
    return $cart->removeFromCart($cart_id);
}

/**
 * Get user's cart
 * @param int $patient_id - Patient ID
 * @return array - Array of cart items
 */
function get_user_cart_ctr($patient_id) {
    $cart = new CartClass();
    return $cart->getUserCart($patient_id);
}

/**
 * Empty user's cart
 * @param int $patient_id - Patient ID
 * @return boolean - true on success, false on failure
 */
function empty_cart_ctr($patient_id) {
    $cart = new CartClass();
    return $cart->emptyCart($patient_id);
}

/**
 * Check if product is in cart
 * @param int $patient_id - Patient ID
 * @param int $prescription_id - Prescription ID
 * @param int $prescription_medicine_id - Medicine ID
 * @return array|boolean - Cart item if exists, false otherwise
 */
function check_product_in_cart_ctr($patient_id, $prescription_id, $prescription_medicine_id) {
    $cart = new CartClass();
    return $cart->checkProductInCart($patient_id, $prescription_id, $prescription_medicine_id);
}

/**
 * Get cart count
 * @param int $patient_id - Patient ID
 * @return int - Number of items in cart
 */
function get_cart_count_ctr($patient_id) {
    $cart = new CartClass();
    return $cart->getCartCount($patient_id);
}

/**
 * Get cart total
 * @param int $patient_id - Patient ID
 * @return float - Total cart value
 */
function get_cart_total_ctr($patient_id) {
    $cart = new CartClass();
    return $cart->getCartTotal($patient_id);
}

?>

