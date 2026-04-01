<?php
/* Template Name: Checkout */
get_header();
?>

<!-- Main Content -->
<main class="py-5 bg-light">
    <div class="container">
        <!-- <h1 class="mb-4">Finalizar Compra</h1> -->
        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
    </div>
</main>

<!-- Productos Relacionados -->
    <?php if ( ! is_order_received_page() ) : ?>
        <section class="featured-section py-5">
            <div class="container">
                <h2 class="section-title text-center mb-5">Productos recomendados</h2>
                <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4">
                    <?php
                    // Obtener el primer producto del carrito para basar las recomendaciones
                    $cart = WC()->cart->get_cart();
                    $base_product_id = 0;
                    
                    if ( ! empty( $cart ) ) {
                        $first_item = reset( $cart );
                        $base_product_id = $first_item['product_id'];
                    }

                    $related_ids = $base_product_id ? wc_get_related_products( $base_product_id, 4 ) : array();
                    
                    if( !empty($related_ids) ) :
                        $args = array(
                            'post_type' => 'product',
                            'post__in' => $related_ids,
                            'posts_per_page' => 4
                        );
                        $related_products = new WP_Query( $args );

                        if ( $related_products->have_posts() ) :
                            while ( $related_products->have_posts() ) : $related_products->the_post();
                                $_rel_product = wc_get_product( get_the_ID() );
                                if ( ! $_rel_product ) continue;
                                ?>
                                <div class="col product-grid-item">
                                    <article class="product-card h-100" data-product-id="<?php echo get_the_ID(); ?>">
                                        <div class="product-image-container">
                                            <?php 
                                            $terms = get_the_terms( get_the_ID(), 'product_cat' );
                                            $cat_name = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : 'Producto';
                                            ?>
                                            <div class="product-category"><?php echo esc_html($cat_name); ?></div>
                                            
                                            <button type="button" class="btn-add-wishlist" title="Agregar a lista de deseos">
                                                <i class="far fa-heart"></i>
                                            </button>

                                            <?php 
                                            if (has_post_thumbnail()) {
                                                the_post_thumbnail('medium', array('class' => 'product-image'));
                                            } else {
                                                echo '<img src="https://via.placeholder.com/300x300?text=No+Image" class="product-image" alt="' . get_the_title() . '">';
                                            }
                                            ?>
                                        </div>
                                        <div class="product-content p-3">
                                            <h3 class="product-title"><?php the_title(); ?></h3>
                                            <p class="product-description">
                                                <?php echo wp_trim_words(get_the_excerpt(), 10, '...'); ?>
                                            </p>
                                            <div class="product-price mb-3">
                                                <?php echo $_rel_product->get_price_html(); ?>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="<?php the_permalink(); ?>" class="btn-card btn-primary flex-grow-1">
                                                    <i class="fas fa-eye me-2"></i> Ver
                                                </a>
                                                <a href="<?php echo esc_url( $_rel_product->add_to_cart_url() ); ?>" class="btn-card btn-primary ajax_add_to_cart flex-grow-1" data-quantity="1" data-product_id="<?php echo get_the_ID(); ?>">
                                                    <i class="fas fa-shopping-cart me-2"></i> Agregar
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                                <?php
                            endwhile;
                            wp_reset_postdata();
                        endif;
                    else: 
                        echo '<div class="col-12 text-center"><p>No hay productos relacionados disponibles.</p></div>';
                    endif;
                    ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

<?php get_footer(); ?>
