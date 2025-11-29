<?php
/**
 * Order Controller for MedLink
 * Wraps order_class methods for use by action scripts
 * Based on Week 9 Activity requirements
 */

require_once(__DIR__ . '/../classes/order_class.php');

/**
 * Create a new order
 * @param array $params - Order parameters
 * @return int|boolean - Order ID on success, false on failure
 */
function create_order_ctr($params) {
    $order = new OrderClass();
    return $order->createOrder($params);
}

/**
 * Add order detail
 * @param array $params - Order detail parameters
 * @return boolean - true on success, false on failure
 */
function add_order_detail_ctr($params) {
    $order = new OrderClass();
    return $order->addOrderDetail($params);
}

/**
 * Record payment
 * @param array $params - Payment parameters
 * @return int|boolean - Payment ID on success, false on failure
 */
function record_payment_ctr($params) {
    $order = new OrderClass();
    return $order->recordPayment($params);
}

/**
 * Get patient orders
 * @param int $patient_id - Patient ID
 * @return array - Array of orders
 */
function get_patient_orders_ctr($patient_id) {
    $order = new OrderClass();
    return $order->getPatientOrders($patient_id);
}

/**
 * Get order by ID
 * @param int $order_id - Order ID
 * @return array|boolean - Order details or false
 */
function get_order_by_id_ctr($order_id) {
    $order = new OrderClass();
    return $order->getOrderById($order_id);
}

/**
 * Get order details
 * @param int $order_id - Order ID
 * @return array - Array of order details
 */
function get_order_details_ctr($order_id) {
    $order = new OrderClass();
    return $order->getOrderDetails($order_id);
}

/**
 * Update order status
 * @param int $order_id - Order ID
 * @param string $status - New status
 * @return boolean - true on success, false on failure
 */
function update_order_status_ctr($order_id, $status) {
    $order = new OrderClass();
    return $order->updateOrderStatus($order_id, $status);
}

/**
 * Generate order reference
 * @return string - Unique order reference
 */
function generate_order_reference_ctr() {
    $order = new OrderClass();
    return $order->generateOrderReference();
}

?>

