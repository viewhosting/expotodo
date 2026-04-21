<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


get_header(); 
?>

<main class="flex-grow-1">
    <?php while ( have_posts() ) : the_post(); global $product; ?>
        <section class="py-5">
            <div class="container">
                <div class="row g-5">
                    <!-- Columna de Imagen -->
                    <div class="col-lg-5">
                        <div class="product-gallery-container shadow-sm border rounded-3 overflow-hidden bg-white mb-3">
                            <div class="product-image-main position-relative">
                                <?php 
                                if ( has_post_thumbnail() ) {
                                    $image_url = get_the_post_thumbnail_url($post->ID, 'full');
                                    echo '<a href="' . esc_url($image_url) . '" class="woocommerce-main-image" data-fancybox="gallery">';
                                    echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="img-fluid w-100">';
                                    echo '<div class="zoom-overlay"><i class="fas fa-search-plus"></i></div>';
                                    echo '</a>';
                                } else {
                                    echo '<img src="' . wc_placeholder_img_src() . '" class="img-fluid w-100" alt="' . get_the_title() . '">';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Galería de Miniaturas -->
                        <?php 
                        $attachment_ids = $product->get_gallery_image_ids();
                        if ( $attachment_ids ) : 
                        ?>
                        <div class="product-thumbnails-gallery row g-2">
                            <?php foreach ( $attachment_ids as $attachment_id ) : ?>
                                <div class="col-3">
                                    <div class="thumbnail-card border rounded shadow-sm overflow-hidden">
                                        <a href="<?php echo wp_get_attachment_url( $attachment_id ); ?>" data-fancybox="gallery">
                                            <?php echo wp_get_attachment_image( $attachment_id, 'thumbnail', false, array( 'class' => 'img-fluid w-100' ) ); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Columna de Contenido -->
                    <div class="col-lg-7">
                        <div class="product-content w-100">
                            <div class="row">
                                <div class="col-md-6">
                                     <h1 class="product-title mb-3"><?php the_title(); ?></h1>
                                    
                                    <div class="product-price mb-3">
                                        <span class="price-label">Precio</span>
                                        <span class="price"><?php echo $product->get_price_html(); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="product-description mb-3">
                                        <?php the_content(); ?>
                                    </div>
                                    <div class="col-md-6">
                                    <!-- Atributos del producto si existen -->
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
                                </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card bg-light border-0 p-3">
                                        <!-- Formulario de Agregar al Carrito (Variables, Cantidad, etc.) -->
                                        <div class="product-add-to-cart-container mb-3">
                                            <?php 
                                            if ( function_exists('expotodo_custom_add_to_cart_button') ) {
                                                expotodo_custom_add_to_cart_button();
                                            } else {
                                                woocommerce_template_single_add_to_cart();
                                            }
                                            ?>
                                        </div>

                                        
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-end">
                                        
                                        <a href="https://2026.expotodo.com.mx/productos" 
                                        class="btn btn-outline-secondary btn-sm mediano" 
                                        style="width: 25%;">
                                            <i class="fas fa-arrow-left me-2"></i> Volver
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Productos Relacionados -->
        <section class="featured-section py-5">
            <div class="container">
                <h2 class="section-title text-center mb-5">Productos recomendados</h2>
                <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4">
                    <?php
                    $related_ids = wc_get_related_products( $product->get_id(), 4 );
                    
                    if( !empty($related_ids) ) :
                        $args = array(
                            'post_type' => 'product',
                            'post__in' => $related_ids,
                            'posts_per_page' => 4
                        );
                        $related_products = new WP_Query( $args );

                        if ( $related_products->have_posts() ) :
                            while ( $related_products->have_posts() ) : $related_products->the_post();
                                global $product;
                                ?>
                                <div class="col product-grid-item">
                                    <article class="product-card h-100">
                                        <div class="product-image-container">
                                            <?php 
                                            $terms = get_the_terms( $product->get_id(), 'product_cat' );
                                            $cat_name = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : '';
                                            if ($cat_name) : 
                                            ?>
                                            <div class="product-category"><?php echo esc_html($cat_name); ?></div>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn-add-wishlist" data-id="<?php echo $product->get_id(); ?>" title="Agregar a lista de deseos">
                                                <i class="far fa-heart <?php echo in_array($product->get_id(), expotodo_get_user_wishlist()) ? 'fas text-danger' : 'far'; ?>"></i>
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
                                                <?php echo wp_trim_words(get_the_excerpt(), 8, '...'); ?>
                                            </p>
                                            <div class="product-price mb-3">
                                                <span class="price new-price"><?php echo $product->get_price_html(); ?></span>
                                            </div>
                                            <a href="<?php the_permalink(); ?>" class="btn-card btn-primary">
                                                <i class="fas fa-eye me-2"></i> Ver detalles
                                            </a>
                                            <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="btn-card btn-primary mt-2 ajax_add_to_cart" data-quantity="1" data-product_id="<?php echo get_the_ID(); ?>">
                                                <i class="fas fa-shopping-cart me-2"></i> Agregar al carrito
                                            </a>
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

    <?php endwhile; // end of the loop. ?>
</main>

<?php get_footer(); ?>
