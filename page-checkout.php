<?php
/* Template Name: Checkout */
get_header();
?>

<!-- Main Content -->
<main class="py-5 bg-light">
    <div class="container">
        <h1 class="mb-4">Finalizar Compra</h1>
        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
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
