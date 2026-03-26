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
                                    <span class="wishlist-count">0</span>
                                </a>
                                <a href="#carrito" class="cart-icon position-relative no-smooth-scroll" title="Carrito" role="button">
                                    <i class="fas fa-shopping-bag"></i>
                                    <span class="cart-count">0</span>
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
            <div class="cart-items flex-grow-1"></div>
            <div class="cart-empty-message text-muted small">Tu carrito está vacío.</div>
            <div class="cart-summary mt-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span class="cart-subtotal">0,00€</span>
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
                    <span class="cart-total">0,00€</span>
                </div>
                <div class="mt-3 d-grid gap-2">
                    <a href="<?php echo home_url('/carrito'); ?>" class="btn btn-outline-primary">Ver Carrito</a>
                    <a href="<?php echo home_url('/checkout'); ?>" class="btn btn-primary">Proceder al Pago</a>
                </div>
            </div>
        </div>
    </div>

    <div class="right-sidebar" id="wishlistPanel">
        <div class="right-sidebar-header">
            <h5 class="right-sidebar-title">Lista de deseos</h5>
            <button type="button" class="close-sidebar-btn" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
            <div class="wishlist-items flex-grow-1"></div>
            <div class="wishlist-empty-message text-muted small">Tu lista de deseos está vacía.</div>
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
                    <a href="<?php echo home_url('/cuenta'); ?>" class="btn btn-primary w-100 mb-2">Ir a Mi Cuenta</a>
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
                    <a href="<?php echo home_url('/cuenta'); ?>" class="btn btn-outline-dark w-100">Crear cuenta</a>
                    <div class="mt-3 text-center">
                        <a href="<?php echo home_url('/cuenta'); ?>" class="text-decoration-none small">Ir a Configuración de Cuenta</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
