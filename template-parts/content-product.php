<?php
/**
 * Template part for displaying a product card
 * Centralized for site-wide consistency
 */

global $product;

if ( ! $product ) {
    return;
}

// Guardia de seguridad: no renderizar tarjetas de productos solo-cotización.
// Este template-part es el último escudo para cualquier loop custom que no
// aplique su propia meta_query de exclusión.
if ( class_exists( 'QuoteOnlyController' ) && QuoteOnlyController::is_quote_only( $product->get_id() ) ) {
    return;
}


$product_id = $product->get_id();
$product_name = $product->get_name();
$product_price = $product->get_price();
$image_url = has_post_thumbnail( $product_id ) ? get_the_post_thumbnail_url( $product_id, 'large' ) : 'https://via.placeholder.com/400';

// Obtener categoría principal
$terms = get_the_terms( $product_id, 'product_cat' );
$category_name = ! empty( $terms ) && ! is_wp_error( $terms ) ? $terms[0]->name : 'Producto';
?>

<article class="product-card h-100" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-product-name="<?php echo esc_attr( $product_name ); ?>" data-product-price="<?php echo esc_attr( $product_price ); ?>">
    <div class="product-image-container">
        <?php /* La categoría está comentada por petición del usuario */ ?>
        <!-- <div class="product-category"><?php echo esc_html( $category_name ); ?></div> -->
        
        <?php if ( $product->is_on_sale() ) : ?>
            <div class="product-category sale" style="top: 40px; background-color: #dc3545;">Oferta</div>
        <?php endif; ?>

        <button type="button" class="btn-add-wishlist" data-id="<?php echo $product_id; ?>" title="Agregar a lista de deseos">
            <i class="far fa-heart <?php echo in_array( $product_id, expotodo_get_user_wishlist() ) ? 'fas text-danger' : 'far'; ?>"></i>
        </button>
        
        <a href="<?php echo get_permalink( $product_id ); ?>">
            <img src="<?php echo esc_url( $image_url ); ?>" 
                 class="product-image" 
                 alt="<?php echo esc_attr( $product_name ); ?>">
        </a>
    </div>
    
    <div class="product-content p-3">
        <h3 class="product-title">
            <a href="<?php echo get_permalink( $product_id ); ?>" class="text-decoration-none text-dark">
                <?php echo esc_html( $product_name ); ?>
            </a>
        </h3>
        <p class="product-description">
            <?php echo wp_trim_words( get_the_excerpt( $product_id ), 15, '...' ); ?>
        </p>
        <div class="product-price mb-3">
            <?php echo $product->get_price_html(); ?>
        </div>
        <a href="<?php echo get_permalink( $product_id ); ?>" class="btn-card btn-primary">
            <i class="fas fa-eye me-2"></i> Ver más
        </a>
        <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="btn-card btn-primary mt-2 <?php echo $product->is_type( 'simple' ) ? 'ajax_add_to_cart' : ''; ?>" data-quantity="1" data-product_id="<?php echo $product_id; ?>" aria-label="Añadir “<?php echo esc_attr( $product_name ); ?>” al carrito">
            <i class="fas fa-shopping-cart me-2"></i> <?php echo $product->is_type( 'variable' ) ? 'Opciones' : 'Añadir'; ?>
        </a>
    </div>
</article>
