<?php
/* Template Name: Buscar */

get_header();

$search_query = get_search_query();
?>

<main class="flex-grow-1">
    <!-- Hero Búsqueda -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">Resultados de búsqueda</h1>
                    <p class="lead text-muted">Mostrando resultados para: <span class="text-primary fw-bold">"<?php echo esc_html($search_query); ?>"</span></p>
                    
                    <form role="search" method="get" class="search-form-page mt-4" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border-0">
                            <input type="search" class="form-control border-0 ps-4" placeholder="¿Qué estás buscando hoy?" value="<?php echo $search_query; ?>" name="s" />
                            <input type="hidden" name="post_type" value="product" />
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Listado de Resultados -->
    <section class="py-5">
        <div class="container">
            <?php if ( have_posts() ) : ?>
                <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
                    <?php
                    while ( have_posts() ) : the_post();
                        global $product;
                        // Asegurar que sea un producto
                        if ( get_post_type() !== 'product' ) continue;
                        // Defensa en profundidad: omitir productos solo-cotización
                        if ( class_exists('QuoteOnlyController') && QuoteOnlyController::is_quote_only( get_the_ID() ) ) continue;
                        ?>
                        <div class="col">
                            <article class="product-card h-100 border-0 shadow-sm hover-shadow transition-all bg-white rounded-3 overflow-hidden">
                                <div class="product-image-container position-relative" style="height: 250px;">
                                    <?php 
                                    $terms = get_the_terms( get_the_ID(), 'product_cat' );
                                    $cat_name = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : '';
                                    if ($cat_name) : 
                                    ?>
                                    <div class="product-category position-absolute top-0 start-0 m-3 badge bg-white text-dark shadow-sm py-2 px-3 fw-bold" style="z-index: 10;">
                                        <?php echo esc_html($cat_name); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <a href="<?php the_permalink(); ?>" class="d-block h-100">
                                        <?php 
                                        if (has_post_thumbnail()) {
                                            the_post_thumbnail('large', array('class' => 'w-100 h-100 object-fit-cover'));
                                        } else {
                                            echo '<img src="' . wc_placeholder_img_src() . '" class="w-100 h-100 object-fit-cover" alt="' . get_the_title() . '">';
                                        }
                                        ?>
                                    </a>
                                </div>
                                <div class="product-content p-3 text-center">
                                    <h3 class="product-title h6 mb-2">
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark fw-bold"><?php the_title(); ?></a>
                                    </h3>
                                    <div class="product-price h5 text-primary mb-3">
                                        <?php echo $product->get_price_html(); ?>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-dark rounded-pill">Ver detalles</a>
                                        <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="btn btn-sm btn-primary rounded-pill ajax_add_to_cart" data-product_id="<?php echo get_the_ID(); ?>">
                                            <i class="fas fa-shopping-cart me-1"></i> Agregar
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagínación -->
                <div class="mt-5 d-flex justify-content-center">
                    <?php
                    the_posts_pagination( array(
                        'prev_text' => '<i class="fas fa-arrow-left"></i>',
                        'next_text' => '<i class="fas fa-arrow-right"></i>',
                        'class'     => 'pagination-boutique'
                    ) );
                    ?>
                </div>

            <?php else : ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search text-muted opacity-25" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="h3 fw-bold">No encontramos resultados</h2>
                    <p class="text-muted mb-4">Intenta con otros términos o explora nuestras categorías principales.</p>
                    <a href="<?php echo home_url('/productos'); ?>" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">Explorar catálogo</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Categorías sugeridas -->
    <section class="py-5 bg-white">
        <div class="container">
            <h4 class="text-center mb-5 fw-bold">O explora por categorías</h4>
            <div class="row g-3 justify-content-center">
                <?php
                $categories = get_terms( array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'parent'     => 0,
                    'number'     => 6
                ) );
                if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
                    foreach ( $categories as $cat ) :
                ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="<?php echo get_term_link( $cat ); ?>" class="btn btn-outline-light text-dark border w-100 py-3 rounded-3 shadow-sm hover-shadow transition-all d-flex flex-column align-items-center">
                        <span class="fw-bold small text-uppercase"><?php echo esc_html( $cat->name ); ?></span>
                    </a>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </section>
</main>

<style>
.pagination-boutique .page-numbers {
    display: flex;
    gap: 5px;
    list-style: none;
    padding: 0;
}
.pagination-boutique .page-numbers a, 
.pagination-boutique .page-numbers span {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #1a1a1a;
    border: 1px solid #eee;
    border-radius: 50%;
    transition: all 0.3s;
}
.pagination-boutique .page-numbers .current {
    background-color: var(--primary-color, #b0d443);
    border-color: var(--primary-color, #b0d443);
    color: white;
}
.pagination-boutique .page-numbers a:hover {
    background-color: #f8f9fa;
}
</style>

<?php get_footer(); ?>
