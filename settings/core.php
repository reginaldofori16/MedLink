<?php
/**
 * Core Functions for MedLink
 * Handles session management and authentication checks
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 * Checks if a session has been created with user information
 * @return boolean - true if user is logged in, false otherwise
 */
function is_logged_in() {
    // Check if session is active
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }
    
    // Check for patient/individual session
    if (isset($_SESSION['patient_id']) || isset($_SESSION['user_id'])) {
        return true;
    }
    
    // Check for hospital session
    if (isset($_SESSION['hospital_id'])) {
        return true;
    }
    
    // Check for pharmacy session
    if (isset($_SESSION['pharmacy_id'])) {
        return true;
    }
    
    // Check for admin session
    if (isset($_SESSION['admin_id'])) {
        return true;
    }
    
    return false;
}

/**
 * Check if a user has administrative privileges
 * Checks the user's role in the session array
 * @return boolean - true if user has admin privileges, false otherwise
 */
function is_admin() {
    // Check if user is logged in first
    if (!is_logged_in()) {
        return false;
    }
    
    // Check for patient/individual with admin role (user_role = 1)
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
        return true;
    }
    
    // Check for patient_id with user_role
    if (isset($_SESSION['patient_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
        return true;
    }
    
    // Check for admin session
    if (isset($_SESSION['admin_id'])) {
        return true;
    }
    
    // Check if admin role is explicitly set in session
    if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin')) {
        return true;
    }
    
    return false;
}

/**
 * Get current user type
 * Returns the type of user currently logged in
 * @return string|false - 'patient', 'hospital', 'pharmacy', 'admin', or false if not logged in
 */
function get_user_type() {
    if (!is_logged_in()) {
        return false;
    }
    
    // Check for dedicated admin session
    if (isset($_SESSION['admin_id'])) {
        return 'admin';
    }
    
    // Check for hospital FIRST (before checking user_id/user_role, since hospitals also set user_id)
    if (isset($_SESSION['hospital_id'])) {
        return 'hospital';
    }
    
    // Check for pharmacy FIRST (before checking user_id/user_role, since pharmacies also set user_id)
    if (isset($_SESSION['pharmacy_id'])) {
        return 'pharmacy';
    }
    
    // Check if patient has admin role (user_role = 1) - only for actual patients, not hospitals/pharmacies
    if ((isset($_SESSION['patient_id']) || isset($_SESSION['user_id'])) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1) {
        return 'admin';
    }
    
    // Check for regular patient (only if no hospital_id or pharmacy_id exists)
    if (isset($_SESSION['patient_id']) || isset($_SESSION['user_id'])) {
        return 'patient';
    }
    
    return false;
}

/**
 * Get current user ID
 * Returns the ID of the currently logged in user
 * @return int|false - User ID or false if not logged in
 */
function get_user_id() {
    if (!is_logged_in()) {
        return false;
    }
    
    if (isset($_SESSION['admin_id'])) {
        return $_SESSION['admin_id'];
    }
    
    if (isset($_SESSION['patient_id'])) {
        return $_SESSION['patient_id'];
    }
    
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    if (isset($_SESSION['hospital_id'])) {
        return $_SESSION['hospital_id'];
    }
    
    if (isset($_SESSION['pharmacy_id'])) {
        return $_SESSION['pharmacy_id'];
    }
    
    return false;
}

/**
 * Require login
 * Redirects to login page if user is not logged in
 * @param string $redirect_url - URL to redirect to (default: login.php)
 */
function require_login($redirect_url = '../view/login.php') {
    if (!is_logged_in()) {
        header('Location: ' . $redirect_url);
        exit();
    }
}

/**
 * Require admin
 * Redirects to appropriate page if user is not an admin
 * @param string $redirect_url - URL to redirect to (default: index.php)
 */
function require_admin($redirect_url = '../index.php') {
    if (!is_admin()) {
        header('Location: ' . $redirect_url);
        exit();
    }
}

/**
 * Redirect if logged in
 * Redirects logged-in users to their appropriate dashboard
 * Useful for registration and login pages
 */
function redirect_if_logged_in() {
    if (is_logged_in()) {
        $userType = get_user_type();
        // Check admin first (since admin can also have patient_id)
        if ($userType === 'admin') {
            header('Location: admin.php');
        } elseif ($userType === 'patient') {
            header('Location: patients.php');
        } elseif ($userType === 'hospital') {
            header('Location: hospital.php');
        } elseif ($userType === 'pharmacy') {
            header('Location: pharmacy.php');
        } else {
            header('Location: ../index.php');
        }
        exit();
    }
}

/**
 * Get redirect URL for logged-in user
 * Returns the appropriate dashboard URL based on user type
 * @return string - Dashboard URL
 */
function get_dashboard_url() {
    $userType = get_user_type();
    // Check admin first (since admin can also have patient_id)
    if ($userType === 'admin') {
        return 'admin.php';
    } elseif ($userType === 'patient') {
        return 'patients.php';
    } elseif ($userType === 'hospital') {
        return 'hospital.php';
    } elseif ($userType === 'pharmacy') {
        return 'pharmacy.php';
    }
    return '../index.php';
}

?>

