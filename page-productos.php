<?php
/*
Template Name: Productos
*/
get_header();
?>

<main class="flex-grow-1">
    <section class="py-5">
        <div class="container">
            <h1 class="text-center mb-5">Nuestras Categorías</h1>
            <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">
                <?php
                $terms = get_terms(array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'parent'     => 0
                ));

                if (!empty($terms) && !is_wp_error($terms)) :
                    foreach ($terms as $term) :
                        $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                        $image_url = wp_get_attachment_url($thumbnail_id);
                        if (!$image_url) {
                            $image_url = 'https://via.placeholder.com/400x300?text=' . urlencode($term->name);
                        }
                ?>
                <div class="col">
                    <a href="<?php echo esc_url(get_term_link($term)); ?>" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                            <div class="card-img-top overflow-hidden" style="height: 250px;">
                                <img src="<?php echo esc_url($image_url); ?>" 
                                     class="w-100 h-100 object-fit-cover" 
                                     alt="<?php echo esc_attr($term->name); ?>">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title text-dark mb-0"><?php echo esc_html($term->name); ?></h5>
                                <p class="card-text text-muted small mt-2">
                                    <?php echo $term->count; ?> Productos
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
                <?php 
                    endforeach;
                else :
                    echo '<div class="col-12 text-center"><p>No hay categorías disponibles.</p></div>';
                endif;
                ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
