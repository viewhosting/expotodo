<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Colecciones de Productos
 * Centraliza el renderizado de cuadrículas de productos como "Nuestra Colección"
 */
class CollectionController {

    public function __construct() {
        // Acciones si fueran necesarias (ej. AJAX para cargar más)
    }

    /**
     * Renderiza una sección de colección (Nuestra Colección)
     */
    public function render_collection($atts = array()) {
        $default_atts = array(
            'title'          => 'Nuestra Colección',
            'posts_per_page' => 4,
            'orderby'        => 'rand',
            'category'       => '',
            'columns'        => 'col-md-3',
            'show_view_more' => true,
            'view_more_url'  => home_url('/productos'),
            'type'           => 'featured' // 'featured', 'recent', 'on_sale'
        );

        $atts = wp_parse_args($atts, $default_atts);

        // Configurar Query
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $atts['posts_per_page'],
            'orderby'        => $atts['orderby'],
            'status'         => 'publish'
        );

        if ($atts['type'] === 'featured') {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'featured',
            );
        }

        if (!empty($atts['category'])) {
            $args['product_cat'] = $atts['category'];
        }

        $query = new WP_Query($args);

        ob_start();

        if ($query->have_posts()) : ?>
            <section class="featured-section py-5" id="coleccion">
                <div class="container">
                    <?php if (!empty($atts['title'])) : ?>
                        <h2 class="section-title text-center mb-5"><?php echo esc_html($atts['title']); ?></h2>
                    <?php endif; ?>
                    
                    <div class="row g-4 mb-5">
                        <?php while ($query->have_posts()) : $query->the_post(); 
                            global $product;
                            $product_id = get_the_ID();
                            
                            // Usar el mismo diseño premium que ya tenemos
                            echo $this->get_product_card_html($product, $atts['columns']);
                        endwhile; wp_reset_postdata(); ?>
                    </div>
                    
                    <?php if ($atts['show_view_more']) : ?>
                        <div class="text-center">
                            <a href="<?php echo esc_url($atts['view_more_url']); ?>" class="btn btn-outline-dark btn-lg px-5">
                                Ver Catálogo Completo
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php
        else :
            echo '<div class="text-center py-5"><p class="text-muted">No hay productos disponibles en esta colección.</p></div>';
        endif;

        return ob_get_clean();
    }

    /**
     * Renderiza una tarjeta de producto estándar premium
     * Centralizado para que si cambia aquí, cambie en todo el sitio.
     */
    private function get_product_card_html($product, $column_class) {
        $product_id = $product->get_id();
        $product_name = $product->get_name();
        $product_price = $product->get_price();
        
        // Obtener categoría principal
        $terms = get_the_terms($product_id, 'product_cat');
        $category_name = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : 'Producto';
        
        // Wishlist (obtener lista del usuario para marcar el corazón si ya está)
        $user_wishlist = function_exists('expotodo_get_user_wishlist') ? expotodo_get_user_wishlist() : array();
        $is_in_wishlist = in_array($product_id, $user_wishlist);
        
        ob_start(); ?>
        <div class="<?php echo esc_attr($column_class); ?>">
            <article class="product-card h-100" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-name="<?php echo esc_attr($product_name); ?>" data-product-price="<?php echo esc_attr($product_price); ?>">
                <div class="product-image-container">
                    <div class="product-category"><?php echo esc_html($category_name); ?></div>
                    <?php if ($product->is_on_sale()) : ?>
                        <div class="product-category sale" style="top: 40px; background-color: #dc3545;">Oferta</div>
                    <?php endif; ?>
                    <button type="button" class="btn-add-wishlist" data-id="<?php echo $product_id; ?>" title="Agregar a lista de deseos">
                        <i class="far fa-heart <?php echo $is_in_wishlist ? 'fas text-danger' : 'far'; ?>"></i>
                    </button>
                    <?php echo $product->get_image('large', array('class' => 'product-image')); ?>
                </div>
                <div class="product-content p-3">
                    <h3 class="product-title"><?php echo esc_html($product_name); ?></h3>
                    <p class="product-description">
                        <?php echo wp_trim_words($product->get_short_description() ?: get_the_excerpt($product_id), 12, '...'); ?>
                    </p>
                    <div class="product-price mb-3">
                        <?php echo $product->get_price_html(); ?>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                         <a href="<?php echo get_permalink($product_id); ?>" class="btn-card btn-primary flex-grow-1">
                            <i class="fas fa-eye me-2"></i> Ver
                        </a>
                         <?php if ( $product->is_type('variable') ) : ?>
                             <a href="<?php echo get_permalink($product_id); ?>" class="btn-card btn-primary flex-grow-1">
                                 <i class="fas fa-eye me-2"></i> Opciones
                             </a>
                         <?php else : ?>
                             <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn-card btn-primary ajax_add_to_cart flex-grow-1" data-quantity="1" data-product_id="<?php echo $product_id; ?>" aria-label="Agregar">
                                 <i class="fas fa-shopping-cart me-2"></i> Agregar
                             </a>
                         <?php endif; ?>
                    </div>
                </div>
            </article>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Wrapper de compatibilidad global si se necesita
if (!function_exists('expotodo_render_collection')) {
    function expotodo_render_collection($atts = array()) {
        $controller = new CollectionController();
        return $controller->render_collection($atts);
    }
}
