jQuery(document).ready(function($) {
    // 1. Lógica para "¿Deseas factura?" (Sección de Facturación)
    function toggleBillingSection() {
        if ($('#request_invoice_checkout').is(':checked')) {
            $('#billing_details_section').slideDown();
            // Aseguramos que los campos internos de RFC estén visibles si la sección se abre
            $('.invoice-field, #billing_rfc_field, #billing_cfdi_usage_field, #billing_company_name_field').show();
        } else {
            $('#billing_details_section').slideUp();
        }
    }

    // Listener checkbox factura
    $('body').on('change', '#request_invoice_checkout', function() {
        toggleBillingSection();
    });

    // 2. Lógica para "Cambiar Dirección" (Sección de Envío)
    $('body').on('click', '#toggle_address_fields', function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
        if ($(this).hasClass('active')) {
            $('#shipping_details_section').slideDown();
        } else {
            $('#shipping_details_section').slideUp();
        }
    });

    // 3. Lógica para ocultar Envío si es Recogida Local
    function handleShippingMethodChange() {
        var $selectedRadio = $('input[name^="shipping_method"]:checked');
        var selectedMethod = $selectedRadio.val();
        var isLocalPickup = selectedMethod && selectedMethod.indexOf('local_pickup') !== -1;

        // Obtener texto del label de envío para buscar "interior"
        var selectedLabel = '';
        if ($selectedRadio.length) {
            var $label = $('label[for="' + $selectedRadio.attr('id') + '"]');
            if (!$label.length) $label = $selectedRadio.siblings('label');
            if (!$label.length) $label = $selectedRadio.closest('label');
            selectedLabel = $label.text().toLowerCase();
        }
        var isEnvioInterior = selectedLabel.indexOf('interior') !== -1;

        if (isLocalPickup) {
            // MOSTRAR campos de ubicación para Mercado Pago (S1)
            // $('#pickup_location_fields').slideDown(); // SIEMPRE OCULTO A PETICIÓN

            // Ocultar botón y sección de envío
            $('#toggle_address_fields').removeClass('active').hide();
            $('#shipping_details_section').slideUp();

            // Sincronizar ship_to_different_address (0 para recogida local)
            if ($('#ship-to-different-address-checkbox').is(':checked')) {
                $('#ship-to-different-address-checkbox').prop('checked', false).trigger('change');
            }
        } else {
            // Ocultar campos de ubicación de S1 (se usarán los de delivery)
            $('#pickup_location_fields').slideUp();

            // Sincronizar ship_to_different_address (1 para delivery) para que WooCommerce active la validación y muestre los campos
            if (!$('#ship-to-different-address-checkbox').is(':checked')) {
                $('#ship-to-different-address-checkbox').prop('checked', true).trigger('change');
            }
            $('.shipping_address').show(); // Forzar visibilidad inmediata de los campos

            // Verificar si el estado de envío actual es restringido
            var state = $('#shipping_state').val();
            var restrictedStates = ['BC', 'BS', 'CH'];
            if (restrictedStates.indexOf(state) !== -1) {
                showBoutiqueToast('Lo sentimos, no realizamos envíos a domicilio a los estados de Baja California, Baja California Sur o Chihuahua.');
                $('#shipping_state').val('').trigger('change');
            }

            // Ocultar o mostrar botón de cambiar dirección según si es envío al interior
            if (isEnvioInterior) {
                $('#toggle_address_fields').show();
                if ($('#toggle_address_fields').is(':hidden')) {
                    $('#toggle_address_fields').fadeIn();
                }

                // Forzar apertura automática para envío al interior
                $('#toggle_address_fields').addClass('active');
                $('#shipping_details_section').slideDown();
            } else {
                // Si no es envío al interior (ni local pickup), ocultar el botón de cambiar dirección y su sección
                $('#toggle_address_fields').removeClass('active').hide();
                $('#shipping_details_section').slideUp();
            }
        }
    }

    // Escuchar cambio inmediato al hacer clic en un método de envío
    $('body').on('change', 'input[name^="shipping_method"]', function() {
        handleShippingMethodChange();
    });

    // Escuchar actualización de checkout de WooCommerce
    $(document.body).on('updated_checkout', function() {
        handleShippingMethodChange();
    });

    // Inicialización
    $('#billing_rfc_field, #billing_cfdi_usage_field, #billing_company_name_field').hide();
    handleShippingMethodChange();

    // 2. Lógica para Ciudad dependiente del Estado (AJAX)

    function loadCities(state, targetSelector) {
        var $citySelect = $(targetSelector);

        // Mostrar cargando
        $citySelect.empty();
        $citySelect.append('<option value="">Cargando ciudades...</option>');
        $citySelect.prop('disabled', true);

        $.ajax({
            url: expotodo_checkout_params.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_get_cities',
                state: state,
                nonce: expotodo_checkout_params.nonce
            },
            success: function(response) {
                $citySelect.empty();
                $citySelect.append('<option value="">Seleccione una ciudad</option>');

                if (response.success && response.data.length > 0) {
                    $.each(response.data, function(index, value) {
                        $citySelect.append('<option value="' + value + '">' + value + '</option>');
                    });
                } else {
                    // Fallback si no hay ciudades o error
                    if (state) {
                        $citySelect.append('<option value="Otra">Otra</option>');
                    }
                }

                $citySelect.prop('disabled', false);

                // Actualizar Select2 si existe
                if ($.fn.select2) {
                    $citySelect.trigger('change');
                }
            },
            error: function() {
                $citySelect.empty();
                $citySelect.append('<option value="">Error al cargar</option>');
                $citySelect.prop('disabled', false);
            }
        });
    }

    // Listener Billing State
    $('body').on('change', '#billing_state', function() {
        var state = $(this).val();
        loadCities(state, '#billing_city');
    });

    // Listener Shipping State
    $('body').on('change', '#shipping_state', function() {
        var state = $(this).val();
        var restrictedStates = ['BC', 'BS', 'CH'];

        var selectedMethod = $('input[name^="shipping_method"]:checked').val();
        var isLocalPickup = selectedMethod && selectedMethod.indexOf('local_pickup') !== -1;

        if (!isLocalPickup && restrictedStates.indexOf(state) !== -1) {
            showBoutiqueToast('Lo sentimos, no realizamos envíos a domicilio a los estados de Baja California, Baja California Sur o Chihuahua.');
            $(this).val('').trigger('change');
            return;
        }

        loadCities(state, '#shipping_city');
    });

    // Cargar ciudades iniciales si hay estado seleccionado (por ejemplo al recargar con error)
    var initialBillingState = $('#billing_state').val();
    if (initialBillingState) {
        // Solo cargar si el select de ciudad está vacío o tiene valor por defecto
        if ($('#billing_city option').length <= 1) {
            loadCities(initialBillingState, '#billing_city');
        }
    }

    var initialShippingState = $('#shipping_state').val();
    if (initialShippingState) {
        if ($('#shipping_city option').length <= 1) {
            loadCities(initialShippingState, '#shipping_city');
        }
    }

    // 3. Controladores de Botones del Carrito en el Checkout (+, -, y Eliminar)
    function updateCheckoutQuantity(cart_item_key, newQty) {
        // Bloquear temporalmente la pantalla (nativamente WooCommerce tiene .processing)
        $('.cart-items-container').addClass('processing').css('opacity', '0.6');

        $.ajax({
            url: expotodo_checkout_params.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_update_checkout_qty',
                cart_item_key: cart_item_key,
                qty: newQty
            },
            success: function(response) {
                if (response.success) {
                    $('body').trigger('update_checkout');
                } else {
                    $('.cart-items-container').removeClass('processing').css('opacity', '1');
                    alert('Error al actualizar la cantidad');
                }
            },
            error: function() {
                $('.cart-items-container').removeClass('processing').css('opacity', '1');
                alert('Error de conexión');
            }
        });
    }

    $('body').on('click', '.qty-checkout-up', function(e) {
        e.preventDefault();
        var $wrapper = $(this).closest('.quantity-wrapper');
        var $input = $wrapper.find('.checkout-qty-input');
        var max = parseFloat($input.attr('max'));
        var currentQty = parseFloat($input.val());

        var newQty = currentQty + 1;
        if (max && newQty > max) newQty = max;

        $input.val(newQty);
        updateCheckoutQuantity($(this).data('cart_item_key'), newQty);
    });

    $('body').on('click', '.qty-checkout-down', function(e) {
        e.preventDefault();
        var $wrapper = $(this).closest('.quantity-wrapper');
        var $input = $wrapper.find('.checkout-qty-input');
        var currentQty = parseFloat($input.val());
        var min = parseFloat($input.attr('min')) || 0;

        var newQty = currentQty - 1;
        if (newQty < min) newQty = min;
        if (newQty === 0) return; // Prevent decrementing to 0 and doing nothing; normally 0 is remove.

        $input.val(newQty);
        updateCheckoutQuantity($(this).data('cart_item_key'), newQty);
    });

    $('body').on('change', '.checkout-qty-input', function(e) {
        var newQty = parseFloat($(this).val());
        var $btn = $(this).closest('.quantity-wrapper').find('.qty-checkout-up');
        updateCheckoutQuantity($btn.data('cart_item_key'), newQty);
    });

    $('body').on('click', '.remove-checkout-item', function(e) {
        e.preventDefault();
        var cart_item_key = $(this).data('cart_item_key');

        $('.cart-items-container').addClass('processing').css('opacity', '0.6');

        $.ajax({
            url: expotodo_checkout_params.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_remove_checkout_item',
                cart_item_key: cart_item_key
            },
            success: function(response) {
                if (response.success) {
                    // Triggers the checkout update to refresh the table and totals
                    $('body').trigger('update_checkout');
                } else {
                    $('.cart-items-container').removeClass('processing').css('opacity', '1');
                }
            }
        });
    });

    // ==========================================
    // SISTEMA DE NOTIFICACIONES TOAST BOUTIQUE
    // ==========================================

    /**
     * Muestra una notificación estilo Toast
     */
    function showBoutiqueToast(message) {
        // Crear contenedor si no existe
        if ($('.boutique-toast-container').length === 0) {
            $('body').append('<div class="boutique-toast-container"></div>');
        }

        var toastHtml = '<div class="boutique-toast">' +
            '<div class="toast-content">' + message + '</div>' +
            '<div class="toast-close" style="margin-left: 15px; opacity: 0.5; font-size: 0.8rem;"><i class="fas fa-times"></i></div>' +
            '</div>';

        var $toast = $(toastHtml);
        $('.boutique-toast-container').append($toast);

        // Auto-eliminar después de 5 segundos
        var timer = setTimeout(function() {
            hideToast($toast);
        }, 5000);

        // Click para cerrar
        $toast.on('click', function() {
            clearTimeout(timer);
            hideToast($toast);
        });
    }

    function hideToast($toast) {
        $toast.addClass('hiding');
        setTimeout(function() {
            $toast.remove();
            // Eliminar contenedor si está vacío
            if ($('.boutique-toast-container').children().length === 0) {
                $('.boutique-toast-container').remove();
            }
        }, 500);
    }

    /**
     * Interceptar errores de WooCommerce Checkout
     */
    $(document.body).on('checkout_error', function(e, error_message) {
        // WooCommerce envía un string HTML con un <ul> y varios <li>
        // Lo convertimos temporalmente en objeto jQuery para parsear
        var $tempDiv = $('<div>' + error_message + '</div>');
        var $errors = $tempDiv.find('li');

        if ($errors.length > 0) {
            $errors.each(function() {
                var msg = $(this).text().trim();
                // Limpiar el mensaje de errores comunes de Woo (ej: "Facturación Nombre es un campo requerido" -> "Nombre es un campo requerido")
                var cleanMsg = msg.replace('Facturación ', '').replace('Envío ', '');
                if (cleanMsg) {
                    showBoutiqueToast(cleanMsg);
                }
            });
        } else {
            // Fallback si no es una lista
            var plainMsg = $tempDiv.text().trim();
            if (plainMsg) {
                showBoutiqueToast(plainMsg);
            } else {
                showBoutiqueToast('Hubo un error al procesar el pedido. Por favor, revisa tus datos.');
            }
        }

        // Devolver false para evitar el scroll nativo de Woo
        return false;
    });

});