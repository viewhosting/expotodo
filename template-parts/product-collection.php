<?php
/**
 * Template Part: Product Collection (Carousel Version)
 * Muestra un carrusel de productos nuevos usando Swiper.js.
 */

// Obtener argumentos centralizados (ahora devuelve 20 por defecto)
$args = WooCommerceController::get_collection_query_args();
$featured_products = new WP_Query($args);
?>

<section class="featured-section py-5" id="coleccion">
    <div class="container">
        <div class="collection-header d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 position-relative">
            <div class="title-wrapper text-center w-100">
                <h2 class="section-title m-0">Productos Nuevos</h2>
            </div>
            <div class="swiper-controls d-flex gap-2 mt-3 mt-md-0 position-md-absolute end-0 top-50 translate-middle-y-md">
                <div class="swiper-button-prev-custom"><i class="fas fa-chevron-left"></i></div>
                <div class="swiper-button-next-custom"><i class="fas fa-chevron-right"></i></div>
            </div>
        </div>
        
        <!-- Swiper Container -->
        <div class="swiper products-swiper mb-5">
            <div class="swiper-wrapper">
                <?php
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
                <div class="swiper-slide h-auto">
                    <?php get_template_part('template-parts/content-product'); ?>
                </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<div class="col-12 text-center"><p>No hay productos disponibles en este momento.</p></div>';
                endif;
                ?>
            </div>
            <!-- Pagination -->
            <div class="swiper-pagination mt-4"></div>
        </div>
        
        <div class="text-center">
            <a href="<?php echo home_url('/productos'); ?>" class="btn btn-outline-dark btn-lg px-5">
                Ver Catálogo Completo
            </a>
        </div>
    </div>
</section>


<?php
/**
 * Fin del componente de colección
 */
