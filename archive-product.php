<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;


get_header();

// Obtener categoría actual si estamos en una página de categoría
$current_term_id = is_product_category() ? get_queried_object_id() : '';
?>

<main class="flex-grow-1">
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Sidebar de Filtros -->
                <div class="col-lg-2">
                    <!-- Botón para móviles -->
                    <button class="btn btn-success w-100 d-lg-none mb-3 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <span><i class="fas fa-filter me-2"></i> Filtrar productos</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <div class="collapse d-lg-block sticky-top" id="filterCollapse" style="top: 90px; z-index: 900;">
                        <div class="product-filter-sidebar bg-white border rounded-3 p-3 shadow-sm">
                            <h5 class="mb-3 d-none d-lg-block">Filtrar productos</h5>
                            <form id="product-filters">
                                <!-- Categorías -->
                                <div class="mb-4">
                                    <h6 class="mb-2">Categorías</h6>
                                    <div class="filter-options" style="max-height: 300px; overflow-y: auto;">
                                        <div class="form-check">
                                            <input class="form-check-input filter-category" type="checkbox" value="all" id="cat-all" <?php echo empty($current_term_id) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="cat-all">
                                                Todas las categorías
                                            </label>
                                        </div>
                                        <?php
                                        $categories = get_terms(array(
                                            'taxonomy' => 'product_cat',
                                            'hide_empty' => true,
                                            'parent' => 0
                                        ));
                                        
                                        if (!empty($categories) && !is_wp_error($categories)) :
                                            foreach($categories as $cat) :
                                                $checked = ($current_term_id == $cat->term_id) ? 'checked' : '';
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input filter-category" type="checkbox" value="<?php echo esc_attr($cat->slug); ?>" id="cat-<?php echo $cat->term_id; ?>" <?php echo $checked; ?>>
                                            <label class="form-check-label" for="cat-<?php echo $cat->term_id; ?>">
                                                <?php echo esc_html($cat->name); ?>
                                            </label>
                                        </div>
                                        <?php 
                                            endforeach; 
                                        endif;
                                        ?>
                                    </div>
                                </div>

                                <!-- Precio -->
                                <div class="mb-4">
                                    <h6 class="mb-2">Precio</h6>
                                    <select class="form-select form-select-sm filter-price" name="price_range" id="price-filter">
                                        <option value="all">Todos los precios</option>
                                        <option value="low">Menos de $300</option>
                                        <option value="mid">$300 - $600</option>
                                        <option value="high">Más de $600</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Listado de Productos -->
                <div class="col-lg-10">
                    <div class="product-list-card border rounded-3 bg-white shadow-sm">
                        <div class="p-3">
                            <div id="product-grid-container" class="row g-2 g-md-4 row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-4">
                                <?php
                                if ( have_posts() ) :
                                    while ( have_posts() ) : the_post();
                                        global $product;
                                        // Asegurar que el objeto producto global esté disponible
                                        if ( !is_a($product, 'WC_Product') ) {
                                            continue;
                                        }
                                        ?>
                                        <div class="col product-grid-item" 
                                             data-category="<?php echo esc_attr( implode(' ', wp_list_pluck( get_the_terms( $product->get_id(), 'product_cat' ), 'slug' ) ) ); ?>"
                                             data-price="<?php echo esc_attr( $product->get_price() ); ?>">
                                            <?php get_template_part('template-parts/content-product'); ?>
                                        </div>
                                        <?php
                                    endwhile;
                                else :
                                    echo '<div class="col-12"><p class="text-center">No se encontraron productos.</p></div>';
                                endif;
                                ?>
                            </div>
                            
                            <!-- Loader para scroll infinito -->
                            <div id="loading-spinner" class="text-center py-4 d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                            
                            <!-- Elemento centinela para IntersectionObserver -->
                            <div id="page-end" style="height: 20px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
