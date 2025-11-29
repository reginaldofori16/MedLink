<?php
/**
 * Registration Page
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
    <title>Sign Up - MedLink</title>
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
    <a href="../index.php" class="back-home-btn" style="position:fixed;top:32px;right:52px;z-index:22;text-decoration:none;font-size:1.02rem;font-weight:500;color:#0574f7;background:rgba(240,242,247,0.9);padding:7px 18px;border-radius:18px;box-shadow:0 3px 10px #c6dbf333;transition:background .18s;color:#0574f7;"> <span style="font-size:1.15em;margin-right:5px;">&#8592;</span> Back to Home</a>
    <div class="register-container fade-in">
        <img src="../assets/apple-medlogo.svg" alt="MedLink Logo" class="apple-logo" style="display:block;margin:0 auto 10px auto;width:52px;height:52px;filter:drop-shadow(0 2px 14px #0574f724);">
        <h2>Sign Up</h2>
        <div id="signup-step-1" class="step-box">
            <label style="margin-bottom:14px;">Please select your registration type:</label>
            <div class="role-options">
                <label><input type="radio" name="role" value="hospital"> Hospital</label>
                <label><input type="radio" name="role" value="pharmacy"> Pharmacy</label>
                <label><input type="radio" name="role" value="individual"> Individual</label>
            </div>
        </div>
        <form id="hospital-form" style="display:none;">
            <label>Hospital Name:</label>
            <input type="text" name="hospital_name" required>
            <label>Government Issued Hospital ID:</label>
            <input type="text" name="hospital_id" required placeholder="Government Hospital ID">
            <label>Contact Information:</label>
            <input type="text" name="hospital_contact" required placeholder="Phone or Email">
            <label>Create Password:</label>
            <input type="password" name="hospital_password1" required placeholder="Min 8 chars: uppercase, lowercase, number, special">
            <label>Confirm Password:</label>
            <input type="password" name="hospital_password2" required placeholder="Re-enter your password">
            <button type="submit" class="btn primary signup-btn">Register Hospital</button>
        </form>
        <form id="pharmacy-form" style="display:none;">
            <label>Pharmacy Name:</label>
            <input type="text" name="pharmacy_name" required>
            <label>Government Issued Pharmacy ID:</label>
            <input type="text" name="pharmacy_id" required placeholder="Government Pharmacy ID">
            <label>Contact Information:</label>
            <input type="text" name="pharmacy_contact" required placeholder="Phone or Email">
            <label>Location:</label>
            <input type="text" name="pharmacy_location" required>
            <label>Create Password:</label>
            <input type="password" name="pharmacy_password1" required placeholder="Min 8 chars: uppercase, lowercase, number, special">
            <label>Confirm Password:</label>
            <input type="password" name="pharmacy_password2" required placeholder="Re-enter your password">
            <button type="submit" class="btn primary signup-btn">Register Pharmacy</button>
        </form>
        <form id="individual-form" style="display:none;">
            <label>Full Name:</label>
            <input type="text" name="full_name" required maxlength="255" placeholder="Enter your full name">
            <label>Email:</label>
            <input type="email" name="email" required maxlength="255" placeholder="your.email@example.com">
            <label>Phone Number:</label>
            <input type="tel" name="phone" required maxlength="20" placeholder="+233XXXXXXXXX or 0XXXXXXXXX">
            <label>Country:</label>
            <input type="text" name="country" required maxlength="100" placeholder="Enter your country">
            <label>City:</label>
            <input type="text" name="city" required maxlength="100" placeholder="Enter your city">
            <label>Create Password:</label>
            <input type="password" name="password1" required placeholder="Min 8 chars: uppercase, lowercase, number, special">
            <label>Confirm Password:</label>
            <input type="password" name="password2" required placeholder="Re-enter your password">
            <button type="submit" class="btn primary signup-btn">Register as Individual</button>
        </form>
        <div class="thankyou-box" id="thankyou-msg" style="display:none;"></div>
        <div style="text-align:center; margin-top:20px;">
            Already have an account? <a href="login.php" style="color:#366be6; font-weight:600; text-decoration:none;">Log in</a>
        </div>
    </div>
    <script src="../js/register.js"></script>
    <script>
    // Show correct form
    document.querySelectorAll('input[name="role"]').forEach(function(radio) {
      radio.addEventListener('change', function() {
        document.getElementById('hospital-form').style.display = 'none';
        document.getElementById('pharmacy-form').style.display = 'none';
        document.getElementById('individual-form').style.display = 'none';
        document.getElementById('thankyou-msg').style.display = 'none';
        if(this.value === 'hospital') {
          document.getElementById('hospital-form').reset();
          document.getElementById('hospital-form').style.display = 'block';
        }
        if(this.value === 'pharmacy') {
          document.getElementById('pharmacy-form').reset();
          document.getElementById('pharmacy-form').style.display = 'block';
        }
        if(this.value === 'individual') {
          document.getElementById('individual-form').reset();
          document.getElementById('individual-form').style.display = 'block';
        }
      });
    });
    // All form submissions are now handled by register.js
    </script>
    <style>
    /* Error styling for form validation */
    input.error {
        border-color: #DC2626 !important;
        background: #FEF2F2 !important;
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
