<?php
/* Template Name: Detalle Producto */
get_header();

// Fetch one product to display (random or latest) — excluye productos solo-cotización
$args = array(
    'post_type'      => 'product',
    'posts_per_page' => 1,
    'post_status'    => 'publish',
    'meta_query'     => class_exists('QuoteOnlyController')
        ? QuoteOnlyController::get_meta_exclusion_args()
        : array(),
);
$loop = new WP_Query( $args );
?>

<main class="flex-grow-1">
    <section class="py-5">
        <div class="container">
            <?php 
            if ( $loop->have_posts() ) :
                while ( $loop->have_posts() ) : $loop->the_post();
                    global $product;
            ?>
            <div class="row g-5">
                <div class="col-lg-6">
                    <article class="product-card h-100" data-product-id="<?php echo get_the_ID(); ?>">
                        <div class="product-image-container">
                            <?php 
                            if ( has_post_thumbnail() ) {
                                echo '<img src="' . get_the_post_thumbnail_url(get_the_ID(), 'full') . '" alt="' . get_the_title() . '" class="product-image">';
                            } else {
                                echo '<img src="' . wc_placeholder_img_src() . '" class="product-image" alt="Placeholder">';
                            }
                            ?>
                        </div>
                    </article>
                </div>
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="product-content w-100">
                        <h1 class="product-title mb-3"><?php the_title(); ?></h1>
                        <div class="product-description mb-3">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <?php if ( $product->has_attributes() ) : ?>
                            <ul class="mb-4">
                                <?php foreach ( $product->get_attributes() as $attribute ) : ?>
                                    <li>
                                        <strong><?php echo wc_attribute_label( $attribute->get_name() ); ?>:</strong>
                                        <?php 
                                        if ( $attribute->is_taxonomy() ) {
                                            $values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
                                            echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );
                                        } else {
                                            $values = array_map( 'trim', explode( WC_DELIMITER, $attribute->get_options() ) );
                                            echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );
                                        }
                                        ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <div class="product-price mb-3">
                            <span class="price-label">Precio</span>
                            <span class="price"><?php echo $product->get_price_html(); ?></span>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-3">
                            <?php if ( $product->is_type('variable') ) : ?>
                                <a href="<?php echo get_permalink( $product->get_id() ); ?>" class="btn-card btn btn-primary">
                                    <i class="fas fa-eye me-2"></i> Seleccionar opciones
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" 
                                   class="btn-card btn btn-primary ajax_add_to_cart" 
                                   data-quantity="1" 
                                   data-product_id="<?php echo get_the_ID(); ?>"
                                   rel="nofollow">
                                    <i class="fas fa-shopping-cart me-2"></i> Agregar al carrito
                                </a>
                            <?php endif; ?>
                            <a class="btn btn-outline-secondary" href="<?php echo home_url('/productos'); ?>">
                                <i class="fas fa-arrow-left me-2"></i> Volver a productos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php 
                endwhile;
                wp_reset_postdata();
            else:
                echo '<p class="text-center">No hay productos disponibles para mostrar.</p>';
            endif;
            ?>
        </div>
    </section>
    
    <section class="featured-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Productos recomendados</h2>
            <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4">
                <?php
                // Show 4 random products — excluye solo-cotización
                $related_args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => 4,
                    'orderby'        => 'rand',
                    'post__not_in'   => array( get_the_ID() ),
                    'meta_query'     => class_exists('QuoteOnlyController')
                        ? QuoteOnlyController::get_meta_exclusion_args()
                        : array(),
                );
                $related = new WP_Query( $related_args );
                
                if ( $related->have_posts() ) :
                    while ( $related->have_posts() ) : $related->the_post();
                        global $product;
                ?>
                <div class="col product-grid-item">
                        <?php get_template_part('template-parts/content-product'); ?>
                </div>
                <?php 
                    endwhile; 
                    wp_reset_postdata();
                endif; 
                ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
