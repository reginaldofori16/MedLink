<?php
/**
 * Paystack Payment Gateway Configuration
 * MedLink - Medical Prescription Platform
 */

// Prevent direct access
if (!defined('PAYSTACK_CONFIG_LOADED')) {
    define('PAYSTACK_CONFIG_LOADED', true);
}

// ==================================
// PAYSTACK API CONFIGURATION
// ==================================

// Environment - TEST MODE ONLY
define('PAYSTACK_ENVIRONMENT', 'test');

// API Keys - Your Paystack Test Keys
// Get your keys from: https://dashboard.paystack.com/#/settings/developers
define('PAYSTACK_SECRET_KEY', 'sk_test_7a6fda86900aa7cf94f87f0825545be4ea033764');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_49d377f947b3f662032919e505a249bca128aeae');

// Get API keys
function paystack_get_secret_key() {
    return PAYSTACK_SECRET_KEY;
}

function paystack_get_public_key() {
    return PAYSTACK_PUBLIC_KEY;
}

// ==================================
// APPLICATION CONFIGURATION
// ==================================

// Base URL - dynamically detected from current request (NO localhost fallback)
function get_app_base_url() {
    // Determine protocol
    $protocol = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $protocol = 'https';
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = 'https';
    } elseif (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        $protocol = 'https';
    }
    
    // Get host - MUST come from the actual request
    $host = '';
    if (!empty($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } elseif (!empty($_SERVER['SERVER_NAME'])) {
        $host = $_SERVER['SERVER_NAME'];
        // Add port if non-standard
        if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            $host .= ':' . $_SERVER['SERVER_PORT'];
        }
    }
    
    // Get the current script path from multiple possible sources
    $script_path = '';
    if (!empty($_SERVER['SCRIPT_NAME'])) {
        $script_path = $_SERVER['SCRIPT_NAME'];
    } elseif (!empty($_SERVER['PHP_SELF'])) {
        $script_path = $_SERVER['PHP_SELF'];
    } elseif (!empty($_SERVER['REQUEST_URI'])) {
        $script_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
    
    // Find 'MedLink' in the path and extract everything up to and including it
    $base_path = '';
    $medlink_pos = strpos($script_path, '/MedLink');
    if ($medlink_pos !== false) {
        $base_path = substr($script_path, 0, $medlink_pos + 8); // 8 = strlen('/MedLink')
    }
    
    return $protocol . '://' . $host . $base_path;
}

// Set the base URL
define('APP_BASE_URL', get_app_base_url());

// Callback URL (where Paystack redirects after payment)
define('PAYSTACK_CALLBACK_URL', APP_BASE_URL . '/view/paystack_callback.php');

// Currency
define('PAYSTACK_CURRENCY', 'GHS'); // Ghana Cedis

// ==================================
// PAYSTACK API ENDPOINTS
// ==================================

define('PAYSTACK_API_BASE', 'https://api.paystack.co');
define('PAYSTACK_INITIALIZE_URL', PAYSTACK_API_BASE . '/transaction/initialize');
define('PAYSTACK_VERIFY_URL', PAYSTACK_API_BASE . '/transaction/verify');

// ==================================
// HELPER FUNCTIONS
// ==================================

/**
 * Convert amount to kobo/pesewas (Paystack uses smallest currency unit)
 * @param float $amount Amount in cedis
 * @return int Amount in pesewas
 */
function paystack_amount_to_pesewas($amount) {
    return (int) ($amount * 100);
}

/**
 * Convert pesewas back to cedis
 * @param int $pesewas Amount in pesewas
 * @return float Amount in cedis
 */
function paystack_pesewas_to_amount($pesewas) {
    return $pesewas / 100;
}

/**
 * Generate unique transaction reference
 * @param int $patient_id Patient ID
 * @return string Transaction reference
 */
function paystack_generate_reference($patient_id) {
    return 'MEDLINK-' . $patient_id . '-' . time();
}

/**
 * Initialize a Paystack transaction
 * @param string $email Customer email
 * @param float $amount Amount in cedis
 * @param string $reference Transaction reference
 * @param array $metadata Additional data to send
 * @return array Response from Paystack
 */
function paystack_initialize_transaction($email, $amount, $reference, $metadata = []) {
    $amount_pesewas = paystack_amount_to_pesewas($amount);
    
    $data = [
        'email' => $email,
        'amount' => $amount_pesewas,
        'currency' => PAYSTACK_CURRENCY,
        'reference' => $reference,
        'callback_url' => PAYSTACK_CALLBACK_URL,
        'metadata' => $metadata
    ];
    
    $response = paystack_make_request(PAYSTACK_INITIALIZE_URL, $data);
    return $response;
}

/**
 * Verify a Paystack transaction
 * @param string $reference Transaction reference
 * @return array Response from Paystack
 */
function paystack_verify_transaction($reference) {
    $url = PAYSTACK_VERIFY_URL . '/' . $reference;
    $response = paystack_make_request($url, null, 'GET');
    return $response;
}

/**
 * Make HTTP request to Paystack API
 * @param string $url API endpoint
 * @param array $data POST data (null for GET)
 * @param string $method HTTP method
 * @return array Response data
 */
function paystack_make_request($url, $data = null, $method = 'POST') {
    $ch = curl_init();
    
    $headers = [
        'Authorization: Bearer ' . paystack_get_secret_key(),
        'Content-Type: application/json',
        'Cache-Control: no-cache'
    ];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST' && $data !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // Log error if request failed
    if ($error) {
        error_log("Paystack API Error: " . $error);
        return [
            'status' => false,
            'message' => 'Connection error: ' . $error
        ];
    }
    
    // Decode response
    $result = json_decode($response, true);
    
    if ($httpcode !== 200) {
        error_log("Paystack API HTTP Error: " . $httpcode . " - " . $response);
    }
    
    return $result;
}

/**
 * Validate Paystack webhook signature
 * @param string $input Raw POST data
 * @return bool True if valid
 */
function paystack_validate_webhook($input) {
    if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
        return false;
    }
    
    $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'];
    $hash = hash_hmac('sha512', $input, paystack_get_secret_key());
    
    return hash_equals($hash, $signature);
}

/**
 * Format amount for display
 * @param float $amount Amount in cedis
 * @return string Formatted amount
 */
function paystack_format_amount($amount) {
    return 'GHS ' . number_format($amount, 2);
}

/**
 * Log transaction details (for debugging)
 * @param string $message Log message
 * @param array $data Additional data
 */
function paystack_log($message, $data = []) {
    $log_file = __DIR__ . '/../logs/paystack.log';
    $log_dir = dirname($log_file);
    
    if (!file_exists($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}";
    
    if (!empty($data)) {
        $log_entry .= " | Data: " . json_encode($data);
    }
    
    $log_entry .= PHP_EOL;
    
    @file_put_contents($log_file, $log_entry, FILE_APPEND);
}

?>

