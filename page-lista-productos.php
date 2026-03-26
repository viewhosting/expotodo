<?php
/* Template Name: Lista Productos */
get_header();
?>

    <main class="flex-grow-1">
        <section class="py-5">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-2">
                        <div class="product-filter-sidebar bg-white border rounded-3 p-2 shadow-sm sticky-top">
                            <h5 class="mb-3">Filtrar productos</h5>
                            <div class="mb-4">
                                <h6 class="mb-2">Categorías</h6>
                                <div class="d-flex flex-wrap gap-2" id="category-filters">
                                    <input type="radio" class="btn-check filter-category-radio" name="category_filter" id="cat-all" value="all" checked>
                                    <label class="btn btn-outline-success btn-sm" for="cat-all">Todas</label>
                                    <?php
                                    $product_categories = get_terms( array(
                                        'taxonomy'   => 'product_cat',
                                        'hide_empty' => true,
                                    ) );
                                    
                                    if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                                        foreach ( $product_categories as $category ) {
                                            echo '<input type="radio" class="btn-check filter-category-radio" name="category_filter" id="cat-' . esc_attr( $category->slug ) . '" value="' . esc_attr( $category->slug ) . '">';
                                            echo '<label class="btn btn-outline-success btn-sm" for="cat-' . esc_attr( $category->slug ) . '">' . esc_html( $category->name ) . '</label>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="mb-4">
                                <h6 class="mb-2">Estado</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <input type="checkbox" class="btn-check filter-flag" value="nuevo" id="checkNuevo">
                                    <label class="btn btn-outline-success btn-sm" for="checkNuevo">Nuevo</label>

                                    <input type="checkbox" class="btn-check filter-flag" value="oferta" id="checkOferta">
                                    <label class="btn btn-outline-success btn-sm" for="checkOferta">Oferta</label>

                                    <input type="checkbox" class="btn-check filter-flag" value="mas-vendido" id="checkMasVendido">
                                    <label class="btn btn-outline-success btn-sm" for="checkMasVendido">Más Vendido</label>
                                </div>
                            </div>
                            <div class="mb-4">
                                <h6 class="mb-2">Precio</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" placeholder="Min" id="priceMin">
                                    </div>
                                    <span>-</span>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" placeholder="Max" id="priceMax">
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary w-100 mt-2" id="btnFilterPrice">Aplicar</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <div class="product-list-card border rounded-3 bg-white shadow-sm">
                            <div class="p-3">
                                <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4" id="product-grid-container">
                                    <?php
                                    // Custom Query for Products
                                    $args = array(
                                        'post_type' => 'product',
                                        'posts_per_page' => 12,
                                        'status' => 'publish',
                                    );
                                    $loop = new WP_Query( $args );

                                    if ( $loop->have_posts() ) :
                                        while ( $loop->have_posts() ) : $loop->the_post();
                                            global $product;
                                            ?>
                                            <div class="col product-grid-item"
                                                data-category="<?php echo esc_attr( implode(' ', wp_list_pluck( get_the_terms( $product->get_id(), 'product_cat' ), 'slug' ) ) ); ?>"
                                                data-price="<?php echo esc_attr( $product->get_price() ); ?>">
                                                <article class="product-card h-100">
                                                    <div class="product-image-container">
                                                        <?php 
                                                        $terms = get_the_terms( $product->get_id(), 'product_cat' );
                                                        if ( !empty($terms) && !is_wp_error($terms) ) {
                                                            echo '<div class="product-category">' . esc_html( $terms[0]->name ) . '</div>';
                                                        }
                                                        ?>
                                                        <button class="btn-add-wishlist" title="Agregar a lista de deseos" type="button" data-product-id="<?php echo get_the_ID(); ?>">
                                                            <i class="far fa-heart"></i>
                                                        </button>
                                                        <?php 
                                                        if ( has_post_thumbnail() ) {
                                                            echo '<img src="' . get_the_post_thumbnail_url() . '" alt="' . get_the_title() . '" class="product-image">';
                                                        } else {
                                                            echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" class="product-image">';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="product-content p-3">
                                                        <h3 class="product-title"><?php the_title(); ?></h3>
                                                        <p class="product-description"><?php echo wp_trim_words( get_the_excerpt(), 10 ); ?></p>
                                                        <div class="product-price mb-3">
                                                            <span class="price new-price"><?php echo $product->get_price_html(); ?></span>
                                                        </div>
                                                        <a class="btn-card btn-primary" href="<?php the_permalink(); ?>">
                                                            <i class="fas fa-eye me-2"></i> Ver detalles
                                                        </a>
                                                        <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" 
                                                           class="btn-card btn-primary mt-2 <?php echo $product->is_type('simple') ? 'ajax_add_to_cart' : ''; ?>" 
                                                           data-quantity="1" 
                                                           data-product_id="<?php echo get_the_ID(); ?>"
                                                           data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                                                           aria-label="Agregar “<?php the_title_attribute(); ?>” al carrito"
                                                           rel="nofollow">
                                                            <i class="fas fa-shopping-cart me-2"></i> <?php echo $product->is_type('variable') ? 'Seleccionar opciones' : 'Agregar al carrito'; ?>
                                                        </a>
                                                    </div>
                                                </article>
                                            </div>
                                            <?php
                                        endwhile;
                                        wp_reset_postdata();
                                    else :
                                        echo '<div class="col-12"><p>No se encontraron productos.</p></div>';
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php get_footer(); ?>
