jQuery(document).ready(function ($) {
    // Header Scroll Effect
    const header = document.querySelector('.main-header');
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
    // AUTO-HIDE WOOCOMMERCE MESSAGES
    // ==========================================
    function autoHideWoocommerceMessages() {
        // Buscamos mensajes de éxito, información y errores
        const selectors = '.woocommerce-message, .woocommerce-info, .woocommerce-error';
        const messages = document.querySelectorAll(selectors);

        messages.forEach(message => {
            // Evitar duplicar el timeout si ya se aplicó
            if (message.dataset.timeoutApplied) return;
            message.dataset.timeoutApplied = 'true';

            setTimeout(() => {
                $(message).fadeOut(600, function () {
                    $(this).remove();
                });
            }, 5000); // 5 segundos
        });
    }

    // Ejecutar para los mensajes que ya vienen en el HTML de carga inicial
    autoHideWoocommerceMessages();

    // Observador para detectar mensajes inyectados por AJAX (ej. al añadir al carrito)
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.addedNodes.length) {
                // Si se añade algún nodo, revisamos si es o contiene un mensaje
                autoHideWoocommerceMessages();
            }
        });
    });

    // Empezamos a observar el body en busca de cambios en la lista de hijos
    observer.observe(document.body, { childList: true, subtree: true });

    // Mobile Navigation
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function (e) {
            e.preventDefault();
            navbarCollapse.classList.add('sidebar-open');
            if (sidebarOverlay) sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Close nav on link click (mobile)
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 992) {
                    navbarCollapse.classList.remove('sidebar-open');
                    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    }

    // Sidebar Management
    const rightSidebars = document.querySelectorAll('.right-sidebar');
    const closeSidebarButtons = document.querySelectorAll('.close-sidebar-btn');

    function openRightSidebar(panelId) {
        rightSidebars.forEach(sidebar => sidebar.classList.remove('open'));
        if (navbarCollapse && navbarCollapse.classList.contains('sidebar-open')) {
            navbarCollapse.classList.remove('sidebar-open');
        }

        const panel = document.getElementById(panelId);
        if (panel) {
            panel.classList.add('open');
            if (sidebarOverlay) sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeRightSidebars() {
        rightSidebars.forEach(sidebar => sidebar.classList.remove('open'));
        const isNavbarOpen = navbarCollapse && navbarCollapse.classList.contains('sidebar-open');
        if (sidebarOverlay && !isNavbarOpen) {
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
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
            closeRightSidebars();
            if (navbarCollapse) navbarCollapse.classList.remove('sidebar-open');
        });
    }

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeRightSidebars();
    });

    // ==========================================
    // CHECKOUT SIDEBAR LOGIC
    // ==========================================

    let checkoutModal = null;

    // Abrir modal de checkout desde cualquier botón de "Finalizar Compra"
    $(document).on('click', '.btn-checkout-modal, .checkout-button', function (e) {
        e.preventDefault();

        // Inicialización perezosa del modal
        if (!checkoutModal) {
            const checkoutModalEl = document.getElementById('checkoutPanel');
            if (checkoutModalEl && typeof bootstrap !== 'undefined') {
                checkoutModal = new bootstrap.Modal(checkoutModalEl);
            }
        }

        if (checkoutModal) {
            checkoutModal.show();
        }

        const $container = $('#payment-gateways-container');
        const $btnPlaceOrder = $('#btn-place-order');
        const $errorContainer = $('#checkout-errors');

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
                    const $initialChecked = $container.find('input[name="payment_method"]:checked');
                    if ($initialChecked.length > 0) {
                        $initialChecked.closest('.wc_payment_method').find('.payment_box').show();
                        $btnPlaceOrder.prop('disabled', false);
                    }

                    // Notificar a WooCommerce que el checkout se ha actualizado
                    $(document.body).trigger('updated_checkout');

                    // Listener para selección de método
                    $container.find('input[name="payment_method"]').on('change', function () {
                        const $parent = $(this).closest('.wc_payment_method');
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
            const $extraFields = $('#billing-different-fields');
            if (!$(this).is(':checked')) {
                $extraFields.slideDown();
            } else {
                $extraFields.slideUp();
                // Limpiar campos si se vuelve a la opción por defecto
                $extraFields.find('input').val('');
            }
        });
    });

    // Acción del botón de Pagar Ahora (Sumisión AJAX real con serialización completa)
    $('#btn-place-order').on('click', function (e) {
        e.preventDefault();
        const selectedMethod = $('input[name="payment_method"]:checked').val();

        if (!selectedMethod) {
            alert('Por favor, selecciona un método de pago.');
            return;
        }

        const $btn = $(this);
        const originalHtml = $btn.html();
        const $errorContainer = $('#checkout-errors');
        const $form = $('form.woocommerce-checkout');

        // Limpiar errores previos
        $errorContainer.empty();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Procesando...');

        // Recopilar TODOS los datos del formulario (incluyendo campos de pasarela y tokens ocultos)
        let checkoutData = $form.serializeArray();

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
                    let data = response;
                    if (typeof response === 'string') {
                        const jsonPos = response.indexOf('{"result"');
                        if (jsonPos > -1) {
                            data = JSON.parse(response.substring(jsonPos));
                        }
                    }

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
    let searchTimeout = null;
    const $searchInput = $('#productoBusqueda');
    const $searchResults = $('#search-results-list');

    if ($searchInput.length && $searchResults.length) {
        $searchInput.on('input', function () {
            const query = $(this).val().trim();

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
});
