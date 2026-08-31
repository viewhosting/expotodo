<?php
/* Template Name: Lista Productos */
get_header();
?>

    <main class="flex-grow-1">
        <section class="py-5">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-2">
                        <!-- Botón para móviles -->
                        <button class="btn btn-success w-100 d-lg-none mb-3 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                            <span><i class="fas fa-filter me-2"></i> Filtrar productos</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div class="collapse d-lg-block sticky-top" id="filterCollapse" style="top: 90px; z-index: 100;">
                            <div class="product-filter-sidebar bg-white border rounded-3 p-3 shadow-sm">
                                <h5 class="mb-3 d-none d-lg-block">Filtrar productos</h5>
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
                    </div>
                    <div class="col-lg-10">
                        <div class="product-list-card border rounded-3 bg-white shadow-sm">
                            <div class="p-3">
                                <div class="row g-2 g-md-4 row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-4" id="product-grid-container">
                                    <?php
                                    // Custom Query for Products — excluye productos solo-cotización
                                    $args = array(
                                        'post_type'      => 'product',
                                        'posts_per_page' => 16,
                                        'post_status'    => 'publish',
                                        'meta_query'     => class_exists('QuoteOnlyController')
                                            ? QuoteOnlyController::get_meta_exclusion_args()
                                            : array(),
                                    );
                                    $loop = new WP_Query( $args );

                                    if ( $loop->have_posts() ) :
                                        while ( $loop->have_posts() ) : $loop->the_post();
                                            global $product;
                                            ?>
                                            <div class="col product-grid-item"
                                                data-category="<?php echo esc_attr( implode(' ', wp_list_pluck( get_the_terms( $product->get_id(), 'product_cat' ), 'slug' ) ) ); ?>"
                                                data-price="<?php echo esc_attr( $product->get_price() ); ?>">
                                                <?php get_template_part('template-parts/content-product'); ?>
                                            </div>
                                            <?php
                                        endwhile;
                                        wp_reset_postdata();
                                    else :
                                        echo '<div class="col-12"><p>No se encontraron productos.</p></div>';
                                    endif;
                                    ?>
                            </div>

                            <!-- Loader para scroll infinito -->
                            <div id="loading-spinner" class="text-center py-4 d-none">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                            
                            <!-- Elemento centinela para IntersectionObserver -->
                            <div id="page-end" data-max-pages="<?php echo $loop->max_num_pages; ?>" style="height: 20px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php get_footer(); ?>
