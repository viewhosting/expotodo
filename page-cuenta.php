<?php
/* Template Name: Cuenta */
get_header();
?>

<!-- Main Content -->
<main class="py-5 bg-light">
    <div class="container">
        <h1 class="mb-4">Mi Cuenta</h1>
        
        <?php if ( ! is_user_logged_in() ) : ?>
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card account-main-card">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-user-circle fa-4x text-primary opacity-25 mb-3"></i>
                                <h4 class="fw-bold">Bienvenido de nuevo</h4>
                                <p class="text-muted small">Ingresa tus credenciales para acceder</p>
                            </div>
                            <form id="main-login-form" class="account-form">
                                <div class="mb-3">
                                    <label class="form-label">Correo electrónico o Usuario</label>
                                    <input type="text" class="form-control" name="username" placeholder="ejemplo@correo.com" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                                </div>
                                <div class="login-message mb-3 small text-danger"></div>
                                <button type="button" id="btn-main-login-submit" class="btn btn-primary w-100 py-2 fw-bold" onclick="expotodo_handle_login(this, event)">Acceder a mi cuenta</button>
                                <div class="text-center mt-3">
                                    <a href="<?php echo wp_lostpassword_url(); ?>" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else : 
            $current_user = wp_get_current_user();
            $endpoint = WC()->query->get_current_endpoint();
            $is_endpoint = !empty($endpoint);
            $base_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : get_permalink();
        ?>
            <div class="row g-4">
                <!-- Sidebar Navigation -->
                <div class="col-md-3">
                    <div class="list-group account-nav shadow-sm mb-4 sticky-top" id="account-tabs-menu" role="tablist" style="top: 100px; z-index: 100;">
                        <a href="<?php echo $is_endpoint ? $base_url . '#dashboard' : '#dashboard'; ?>" 
                           class="list-group-item list-group-item-action <?php echo (!$is_endpoint) ? 'active' : ''; ?>" 
                           <?php echo (!$is_endpoint) ? 'data-bs-toggle="list"' : ''; ?>
                           id="tab-dashboard-link" role="tab" aria-controls="dashboard" aria-selected="true">
                            <i class="fas fa-th-large me-2"></i> Escritorio
                        </a>
                        <a href="<?php echo $is_endpoint ? $base_url . '#perfil' : '#perfil'; ?>" 
                           class="list-group-item list-group-item-action" 
                           <?php echo (!$is_endpoint) ? 'data-bs-toggle="list"' : ''; ?>
                           id="tab-perfil-link" role="tab" aria-controls="perfil" aria-selected="false">
                            <i class="fas fa-user-edit me-2"></i> Mi Perfil
                        </a>
                        <a href="<?php echo $is_endpoint ? $base_url . '#pedidos' : '#pedidos'; ?>" 
                           class="list-group-item list-group-item-action <?php echo ($endpoint === 'view-order') ? 'active' : ''; ?>" 
                           <?php echo (!$is_endpoint) ? 'data-bs-toggle="list"' : ''; ?>
                           id="tab-pedidos-link" role="tab" aria-controls="pedidos" aria-selected="false">
                            <i class="fas fa-shopping-bag me-2"></i> Mis Pedidos
                        </a>
                        <a href="<?php echo $is_endpoint ? $base_url . '#direcciones' : '#direcciones'; ?>" 
                           class="list-group-item list-group-item-action <?php echo ($endpoint === 'edit-address') ? 'active' : ''; ?>" 
                           <?php echo (!$is_endpoint) ? 'data-bs-toggle="list"' : ''; ?>
                           id="tab-direcciones-link" role="tab" aria-controls="direcciones" aria-selected="false">
                            <i class="fas fa-map-marked-alt me-2"></i> Direcciones
                        </a>
                        <a href="<?php echo $is_endpoint ? $base_url . '#wishlist' : '#wishlist'; ?>" 
                           class="list-group-item list-group-item-action" 
                           <?php echo (!$is_endpoint) ? 'data-bs-toggle="list"' : ''; ?>
                           id="tab-wishlist-link" role="tab" aria-controls="wishlist" aria-selected="false">
                            <i class="fas fa-heart me-2"></i> Mi Wishlist
                        </a>
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="list-group-item list-group-item-action text-danger mt-2 border-top">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="col-md-9">
                    <?php 
                    if ( $endpoint && $endpoint === 'view-order' ) {
                        $order_id = get_query_var( 'view-order' );
                        if ( $order_id ) {
                            woocommerce_account_view_order( $order_id );
                        }
                    } elseif ( $endpoint && $endpoint === 'edit-address' ) {
                        $address_type = get_query_var( 'edit-address' );
                        woocommerce_account_edit_address( $address_type );
                    } else {
                    ?>
                    <div class="tab-content">
                        <!-- Dashboard -->
                        <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="tab-dashboard-link">
                            <?php echo expotodo_render_account_dashboard(get_current_user_id()); ?>
                        </div>

                        <!-- Perfil -->
                        <div class="tab-pane fade" id="perfil" role="tabpanel" aria-labelledby="tab-perfil-link">
                            <div class="card account-main-card">
                                <div class="account-header">
                                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Información Personal</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form id="profile-form" class="account-form">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" name="first_name" class="form-control" value="<?php echo esc_attr($current_user->first_name); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Apellidos</label>
                                                <input type="text" name="last_name" class="form-control" value="<?php echo esc_attr($current_user->last_name); ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Correo Electrónico (No modificable)</label>
                                                <input type="email" class="form-control bg-light" value="<?php echo esc_attr($current_user->user_email); ?>" readonly disabled>
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="button" class="btn btn-primary px-4 py-2 fw-bold" onclick="expotodo_save_profile_boutique(this, event)">
                                                    Actualizar Perfil <i class="fas fa-save ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <hr class="my-5">

                                    <!-- Sección de Seguridad -->
                                    <div class="password-section">
                                        <h4 class="password-section-title"><i class="fas fa-shield-alt me-2 text-primary"></i>Seguridad y Contraseña</h4>
                                        <p class="text-muted small mb-4">Recomendamos usar una contraseña fuerte que no utilices en otros sitios.</p>
                                        
                                        <form id="password-form" class="account-form">
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <label class="form-label">Nueva Contraseña</label>
                                                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Confirmar Contraseña</label>
                                                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••">
                                                </div>
                                                <div class="col-12 text-end">
                                                    <button type="button" class="btn btn-outline-dark px-4 py-2 fw-bold" onclick="expotodo_change_password_boutique(this, event)">
                                                        Cambiar Contraseña <i class="fas fa-key ms-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pedidos -->
                        <div class="tab-pane fade" id="pedidos" role="tabpanel" aria-labelledby="tab-pedidos-link">
                            <div class="card account-main-card">
                                <div class="account-header">
                                    <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Historial de Pedidos</h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php
                                    // Get user orders
                                    $customer_orders = get_posts( array(
                                        'numberposts' => 15,
                                        'meta_key'    => '_customer_user',
                                        'meta_value'  => get_current_user_id(),
                                        'post_type'   => 'shop_order',
                                        'post_status' => array_keys( wc_get_order_statuses() ),
                                    ) );
                                    
                                    if ( ! $customer_orders ) :
                                    ?>
                                        <div class="p-5 text-center">
                                            <i class="fas fa-box-open fa-3x mb-3 text-muted opacity-50"></i>
                                            <p class="text-muted">Aún no has realizado ningún pedido.</p>
                                            <a href="<?php echo home_url('/productos'); ?>" class="btn btn-outline-primary btn-sm mt-2">Explorar Productos</a>
                                        </div>
                                    <?php else : ?>
                                        <!-- Header de la cuadrícula (Desktop) -->
                                        <div class="order-grid-header d-none d-md-flex mx-3 mt-3">
                                            <div class="col-2">Pedido</div>
                                            <div class="col-3">Fecha de compra</div>
                                            <div class="col-3">Estado actual</div>
                                            <div class="col-2">Total</div>
                                            <div class="col-2 text-end">Detalles</div>
                                        </div>

                                        <div class="order-list">
                                            <?php foreach ( $customer_orders as $customer_order ) : 
                                                $order = wc_get_order( $customer_order->ID );
                                                $status = $order->get_status();
                                            ?>
                                            <div class="order-row d-flex flex-wrap">
                                                <div class="col-12 col-md-2 mb-2 mb-md-0">
                                                    <span class="d-md-none fw-bold text-muted small me-2">PEDIDO:</span>
                                                    <span class="order-number">#<?php echo $order->get_order_number(); ?></span>
                                                </div>
                                                <div class="col-12 col-md-3 mb-2 mb-md-0">
                                                    <span class="d-md-none fw-bold text-muted small me-2">FECHA:</span>
                                                    <span class="order-date"><?php echo $order->get_date_created()->date('d M, Y'); ?></span>
                                                </div>
                                                <div class="col-12 col-md-3 mb-2 mb-md-0">
                                                    <span class="d-md-none fw-bold text-muted small me-2">ESTADO:</span>
                                                    <span class="status-badge status-<?php echo esc_attr( $status ); ?>">
                                                        <?php echo wc_get_order_status_name( $status ); ?>
                                                    </span>
                                                </div>
                                                <div class="col-12 col-md-2 mb-3 mb-md-0">
                                                    <span class="d-md-none fw-bold text-muted small me-2">TOTAL:</span>
                                                    <span class="order-total"><?php echo $order->get_formatted_order_total(); ?></span>
                                                </div>
                                                <div class="col-12 col-md-2 text-md-end order-actions">
                                                    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="btn btn-sm btn-primary px-3 rounded-pill">
                                                        Ver pedido <i class="fas fa-chevron-right ms-1 small"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Direcciones -->
                        <div class="tab-pane fade" id="direcciones" role="tabpanel" aria-labelledby="tab-direcciones-link">
                            <div class="card account-main-card">
                                <div class="account-header">
                                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Mis Direcciones Registradas</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="address-card">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h6 class="mb-0"><i class="fas fa-file-invoice text-primary"></i> Facturación</h6>
                                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 btn-edit-address" data-address-type="billing">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                </div>
                                                <address class="address-content mb-0">
                                                    <?php 
                                                        $address = wc_get_account_formatted_address( 'billing' ); 
                                                        echo $address ? $address : '<span class="text-muted italic">No has configurado tu dirección de facturación.</span>';
                                                    ?>
                                                </address>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="address-card">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h6 class="mb-0"><i class="fas fa-truck text-primary"></i> Envío</h6>
                                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 btn-edit-address" data-address-type="shipping">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                </div>
                                                <address class="address-content mb-0">
                                                    <?php 
                                                        $address = wc_get_account_formatted_address( 'shipping' ); 
                                                        echo $address ? $address : '<span class="text-muted italic">No has configurado tu dirección de envío.</span>';
                                                    ?>
                                                </address>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Lista de Deseos (Wishlist) -->
                        <div class="tab-pane fade" id="wishlist" role="tabpanel" aria-labelledby="tab-wishlist-link">
                            <div class="card account-main-card">
                                <div class="account-header">
                                    <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Mi Lista de Deseos</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="wishlist-page-container">
                                        <?php 
                                        echo expotodo_get_wishlist_items_html(0, 'grid'); 
                                        ?>
                                    </div>
                                    <?php if (empty(expotodo_get_user_wishlist())) : ?>
                                        <div class="p-5 text-center">
                                            <i class="far fa-heart fa-3x text-muted mb-3 opacity-25"></i>
                                            <p class="text-muted">Aún no tienes productos en tu lista de deseos.</p>
                                            <a href="<?php echo home_url('/productos'); ?>" class="btn btn-primary">Explorar Productos</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Función segura para mostrar pestañas
                        function safeShowTab(tabId) {
                            const tabLink = document.querySelector(tabId);
                            if (tabLink && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                                bootstrap.Tab.getOrCreateInstance(tabLink).show();
                            } else {
                                // Reintento si bootstrap aún no está listo (footer delay)
                                setTimeout(() => {
                                    if (tabLink && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                                        bootstrap.Tab.getOrCreateInstance(tabLink).show();
                                    }
                                }, 500);
                            }
                        }

                        // 1. Manejo por parámetros (Legacy/Wishlist)
                        const urlParams = new URLSearchParams(window.location.search);
                        const tabParam = urlParams.get('tab');
                        if (tabParam === 'wishlist') {
                            safeShowTab('#tab-wishlist-link');
                        }

                        // 2. Manejo por Hash (Navegación desde endpoints)
                        const hash = window.location.hash;
                        if (hash) {
                            const hashTab = `#tab-${hash.replace('#', '')}-link`;
                            safeShowTab(hashTab);
                        }
                    });
                    </script>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para Edición de Direcciones AJAX -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 bg-light" style="border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title fw-bold" id="addressModalLabel">Editar Dirección</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="address-modal-container">
                    <!-- El contenido se cargará vía AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando formulario...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
// Renderizar la colección usando el nuevo controlador centralizado
echo expotodo_render_collection(array(
    'title'          => 'Nuestra Colección',
    'posts_per_page' => 4,
    'type'           => 'featured', 
    'orderby'        => 'rand',
)); 
?>


<?php get_footer(); ?>
