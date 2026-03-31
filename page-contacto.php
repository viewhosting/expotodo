<?php
/*
Template Name: Contacto
*/
get_header();
?>

<main class="flex-grow-1">
    <section class="py-5 bg-light">
        <div class="container">
            <h1 class="text-center mb-5 section-title">Contáctanos</h1>
            <div class="row g-5">
                <!-- Mapa y Sucursales -->
                <div class="col-lg-6">
                    <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                        <h3 class="mb-4">Nuestras Sucursales</h3>
                        <div class="ratio ratio-4x3 mb-4">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d60200.70636886737!2d-99.17557356616606!3d19.432607699999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1sexpotodo%20sucursales!5e0!3m2!1ses!2smx!4v1709660000000!5m2!1ses!2smx" 
                                    style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                                    <div>
                                        <h5 class="h6 fw-bold">Matriz Centro</h5>
                                        <p class="small text-muted mb-0">República de Uruguay 123, Centro Histórico, CDMX</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                                    <div>
                                        <h5 class="h6 fw-bold">Sucursal Norte</h5>
                                        <p class="small text-muted mb-0">Av. Insurgentes Norte 456, Lindavista, CDMX</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Contacto -->
                <div class="col-lg-6">
                    <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                        <h3 class="mb-4">Envíanos un mensaje</h3>
                        <form id="expotodo-contact-form" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="name" id="nombre" placeholder="Tu nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="tucorreo@ejemplo.com" required>
                                </div>
                                <div class="col-12">
                                    <label for="asunto" class="form-label">Asunto</label>
                                    <input type="text" class="form-control" name="subject" id="asunto" placeholder="Asunto del mensaje" required>
                                </div>
                                <div class="col-12">
                                    <label for="mensaje" class="form-label">Mensaje</label>
                                    <textarea class="form-control" name="message" id="mensaje" rows="5" placeholder="¿En qué podemos ayudarte?" required></textarea>
                                </div>
                                
                                <!-- Honeypot anti-spam -->
                                <div style="display:none;">
                                    <input type="text" name="hp_field" value="">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <span class="btn-text">Enviar Mensaje</span>
                                        <span class="btn-loading d-none"><i class="fas fa-spinner fa-spin"></i> Enviando...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="contact-response" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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