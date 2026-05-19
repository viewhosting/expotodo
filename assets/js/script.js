//alert("Compatibilidad universal activada.");
jQuery(document).ready(function ($) {
    // Header Scroll Effect
    var header = document.querySelector('.main-header');
    if (header) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        }
    }

    // ==========================================
    // SISTEMA DE TOASTS SIMPLE Y APILABLE
    // ==========================================
    function autoHideWoocommerceMessages() {
        var toastSelectors = '.woocommerce-message, .woocommerce-info, .woocommerce-error, .coupon-error-notice';
        var $foundMessages = $(toastSelectors).not('#expotodo-toast-container *');

        if ($foundMessages.length > 0) {
            if ($('#expotodo-toast-container').length === 0) {
                $('body').append('<div id="expotodo-toast-container"></div>');
            }
            var $container = $('#expotodo-toast-container');

            $foundMessages.each(function () {
                var $msg = $(this);
                var msgText = $msg.text().replace(/\s+/g, ' ').trim();

                if (!msgText) return;

                // CENTINELA 360: Bloqueo de Zona/México (Fail-safe en JS)
                var lowText = msgText.toLowerCase();
                if (lowText.includes('zona') || lowText.includes('méxico')) {
                    $msg.remove();
                    return;
                }

                // Evitar duplicados físicos idénticos que ya estén visibles
                var isAlreadyVisible = false;
                $container.children().each(function () {
                    if ($(this).text().replace(/\s+/g, ' ').trim() === msgText) isAlreadyVisible = true;
                });

                if (isAlreadyVisible) {
                    $msg.remove();
                    return;
                }

                // Teletransportar al contenedor y mostrar (NUEVO ORDEN: prependTo)
                $msg.prependTo($container).show();

                // Auto-ocultar después de 8 segundos
                setTimeout(function () {
                    $msg.fadeOut(600, function () {
                        $(this).remove();
                        if ($container.children().length === 0) $container.hide();
                    });
                }, 8000);
            });
            $container.show();
        }
    }

    // Inicializar
    autoHideWoocommerceMessages();

    // Escuchar eventos de actualización de WooCommerce (AJAX)
    $(document.body).on('updated_wc_div updated_cart_totals updated_checkout updated_shipping_method', function () {
        autoHideWoocommerceMessages();
    });

    // LISTENER VIP: Detectar cuando se agrega un producto vía AJAX y mostrar el Toast
    $(document.body).on('added_to_cart', function (event, fragments, cart_hash, $button) {
        // Obtenemos el nombre del producto si está disponible en el botón
        var productName = $button.closest('.product-card').find('.product-title').text() || 'el producto';
        var successHtml = '<div class="woocommerce-message">' +
            '<span>¡Hecho! Se ha agregado "' + productName + '" al carrito con éxito.</span>' +
            '<button type="button" class="close-sidebar-btn" aria-label="Cerrar">' +
            '<i class="fas fa-times"></i>' +
            '</button>' +
            '</div>';

        // Inyectamos el mensaje en el body temporalmente para que autoHideWoocommerceMessages lo capture
        $('body').append(successHtml);
        autoHideWoocommerceMessages();

        sessionStorage.removeItem('expotodo_toasts_seen');
        setTimeout(autoHideWoocommerceMessages, 400);
    });

    // Detector de cierre manual de Toasts
    $(document).on('click', '#expotodo-toast-container .close-sidebar-btn', function (e) {
        e.preventDefault();
        e.stopPropagation(); // Evitamos que cierre el sidebar si el botón se llama igual
        $(this).closest('.woocommerce-message, .woocommerce-info, .woocommerce-error').fadeOut(300, function () {
            $(this).remove();
            if ($('#expotodo-toast-container').children().length === 0) {
                $('#expotodo-toast-container').hide();
            }
        });
    });

    // Observador de cambios
    var observer = new MutationObserver(function (mutations) {
        autoHideWoocommerceMessages();
    });
    observer.observe(document.body, { childList: true, subtree: true });

    // Mobile Navigation
    var navbarToggler = document.querySelector('.navbar-toggler');
    var navbarCollapse = document.querySelector('.navbar-collapse');
    var sidebarOverlay = document.querySelector('.sidebar-overlay');

    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function (e) {
            e.preventDefault();
            navbarCollapse.classList.add('sidebar-open');
            if (sidebarOverlay) sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Close nav on link click (mobile)
        var navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        for (var i = 0; i < navLinks.length; i++) {
            navLinks[i].addEventListener('click', function () {
                if (window.innerWidth <= 992) {
                    navbarCollapse.classList.remove('sidebar-open');
                    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }
    }

    // Sidebar Management
    var rightSidebars = document.querySelectorAll('.right-sidebar');
    var closeSidebarButtons = document.querySelectorAll('.close-sidebar-btn');

    function openRightSidebar(panelId) {
        for (var j = 0; j < rightSidebars.length; j++) {
            rightSidebars[j].classList.remove('open');
        }
        if (navbarCollapse && navbarCollapse.classList.contains('sidebar-open')) {
            navbarCollapse.classList.remove('sidebar-open');
        }

        var panel = document.getElementById(panelId);
        if (panel) {
            panel.classList.add('open');
            if (sidebarOverlay) sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';

            // EMPUJAR TOASTS: Si el sidebar se abre, empujamos el contenedor de avisos
            $('#expotodo-toast-container').addClass('pushed');
        }
    }

    function closeRightSidebars() {
        for (var k = 0; k < rightSidebars.length; k++) {
            rightSidebars[k].classList.remove('open');
        }
        var isNavbarOpen = navbarCollapse && navbarCollapse.classList.contains('sidebar-open');
        if (sidebarOverlay && !isNavbarOpen) {
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';

            // REGRESAR TOASTS: Al cerrar el sidebar, regresan a su sitio
            $('#expotodo-toast-container').removeClass('pushed');
        }
    }

    // Sidebar Triggers
    $('.buscar-icon').on('click', function (e) {
        e.preventDefault();
        openRightSidebar('buscarPanel');
    });

    $('.cart-icon').on('click', function (e) {
        e.preventDefault();
        openRightSidebar('cartPanel');
    });

    $('.wishlist-icon').on('click', function (e) {
        e.preventDefault();
        openRightSidebar('wishlistPanel');
    });

    $('.account-icon').on('click', function (e) {
        e.preventDefault();
        openRightSidebar('accountPanel');
    });

    $('.close-sidebar-btn').on('click', closeRightSidebars);

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            if (navbarCollapse) navbarCollapse.classList.remove('sidebar-open');
            closeRightSidebars();
        });
    }

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            if (navbarCollapse) navbarCollapse.classList.remove('sidebar-open');
            closeRightSidebars();
        }
    });

    // ==========================================
    // CHECKOUT SIDEBAR LOGIC
    // ==========================================

    var checkoutModal = null;

    // Abrir modal de checkout desde cualquier botón de "Finalizar Compra"
    $(document).on('click', '.btn-checkout-modal, .checkout-button', function (e) {
        e.preventDefault();

        // Inicialización perezosa del modal
        if (!checkoutModal) {
            var checkoutModalEl = document.getElementById('checkoutPanel');
            if (checkoutModalEl && typeof bootstrap !== 'undefined') {
                checkoutModal = new bootstrap.Modal(checkoutModalEl);
            }
        }

        if (checkoutModal) {
            checkoutModal.show();
        }

        var $container = $('#payment-gateways-container');
        var $btnPlaceOrder = $('#btn-place-order');
        var $errorContainer = $('#checkout-errors');

        // Reset state
        $errorContainer.empty();
        $btnPlaceOrder.prop('disabled', true);
        $container.html('<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2 text-muted">Cargando métodos de pago...</span></div>');

        // Cargar métodos de pago via AJAX
        $.ajax({
            url: expotodo_globals.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_get_checkout_data'
            },
            success: function (response) {
                if (response.success) {
                    $container.html(response.data.html);
                    // Actualizar total en el panel (por si cambió)
                    $('#checkoutPanel .cart-total').html(response.data.total);

                    // Inicializar visibilidad de campos de pago y habilitar botón
                    var $initialChecked = $container.find('input[name="payment_method"]:checked');
                    if ($initialChecked.length > 0) {
                        $initialChecked.closest('.wc_payment_method').find('.payment_box').show();
                        $btnPlaceOrder.prop('disabled', false);
                    }

                    // Notificar a WooCommerce que el checkout se ha actualizado
                    $(document.body).trigger('updated_checkout');

                    // Listener para selección de método
                    $container.find('input[name="payment_method"]').on('change', function () {
                        var $parent = $(this).closest('.wc_payment_method');
                        $('.payment_box').slideUp(200);
                        if ($(this).is(':checked')) {
                            $parent.find('.payment_box').slideDown(200);
                            $btnPlaceOrder.prop('disabled', false);
                            $(document.body).trigger('payment_method_selected');
                        }
                    });
                } else {
                    $container.html('<div class="alert alert-danger small m-3">Error: ' + (response.data.message || 'No se pudieron cargar los métodos.') + '</div>');
                }
            },
            error: function () {
                $container.html('<div class="alert alert-danger small m-3">Error de conexión al cargar métodos de pago.</div>');
            }
        });

        // Alternar campos de facturación diferentes (Logic nueva para el modal)
        $(document).on('change', '#use-shipping-for-billing', function () {
            var $extraFields = $('#billing-different-fields');
            if (!$(this).is(':checked')) {
                $extraFields.slideDown();
            } else {
                $extraFields.slideUp();
                // Limpiar campos si se vuelve a la opción por defecto
                $extraFields.find('input').val('');
            }
        });
    });

    // ==========================================
    // MODAL DE CHECKOUT DESACTIVADO
    // Razón: El Modal de pasarelas cargadas por AJAX no es compatible con el SDK 
    //        de Mercado Pago (Checkout Pro modal). Esta lógica se desactiva para volver
    //        al flujo nativo en /checkout/
    // ==========================================
    /*
    // Acción del botón de Pagar Ahora (Sumisión AJAX real con serialización completa)
    $('#btn-place-order').on('click', function (e) {
        e.preventDefault();
        var selectedMethod = $('input[name="payment_method"]:checked').val();

        if (!selectedMethod) {
            alert('Por favor, selecciona un método de pago.');
            return;
        }

        var $btn = $(this);
        var originalHtml = $btn.html();
        var $errorContainer = $('#checkout-errors');
        var $form = $('form.woocommerce-checkout');

        // Limpiar errores previos
        $errorContainer.empty();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Procesando...');

        // Recopilar TODOS los datos del formulario (incluyendo campos de pasarela y tokens ocultos)
        var checkoutData = $form.serializeArray();

        // Asegurar campos críticos
        checkoutData.push({ name: '_wpnonce', value: expotodo_globals.checkout_nonce });
        checkoutData.push({ name: 'woocommerce-process-checkout-nonce', value: expotodo_globals.checkout_nonce });
        checkoutData.push({ name: 'ship_to_different_address', value: '0' });
        checkoutData.push({ name: 'terms', value: 'on' });
        checkoutData.push({ name: 'terms-field', value: '1' });

        $.ajax({
            url: '/?wc-ajax=checkout',
            type: 'POST',
            data: checkoutData,
            success: function (response) {
                try {
                    var data = response;
                    if (typeof response === 'string') {
                        var jsonPos = response.indexOf('{"result"');
                        if (jsonPos > -1) {
                            data = JSON.parse(response.substring(jsonPos));
                        }
                    }
                    console.log(data.messages);
                    if (data.result === 'success') {
                        // LA PASARELA O WC HACEN LA REDIRECCIÓN OFICIAL
                        window.location.href = data.redirect;
                    } else {
                        // MOSTRAR ERRORES EN EL SIDEBAR
                        if (data.messages) {

                            $errorContainer.html(data.messages).hide().fadeIn();
                            // Scroll al inicio del panel para ver errores
                            $('#checkoutPanel').animate({ scrollTop: 0 }, 400);
                        } else {
                            // Fallback si no hay mensajes
                            window.location.href = expotodo_globals.checkout_url + '?payment_method=' + selectedMethod;
                        }
                        // Restaurar botón
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                } catch (err) {
                    console.error('Error parseando checkout:', err);
                    window.location.href = expotodo_globals.checkout_url + '?payment_method=' + selectedMethod;
                }
            },
            error: function () {
                window.location.href = expotodo_globals.checkout_url + '?payment_method=' + selectedMethod;
            }
        });
    });
    */

    // ==========================================
    // SERVER-SIDE CART IMPLEMENTATION
    // ==========================================

    // Add classes to single product add to cart button
    $('button.single_add_to_cart_button').addClass('btn-card btn-primary mt-2 ajax_add_to_cart');

    // ==========================================
    // PRODUCT DETAILS LAYOUT LOGIC
    // ==========================================

    // Mover el contenedor de agregar al carrito a la barra lateral (sticky panel)
    // Se ejecuta al cargar y cuando WooCommerce actualiza las variaciones
    function teleportCartButton() {
        var $variationWrap = $('.single_variation_wrap');
        var $stickyPanel = $('#sticky-cart-panel');
        var $placeholder = $('.cart-placeholder-text');

        if ($variationWrap.length && $stickyPanel.length) {
            // Mover el wrap al panel lateral
            // Nota: Al moverlo fuera del <form>, el botón de submit necesita ayuda para funcionar

            // Si ya está dentro del panel, no hacer nada
            if ($variationWrap.parent().is('#sticky-cart-panel')) {
                return;
            }

            // Antes de mover, aseguramos que el formulario se envíe correctamente
            var $form = $('.variations_form');

            // Movemos el elemento
            $variationWrap.appendTo($stickyPanel);

            // Ajustar visibilidad del placeholder
            // WooCommerce oculta single_variation_wrap hasta que se selecciona algo
            // Podemos usar un observer o evento
        }
    }

    // Ejecutar al inicio
    teleportCartButton();

    // WooCommerce dispara eventos cuando se encuentra/actualiza una variación
    $('.variations_form').on('show_variation', function () {
        teleportCartButton();
        $('#sticky-cart-panel .cart-placeholder-text').hide();
        $('#sticky-cart-panel .single_variation_wrap').show();
    });

    $('.variations_form').on('hide_variation', function () {
        // Cuando no hay variación válida, ocultar el botón (WooCommerce lo hace con style display:none)
        // Mostramos el texto placeholder
        $('#sticky-cart-panel .cart-placeholder-text').show();
    });

    $('.variations_form').on('reset_image', function () {
        $('#sticky-cart-panel .cart-placeholder-text').show();
    });

    /**
     * Función global para agregar al carrito via AJAX
     * @param {jQuery} $btn - El botón que disparó la acción
     * @param {Object|String} data - Datos a enviar
     * @param {Boolean} isForm - Si los datos vienen de un formulario serializado
     */
    window.agregarCarrito = function ($btn, data, isForm) {
        if ($btn.hasClass('loading')) return;

        // Guardar el contenido original para restaurarlo después
        var originalHtml = $btn.html();

        $btn.addClass('loading').prop('disabled', true);
        $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Agregando...');

        // Si es formulario (single product), posteamos a la misma URL para que WC lo procese
        // Si no, usamos el endpoint de AJAX de WooCommerce
        var ajaxUrl = isForm ? window.location.href : (typeof wc_add_to_cart_params !== 'undefined' ? wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart') : '/?wc-ajax=add_to_cart');

        $.ajax({
            url: ajaxUrl,
            data: data,
            method: 'POST',
            success: function (response) {
                // Actualizar fragmentos del carrito
                $(document.body).trigger('wc_fragment_refresh');

                // Disparar evento de éxito
                if (response && response.fragments) {
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $btn]);
                } else {
                    $(document.body).trigger('added_to_cart', [null, null, $btn]);
                }
            },
            error: function () {
                // Fallback: si falla el AJAX de formulario, enviamos normal
                if (isForm) {
                    var $form = $btn.closest('form.cart') || $('form.variations_form, form.cart');
                    if ($form.length) $form.submit();
                }
            },
            complete: function () {
                $btn.removeClass('loading').prop('disabled', false);
                $btn.html(originalHtml);
            }
        });
    };

    // AJAX Add to Cart para Página de Producto (Simple y Variable)
    $(document).on('click', '.single_add_to_cart_button', function (e) {
        var $btn = $(this);
        var $form = $btn.closest('form.cart');

        if (!$form.length) {
            $form = $('form.variations_form, form.cart');
        }

        if (!$form.length) return;

        e.preventDefault();

        // Validar campos requeridos (especialmente variaciones)
        if ($form[0].checkValidity && !$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        var data = $form.serialize();
        var addToCartVal = $btn.val() || $form.find('[name="add-to-cart"]').val();
        if (addToCartVal) {
            data += '&add-to-cart=' + addToCartVal;
        }

        window.agregarCarrito($btn, data, true);
    });

    // AJAX Add to Cart para Listados (Archive/Grid)
    $(document).on('click', '.add_to_cart_button:not(.single_add_to_cart_button), .ajax_add_to_cart', function (e) {
        var $btn = $(this);

        // Si no tiene product_id o es variable en listado (que redirige), dejamos comportamiento nativo
        if (!$btn.data('product_id')) return;

        e.preventDefault();

        var data = {
            product_id: $btn.data('product_id'),
            quantity: $btn.data('quantity') || 1,
            product_sku: $btn.data('product_sku') || ''
        };

        window.agregarCarrito($btn, data, false);
    });

    // Auto-open cart panel when item is added
    $(document.body).on('added_to_cart', function () {
        // Ocultar el botón "Ver carrito" que WooCommerce añade (ya que usamos Toast/Sidebar)
        $('.added_to_cart').css('display', 'none');

        // Mostrar Toast de éxito
        var $toastEl = $('#cartToast');
        if ($toastEl.length && typeof bootstrap !== 'undefined') {
            var toast = new bootstrap.Toast($toastEl[0], { delay: 3000 });
            toast.show();
        }

        openRightSidebar('cartPanel');
    });

    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // ==========================================
    // AJAX LOGIN
    // ==========================================
    // Function moved to global scope window.expotodo_handle_login
    // to be called via onclick attribute


    // ==========================================
    // REAL-TIME SEARCH (JQUERY)
    // ==========================================
    // ==========================================
    // REAL-TIME SEARCH (JQUERY)
    // ==========================================
    var searchTimeout = null;
    var $searchInput = $('#productoBusqueda');
    var $searchResults = $('#search-results-list');

    if ($searchInput.length && $searchResults.length) {
        $searchInput.on('input', function () {
            var query = $(this).val().trim();

            if (searchTimeout) clearTimeout(searchTimeout);

            if (query.length < 2) {
                $searchResults.empty();
                return;
            }

            // Show loading state
            $searchResults.html('<div class="p-4 text-center text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Buscando...</div>');

            searchTimeout = setTimeout(function () {
                $.ajax({
                    url: expotodo_globals.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'expotodo_live_search',
                        query: query
                    },
                    success: function (response) {
                        if (response.success) {
                            $searchResults.html(response.data.html);
                        } else {
                            $searchResults.html('<div class="p-4 text-center text-danger">Error al realizar la búsqueda.</div>');
                        }
                    },
                    error: function () {
                        $searchResults.html('<div class="p-4 text-center text-danger">Error de conexión.</div>');
                    }
                });
            }, 300);
        });
    }

    // Product Gallery: Swap main image when thumbnail is clicked
    $(document).on('click', '.thumbnail-card a', function (e) {
        e.preventDefault();
        var fullImageUrl = $(this).attr('href');
        var $mainAnchor = $('.product-image-main a');
        var $mainImage = $('.product-image-main img');

        // Efecto de transición sutil
        $mainImage.css('opacity', '0.5');
        setTimeout(function () {
            $mainImage.attr('src', fullImageUrl);
            $mainAnchor.attr('href', fullImageUrl);
            $mainImage.css('opacity', '1');
        }, 150);

        // Opcional: Resaltar miniatura activa
        $('.thumbnail-card').removeClass('border-primary').addClass('border-light');
        $(this).closest('.thumbnail-card').removeClass('border-light').addClass('border-primary');
    });

    // Initialize Fancybox 5
    if (typeof Fancybox !== "undefined") {
        Fancybox.bind("[data-fancybox='gallery']", {
            Hash: false,
            Thumbs: {
                autoStart: true,
            },
        });
    }

    /**
     * --- LÓGICA DE FACTURACIÓN EN CARRITO (jQuery Version Refinada) ---
     */
    $(document).on('change', '#request_invoice', function () {
        var $extraFields = $('#extra_billing_fields');
        if ($(this).is(':checked')) {
            // Detenemos animaciones, activamos grid y deslizamos
            $extraFields.stop(true, true).addClass('is-active').hide().slideDown(400);
        } else {
            // Deslizamos y al terminar quitamos la clase activa
            $extraFields.stop(true, true).slideUp(400, function () {
                $(this).removeClass('is-active');
            });
        }
    });

    // Función para verificar el estado inicial
    function initBillingToggle() {
        var $checkbox = $('#request_invoice');
        var $extraFields = $('#extra_billing_fields');

        if ($checkbox.length && $extraFields.length) {
            if ($checkbox.is(':checked')) {
                $extraFields.addClass('is-active').show();
            } else {
                $extraFields.removeClass('is-active').hide();
            }
        }
    }

    // Ejecutar al cargar
    initBillingToggle();

    // Ejecutar cuando WooCommerce actualice los fragmentos del carrito
    $(document.body).on('updated_cart_totals', function () {
        initBillingToggle();
    });

    // ==========================================
    // PRODUCT COLLECTION CAROUSEL (SWIPER)
    // ==========================================
    if (typeof Swiper !== 'undefined' && $('.products-swiper').length > 0) {
        new Swiper('.products-swiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next-custom',
                prevEl: '.swiper-button-prev-custom',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                992: {
                    slidesPerView: 3,
                },
                1200: {
                    slidesPerView: 4,
                }
            }
        });
    }

    // ==========================================
    // INTERACTIVIDAD CHECKOUT BOUTIQUE
    // ==========================================

    // 1. Mostrar/Ocultar Dirección de Entrega
    $(document).on('click', '#toggle_address_fields', function (e) {
        e.preventDefault();
        $('#shipping_details_section').slideToggle(400);
        $(this).toggleClass('active');
    });

    // 2. Mostrar/Ocultar Campos de Facturación
    $(document).on('change', '#request_invoice_checkout', function () {
        if ($(this).is(':checked')) {
            $('#billing_details_section').slideDown(400);
            $('.invoice-field').show(); // Volvemos a la clase original
        } else {
            $('#billing_details_section').slideUp(400);
        }
    });

    // 3. Auto-marcar dirección si es recogida local (Mejora de UX)
    $(document.body).on('updated_checkout', function () {
        var isPickup = $('input[name^="shipping_method"]:checked').val() && $('input[name^="shipping_method"]:checked').val().indexOf('local_pickup') !== -1;
        if (isPickup) {
            // Si es recogida local, ocultamos el botón de cambiar dirección para no confundir
            $('#toggle_address_fields').parent().hide();
            $('#shipping_details_section').hide();
        } else {
            $('#toggle_address_fields').parent().show();
        }
    });

    // ==========================================
    // WOOCOMMERCE CHECKOUT AUTO-UPDATE
    // ==========================================
    // 1. Detectar cambios directos en los radios
    $(document.body).on('change', 'input[name^="shipping_method"]', function () {
        $(document.body).trigger('update_checkout');
    });

    // 2. Forzar marcado de radio y actualización al hacer clic en las tarjetas visuales
    $(document).on('click', '.shipping-options-section li, .shipping__list_item, .shipping-method-option', function (e) {
        var $radio = $(this).find('input[type="radio"]');

        if ($radio.length) {
            // Marcamos el radio
            $radio.prop('checked', true).trigger('change');

            // Forzar actualización visual de las tarjetas (clase activa)
            $('.shipping-options-section li, .shipping__list_item').removeClass('selected active');
            $(this).addClass('selected active');

            // Disparar actualización de WooCommerce explícitamente
            $(document.body).trigger('update_checkout');
        }
    });

    // ==========================================
    // PORTAL DE ENVÍO - MODAL CARRITO
    // ==========================================
    $(document).on('click', '#cartCalculateShipping', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $input = $('#cartPostalCode');
        var postcode = $input.val().trim();
        var $message = $('.cart-shipping-message');

        if (!postcode) {
            $message.html('<span class="text-danger">Por favor, ingresa un código postal.</span>');
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        $message.html('<span class="text-muted">Calculando envío...</span>');

        $.ajax({
            url: expotodo_globals.ajax_url,
            type: 'POST',
            data: {
                action: 'expotodo_calculate_shipping_ajax',
                postcode: postcode
            },
            success: function (response) {
                if (response.success) {
                    $message.html('<span class="text-success">' + (response.data.message || 'Calculado con éxito.') + '</span>');
                    // Actualizar valores de la barra lateral
                    if (response.data.subtotal) $('.cart-subtotal').html(response.data.subtotal);
                    if (response.data.shipping) $('.cart-shipping').html(response.data.shipping);
                    if (response.data.total) $('.cart-total').html(response.data.total);

                    // Disparar la actualización de fragmentos de WooCommerce para que todo se sincronice
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    $message.html('<span class="text-danger">' + (response.data.message || 'Error al calcular.') + '</span>');
                }
            },
            error: function () {
                $message.html('<span class="text-danger">Error de red. Intenta nuevamente.</span>');
            },
            complete: function () {
                $btn.prop('disabled', false).html('Calcular');
            }
        });
    });
});
