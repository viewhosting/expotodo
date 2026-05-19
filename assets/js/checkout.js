document.addEventListener('DOMContentLoaded', function () {
    var checkoutItemsContainer = document.getElementById('checkout-cart-items');
    var checkoutCount = document.getElementById('checkout-cart-count');
    var checkoutSubtotal = document.getElementById('checkout-subtotal');
    var checkoutShipping = document.getElementById('checkout-shipping');
    var checkoutTotal = document.getElementById('checkout-total');
    var placeOrderBtn = document.getElementById('placeOrderBtn');

    function loadCheckout() {
        var cart = JSON.parse(localStorage.getItem('cart')) || [];
        var shippingCost = 0;

        if (checkoutItemsContainer) {
            checkoutItemsContainer.innerHTML = '';
            var subtotal = 0;
            var totalItems = 0;

            for (var i = 0; i < cart.length; i++) {
                var item = cart[i];
                subtotal += item.price * item.quantity;
                totalItems += item.quantity;

                var li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between lh-sm';
                li.innerHTML =
                    '<div>' +
                    '<h6 class="my-0">' + item.name + '</h6>' +
                    '<small class="text-muted">Cantidad: ' + item.quantity + '</small>' +
                    '</div>' +
                    '<span class="text-muted">' + (item.price * item.quantity).toFixed(2).replace('.', ',') + '$</span>';
                checkoutItemsContainer.appendChild(li);
            }

            if (checkoutCount) checkoutCount.textContent = totalItems;
            if (checkoutSubtotal) checkoutSubtotal.textContent = subtotal.toFixed(2).replace('.', ',') + '$';

            // Simple shipping logic for display
            if (subtotal >= 1000) {
                shippingCost = 0;
            } else if (subtotal > 0) {
                shippingCost = 15; // Standard default
            }

            if (checkoutShipping) checkoutShipping.textContent = shippingCost > 0 ? shippingCost.toFixed(2).replace('.', ',') + '$' : 'Gratis';
            if (checkoutTotal) checkoutTotal.textContent = (subtotal + shippingCost).toFixed(2).replace('.', ',') + '$';
        }
    }

    loadCheckout();

    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function () {
            // Validate form
            var form = document.getElementById('checkoutForm');
            if (form && !form.checkValidity()) {
                if (form.reportValidity) {
                    form.reportValidity();
                }
                return;
            }

            // Simulate order placement
            localStorage.removeItem('cart');

            window.location.href = '/gracias';
        });
    }
});
