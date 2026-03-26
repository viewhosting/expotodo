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
    // SERVER-SIDE CART IMPLEMENTATION
    // ==========================================

    // Add classes to single product add to cart button
    $('button.single_add_to_cart_button').addClass('btn-card btn-primary mt-2 ajax_add_to_cart');

    // ==========================================
    // PRODUCT DETAILS LAYOUT LOGIC
    // ==========================================

    // Mover el contenedor de añadir al carrito a la barra lateral (sticky panel)
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

    // Hack para el botón de submit fuera del form
    $(document).on('click', '.single_add_to_cart_button', function (e) {
        var $btn = $(this);
        var $form = $('.variations_form');

        // Si el botón está fuera del form (que lo está ahora), enviamos el form manualmente
        if ($btn.closest('form').length === 0 && $form.length) {
            e.preventDefault();

            // Verificar validez HTML5 si es posible
            if ($form[0].checkValidity && !$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }

            // Crear un input hidden con el valor del botón (add-to-cart)
            // WooCommerce busca este valor para procesar
            if (!$form.find('input[name="add-to-cart"]').length) {
                $form.append('<input type="hidden" name="add-to-cart" value="' + $btn.val() + '" />');
            }

            // También necesitamos product_id si es simple, o variation_id si es variable
            // El form ya los tiene.

            $form.submit();
        }
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
