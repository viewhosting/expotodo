<?php
/**
 * The template for displaying the footer
 */
?>
    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-container py-4">
                <div class="footer-info">
                    <div class="footer-left">
                        <a href="<?php echo home_url(); ?>" class="logo">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="<?php bloginfo('name'); ?>" class="footer-logo">
                        </a>
                        <div class="footer-copyright mt-2">
                            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Diseñado con pasión por el detalle.</p>
                        </div>
                    </div>
                    
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'container'      => 'nav',
                        'container_class' => 'footer-menu',
                        'fallback_cb'    => '__return_false',
                        'items_wrap'     => '%3$s',
                        'depth'          => 1,
                    ) );
                    ?>
                </div>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>
