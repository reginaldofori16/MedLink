/**
 * MedLink Checkout with Paystack Integration
 * Based on Week 9 Activity requirements
 */

let checkoutData = null;

function initializeCheckout() {
    const storedData = sessionStorage.getItem('checkoutData');
    if (!storedData) {
        alert('No checkout data found. Redirecting to prescriptions...');
        window.location.href = 'patients.php';
        return;
    }

    checkoutData = JSON.parse(storedData);
    
    // Populate order summary (use prescription code for display)
    const prescriptionCode = checkoutData.prescription.id || checkoutData.prescription.prescription_code;
    document.getElementById('checkoutPrescriptionId').textContent = prescriptionCode;
    document.getElementById('checkoutHospital').textContent = checkoutData.prescription.hospital;
    document.getElementById('orderSubtotal').textContent = `GHS ${checkoutData.subtotal.toFixed(2)}`;
    document.getElementById('orderTax').textContent = `GHS ${checkoutData.tax.toFixed(2)}`;
    document.getElementById('orderTotal').textContent = `GHS ${checkoutData.total.toFixed(2)}`;

    // Populate order items
    const orderItemsContainer = document.getElementById('orderItems');
    const itemTemplate = document.getElementById('orderItemTemplate');
    orderItemsContainer.innerHTML = '';

    checkoutData.items.forEach(item => {
        const itemElement = itemTemplate.content.cloneNode(true);
        itemElement.querySelector('.item-name').textContent = item.name;
        itemElement.querySelector('.item-details').textContent = `${item.dosage}, ${item.frequency} for ${item.duration}`;
        itemElement.querySelector('.item-price').textContent = `GHS ${item.price.toFixed(2)}`;
        orderItemsContainer.appendChild(itemElement);
    });
}

function processPayment(event) {
    event.preventDefault();
    
    // Get customer email
    const email = prompt('Please enter your email address for payment receipt:');
    
    if (!email) {
        alert('Email is required to process payment');
        return;
    }
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.checkout-submit-btn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Processing...';
    
    // Store prescription ID for callback page (use numeric ID, not code)
    const prescriptionId = checkoutData.prescription.prescription_id || checkoutData.prescription.id;
    sessionStorage.setItem('checkout_prescription_id', prescriptionId);
    
    // Initialize Paystack transaction
    const requestData = {
        email: email,
        prescription_id: prescriptionId
    };
    
    fetch('../actions/paystack_init_transaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        if (data.status === 'success') {
            // Redirect to Paystack payment page
            window.location.href = data.authorization_url;
        } else {
            alert('Payment initialization failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        alert('Network error. Please try again.');
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    initializeCheckout();
    
    // Bind payment form submission
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', processPayment);
    }
});
