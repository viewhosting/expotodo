<?php
/* Template Name: Carrito */
get_header();
?>

<!-- Main Content -->
<main class="py-5 bg-light">
    <div class="container">
        <h1 class="mb-4">Carrito de Compras1</h1>
        <div class="cart-wrapper card shadow-sm p-4">
            <?php echo do_shortcode('[woocommerce_cart]'); ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
