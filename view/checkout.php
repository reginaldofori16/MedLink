<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MedLink</title>
    <link rel="stylesheet" href="../css/checkout.css">
</head>
<body class="checkout-body">
    <header class="checkout-header">
        <div>
            <p class="eyebrow">Patient console</p>
            <h1>Checkout</h1>
            <p class="subtitle">Complete your payment to finalize your order</p>
        </div>
        <div class="header-actions">
            <a href="cart.php" class="ghost-btn">Back to cart</a>
            <a href="../index.php" class="ghost-btn">Back to MedLink</a>
        </div>
    </header>

    <div class="checkout-container">
        <section class="order-summary glass">
            <h2>Order Summary</h2>
            <div class="prescription-info">
                <p><strong>Prescription ID:</strong> <span id="checkoutPrescriptionId">—</span></p>
                <p><strong>Hospital:</strong> <span id="checkoutHospital">—</span></p>
            </div>
            <div class="order-items" id="orderItems">
                <!-- Order items will be populated here -->
            </div>
            <div class="order-summary-totals">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="orderSubtotal">GHS 0.00</span>
                </div>
                <div class="summary-row">
                    <span>Tax (5%):</span>
                    <span id="orderTax">GHS 0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="orderTotal">GHS 0.00</span>
                </div>
            </div>
        </section>

        <section class="payment-section glass">
            <h2>Secure Payment</h2>
            <div style="text-align: center; padding: 20px 0;">
                <div style="margin-bottom: 20px;">
                    <svg width="120" height="40" viewBox="0 0 120 40" style="margin: 0 auto;">
                        <rect width="120" height="40" fill="#00C3F7" rx="4"/>
                        <text x="60" y="25" text-anchor="middle" fill="white" font-size="18" font-weight="bold">Paystack</text>
                    </svg>
                </div>
                <p style="color: #7E7469; margin: 15px 0;">
                    🔒 Secure payment powered by Paystack
                </p>
                <p style="color: #9B8B7E; font-size: 0.95rem;">
                    You will be redirected to Paystack's secure payment page
                </p>
            </div>
            
            <form id="checkoutForm">
                <div style="background: rgba(245, 240, 230, 0.7); padding: 20px; border-radius: 12px; margin: 20px 0;">
                    <h3 style="margin: 0 0 15px 0; color: #5A5449; font-size: 1.1rem;">Payment Methods Available:</h3>
                    <ul style="color: #6B6057; line-height: 1.8; margin: 0;">
                        <li>💳 Credit/Debit Cards (Visa, Mastercard, Verve)</li>
                        <li>📱 Mobile Money (MTN, Vodafone, AirtelTigo)</li>
                        <li>🏦 Bank Transfer</li>
                        <li>📲 USSD</li>
                    </ul>
                </div>

                <button type="submit" class="primary checkout-submit-btn" style="width: 100%; padding: 16px; font-size: 1.1rem;">
                    💳 Pay with Paystack
                </button>
                
                <p style="text-align: center; color: #9B8B7E; font-size: 0.85rem; margin-top: 15px;">
                    By proceeding, you agree to our terms and conditions
                </p>
            </form>
        </section>
    </div>

    <template id="orderItemTemplate">
        <div class="order-item">
            <div class="item-info">
                <h3 class="item-name"></h3>
                <p class="item-details"></p>
            </div>
            <span class="item-price"></span>
        </div>
    </template>

    <!-- Include Paystack Checkout JavaScript -->
    <script src="../js/checkout.js"></script>
</body>
</html>

