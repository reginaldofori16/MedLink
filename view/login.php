<?php
/**
 * Login Page
 * Includes core functions for session management
 */
require_once __DIR__ . '/../settings/core.php';

// Redirect if user is already logged in
redirect_if_logged_in();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MedLink</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <a href="../index.php" class="back-home-btn" style="position:fixed;top:32px;right:52px;z-index:22;text-decoration:none;font-size:1.02rem;font-weight:500;color:#0574f7;background:rgba(240,242,247,0.9);padding:7px 18px;border-radius:18px;box-shadow:0 3px 10px #c6dbf333;transition:background .18s;color:#0574f7;"> <span style="font-size:1.15em;margin-right:5px;">&#8592;</span> Back to Home</a>
    <div class="login-container fade-in">
        <img src="../assets/apple-medlogo.svg" alt="MedLink Logo" class="apple-logo" style="display:block;margin:0 auto 10px auto;width:52px;height:52px;filter:drop-shadow(0 2px 14px #0574f724);">
        <h2>Sign In</h2>
        <div id="login-step-1" class="step-box">
            <label>Select your user type:</label>
            <div class="role-options">
                <label><input type="radio" name="loginrole" value="hospital"> Hospital</label>
                <label><input type="radio" name="loginrole" value="pharmacy"> Pharmacy</label>
                <label><input type="radio" name="loginrole" value="individual"> Individual</label>
            </div>
        </div>
        <form id="login-form" style="display:none;">
            <label id="login-username-label">Email:</label>
            <input type="text" name="username" id="login-username-input" required placeholder="your.email@example.com">
            <label>Password:</label>
            <input type="password" name="password" required placeholder="Enter your password">
            <button type="submit" class="btn primary login-btn">Login</button>
        </form>
        <div style="text-align:center; margin-top:26px;">
            Don't have an account? <a href="register.php" style="color:#0574f7; font-weight:600; text-decoration:none;">Sign Up</a>
        </div>
        <div class="thankyou-box" id="login-msg" style="display:none;"></div>
    </div>
    <script src="../js/login.js"></script>
    <script>
    // Show correct form based on role selection
    document.querySelectorAll('input[name="loginrole"]').forEach(function(radio) {
      radio.addEventListener('change', function() {
        document.getElementById('login-form').reset();
        document.getElementById('login-form').style.display = 'block';
        document.getElementById('login-msg').style.display = 'none';
        var label = document.getElementById('login-username-label');
        var input = document.getElementById('login-username-input');
        if(this.value === 'hospital') {
          label.textContent = 'Government Issued Hospital ID:';
          input.type = 'text';
          input.placeholder = 'Enter Government Hospital ID';
        } else if(this.value === 'pharmacy') {
          label.textContent = 'Government Issued Pharmacy ID:';
          input.type = 'text';
          input.placeholder = 'Enter Government Pharmacy ID';
        } else {
          label.textContent = 'Email:';
          input.type = 'email';
          input.placeholder = 'your.email@example.com';
        }
      });
    });
    </script>
    <style>
    /* Error styling for form validation */
    input.error {
        border-color: #DC2626 !important;
        background: #FEF2F2 !important;
    }
    .error-message {
        color: #DC2626;
        font-size: 0.875rem;
        margin-top: 4px;
        margin-bottom: 8px;
    }
    .loading-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
        margin-right: 8px;
        vertical-align: middle;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    </style>
</body>
</html>
