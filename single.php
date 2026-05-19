<?php
get_header();
?>

<main class="flex-grow-1">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <article class="product-card h-100" data-product-id="<?php echo get_the_ID(); ?>"
                        data-product-name="<?php the_title(); ?>" data-product-price="520">
                        <div class="product-image-container">
                            <?php 
                            if ( has_post_thumbnail() ) {
                                the_post_thumbnail('large', array('class' => 'product-image'));
                            } else {
                                echo '<img alt="' . get_the_title() . '" class="product-image" src="https://www.expotodo.com.mx/wp-content/uploads/2023/07/EXPO-TODO-34-400x400.png" />';
                            }
                            ?>
                        </div>
                    </article>
                </div>
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="product-content w-100">
                        <h1 class="product-title mb-3"><?php the_title(); ?></h1>
                        <div class="product-description mb-3">
                            <?php the_content(); ?>
                        </div>
                        
                        <!-- Static specs for now, ideally Custom Fields -->
                        <ul class="mb-4">
                            <li>Altura aproximada: 190 cm</li>
                            <li>Acabado: blanco mate</li>
                            <li>Base: cristal templado con soporte metálico</li>
                            <li>Uso recomendado: tiendas deportivas, vitrinas y exhibiciones especiales</li>
                        </ul>
                        
                        <div class="product-price mb-3">
                            <span class="price-label">Precio</span>
                            <span class="price">520€</span>
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                            <a class="btn-card btn btn-primary" href="#">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al carrito
                            </a>
                            <a class="btn btn-outline-secondary" href="<?php echo home_url('/productos'); ?>">
                                <i class="fas fa-arrow-left me-2"></i>Volver a productos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endwhile; endif; ?>

    <section class="featured-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Productos recomendados</h2>
            <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4">
                <?php
                // Obtener 4 productos recomendados de forma dinámica
                $recommended_args = array(
                    'post_type' => 'product',
                    'posts_per_page' => 4,
                    'orderby' => 'rand', // Aleatorios para que siempre varíe
                );
                $recommended_products = new WP_Query($recommended_args);

                if ($recommended_products->have_posts()) :
                    while ($recommended_products->have_posts()) : $recommended_products->the_post();
                        ?>
                        <div class="col product-grid-item">
                            <?php get_template_part('template-parts/content-product'); ?>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p class="text-center">No hay productos recomendados en este momento.</p>';
                endif;
                ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
