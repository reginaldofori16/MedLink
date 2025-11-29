<?php
/**
 * Payment Controller
 * Handles payment operations
 */

require_once(__DIR__ . '/../classes/payment_class.php');

/**
 * Record a payment
 */
function record_payment_ctr(
    $prescription_id,
    $patient_id,
    $amount,
    $currency = 'GHS',
    $payment_method = 'paystack',
    $transaction_ref,
    $authorization_code = null,
    $payment_channel = null,
    $payment_status = 'success'
) {
    $payment = new Payment();
    return $payment->record_payment(
        $prescription_id,
        $patient_id,
        $amount,
        $currency,
        $payment_method,
        $transaction_ref,
        $authorization_code,
        $payment_channel,
        $payment_status
    );
}

/**
 * Get payment by transaction reference
 */
function get_payment_by_reference_ctr($transaction_ref) {
    $payment = new Payment();
    return $payment->get_payment_by_reference($transaction_ref);
}

/**
 * Get payment by prescription ID
 */
function get_payment_by_prescription_ctr($prescription_id) {
    $payment = new Payment();
    return $payment->get_payment_by_prescription($prescription_id);
}

/**
 * Get all payments by patient
 */
function get_payments_by_patient_ctr($patient_id) {
    $payment = new Payment();
    return $payment->get_payments_by_patient($patient_id);
}

/**
 * Update payment status
 */
function update_payment_status_ctr($payment_id, $status) {
    $payment = new Payment();
    return $payment->update_payment_status($payment_id, $status);
}

/**
 * Get payment statistics
 */
function get_payment_statistics_ctr($start_date = null, $end_date = null) {
    $payment = new Payment();
    return $payment->get_payment_statistics($start_date, $end_date);
}

/**
 * Check if prescription has successful payment
 */
function prescription_has_payment_ctr($prescription_id) {
    $payment = new Payment();
    return $payment->prescription_has_payment($prescription_id);
}

?>

