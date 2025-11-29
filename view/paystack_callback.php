<?php
/**
 * Paystack Callback Handler
 * User lands here after payment attempt
 */

require_once('../settings/core.php');

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get transaction reference from URL
$reference = isset($_GET['reference']) ? $_GET['reference'] : null;

if (!$reference) {
    // No reference - redirect to patients page
    header('Location: patients.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - MedLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #FAEBD7 0%, #F5E6D3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5A5449;
        }

        .processing-container {
            background: rgba(255, 255, 240, 0.95);
            backdrop-filter: blur(10px);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(90, 84, 73, 0.15);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(107, 96, 87, 0.1);
            border-top-color: #6B6057;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 30px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        h1 {
            color: #5A5449;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }

        p {
            color: #7E7469;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .reference {
            font-family: 'Courier New', monospace;
            background: rgba(245, 230, 210, 0.6);
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid rgba(239, 68, 68, 0.3);
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            display: none;
        }

        .error-message h2 {
            color: #ef4444;
            margin-bottom: 10px;
        }

        .error-message p {
            color: #7E7469;
        }

        .btn {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(135deg, #8B7E74 0%, #6B6057 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 96, 87, 0.3);
        }
    </style>
</head>
<body>
    <div class="processing-container">
        <div class="spinner"></div>
        <h1>Verifying Payment</h1>
        <p>Please wait while we confirm your payment...</p>
        <div class="reference">Ref: <?php echo htmlspecialchars($reference); ?></div>
        
        <div class="error-message" id="errorMessage">
            <h2>⚠️ Payment Verification Failed</h2>
            <p id="errorText"></p>
            <a href="patients.php" class="btn">Return to Prescriptions</a>
        </div>
    </div>

    <script>
        const reference = '<?php echo addslashes($reference); ?>';
        const prescriptionId = sessionStorage.getItem('checkout_prescription_id');

        // Verify payment and process checkout
        fetch('../actions/process_checkout_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                transaction_reference: reference,
                prescription_id: prescriptionId || 0
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Store order details for success page
                sessionStorage.setItem('orderDetails', JSON.stringify(data));
                sessionStorage.removeItem('checkout_prescription_id');
                
                // Redirect to success page
                window.location.href = 'payment_success.php?order=' + data.order_reference;
            } else {
                // Show error
                document.querySelector('.spinner').style.display = 'none';
                document.getElementById('errorText').textContent = data.message || 'Payment verification failed';
                document.getElementById('errorMessage').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('.spinner').style.display = 'none';
            document.getElementById('errorText').textContent = 'Network error. Please contact support.';
            document.getElementById('errorMessage').style.display = 'block';
        });
    </script>
</body>
</html>
