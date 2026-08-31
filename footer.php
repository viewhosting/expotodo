<?php
/**
 * The template for displaying the footer
 */
?>
    <!-- Testimonios HERO Slider Section -->
    <?php
    if (class_exists('ReviewsController')) {
        $active_reviews = ReviewsController::get_active_reviews();
        if (!empty($active_reviews)) {
            ?>
            <section class="testimonials-section py-5">
                <div class="container text-center">
                    <span class="section-subtitle">Testimonios</span>
                    <h2 class="section-title">Lo que nuestros clientes dicen</h2>
                </div>

                <div class="swiper testimonials-swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($active_reviews as $rev) : ?>
                            <div class="swiper-slide testimonial-slide">
                                <div class="testimonial-card-wrap">
                                    <div class="testimonial-quote">
                                        "<?php echo wp_kses($rev->review, array('br' => array(), 'p' => array())); ?>"
                                    </div>
                                    <div class="testimonial-user">
                                        <?php if ($rev->photo) : ?>
                                            <img src="<?php echo esc_url($rev->photo); ?>" alt="<?php echo esc_attr($rev->name); ?>" class="testimonial-avatar">
                                        <?php else : ?>
                                            <div class="testimonial-avatar-placeholder">
                                                <?php
                                                $initials = '';
                                                $words = explode(' ', $rev->name);
                                                foreach ($words as $w) {
                                                    $initials .= strtoupper(substr($w, 0, 1));
                                                    if (strlen($initials) >= 2) break;
                                                }
                                                echo esc_html($initials ? $initials : '?');
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h4 class="testimonial-name"><?php echo esc_html($rev->name); ?></h4>
                                            <span class="testimonial-status-badge">Compra Verificada</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Controls -->
                    <div class="testimonials-swiper-controls mt-4">
                        <div class="testimonials-prev-btn"><i class="fas fa-chevron-left"></i></div>
                        <div class="testimonials-pagination"></div>
                        <div class="testimonials-next-btn"><i class="fas fa-chevron-right"></i></div>
                    </div>
                </div>
            </section>
            <?php
        }
    }
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
