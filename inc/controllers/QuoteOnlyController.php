<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * QuoteOnlyController
 *
 * Excluye del frontend todos los productos marcados como "solo cotización"
 * mediante el meta: _et_quote_only = 1
 *
 * Estos productos existen únicamente para el sistema interno de cotizaciones
 * y no deben ser visibles ni comprables desde el frontend público.
 *
 * Estrategia en capas:
 *  1. pre_get_posts                    → loops principales de WP (tienda, categorías, búsqueda, etiquetas)
 *  2. woocommerce_product_query        → queries nativas de WooCommerce
 *  3. template_redirect                → bloquear acceso directo a la ficha del producto (404)
 *  4. woocommerce_is_purchasable       → evitar que sea comprable aunque lleguen al objeto
 *  5. woocommerce_add_to_cart_validation → bloquear add-to-cart por URL directa
 *  6. woocommerce_related_posts_args   → excluir de productos relacionados (query WC nativa)
 *
 * Las queries custom del tema (WP_Query manuales) deben usar
 * self::get_meta_exclusion_args() para agregar el filtro a sus propios args.
 */
class QuoteOnlyController {

    /**
     * Meta key y valor que identifican un producto como "solo cotización".
     */
    const META_KEY   = '_et_quote_only';
    const META_VALUE = '1';

    public function __construct() {
        // 1. Excluir de loops principales de WP (no aplica en admin)
        add_action( 'pre_get_posts', array( $this, 'exclude_from_main_query' ) );

        // 2. Excluir de queries nativas de WooCommerce
        add_action( 'woocommerce_product_query', array( $this, 'exclude_from_wc_query' ) );

        // 3. Bloquear acceso directo a la ficha pública del producto → 404
        add_action( 'template_redirect', array( $this, 'block_single_product_access' ) );

        // 4. Marcar como no comprable
        add_filter( 'woocommerce_is_purchasable', array( $this, 'prevent_purchase' ), 10, 2 );

        // 5. Bloquear add-to-cart por URL directa (?add-to-cart=ID)
        add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'block_add_to_cart' ), 10, 2 );

        // 6. Excluir de productos relacionados (WC nativa)
        add_filter( 'woocommerce_related_posts_args', array( $this, 'exclude_from_related_posts' ) );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper público — usar en WP_Query custom del tema
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Devuelve el fragmento de meta_query para excluir productos quote-only.
     *
     * Uso en WP_Query custom:
     *   $args['meta_query'] = array_merge(
     *       isset($args['meta_query']) ? $args['meta_query'] : array(),
     *       QuoteOnlyController::get_meta_exclusion_args()
     *   );
     *
     * @return array
     */
    public static function get_meta_exclusion_args() {
        return array(
            'relation' => 'AND',
            array(
                'relation' => 'OR',
                array(
                    'key'     => self::META_KEY,
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => self::META_KEY,
                    'value'   => self::META_VALUE,
                    'compare' => '!=',
                ),
            ),
        );
    }

    /**
     * Comprueba si un producto es de solo cotización.
     *
     * @param  int $product_id
     * @return bool
     */
    public static function is_quote_only( $product_id ) {
        return get_post_meta( (int) $product_id, self::META_KEY, true ) === self::META_VALUE;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Hooks
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Hook: pre_get_posts
     * Excluye productos quote-only de cualquier loop principal de WordPress
     * (tienda, categorías, etiquetas, búsqueda, páginas de archivo de productos).
     *
     * No aplica en admin para no interferir con la gestión interna.
     */
    public function exclude_from_main_query( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        // Solo actuar cuando el loop incluye productos
        $post_type = $query->get( 'post_type' );
        $is_product_loop = (
            $post_type === 'product'
            || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) )
            || is_shop()
            || is_product_category()
            || is_product_tag()
            || ( is_search() && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' )
        );

        if ( ! $is_product_loop ) {
            return;
        }

        // Combinar con meta_query existente si la hay
        $existing_meta_query = (array) $query->get( 'meta_query' );
        $exclusion            = self::get_meta_exclusion_args();

        if ( ! empty( $existing_meta_query ) ) {
            $merged = array( 'relation' => 'AND', $existing_meta_query, $exclusion );
        } else {
            $merged = $exclusion;
        }

        $query->set( 'meta_query', $merged );
    }

    /**
     * Hook: woocommerce_product_query
     * Aplica la exclusión también sobre el objeto WP_Query de WooCommerce
     * (útil para shortcodes [products], bloques de Gutenberg, etc.)
     */
    public function exclude_from_wc_query( $q ) {
        $existing_meta_query = (array) $q->get( 'meta_query' );
        $exclusion            = self::get_meta_exclusion_args();

        if ( ! empty( $existing_meta_query ) ) {
            $merged = array( 'relation' => 'AND', $existing_meta_query, $exclusion );
        } else {
            $merged = $exclusion;
        }

        $q->set( 'meta_query', $merged );
    }

    /**
     * Hook: template_redirect
     * Si alguien accede directamente a la URL de un producto quote-only,
     * se lanza un 404 antes de renderizar cualquier contenido.
     */
    public function block_single_product_access() {
        if ( ! is_singular( 'product' ) ) {
            return;
        }

        $product_id = get_queried_object_id();

        if ( self::is_quote_only( $product_id ) ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();
            include( get_query_template( '404' ) );
            exit;
        }
    }

    /**
     * Hook: woocommerce_is_purchasable (filter)
     * Marca el producto como no comprable, lo que suprime el botón de
     * agregar al carrito y evita que sea comprado directamente.
     *
     * @param  bool        $purchasable
     * @param  WC_Product  $product
     * @return bool
     */
    public function prevent_purchase( $purchasable, $product ) {
        if ( self::is_quote_only( $product->get_id() ) ) {
            return false;
        }
        return $purchasable;
    }

    /**
     * Hook: woocommerce_add_to_cart_validation (filter)
     * Bloquea la adición al carrito aunque lleguen al endpoint ?add-to-cart=ID.
     *
     * @param  bool $passed
     * @param  int  $product_id
     * @return bool
     */
    public function block_add_to_cart( $passed, $product_id ) {
        if ( $passed && self::is_quote_only( $product_id ) ) {
            return false;
        }
        return $passed;
    }

    /**
     * Hook: woocommerce_related_posts_args (filter)
     * Excluye productos quote-only de la query de productos relacionados
     * que genera WooCommerce internamente.
     *
     * @param  array $args  Argumentos de WP_Query para productos relacionados
     * @return array
     */
    public function exclude_from_related_posts( $args ) {
        $existing_meta_query = isset( $args['meta_query'] ) ? (array) $args['meta_query'] : array();
        $exclusion            = self::get_meta_exclusion_args();

        if ( ! empty( $existing_meta_query ) ) {
            $args['meta_query'] = array( 'relation' => 'AND', $existing_meta_query, $exclusion );
        } else {
            $args['meta_query'] = $exclusion;
        }

        return $args;
    }
}
