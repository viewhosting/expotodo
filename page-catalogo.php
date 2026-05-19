<?php
/*
Template Name: Catálogo
*/
get_header();
?>

<main class="flex-grow-1">
    <section class="py-5 bg-light">
        <div class="container">
            <h1 class="text-center mb-4 section-title">Catálogo 2026</h1>
            <p class="text-center mb-4">
                Explora nuestro catálogo interactivo directamente desde esta página.
            </p>
            <div class="page-content-area shadow-sm bg-white p-4 rounded border">
                <?php
                while (have_posts()) : the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        </div>
    </section>

    <!-- Sección de Colección Centralizada -->
    <?php get_template_part('template-parts/product-collection'); ?>

</main>

<?php get_footer(); ?>
