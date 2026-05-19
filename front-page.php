<?php get_header(); ?>

    <!-- Sección Hero -->
    <section class="hero">
        <?php
            echo do_shortcode('[rev_slider alias="principal"][/rev_slider]');

        ?>
    </section>

    <!-- Sección de Productos Destacados -->
    <?php get_template_part('template-parts/product-collection'); ?>

<?php get_footer(); ?>
