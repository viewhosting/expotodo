<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div class="sidebar-overlay"></div>
    <!-- Header con Bootstrap Navbar -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand logo" href="<?php echo home_url(); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="<?php bloginfo('name'); ?>" class="logo-image">
                </a>
                
                <!-- Botón hamburguesa para móvil -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Contenido del menú -->
                <div class="collapse navbar-collapse" id="navbarMain">
                    <!-- Menú de navegación -->
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                        <li class="nav-item pt-3">
                            <a class="nav-link" href="<?php echo home_url(); ?>">Principal</a>
                        </li>
                        <li class="nav-item pt-3">
                            <a class="nav-link" href="<?php echo home_url('/productos'); ?>">Productos</a>
                        </li>
                        <li class="nav-item pt-3">
                            <a class="nav-link" href="<?php echo home_url('/catalogo'); ?>">Catálogo</a>
                        </li>
                        <li class="nav-item pt-3">
                            <a class="nav-link" href="<?php echo home_url('/#mercado-libre'); ?>">Mercado Libre</a>
                        </li>
                        <li class="nav-item pt-3">
                            <a class="nav-link" href="<?php echo home_url('/#amazon'); ?>">Amazon</a>
                        </li>
                        <li class="nav-item pt-3">
                            <a class="nav-link" href="<?php echo home_url('/contacto'); ?>">Contacto</a>
                        </li>
                        
                        <!-- Teléfono -->
                        <li class="nav-item menu-item-hotline">
                            <div class="d-flex align-items-center h-100">
                                <i class="fas fa-phone icon-telephone"></i>
                                <div class="ms-2 hotline-content">
                                    <label class="mb-0">LLAMA AHORA</label>
                                    <span>(55) 5510 1477</span>
                                </div>
                            </div>
                        </li>
                        
                        <!-- Acciones -->
                        <li class="nav-item header-actions">
                            <div class="d-flex align-items-center gap-3">
                                <a href="<?php echo home_url('/buscar'); ?>" class="buscar-icon no-smooth-scroll" title="Buscar" role="button">
                                    <i class="fas fa-search"></i>
                                </a>
                                <a href="<?php echo home_url('/cuenta'); ?>" class="account-icon no-smooth-scroll" title="Mi cuenta" role="button">
                                    <i class="fas fa-user"></i>
                                </a>
                                <a href="#wishlist" class="wishlist-icon position-relative no-smooth-scroll" title="Lista de deseos" role="button">
                                    <i class="fas fa-heart"></i>
                                    <span class="wishlist-count"><?php echo count(expotodo_get_user_wishlist()); ?></span>
                                </a>
                                <a href="#carrito" class="cart-icon position-relative no-smooth-scroll" title="Carrito" role="button">
                                    <i class="fas fa-shopping-bag"></i>
                                    <span class="cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : '0'; ?></span>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Sidebars -->

    <div class="right-sidebar" id="buscarPanel">
        <div class="right-sidebar-header">
            <h5 class="right-sidebar-title">Tú búsqueda</h5>
            <button type="button" class="close-sidebar-btn" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
            <div class="mb-12">
                    <label for="productoBusqueda" class="form-label mb-1">Puedes buscar por modelo</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="productoBusqueda" placeholder="Ej. A-13">
                    </div>
                    <div class="mt-1 small text-muted cart-shipping-message"></div>
                </div>
            <div id="search-results-list" class="search-results-list flex-grow-1 overflow-auto"></div>
            <div class="cart-summary mt-4">
                <div class="mt-3 d-grid gap-2">
                    <a href="<?php echo home_url('/buscar'); ?>" class="btn btn-primary">Buscar</a>
                </div>
            </div>
        </div>
    </div>


    <div class="right-sidebar" id="cartPanel">
        <div class="right-sidebar-header">
            <h5 class="right-sidebar-title">Tu carrito</h5>
            <button type="button" class="close-sidebar-btn" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
            <?php echo expotodo_get_cart_items_html(); ?>
            <div class="cart-empty-message text-muted small <?php echo (WC()->cart && WC()->cart->is_empty()) ? '' : 'd-none'; ?>">
                Tu carrito está vacío.
            </div>
            <div class="cart-summary mt-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span class="cart-subtotal"><?php echo WC()->cart ? WC()->cart->get_cart_subtotal() : '0,00€'; ?></span>
                </div>
                <div class="mb-3">
                    <label for="cartPostalCode" class="form-label mb-1">Código postal para envío</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="cartPostalCode" placeholder="Ej. 01000">
                        <button class="btn btn-outline-dark" type="button" id="cartCalculateShipping">Calcular</button>
                    </div>
                    <div class="mt-1 small text-muted cart-shipping-message"></div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Envío</span>
                    <span class="cart-shipping">—</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span class="cart-total"><?php echo WC()->cart ? WC()->cart->get_total() : '0,00€'; ?></span>
                </div>
                <div class="mt-3 d-grid gap-2">
                    <!-- Redirigido directo al Checkout oficial para permitir scripts de Mercado Pago -->
                    <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn-outline-primary">Ver Carrito</a>
                    <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn-primary">Finalizar Compra</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 
    MODAL DE CHECKOUT DESACTIVADO
    =============================
    Razón: Para que el SDK de Javascript de Mercado Pago (y otras pasarelas) 
    pueda inyectar su Pop-Up nativo (Checkout Pro) y procesar tokens, es obligatorio
    que el flujo de pago se realice en la página oficial `/checkout/`. 
    Al cargar los métodos de pago via AJAX en este modal, el SDK de MP no se lograba 
    enganchar a los botones, forzando un fallo o redirección oculta.
    -->
    <!-- <div class="modal fade" id="checkoutPanel" tabindex="-1" aria-labelledby="checkoutPanelLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-bottom-0 p-4 pb-0">
                    <h5 class="modal-title h4 fw-bold" id="checkoutPanelLabel">Finalizar Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-1">
                    <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
                        <div class="checkout-summary mb-4 p-3 bg-light rounded-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small text-uppercase fw-semibold letter-spacing-1">Total a pagar:</span>
                                <span class="cart-total h3 fw-bold mb-0 text-primary">
                                    <?php echo WC()->cart ? WC()->cart->get_total() : '0,00€'; ?>
                                </span>
                            </div>
                        </div>

                        <div id="checkout-errors" class="mb-3"></div>

              
                        <div class="customer-details-section mb-4">
                            <h6 class="fw-bold small text-uppercase mb-3 letter-spacing-1 border-bottom pb-2">Datos de Envío</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" name="billing_first_name" class="form-control form-control-sm" placeholder="Nombre *" value="<?php echo WC()->customer ? WC()->customer->get_billing_first_name() : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="billing_last_name" class="form-control form-control-sm" placeholder="Apellidos *" value="<?php echo WC()->customer ? WC()->customer->get_billing_last_name() : ''; ?>">
                                </div>
                                <div class="col-12">
                                    <input type="text" name="billing_address_1" class="form-control form-control-sm" placeholder="Dirección (Calle y Número) *" value="<?php echo WC()->customer ? WC()->customer->get_billing_address_1() : ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="billing_city" class="form-control form-control-sm" placeholder="Ciudad *" value="<?php echo WC()->customer ? WC()->customer->get_billing_city() : ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="billing_state" class="form-control form-control-sm" placeholder="Estado *" value="<?php echo WC()->customer ? WC()->customer->get_billing_state() : ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="billing_postcode" class="form-control form-control-sm" placeholder="C.P. *" value="<?php echo WC()->customer ? WC()->customer->get_billing_postcode() : ''; ?>">
                                </div>
                                <div class="col-8">
                                    <input type="email" name="billing_email" class="form-control form-control-sm" placeholder="Email *" value="<?php echo WC()->customer ? WC()->customer->get_billing_email() : ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="billing_phone" class="form-control form-control-sm" placeholder="Teléfono *" value="<?php echo WC()->customer ? WC()->customer->get_billing_phone() : ''; ?>">
                                </div>
                                <div class="col-12 mt-1 d-none">
                                    <select name="billing_country" class="form-select form-select-sm">
                                        <option value="MX" selected>México</option>
                                        <?php 
                                            $countries = WC()->countries->get_allowed_countries();
                                            foreach($countries as $code => $name) {
                                                if($code !== 'MX') echo '<option value="'.$code.'">'.$name.'</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input-checkbox" type="checkbox" name="use_shipping_for_billing" id="use-shipping-for-billing" checked>
                                <label class="form-check-label-checkbox small fw-bold" for="use-shipping-for-billing">
                                    Usar datos de envío para facturación
                                </label>
                            </div>

 
                            <div id="billing-different-fields" class="mt-3 p-3 bg-light rounded-3" style="display:none; border: 1px dashed #ddd;">
                                <h6 class="fw-bold small text-uppercase mb-3 letter-spacing-1">Datos de Facturación Diferentes</h6>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="text" name="shipping_address_1" class="form-control form-control-sm" placeholder="Dirección de Facturación">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="shipping_city" class="form-control form-control-sm" placeholder="Ciudad">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="shipping_postcode" class="form-control form-control-sm" placeholder="C.P.">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="small text-muted"><i class="fas fa-info-circle me-1"></i> Estos datos se utilizarán para la factura oficial.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold small text-uppercase mb-3 letter-spacing-1 border-bottom pb-2">Método de Pago</h6>
                            <div id="payment-gateways-container" class="payment-gateways-list" style="max-height: 250px; overflow-y: auto;">
                                <div class="text-center py-5">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="ms-2">Cargando métodos de pago...</span>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold text-uppercase h5 mb-3" id="btn-place-order" disabled style="border-radius: 12px; font-size: 1.1rem;">
                                Pagar Ahora
                            </button>
                            <p class="text-muted small text-center mb-0">
                                <i class="fas fa-lock me-1"></i> Transacción 100% segura procesada por pasarelas certificadas.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> -->

    <div class="right-sidebar" id="wishlistPanel">
        <div class="right-sidebar-header">
            <h5 class="right-sidebar-title">Lista de deseos</h5>
            <button type="button" class="close-sidebar-btn" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
            <div id="wishlist-items-container" class="wishlist-items flex-grow-1 overflow-auto">
                <?php echo expotodo_get_wishlist_items_html(); ?>
            </div>
            <div class="wishlist-empty-message text-muted small <?php echo count(expotodo_get_user_wishlist()) > 0 ? 'd-none' : ''; ?>">
                Tu lista de deseos está vacía.
            </div>
            <div class="wishlist-summary p-3 bg-light border-top mt-auto">
                <a href="<?php echo home_url('/my-account/?tab=wishlist'); ?>" class="btn btn-primary btn-sm w-100">Ver Lista Completa</a>
            </div>
        </div>
    </div>

    <div class="right-sidebar" id="accountPanel">
        <div class="right-sidebar-header">
            <h5 class="right-sidebar-title">Mi cuenta</h5>
            <button type="button" class="close-sidebar-btn" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="account-content">
            <?php if ( is_user_logged_in() ) : ?>
                <div class="text-center p-3">
                    <p class="mb-3">Hola, <strong><?php echo wp_get_current_user()->display_name; ?></strong></p>
                    <a href="<?php echo home_url('/my-account'); ?>" class="btn btn-primary w-100 mb-2">Ir a Mi Cuenta</a>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline-danger w-100">Cerrar Sesión</a>
                </div>
            <?php else : ?>
                <form class="account-form" id="login-form">
                    <div class="mb-3">
                        <label class="form-label" for="login-username">Correo electrónico o Usuario1</label>
                        <input type="text" class="form-control" id="login-username" name="username" placeholder="tu@correo.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="login-password">Contraseña</label>
                        <input type="password" class="form-control" id="login-password" name="password" placeholder="••••••••" required>
                    </div>
                    <div id="login-message" class="mb-3 small text-danger"></div>
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btn-login-submit" onclick="expotodo_handle_login(this, event)">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                        Iniciar sesión
                    </button>
                    <a href="<?php echo home_url('/my-account'); ?>" class="btn btn-outline-dark w-100">Crear cuenta</a>
                    <div class="mt-3 text-center">
                        <a href="<?php echo home_url('/my-account'); ?>" class="text-decoration-none small">Ir a Configuración de Cuenta</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenedor Único para Toasts Dinámicos de WooCommerce -->
    <div id="expotodo-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1070;">
        <!-- Los Toasts reales se inyectarán aquí vía JavaScript (script.js) -->
    </div>
