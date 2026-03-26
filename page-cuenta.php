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
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="card-title mb-4 text-center">Iniciar Sesión</h4>
                            <form id="main-login-form" class="account-form">
                                <div class="mb-3">
                                    <label class="form-label">Correo electrónico o Usuario</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="login-message mb-3 small text-danger"></div>
                                <button type="button" id="btn-main-login-submit" class="btn btn-primary w-100 mb-3" onclick="expotodo_handle_login(this, event)">Iniciar sesión</button>
                                <div class="text-center">
                                    <a href="<?php echo wp_lostpassword_url(); ?>" class="text-decoration-none small">¿Olvidaste tu contraseña?</a>
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
                    <div class="list-group shadow-sm">
                        <a href="#perfil" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-user me-2"></i> Perfil
                        </a>
                        <a href="#pedidos" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-box me-2"></i> Mis Pedidos
                        </a>
                        <a href="#direcciones" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-map-marker-alt me-2"></i> Direcciones
                        </a>
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="col-md-9">
                    <div class="tab-content">
                        <!-- Perfil -->
                        <div class="tab-pane fade show active" id="perfil">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Información Personal</h5>
                                </div>
                                <div class="card-body">
                                    <form id="profile-form" class="account-form">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" name="first_name" class="form-control" value="<?php echo esc_attr($current_user->first_name); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Apellidos</label>
                                                <input type="text" name="last_name" class="form-control" value="<?php echo esc_attr($current_user->last_name); ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="<?php echo esc_attr($current_user->user_email); ?>" readonly disabled>
                                            </div>
                                            <div class="col-12 mt-4">
                                                <div class="profile-message mb-3 small"></div>
                                                <button type="button" id="btn-save-profile" class="btn btn-primary" onclick="if(typeof expotodo_save_profile === 'function') { expotodo_save_profile(this, event); } else { alert('El sistema aún está cargando. Por favor espera un momento.'); }">Guardar Cambios</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Pedidos -->
                        <div class="tab-pane fade" id="pedidos">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Historial de Pedidos</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Get user orders
                                    $customer_orders = get_posts( array(
                                        'numberposts' => 10,
                                        'meta_key'    => '_customer_user',
                                        'meta_value'  => get_current_user_id(),
                                        'post_type'   => 'shop_order',
                                        'post_status' => array_keys( wc_get_order_statuses() ),
                                    ) );
                                    
                                    if ( ! $customer_orders ) :
                                    ?>
                                        <p class="text-center py-4">No has realizado ningún pedido aún.</p>
                                    <?php else : ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Pedido</th>
                                                        <th>Fecha</th>
                                                        <th>Estado</th>
                                                        <th>Total</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ( $customer_orders as $customer_order ) : 
                                                        $order = wc_get_order( $customer_order->ID );
                                                    ?>
                                                    <tr>
                                                        <td>#<?php echo $order->get_order_number(); ?></td>
                                                        <td><?php echo $order->get_date_created()->date('d M Y'); ?></td>
                                                        <td><span class="badge bg-secondary"><?php echo wc_get_order_status_name( $order->get_status() ); ?></span></td>
                                                        <td><?php echo $order->get_formatted_order_total(); ?></td>
                                                        <td><a href="<?php echo $order->get_view_order_url(); ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Direcciones -->
                        <div class="tab-pane fade" id="direcciones">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Mis Direcciones</h5>
                                    <a href="<?php echo wc_get_endpoint_url( 'edit-address', 'billing' ); ?>" class="btn btn-sm btn-primary">Editar Dirección</a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Dirección de Facturación</h6>
                                            <address>
                                                <?php 
                                                    $address = wc_get_account_formatted_address( 'billing' ); 
                                                    echo $address ? $address : 'No has configurado tu dirección de facturación.';
                                                ?>
                                            </address>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Dirección de Envío</h6>
                                            <address>
                                                <?php 
                                                    $address = wc_get_account_formatted_address( 'shipping' ); 
                                                    echo $address ? $address : 'No has configurado tu dirección de envío.';
                                                ?>
                                            </address>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
                        <button type="button" class="btn-add-wishlist" title="Agregar a lista de deseos">
                            <i class="far fa-heart"></i>
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
                                 <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn-card btn-primary ajax_add_to_cart flex-grow-1" data-quantity="1" data-product_id="<?php echo get_the_ID(); ?>" aria-label="Añadir “<?php the_title_attribute(); ?>” al carrito">
                                     <i class="fas fa-shopping-cart me-2"></i> Añadir
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
