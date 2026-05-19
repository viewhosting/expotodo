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
                                    <?php get_template_part('template-parts/content-product'); ?>
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
