<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - MedLink</title>
    <link rel="stylesheet" href="../css/cart.css">
</head>
<body class="cart-body">
    <header class="cart-header">
        <div>
            <p class="eyebrow">Patient console</p>
            <h1>Shopping Cart</h1>
            <p class="subtitle" id="cartSubtitle">Review your prescription items and proceed to checkout</p>
        </div>
        <div class="header-actions">
            <a href="patients.php" class="ghost-btn">Back to prescriptions</a>
            <a href="../index.php" class="ghost-btn">Back to MedLink</a>
        </div>
    </header>

    <section class="cart-card glass" id="cartCard">
        <div class="cart-header-info">
            <h2 id="cartPrescriptionId">Prescription</h2>
            <p id="cartHospital">Hospital</p>
        </div>
        <div class="cart-items" id="cartItems">
            <!-- Cart items will be populated here -->
        </div>
        <div class="cart-actions">
            <button class="ghost" id="emptyCartBtn">Empty cart</button>
        </div>
        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span id="subtotal">GHS 0.00</span>
            </div>
            <div class="summary-row">
                <span>Tax:</span>
                <span id="tax">GHS 0.00</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span id="total">GHS 0.00</span>
            </div>
        </div>
        <button class="primary checkout-btn" id="checkoutBtn" disabled>Proceed to checkout</button>
    </section>

    <section class="empty-cart glass" id="emptyCartMessage" style="display:none;">
        <h2>Your cart is empty</h2>
        <p>No items in your cart. Return to your prescriptions to add items.</p>
        <a href="patients.php" class="primary">View prescriptions</a>
    </section>

    <template id="cartItemTemplate">
        <div class="cart-item">
            <div class="item-info">
                <h3 class="item-name"></h3>
                <p class="item-details"></p>
            </div>
            <div class="item-price">
                <span class="price-amount"></span>
                <button class="remove-item-btn" data-index="">Remove</button>
            </div>
        </div>
    </template>

    <script>
    let cartData = null;
    let cartItems = [];

    function initializeCart() {
        const prescriptionData = sessionStorage.getItem('cartPrescription');
        if (!prescriptionData) {
            // No prescription data, show empty cart
            document.getElementById('cartCard').style.display = 'none';
            document.getElementById('emptyCartMessage').style.display = 'block';
            return;
        }

        cartData = JSON.parse(prescriptionData);
        document.getElementById('cartPrescriptionId').textContent = 'Prescription ' + cartData.id;
        document.getElementById('cartHospital').textContent = cartData.hospital;
        document.getElementById('cartSubtitle').textContent = 'Review your prescription items and proceed to checkout';

        // Convert medicines to cart items with real prices from database
        cartItems = cartData.medicines.map((med, index) => {
            // Use the real price set by pharmacy, or 0 if not set yet
            const price = med.price !== null && med.price !== undefined ? parseFloat(med.price) : 0;
            return {
                ...med,
                price: price,
                index: index
            };
        });

        renderCart();
    }

    function renderCart() {
        const cartItemsContainer = document.getElementById('cartItems');
        const itemTemplate = document.getElementById('cartItemTemplate');
        cartItemsContainer.innerHTML = '';

        if (cartItems.length === 0) {
            document.getElementById('cartCard').style.display = 'none';
            document.getElementById('emptyCartMessage').style.display = 'block';
            return;
        }

        cartItems.forEach((item, index) => {
            const itemElement = itemTemplate.content.cloneNode(true);
            itemElement.querySelector('.item-name').textContent = item.name;
            itemElement.querySelector('.item-details').textContent = `${item.dosage}, ${item.frequency} for ${item.duration}`;
            itemElement.querySelector('.price-amount').textContent = `GHS ${item.price.toFixed(2)}`;
            itemElement.querySelector('.remove-item-btn').dataset.index = index;
            cartItemsContainer.appendChild(itemElement);
        });

        updateSummary();
        bindItemEvents();
    }

    function updateSummary() {
        const subtotal = cartItems.reduce((sum, item) => sum + item.price, 0);
        const tax = subtotal * 0.05; // 5% tax
        const total = subtotal + tax;

        document.getElementById('subtotal').textContent = `GHS ${subtotal.toFixed(2)}`;
        document.getElementById('tax').textContent = `GHS ${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `GHS ${total.toFixed(2)}`;

        document.getElementById('checkoutBtn').disabled = cartItems.length === 0;
    }

    function bindItemEvents() {
        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.dataset.index);
                removeItem(index);
            });
        });
    }

    function removeItem(index) {
        cartItems.splice(index, 1);
        // Re-index items
        cartItems.forEach((item, i) => {
            item.index = i;
        });
        renderCart();
    }

    function emptyCart() {
        if (confirm('Are you sure you want to empty your cart?')) {
            cartItems = [];
            renderCart();
        }
    }

    function proceedToCheckout() {
        if (cartItems.length === 0) {
            alert('Your cart is empty.');
            return;
        }

        // Store updated cart data (preserve all prescription fields including prescription_id)
        const updatedPrescription = {
            id: cartData.id, // Prescription code (RX-2025-014)
            prescription_id: cartData.prescription_id, // Numeric ID for database
            hospital: cartData.hospital,
            medicines: cartItems.map(item => ({
                name: item.name,
                dosage: item.dosage,
                frequency: item.frequency,
                duration: item.duration
            }))
        };

        // Calculate total
        const subtotal = cartItems.reduce((sum, item) => sum + item.price, 0);
        const tax = subtotal * 0.05;
        const total = subtotal + tax;

        // Store checkout data
        sessionStorage.setItem('checkoutData', JSON.stringify({
            prescription: updatedPrescription,
            items: cartItems,
            subtotal: subtotal,
            tax: tax,
            total: total
        }));

        // Redirect to checkout page
        window.location.href = 'checkout.php';
    }

    document.getElementById('emptyCartBtn').addEventListener('click', emptyCart);
    document.getElementById('checkoutBtn').addEventListener('click', proceedToCheckout);

    initializeCart();
    </script>
</body>
</html>

