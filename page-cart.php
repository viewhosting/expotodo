<?php
/* Template Name: Cart */
get_header();
?>

<!-- Main Content -->
<main class="py-5 cart-page-container bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <header class="page-header mb-5 text-center">
                    <h1 class="page-title display-5 fw-bold text-dark">Tu Carrito</h1>
                    <p class="text-muted">Revisa tus productos antes de finalizar la compra</p>
                </header>
                
                <div class="cart-wrapper bg-white shadow-sm rounded-4 p-4 p-lg-5">
                    <?php echo do_shortcode('[woocommerce_cart]'); ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>