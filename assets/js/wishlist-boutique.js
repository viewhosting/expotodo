/**
 * Sistema de Wishlist Boutique - Expotodo
 * Gestión asíncrona de productos favoritos
 */
jQuery(document).ready(function ($) {

    // 1. Toggle Wishlist (Botón principal en tarjeta de producto)
    $(document).on('click', '.btn-add-wishlist', function (e) {
        e.preventDefault();
        const btn = $(this);
        // Intentar obtener el ID del botón o de la tarjeta padre (.product-card)
        const productId = btn.data('id') || btn.closest('.product-card').data('product-id');
        const icon = btn.find('i');

        if (!productId) {
            console.error('Wishlist Error: No product ID found.');
            return;
        }

        // Efecto visual instantáneo
        btn.addClass('loading');

        $.ajax({
            url: expotodo_wishlist_params.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_toggle_wishlist',
                product_id: productId,
                security: expotodo_wishlist_params.nonce
            },
            success: function (response) {
                btn.removeClass('loading');
                if (response.success) {
                    // Actualizar icono
                    if (response.data.action === 'added') {
                        icon.removeClass('far').addClass('fas text-danger');
                        showToast('¡Añadido!', 'Producto guardado en tu lista de deseos.');
                    } else {
                        icon.removeClass('fas text-danger').addClass('far');
                        showToast('Eliminado', 'Producto quitado de tu lista.');
                    }

                    // Sincronizar UI Global
                    updateWishlistUI(response.data);

                    // SI ESTAMOS EN LA PÁGINA DE CUENTA: Eliminar la tarjeta del grid
                    if ($('.wishlist-page-container').length > 0 && response.data.action === 'removed') {
                        const productCol = btn.closest('[class*="col-"]');
                        productCol.fadeOut(300, function () {
                            $(this).remove();
                            // Verificar si quedó vacía para mostrar el mensaje
                            if ($('.wishlist-page-container .product-card').length === 0) {
                                location.reload(); // Recarga simple para mostrar el estado vacío oficial
                            }
                        });
                    }
                } else if (response.data.require_login) {
                    showToast('Atención', response.data.message, 'warning');
                    // Opcional: abrir panel de login
                    $('#accountPanel').addClass('active');
                    $('.sidebar-overlay').fadeIn();
                }
            }
        });
    });

    // 2. Eliminar desde el Panel Lateral (Right Sidebar)
    $(document).on('click', '.btn-remove-wishlist', function (e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const itemRow = $(this).closest('.wishlist-item');

        itemRow.css('opacity', '0.5');

        $.ajax({
            url: expotodo_wishlist_params.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_toggle_wishlist',
                product_id: productId,
                security: expotodo_wishlist_params.nonce
            },
            success: function (response) {
                if (response.success) {
                    updateWishlistUI(response.data);
                    // Actualizar también los corazones en la lista de productos si existen
                    $(`.btn-add-wishlist[data-id="${productId}"] i`).removeClass('fas text-danger').addClass('far');
                }
            }
        });
    });

    // 3. Abrir Panel de Wishlist desde el Header
    $('.wishlist-icon').on('click', function (e) {
        e.preventDefault();
        $('#wishlistPanel').addClass('active');
        $('.sidebar-overlay').fadeIn();
    });

    /**
     * Sincroniza el contador y el contenido del panel lateral
     */
    function updateWishlistUI(data) {
        // Actualizar contador en el header
        $('.wishlist-count').text(data.count);

        // Actualizar items en el panel
        $('#wishlist-items-container').html(data.html);

        // Mostrar/Ocultar mensaje de vacío
        if (data.count > 0) {
            $('.wishlist-empty-message').addClass('d-none');
        } else {
            $('.wishlist-empty-message').removeClass('d-none');
        }
    }

    /**
     * Función para mostrar notificaciones estilo Boutique compatibles con script.js
     */
    window.showToast = function (title, message, type = 'success') {
        let alertClass = 'woocommerce-message';
        if (type === 'warning' || type === 'info') alertClass = 'woocommerce-info';
        if (type === 'error') alertClass = 'woocommerce-error';

        const toastHtml = `
            <div class="${alertClass} animate-f-in">
                <span><strong>${title}:</strong> ${message}</span>
                <button type="button" class="close-sidebar-btn" aria-label="Cerrar" onclick="this.parentElement.remove()">
                    <i class="fas fa-times" style="margin-left:10px; cursor:pointer;"></i>
                </button>
            </div>
        `;

        // Inyectar en el body para que el MutationObserver en script.js lo capture
        $('body').append(toastHtml);

        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            $('.woocommerce-message, .woocommerce-info, .woocommerce-error').fadeOut(500, function () {
                $(this).remove();
            });
        }, 5000);
    };

});
