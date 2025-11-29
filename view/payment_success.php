<?php
/**
 * Payment Success Page
 * Displays order confirmation after successful payment
 */

require_once('../settings/core.php');

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../index.php');
    exit();
}

$order_reference = isset($_GET['order']) ? $_GET['order'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - MedLink</title>
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
            padding: 20px;
        }

        .success-container {
            background: rgba(255, 255, 240, 0.95);
            backdrop-filter: blur(10px);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(90, 84, 73, 0.15);
            text-align: center;
            max-width: 600px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }

        .checkmark svg {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            animation: drawCheck 0.5s ease-out 0.3s forwards;
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes drawCheck {
            to {
                stroke-dashoffset: 0;
            }
        }

        h1 {
            color: #10b981;
            margin-bottom: 15px;
            font-size: 2rem;
        }

        .subtitle {
            color: #7E7469;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .order-details {
            background: rgba(245, 230, 210, 0.4);
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(90, 84, 73, 0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #7E7469;
            font-weight: 500;
        }

        .detail-value {
            color: #5A5449;
            font-weight: 600;
        }

        .order-reference {
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 240, 0.9);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1.2rem;
        }

        .amount {
            font-size: 1.5rem;
            color: #10b981;
        }

        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8B7E74 0%, #6B6057 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 96, 87, 0.3);
        }

        .btn-secondary {
            background: rgba(245, 230, 210, 0.6);
            color: #6B6057;
        }

        .btn-secondary:hover {
            background: rgba(245, 230, 210, 0.9);
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #10b981;
            opacity: 0;
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid rgba(59, 130, 246, 0.3);
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
        }

        .info-box p {
            color: #6B6057;
            margin: 8px 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="checkmark">
            <svg viewBox="0 0 52 52">
                <polyline points="14 27 22 35 38 19"/>
            </svg>
        </div>
        
        <h1>Payment Successful!</h1>
        <p class="subtitle">Thank you for your order. Your prescription will be prepared shortly.</p>
        
        <div class="order-details" id="orderDetails">
            <div class="detail-row">
                <span class="detail-label">Order Reference</span>
                <span class="detail-value order-reference" id="orderRef">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Transaction Reference</span>
                <span class="detail-value" id="transactionRef">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Paid</span>
                <span class="detail-value amount" id="amount">—</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value" id="paymentMethod">—</span>
            </div>
        </div>
        
        <div class="info-box">
            <p><strong>📱 What's Next?</strong></p>
            <p>• Your pharmacy will prepare your medicines</p>
            <p>• You'll receive a notification when ready for pickup</p>
            <p>• Track your order status on your prescriptions page</p>
        </div>
        
        <div class="buttons">
            <a href="patients.php" class="btn btn-primary">View My Prescriptions</a>
            <a href="../index.php" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>

    <script>
        // Create confetti animation
        function createConfetti() {
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                confetti.style.background = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'][Math.floor(Math.random() * 4)];
                confetti.style.animation = 'confetti-fall linear forwards';
                document.querySelector('.success-container').appendChild(confetti);
            }
        }

        // Load order details
        const orderDetails = sessionStorage.getItem('orderDetails');
        if (orderDetails) {
            const data = JSON.parse(orderDetails);
            document.getElementById('orderRef').textContent = data.order_reference || '—';
            document.getElementById('transactionRef').textContent = data.transaction_reference || '—';
            document.getElementById('amount').textContent = `GHS ${parseFloat(data.amount || 0).toFixed(2)}`;
            document.getElementById('paymentMethod').textContent = 
                (data.payment_channel || 'Paystack').charAt(0).toUpperCase() + 
                (data.payment_channel || 'Paystack').slice(1).replace('_', ' ');
            
            // Clear session storage
            sessionStorage.removeItem('orderDetails');
        }

        // Trigger confetti
        createConfetti();
    </script>
</body>
</html>
