<?php get_header(); ?>

    <!-- Sección Hero -->
    <section class="hero">
        <?php
            echo do_shortcode('[rev_slider alias="principal"][/rev_slider]');

        ?>
    </section>

<?php 
// Renderizar la colección usando el nuevo controlador centralizado
echo expotodo_render_collection(array(
    'title'          => 'Nuestra Colección',
    'posts_per_page' => 4,
    'type'           => 'featured',
    'orderby'        => 'rand'
)); 
?>

<?php get_footer(); ?>
