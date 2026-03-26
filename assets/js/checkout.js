document.addEventListener('DOMContentLoaded', function() {
    const checkoutItemsContainer = document.getElementById('checkout-cart-items');
    const checkoutCount = document.getElementById('checkout-cart-count');
    const checkoutSubtotal = document.getElementById('checkout-subtotal');
    const checkoutShipping = document.getElementById('checkout-shipping');
    const checkoutTotal = document.getElementById('checkout-total');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    function loadCheckout() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        // Recalcular shipping based on existing rules logic (simplified here or reused)
        // For consistency, let's assume shipping is 0 unless calculated, or default standard.
        // In a real app, we'd pull shippingCost from localStorage or recalculate.
        // Let's reuse the shipping cost if possible or default to 0.
        let shippingCost = 0; // Default
        
        if (checkoutItemsContainer) {
            checkoutItemsContainer.innerHTML = '';
            let subtotal = 0;
            let totalItems = 0;

            cart.forEach(item => {
                subtotal += item.price * item.quantity;
                totalItems += item.quantity;
                
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between lh-sm';
                li.innerHTML = `
                    <div>
                        <h6 class="my-0">${item.name}</h6>
                        <small class="text-muted">Cantidad: ${item.quantity}</small>
                    </div>
                    <span class="text-muted">${(item.price * item.quantity).toFixed(2).replace('.', ',')}€</span>
                `;
                checkoutItemsContainer.appendChild(li);
            });

            if (checkoutCount) checkoutCount.textContent = totalItems;
            if (checkoutSubtotal) checkoutSubtotal.textContent = subtotal.toFixed(2).replace('.', ',') + '€';
            
            // Simple shipping logic for display
            if (subtotal >= 1000) {
                shippingCost = 0;
            } else if (subtotal > 0) {
                shippingCost = 15; // Standard default
            }
            
            if (checkoutShipping) checkoutShipping.textContent = shippingCost > 0 ? shippingCost.toFixed(2).replace('.', ',') + '€' : 'Gratis';
            if (checkoutTotal) checkoutTotal.textContent = (subtotal + shippingCost).toFixed(2).replace('.', ',') + '€';
        }
    }

    loadCheckout();

    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function() {
            // Validate form
            const form = document.getElementById('checkoutForm');
            if (form && !form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Simulate order placement
            localStorage.removeItem('cart'); // Clear cart
            
            // Redirect to thank you page - using dynamic URL handled by WordPress
            // The URL will be injected via wp_localize_script or handled via data attribute
            // For now, let's use a relative path that works if the page slug is 'gracias'
            window.location.href = '/gracias'; 
        });
    }
});
