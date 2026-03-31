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
        ?>
            <div class="row g-4">
                <!-- Sidebar Navigation -->
                <div class="col-md-3">
                    <div class="list-group account-nav shadow-sm mb-4">
                        <a href="#perfil" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-id-card me-2"></i> Perfil Personal
                        </a>
                        <a href="#pedidos" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-shopping-bag me-2"></i> Mis Pedidos
                        </a>
                        <a href="#direcciones" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-map-marked-alt me-2"></i> Direcciones
                        </a>
                        <a href="#wishlist" class="list-group-item list-group-item-action" data-bs-toggle="list" id="tab-wishlist-link">
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
                    // Soporte para endpoints de WooCommerce
                    $endpoint = WC()->query->get_current_endpoint();
                    
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
                        <!-- Perfil -->
                        <div class="tab-pane fade show active" id="perfil">
                            <div class="card account-main-card">
                                <div class="account-header">
                                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Mi Información Personal</h5>
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
                                                <div class="profile-message mb-3 small"></div>
                                                <button type="button" id="btn-save-profile" class="btn btn-primary px-4 py-2 fw-bold" onclick="if(typeof expotodo_save_profile === 'function') { expotodo_save_profile(this, event); } else { alert('Procesando...'); }">
                                                    Actualizar Perfil <i class="fas fa-save ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Pedidos -->
                        <div class="tab-pane fade" id="pedidos">
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
                        <div class="tab-pane fade" id="direcciones">
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
                        <div class="tab-pane fade" id="wishlist">
                            <div class="card account-main-card">
                                <div class="account-header">
                                    <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Mi Lista de Deseos</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="wishlist-page-container">
                                        <?php 
                                        $debug_items = expotodo_get_user_wishlist();
                                        echo "<!-- DEBUG ITEMS: " . print_r($debug_items, true) . " -->"; 
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
                        const urlParams = new URLSearchParams(window.location.search);
                        const tab = urlParams.get('tab');
                        if (tab === 'wishlist') {
                            const wishlistTab = document.querySelector('#tab-wishlist-link');
                            if (wishlistTab) {
                                bootstrap.Tab.getOrCreateInstance(wishlistTab).show();
                            }
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

<!-- Sección de Productos Destacados -->
<section class="featured-section py-5" id="coleccion">
    <div class="container">
        <h2 class="section-title text-center mb-5">Nuestra Colección</h2>
        
        <div class="row g-4 mb-5">
            <?php
            // Consulta de productos destacados aleatorios de WooCommerce
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 4,
                'orderby'        => 'rand',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => 'featured',
                    ),
                ),
            );

            $featured_products = new WP_Query($args);

            if ($featured_products->have_posts()) :
                while ($featured_products->have_posts()) : $featured_products->the_post();
                    global $product;
                    $product_id = get_the_ID();
                    $product_name = get_the_title();
                    $product_price = $product->get_price();
                    
                    // Obtener categoría principal
                    $terms = get_the_terms($product_id, 'product_cat');
                    $category_name = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : 'Producto';
                    
                    // Obtener imagen
                    $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url($product_id, 'large') : 'https://via.placeholder.com/400';
            ?>
            <div class="col-md-3">
                <article class="product-card h-100" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-name="<?php echo esc_attr($product_name); ?>" data-product-price="<?php echo esc_attr($product_price); ?>">
                    <div class="product-image-container">
                        <div class="product-category"><?php echo esc_html($category_name); ?></div>
                        <?php if ($product->is_on_sale()) : ?>
                            <div class="product-category sale" style="top: 40px; background-color: #dc3545;">Oferta</div>
                        <?php endif; ?>
                        <button type="button" class="btn-add-wishlist" data-id="<?php echo $product_id; ?>" title="Agregar a lista de deseos">
                            <i class="far fa-heart <?php echo in_array($product_id, expotodo_get_user_wishlist()) ? 'fas text-danger' : 'far'; ?>"></i>
                        </button>
                        <img src="<?php echo esc_url($image_url); ?>" 
                             class="product-image" 
                             alt="<?php echo esc_attr($product_name); ?>">
                    </div>
                    <div class="product-content p-3">
                        <h3 class="product-title"><?php echo esc_html($product_name); ?></h3>
                        <p class="product-description">
                            <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                        </p>
                        <div class="product-price mb-3">
                            <?php echo $product->get_price_html(); ?>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                             <a href="<?php echo get_permalink(); ?>" class="btn-card btn-primary flex-grow-1">
                                <i class="fas fa-eye me-2"></i> Ver
                            </a>
                             <?php if ( $product->is_type('variable') ) : ?>
                                 <a href="<?php echo get_permalink(); ?>" class="btn-card btn-primary flex-grow-1">
                                     <i class="fas fa-eye me-2"></i> Opciones
                                 </a>
                             <?php else : ?>
                                 <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn-card btn-primary ajax_add_to_cart flex-grow-1" data-quantity="1" data-product_id="<?php echo get_the_ID(); ?>" aria-label="Agregar “<?php the_title_attribute(); ?>” al carrito">
                                     <i class="fas fa-shopping-cart me-2"></i> Agregar
                                 </a>
                             <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<div class="col-12 text-center"><p>No hay productos destacados disponibles en este momento.</p></div>';
            endif;
            ?>
        </div>
        
        <div class="text-center">
            <a href="<?php echo home_url('/productos'); ?>" class="btn btn-outline-dark btn-lg px-5">
                Ver Catálogo Completo
            </a>
        </div>
    </div>
</section>


<?php get_footer(); ?>
