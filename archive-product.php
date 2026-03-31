<?php

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
                    <div class="product-filter-sidebar bg-white border rounded-3 p-2 shadow-sm sticky-top" style="top: 80px; z-index: 900;">
                        <h5 class="mb-3">Filtrar productos</h5>
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

                <!-- Listado de Productos -->
                <div class="col-lg-10">
                    <div class="product-list-card border rounded-3 bg-white shadow-sm">
                        <div class="p-3">
                            <div id="product-grid-container" class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4">
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
                                            <article class="product-card h-100">
                                                <div class="product-image-container">
                                                    <?php 
                                                    // Categoría principal para mostrar
                                                    $terms = get_the_terms( $product->get_id(), 'product_cat' );
                                                    $cat_name = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : '';
                                                    if ($cat_name) : 
                                                    ?>
                                                    <div class="product-category"><?php echo esc_html($cat_name); ?></div>
                                                    <?php endif; ?>

                                                    <?php if ( $product->is_on_sale() ) : ?>
                                                        <div class="product-category sale" style="top: 40px; background-color: #dc3545;">Oferta</div>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" class="btn-add-wishlist" data-id="<?php echo $product->get_id(); ?>" title="Agregar a lista de deseos">
                                                        <i class="far fa-heart <?php echo in_array($product->get_id(), expotodo_get_user_wishlist()) ? 'fas text-danger' : 'far'; ?>"></i>
                                                    </button>

                                                    <a href="<?php the_permalink(); ?>">
                                                        <?php 
                                                        if (has_post_thumbnail()) {
                                                            the_post_thumbnail('large', array('class' => 'product-image'));
                                                        } else {
                                                            echo '<img src="https://via.placeholder.com/300x300?text=No+Image" class="product-image" alt="' . get_the_title() . '">';
                                                        }
                                                        ?>
                                                    </a>
                                                </div>
                                                <div class="product-content p-3">
                                                    <h3 class="product-title"><a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark"><?php the_title(); ?></a></h3>
                                                    <p class="product-description small text-muted">
                                                        <?php echo wp_trim_words(get_the_excerpt(), 10, '...'); ?>
                                                    </p>
                                                    <div class="product-price mb-3">
                                                        <?php echo $product->get_price_html(); ?>
                                                    </div>
                                                    <a href="<?php the_permalink(); ?>" class="btn-card btn-primary w-100 mb-2">
                                                        <i class="fas fa-eye me-2"></i> Ver detalles
                                                    </a>
                                                    <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="btn-card btn-outline-primary w-100 ajax_add_to_cart" data-quantity="1" data-product_id="<?php echo get_the_ID(); ?>">
                                                        <i class="fas fa-shopping-cart me-2"></i> Agregar
                                                    </a>
                                                </div>
                                            </article>
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
