<?php
/**
 * Logout Action
 * Destroys the session and redirects to index page
 */

// Include core functions (starts session automatically)
require_once __DIR__ . '/../settings/core.php';

// Destroy all session data
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to index page
header('Location: ../index.php');
exit();
?>

