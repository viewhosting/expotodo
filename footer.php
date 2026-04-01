<?php
/**
 * The template for displaying the footer
 */
?>
    <!-- Footer -->
    <?php 
    $footer_bg_url = get_option('expotodo_footer_bg', ''); 
    $footer_bg_align = get_option('expotodo_footer_bg_align', 'center center');
    if (!empty($footer_bg_url)) : ?>
        <style>
            :root { 
                --footer-bg: url('<?php echo esc_url($footer_bg_url); ?>');
                --footer-bg-align: <?php echo esc_attr($footer_bg_align); ?>;
            }
        </style>
    <?php endif; ?>

    <footer class="main-footer">
        <div class="container-fluid px-5">
            <div class="footer-container">
                <div class="footer-info">
                    <div class="footer-left">
                        <a href="<?php echo home_url(); ?>" class="logo">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="<?php bloginfo('name'); ?>" class="footer-logo">
                        </a>
                        <div class="footer-copyright mt-2">
                            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Diseñado con pasión por el detalle.</p>
                        </div>
                    </div>
                    
                    <nav class="footer-menu">
                        <a href="<?php echo home_url(); ?>">Principal</a>
                        <a href="<?php echo home_url('/productos'); ?>">Productos</a>
                        <a href="<?php echo home_url('/catalogo'); ?>">Catálogo</a>
                        <a href="<?php echo home_url('/#mercado-libre'); ?>">Mercado Libre</a>
                        <a href="<?php echo home_url('/#amazon'); ?>">Amazon</a>
                        <a href="<?php echo home_url('/contacto'); ?>">Contacto</a>
                    </nav>
                </div>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>
