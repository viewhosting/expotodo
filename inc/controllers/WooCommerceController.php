<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de WooCommerce y Checkout
 * Optimiza el flujo de compra, filtros de productos y checkout personalizado.
 */
class WooCommerceController {

    public function __construct() {
        // Checkout & Fields (Prioridad 9999 para imponer orden y labels sobre la localización por defecto)
        add_filter( 'woocommerce_checkout_fields', array( $this, 'custom_checkout_fields' ), 9999 );
        add_filter( 'woocommerce_get_country_locale', array( $this, 'custom_mx_country_locale' ), 9999 );
        add_filter( 'woocommerce_default_address_fields', array( $this, 'custom_default_address_fields' ), 9999 );
        
        // Restricción de Estados en México
        add_filter( 'woocommerce_states', array( $this, 'remove_restricted_states' ) );

        // Guardar y mostrar campos personalizados (Número Exterior, Interior y Delegación)
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_checkout_fields' ) );
        add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'display_custom_fields_admin_shipping' ) );
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_custom_fields_admin_billing' ) );

        add_action( 'wp_ajax_expotodo_get_cities', array( $this, 'ajax_get_cities' ) );
        add_action( 'wp_ajax_nopriv_expotodo_get_cities', array( $this, 'ajax_get_cities' ) );
        
        // Product Filters
        add_action( 'wp_ajax_expotodo_filter_products', array( $this, 'ajax_filter_products' ) );
        add_action( 'wp_ajax_nopriv_expotodo_filter_products', array( $this, 'ajax_filter_products' ) );
        
        // Cart AJAX
        add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ) );
        add_action( 'wp_ajax_expotodo_get_checkout_data', array( $this, 'ajax_get_checkout_data' ) );
        add_action( 'wp_ajax_nopriv_expotodo_get_checkout_data', array( $this, 'ajax_get_checkout_data' ) );
        add_action( 'wp_ajax_expotodo_calculate_shipping_ajax', array( $this, 'ajax_calculate_shipping' ) );
        add_action( 'wp_ajax_nopriv_expotodo_calculate_shipping_ajax', array( $this, 'ajax_calculate_shipping' ) );
        
        // Checkout Logic & MP Sync
        add_action( 'woocommerce_checkout_process', array( $this, 'sync_checkout_fields' ) );
        add_action( 'wp_ajax_expotodo_update_checkout_qty', array( $this, 'ajax_update_qty' ) );
        add_action( 'wp_ajax_nopriv_expotodo_update_checkout_qty', array( $this, 'ajax_update_qty' ) );
        add_action( 'wp_ajax_expotodo_remove_checkout_item', array( $this, 'ajax_remove_item' ) );
        add_action( 'wp_ajax_nopriv_expotodo_remove_checkout_item', array( $this, 'ajax_remove_item' ) );
        
        // MP Redirect Fixes
        add_filter( 'woocommerce_payment_successful_result', array( $this, 'force_mercadopago_redirect' ), 9999, 2 );
        add_filter( 'woocommerce_mercadopago_preference_body', array( $this, 'custom_mp_back_urls' ) );
        
        // UI Cleanups
        add_filter( 'woocommerce_add_notice', array( $this, 'kill_zone_notices_at_birth' ), 999 );
        add_filter( 'woocommerce_shipping_package_name', '__return_empty_string', 999 );
        add_filter( 'woocommerce_checkout_billing_fields_title', '__return_empty_string' );
        
        // Account Address Form
        add_action( 'wp_ajax_expotodo_get_address_form', array( $this, 'ajax_get_address_form' ) );
        add_action( 'wp_ajax_expotodo_save_address_ajax', array( $this, 'ajax_save_address' ) );

        // Fix Visibilidad Global (Carga Inicial)
        add_action( 'woocommerce_product_query', array( $this, 'modify_wc_product_query' ) );

        // Limpiar etiqueta de envío (Quitar ¡GRATIS! en recogida local)
        add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'clean_shipping_labels' ), 10, 2 );
        add_filter( 'woocommerce_cart_shipping_total', array( $this, 'clean_shipping_total_label' ), 10, 1 );

        // Fragmentos para actualizar totales personalizados
        add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'checkout_review_fragments' ) );
    }

    public function checkout_review_fragments( $fragments ) {
        ob_start();
        ?>
        <span class="value subtotal-value"><?php wc_cart_totals_subtotal_html(); ?></span>
        <?php
        $fragments['span.subtotal-value'] = ob_get_clean();

        ob_start();
        ?>
        <span class="value shipping-value"><?php echo WC()->cart->get_cart_shipping_total(); ?></span>
        <?php
        $fragments['span.shipping-value'] = ob_get_clean();

        ob_start();
        ?>
        <span class="value total-value"><?php wc_cart_totals_order_total_html(); ?></span>
        <?php
        $fragments['span.total-value'] = ob_get_clean();

        return $fragments;
    }

    public function clean_shipping_total_label( $label ) {
        // Si el label contiene "Gratis", lo dejamos vacío o solo con el texto de Envío
        if ( strpos( strtolower($label), 'gratis' ) !== false ) {
            return '';
        }
        return $label;
    }

    public function clean_shipping_labels( $label, $method ) {
        // Si es recogida local, quitamos el precio (que sale como Gratis)
        if ( strpos( strtolower($method->get_label()), 'recogida local' ) !== false || $method->get_method_id() === 'local_pickup' ) {
            return $method->get_label();
        }
        return $label;
    }

    public function custom_checkout_fields( $fields ) {
        // --- FACTURACIÓN (BILLING) ---
        $fields['billing']['billing_first_name']['priority'] = 10;
        $fields['billing']['billing_first_name']['class'] = array('form-row-first');

        $fields['billing']['billing_last_name']['priority'] = 20;
        $fields['billing']['billing_last_name']['class'] = array('form-row-last');

        $fields['billing']['billing_email']['priority'] = 27;
        $fields['billing']['billing_phone']['priority'] = 28;

        $fields['billing']['billing_address_1']['priority'] = 30;
        $fields['billing']['billing_address_1']['class'] = array('form-row-wide');
        $fields['billing']['billing_address_1']['label'] = 'Calle';
        $fields['billing']['billing_address_1']['placeholder'] = 'Calle';

        // Número Exterior
        $fields['billing']['billing_number_exterior'] = array(
            'type'        => 'text',
            'label'       => 'Nº Ext.',
            'placeholder' => 'Número exterior',
            'required'    => true,
            'class'       => array('form-row-first'),
            'priority'    => 40,
        );

        // Número Interior
        $fields['billing']['billing_number_interior'] = array(
            'type'        => 'text',
            'label'       => 'Nº Int.',
            'placeholder' => 'Número interior',
            'required'    => false,
            'class'       => array('form-row-last'),
            'priority'    => 45,
        );

        $fields['billing']['billing_address_2']['priority'] = 50;
        $fields['billing']['billing_address_2']['class'] = array('form-row-wide');
        $fields['billing']['billing_address_2']['label'] = 'Colonia';
        $fields['billing']['billing_address_2']['placeholder'] = 'Colonia';
        $fields['billing']['billing_address_2']['required'] = true;
        $fields['billing']['billing_address_2']['label_class'] = array(); // Eliminar screen-reader-text

        $fields['billing']['billing_postcode']['priority'] = 55;
        $fields['billing']['billing_postcode']['class'] = array('form-row-first');

        // Delegación / Municipio
        $fields['billing']['billing_delegacion'] = array(
            'type'        => 'text',
            'label'       => 'Delegación / Municipio',
            'placeholder' => 'Delegación / Municipio',
            'required'    => true,
            'class'       => array('form-row-first'),
            'priority'    => 60,
        );

        $fields['billing']['billing_city']['priority'] = 70;
        $fields['billing']['billing_city']['class'] = array('form-row-last');
        $fields['billing']['billing_city']['label'] = 'Ciudad';
        $fields['billing']['billing_city']['placeholder'] = 'Ciudad';

        $fields['billing']['billing_state']['priority'] = 80;
        $fields['billing']['billing_state']['class'] = array('form-row-first');

        $fields['billing']['billing_country']['priority'] = 90;
        $fields['billing']['billing_country']['class'] = array('form-row-last');

        // --- ENVÍO (SHIPPING) ---
        if (isset($fields['shipping'])) {
            $fields['shipping']['shipping_first_name']['priority'] = 10;
            $fields['shipping']['shipping_first_name']['class'] = array('form-row-first');

            $fields['shipping']['shipping_last_name']['priority'] = 20;
            $fields['shipping']['shipping_last_name']['class'] = array('form-row-last');

            // Teléfono / Celular de envío
            $fields['shipping']['shipping_phone'] = array(
                'type'        => 'tel',
                'label'       => 'Teléfono / Celular',
                'placeholder' => 'Teléfono / Celular',
                'required'    => true,
                'class'       => array('form-row-wide'),
                'priority'    => 25,
            );

            // Número Exterior
            $fields['shipping']['shipping_number_exterior'] = array(
                'type'        => 'text',
                'label'       => 'Nº Ext.',
                'placeholder' => 'Número exterior',
                'required'    => true,
                'class'       => array('form-row-first'),
                'priority'    => 30,
            );

            // Número Interior
            $fields['shipping']['shipping_number_interior'] = array(
                'type'        => 'text',
                'label'       => 'Nº Int.',
                'placeholder' => 'Número interior',
                'required'    => false,
                'class'       => array('form-row-last'),
                'priority'    => 35,
            );

            $fields['shipping']['shipping_address_1']['priority'] = 40;
            $fields['shipping']['shipping_address_1']['class'] = array('form-row-wide');
            $fields['shipping']['shipping_address_1']['label'] = 'Calle';
            $fields['shipping']['shipping_address_1']['placeholder'] = 'Calle';

            $fields['shipping']['shipping_address_2']['priority'] = 50;
            $fields['shipping']['shipping_address_2']['class'] = array('form-row-wide');
            $fields['shipping']['shipping_address_2']['label'] = 'Colonia';
            $fields['shipping']['shipping_address_2']['placeholder'] = 'Colonia';
            $fields['shipping']['shipping_address_2']['required'] = true;
            $fields['shipping']['shipping_address_2']['label_class'] = array(); // Eliminar screen-reader-text para igualar a address_1

            $fields['shipping']['shipping_postcode']['priority'] = 55;
            $fields['shipping']['shipping_postcode']['class'] = array('form-row-first');

            // Delegación / Municipio
            $fields['shipping']['shipping_delegacion'] = array(
                'type'        => 'text',
                'label'       => 'Delegación / Municipio',
                'placeholder' => 'Delegación / Municipio',
                'required'    => true,
                'class'       => array('form-row-first'),
                'priority'    => 60,
            );

            $fields['shipping']['shipping_city']['priority'] = 70;
            $fields['shipping']['shipping_city']['class'] = array('form-row-last');
            $fields['shipping']['shipping_city']['label'] = 'Ciudad';
            $fields['shipping']['shipping_city']['placeholder'] = 'Ciudad';

            $fields['shipping']['shipping_state']['priority'] = 80;
            $fields['shipping']['shipping_state']['class'] = array('form-row-first');

            $fields['shipping']['shipping_country']['priority'] = 90;
            $fields['shipping']['shipping_country']['class'] = array('form-row-last');
        }

        unset($fields['billing']['billing_company']);
        unset($fields['shipping']['shipping_company']);
        $fields['billing']['billing_country']['default'] = 'MX';
        $fields['billing']['billing_country']['class'] = array('d-none');

        // Hacer campos opcionales si es recogida local o para evitar bloqueos innecesarios
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
        $is_local_pickup = false;
        if ( !empty($chosen_methods) && (strpos($chosen_methods[0], 'local_pickup') !== false) ) {
            $is_local_pickup = true;
        }

        if ( $is_local_pickup ) {
            $fields['billing']['billing_address_1']['required'] = false;
            $fields['billing']['billing_city']['required'] = false;
            $fields['billing']['billing_postcode']['required'] = false;
            
            $fields['shipping']['shipping_address_1']['required'] = false;
            $fields['shipping']['shipping_city']['required'] = false;
            $fields['shipping']['shipping_postcode']['required'] = false;
        }
        


        // Invoice Fields (Boutique)
        $fields['billing']['billing_rfc'] = array('type' => 'text', 'label' => 'RFC', 'class' => array('form-row-first', 'invoice-field'), 'priority' => 100);
        $fields['billing']['billing_company_name'] = array('type' => 'text', 'label' => 'Razón Social', 'class' => array('form-row-last', 'invoice-field'), 'priority' => 101);
        $fields['billing']['billing_cfdi_usage'] = array('type' => 'select', 'label' => 'Uso de CFDI', 'class' => array('form-row-wide', 'invoice-field'), 'options' => array('G01' => 'G01', 'G03' => 'G03', 'S01' => 'S01'), 'priority' => 102);

        // Cambiar etiqueta y placeholder de las indicaciones del pedido
        if ( isset( $fields['order']['order_comments'] ) ) {
            $fields['order']['order_comments']['label'] = 'Información Adicional';
            $fields['order']['order_comments']['placeholder'] = 'Detalles adicionales del pedido, referencias de la dirección, etc.';
        }

        return $fields;
    }

    /**
     * Forzar a que la localización de México en WooCommerce use 'Colonia' como etiqueta y placeholder
     */
    public function custom_mx_country_locale( $locale ) {
        if ( isset( $locale['MX']['address_2'] ) ) {
            $locale['MX']['address_2']['label'] = 'Colonia';
            $locale['MX']['address_2']['placeholder'] = 'Colonia';
            $locale['MX']['address_2']['required'] = true;
        }
        return $locale;
    }

    /**
     * Forzar etiqueta 'Colonia' en los campos de dirección globales para evitar que WooCommerce los sobreescriba
     */
    public function custom_default_address_fields( $fields ) {
        if ( isset( $fields['address_2'] ) ) {
            $fields['address_2']['label'] = 'Colonia';
            $fields['address_2']['placeholder'] = 'Colonia';
            $fields['address_2']['required'] = true;
        }
        return $fields;
    }

    /**
     * Remover Chihuahua (CH), Baja California (BC), Sonora (SO), Coahuila (CO), Nuevo León (NL) y Tamaulipas (TM) del listado de estados de México
     */
    public function remove_restricted_states( $states ) {
        if ( isset( $states['MX'] ) ) {
            unset( $states['MX']['BC'] ); // Baja California
            unset( $states['MX']['CH'] ); // Chihuahua
            unset( $states['MX']['SO'] ); // Sonora
            unset( $states['MX']['CO'] ); // Coahuila
            unset( $states['MX']['NL'] ); // Nuevo León
            unset( $states['MX']['TM'] ); // Tamaulipas
        }
        return $states;
    }

    public function ajax_get_cities() {
        $state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
        
        $cities_by_state = array(
            'AG' => array('Aguascalientes', 'Asientos', 'Calvillo', 'Cosío', 'Jesús María', 'Pabellón de Arteaga', 'Rincón de Romos', 'San José de Gracia', 'Tepezalá', 'El Llano', 'San Francisco de los Romo'),
          //  'BC' => array('Ensenada', 'Mexicali', 'Tecate', 'Tijuana', 'Playas de Rosarito', 'San Quintín', 'San Felipe'),
            'BS' => array('La Paz', 'Los Cabos', 'Comondú', 'Loreto', 'Mulegé'),
            'CM' => array('Campeche', 'Carmen', 'Champotón', 'Escárcega', 'Calkiní', 'Hecelchakán', 'Hopelchén', 'Palizada', 'Tenabo', 'Candelaria', 'Calakmul'),
          //  'CO' => array('Saltillo', 'Torreón', 'Monclova', 'Piedras Negras', 'Acuña', 'Matamoros', 'San Pedro', 'Ramos Arizpe', 'Frontera', 'Múzquiz'),
            'CL' => array('Colima', 'Manzanillo', 'Tecomán', 'Villa de Álvarez', 'Armería', 'Coquimatlán', 'Cuauhtémoc', 'Ixtlahuacán', 'Minatitlán', 'Comala'),
            'JA' => array('Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tonalá', 'Puerto Vallarta', 'Tlajomulco de Zúñiga', 'Lagos de Moreno', 'Tepatitlán de Morelos', 'Ciudad Guzmán', 'Ocotlán'),
            'MX' => array('Ecatepec de Morelos', 'Nezahualcóyotl', 'Toluca de Lerdo', 'Naucalpan de Juárez', 'Chimalhuacán', 'Tlalnepantla de Baz', 'Cuautitlán Izcalli', 'Tecámac', 'Ixtapaluca', 'Atizapán de Zaragoza'),
            'MI' => array('Morelia', 'Uruapan', 'Zamora', 'Lázaro Cárdenas', 'Zitácuaro', 'Apatzingán', 'La Piedad', 'Pátzcuaro', 'Sahuayo', 'Maravatío'),
            'MO' => array('Cuernavaca', 'Jiutepec', 'Cuautla', 'Temixco', 'Yautepec', 'Emiliano Zapata', 'Zacatepec', 'Xochitepec', 'Tlaltizapán', 'Jojutla'),
            'NA' => array('Tepic', 'Xalisco', 'Santiago Ixcuintla', 'Bahía de Banderas', 'Compostela', 'Ixtlán del Río', 'Tecuala', 'San Blas', 'Acaponeta', 'Tuxpan'),
          //  'NL' => array('Monterrey', 'Guadalupe', 'Apodaca', 'San Nicolás de los Garza', 'General Escobedo', 'Santa Catarina', 'Juárez', 'García', 'San Pedro Garza García', 'Cadereyta Jiménez'),
            'OA' => array('Oaxaca de Juárez', 'San Juan Bautista Tuxtepec', 'Salina Cruz', 'Juchitán de Zaragoza', 'Santa Cruz Xoxocotlán', 'Huajuapan de León', 'Santo Domingo Tehuantepec', 'Loma Bonita', 'Miahuatlán de Porfirio Díaz', 'Puerto Escondido'),
            'PU' => array('Puebla', 'Tehuacán', 'Cholula', 'Atlixco', 'San Martín Texmelucan'),
            'QE' => array('Santiago de Querétaro', 'San Juan del Río', 'El Marqués', 'Corregidora', 'Tequisquiapan'),
            'QR' => array('Cancún', 'Playa del Carmen', 'Chetumal', 'Cozumel', 'Tulum'),
            'SL' => array('San Luis Potosí', 'Soledad de Graciano Sánchez', 'Ciudad Valles', 'Matehuala', 'Rioverde'),
            'SI' => array('Culiacán', 'Mazatlán', 'Los Mochis', 'Guasave', 'Guamúchil'),
          //  'SO' => array('Hermosillo', 'Ciudad Obregón', 'Nogales', 'San Luis Río Colorado', 'Navojoa'),
            'TB' => array('Villahermosa', 'Cárdenas', 'Comalcalco', 'Huimanguillo', 'Macuspana'),
          //  'TM' => array('Reynosa', 'Matamoros', 'Nuevo Laredo', 'Tampico', 'Ciudad Victoria'),
            'TL' => array('Tlaxcala', 'Apizaco', 'Huamantla', 'Chiautempan', 'Zacatelco'),
            'VE' => array('Veracruz', 'Xalapa', 'Coatzacoalcos', 'Córdoba', 'Poza Rica'),
            'YU' => array('Mérida', 'Kanasín', 'Valladolid', 'Tizimín', 'Progreso'),
            'ZA' => array('Zacatecas', 'Guadalupe', 'Fresnillo', 'Jerez', 'Río Grande'),
        );

        if ( isset( $cities_by_state[ $state ] ) ) {
            wp_send_json_success( $cities_by_state[ $state ] );
        } else {
            wp_send_json_error( array( 'message' => 'No se encontraron ciudades para este estado.' ) );
        }
    }

    public function ajax_filter_products() {
        $category = isset($_POST['categories']) ? (array) $_POST['categories'] : array();
        $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
        $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 999999;

        $args = array(
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'posts_per_page'      => 50, // Aumentamos para verlos todos de golpe
            'paged'               => $paged,
            'suppress_filters'    => true,
            'ignore_sticky_posts' => true,
            'tax_query'           => array(
                'relation' => 'AND',
                // Forzamos que NO se excluyan del catálogo
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'exclude-from-catalog',
                    'operator' => 'NOT IN',
                ),
            ),
            'meta_query'          => array('relation' => 'AND'),
            'orderby'             => 'date',
            'order'               => 'DESC'
        );

        // Filtro por Categorías
        if ( !empty($category) && $category[0] !== 'all' ) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category
            );
        }

        // Filtro por Precio
        if ($min_price > 0 || $max_price < 999999) {
            $args['meta_query'][] = array(
                'key'     => '_price',
                'value'   => array($min_price, $max_price),
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC'
            );
        }

        $loop = new WP_Query( $args );
        ob_start();
        if ( $loop->have_posts() ) :
            while ( $loop->have_posts() ) : $loop->the_post();
                global $product;
                ?>
                <div class="col product-grid-item">
                    <?php get_template_part('template-parts/content-product'); ?>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        endif;
        $content = ob_get_clean();

        wp_send_json_success( array(
            'html'      => $content, 
            'max_pages' => $loop->max_num_pages
        ));
    }

    /**
     * Ajusta la consulta inicial de WooCommerce para mostrar productos ocultos
     */
    public function modify_wc_product_query( $q ) {
        $tax_query = (array) $q->get( 'tax_query' );

        $tax_query[] = array(
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => array( 'exclude-from-catalog', 'exclude-from-search' ),
            'operator' => 'NOT IN',
        );

        $q->set( 'tax_query', $tax_query );
        $q->set( 'posts_per_page', 50 ); // Forzamos 50 para ver todos los ganchos
    }

    public function cart_fragments( $fragments ) {
        $fragments['span.cart-count'] = '<span class="cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
        $fragments['span.cart-total'] = '<span class="cart-total">' . WC()->cart->get_total() . '</span>';
        $fragments['span.cart-subtotal'] = '<span class="cart-subtotal">' . WC()->cart->get_cart_subtotal() . '</span>';
        
        // Actualizar la lista de productos
        $fragments['div.cart-items'] = self::get_cart_items_html();
        
        // Actualizar el estado de "vacío"
        $is_empty = WC()->cart->is_empty();
        $fragments['div.cart-empty-message'] = '<div class="cart-empty-message text-muted small ' . ($is_empty ? '' : 'd-none') . '">Tu carrito está vacío.</div>';
        
        return $fragments;
    }

    public function ajax_get_checkout_data() {
        if ( ! WC()->cart ) wp_send_json_error();
        wp_send_json_success( array('total' => WC()->cart->get_total(), 'count' => WC()->cart->get_cart_contents_count()) );
    }

    public function ajax_calculate_shipping() {
        if ( ! WC()->cart ) {
            wp_send_json_error( array( 'message' => 'El carrito está vacío.' ) );
        }

        $postcode = isset( $_POST['postcode'] ) ? sanitize_text_field( $_POST['postcode'] ) : '';
        if ( empty( $postcode ) ) {
            wp_send_json_error( array( 'message' => 'Código postal no proporcionado.' ) );
        }

        // Resolviendo estado de México según el CP
        $state = $this->get_state_from_postcode( $postcode );

        // Verificar si pertenece a zona restringida fronteriza
        $restricted_states = array( 'BC', 'CH', 'SO', 'CO', 'NL', 'TM' );
        if ( in_array( $state, $restricted_states ) ) {
            wp_send_json_error( array( 'message' => 'Lo sentimos, actualmente no realizamos entregas en la zona fronteriza.' ) );
        }

        // Establecer país México y el código postal / estado en la sesión de WooCommerce
        WC()->customer->set_billing_country( 'MX' );
        WC()->customer->set_shipping_country( 'MX' );
        WC()->customer->set_billing_postcode( $postcode );
        WC()->customer->set_shipping_postcode( $postcode );

        if ( $state ) {
            WC()->customer->set_billing_state( $state );
            WC()->customer->set_shipping_state( $state );
        }

        WC()->customer->save();

        // Recalcular envío y totales del carrito
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();

        // Obtener resultados actualizados para el frontend
        $shipping_html = WC()->cart->get_cart_shipping_total();
        $total_html = WC()->cart->get_total();
        $subtotal_html = WC()->cart->get_cart_subtotal();

        wp_send_json_success( array(
            'shipping' => $shipping_html,
            'total'    => $total_html,
            'subtotal' => $subtotal_html,
            'message'  => 'Costo de envío: ' . strip_tags( $shipping_html )
        ) );
    }

    private function get_state_from_postcode( $postcode ) {
        $prefix2 = substr( $postcode, 0, 2 );
        
        $map = array(
            '20' => 'AG', // Aguascalientes
            '21' => 'BC', // Baja California
            '22' => 'BC', // Baja California
            '23' => 'BS', // Baja California Sur
            '24' => 'CM', // Campeche
            '25' => 'CO', // Coahuila
            '26' => 'CO', // Coahuila
            '27' => 'CO', // Coahuila
            '28' => 'CL', // Colima
            '29' => 'CS', // Chiapas
            '30' => 'CS', // Chiapas
            '31' => 'CH', // Chihuahua
            '32' => 'CH', // Chihuahua
            '33' => 'CH', // Chihuahua
            '34' => 'DG', // Durango
            '35' => 'DG', // Durango
            '36' => 'GT', // Guanajuato
            '37' => 'GT', // Guanajuato
            '38' => 'GT', // Guanajuato
            '39' => 'GR', // Guerrero
            '40' => 'GR', // Guerrero
            '41' => 'GR', // Guerrero
            '42' => 'HG', // Hidalgo
            '43' => 'HG', // Hidalgo
            '44' => 'JA', // Jalisco
            '45' => 'JA', // Jalisco
            '46' => 'JA', // Jalisco
            '47' => 'JA', // Jalisco
            '48' => 'JA', // Jalisco
            '49' => 'JA', // Jalisco
            '50' => 'MX', // Estado de México
            '51' => 'MX', // Estado de México
            '52' => 'MX', // Estado de México
            '53' => 'MX', // Estado de México
            '54' => 'MX', // Estado de México
            '55' => 'MX', // Estado de México
            '56' => 'MX', // Estado de México
            '57' => 'MX', // Estado de México
            '58' => 'MI', // Michoacán
            '59' => 'MI', // Michoacán
            '60' => 'MI', // Michoacán
            '61' => 'MI', // Michoacán
            '62' => 'MO', // Morelos
            '63' => 'NA', // Nayarit
            '64' => 'NL', // Nuevo León
            '65' => 'NL', // Nuevo León
            '66' => 'NL', // Nuevo León
            '67' => 'NL', // Nuevo León
            '68' => 'OA', // Oaxaca
            '69' => 'OA', // Oaxaca
            '70' => 'OA', // Oaxaca
            '71' => 'OA', // Oaxaca
            '72' => 'PU', // Puebla
            '73' => 'PU', // Puebla
            '74' => 'PU', // Puebla
            '75' => 'PU', // Puebla
            '76' => 'QE', // Querétaro
            '77' => 'QR', // Quintana Roo
            '78' => 'SL', // San Luis Potosí
            '79' => 'SL', // San Luis Potosí
            '80' => 'SI', // Sinaloa
            '81' => 'SI', // Sinaloa
            '82' => 'SI', // Sinaloa
            '83' => 'SO', // Sonora
            '84' => 'SO', // Sonora
            '85' => 'SO', // Sonora
            '86' => 'TB', // Tabasco
            '87' => 'TM', // Tamaulipas
            '88' => 'TM', // Tamaulipas
            '89' => 'TM', // Tamaulipas
            '90' => 'TL', // Tlaxcala
            '91' => 'VE', // Veracruz
            '92' => 'VE', // Veracruz
            '93' => 'VE', // Veracruz
            '94' => 'VE', // Veracruz
            '95' => 'VE', // Veracruz
            '96' => 'VE', // Veracruz
            '97' => 'YU', // Yucatán
            '98' => 'ZA', // Zacatecas
            '99' => 'ZA', // Zacatecas
        );

        $first_digit = substr($postcode, 0, 1);
        if ($first_digit === '0' || $first_digit === '1') {
            return 'DF'; // Ciudad de México / Distrito Federal
        }

        if ( isset( $map[$prefix2] ) ) {
            return $map[$prefix2];
        }
        return '';
    }

    public function sync_checkout_fields() {
        // Sincronizar nombres del contacto a envío si no se especificaron
        if ( ! empty( $_POST['billing_first_name'] ) ) {
            if ( empty( $_POST['shipping_first_name'] ) ) $_POST['shipping_first_name'] = $_POST['billing_first_name'];
            if ( empty( $_POST['shipping_last_name'] ) ) $_POST['shipping_last_name'] = $_POST['billing_last_name'];
        }

        // Sincronizar todos los campos de dirección de envío a facturación para evitar fallos de validación en gateways
        $fields_to_sync = array(
            'address_1',
            'address_2',
            'city',
            'state',
            'postcode',
            'country',
            'number_exterior',
            'number_interior',
            'delegacion'
        );
        foreach ( $fields_to_sync as $field ) {
            if ( ! empty( $_POST['shipping_' . $field] ) ) {
                $_POST['billing_' . $field] = $_POST['shipping_' . $field];
            }
        }

        // Restringir ventas a la zona fronteriza (Chihuahua, Baja California, Sonora, Coahuila, Nuevo León, Tamaulipas) y Tijuana
        $shipping_state = ! empty( $_POST['shipping_state'] ) ? sanitize_text_field( $_POST['shipping_state'] ) : '';
        $shipping_city = ! empty( $_POST['shipping_city'] ) ? sanitize_text_field( $_POST['shipping_city'] ) : '';

        $restricted_states = array( 'BC', 'CH', 'SO', 'CO', 'NL', 'TM' );
        $is_restricted = false;
        if ( in_array( $shipping_state, $restricted_states ) ) {
            $is_restricted = true;
        }
        if ( ! empty( $shipping_city ) && strcasecmp( trim( $shipping_city ), 'tijuana' ) === 0 ) {
            $is_restricted = true;
        }

        if ( $is_restricted ) {
            wc_add_notice( __( 'Lo sentimos, actualmente no realizamos entregas ni ventas en la zona fronteriza (Baja California, Sonora, Chihuahua, Coahuila, Nuevo León o Tamaulipas).', 'woocommerce' ), 'error' );
        }
    }

    public function ajax_update_qty() {
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        $qty = (int) $_POST['qty'];
        if ( WC()->cart->set_quantity($cart_item_key, $qty) ) {
            WC()->cart->calculate_totals();
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function ajax_remove_item() {
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        if ( WC()->cart->remove_cart_item($cart_item_key) ) {
            WC()->cart->calculate_totals();
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function force_mercadopago_redirect( $result, $order_id ) {
        if ( isset($result['redirect']) && strpos($result['redirect'], 'order-pay') !== false ) {
            $order = wc_get_order( $order_id );
            $init_point = get_post_meta( $order_id, '_mercadopago_init_point', true );
            if ( !empty($init_point) ) $result['redirect'] = $init_point;
        }
        return $result;
    }

    public function custom_mp_back_urls( $preference ) {
        $checkout_url = wc_get_checkout_url();
        if ( isset( $preference['back_urls'] ) ) {
            $preference['back_urls']['failure'] = $checkout_url;
            $preference['back_urls']['pending'] = $checkout_url;
        }
        return $preference;
    }

    public function kill_zone_notices_at_birth( $notice ) {
        if ( isset($notice['notice']) && (stripos($notice['notice'], 'Zona') !== false || stripos($notice['notice'], 'México') !== false) ) {
            return false;
        }
        return $notice;
    }

    public function ajax_get_address_form() {
        check_ajax_referer('expotodo_address_nonce', 'security');
        $load_address = sanitize_text_field($_POST['address_type']);
        ob_start();
        wc_get_template( 'myaccount/form-edit-address.php', array('load_address' => $load_address, 'address' => array()) );
        wp_send_json_success( array('html' => ob_get_clean()) );
    }

    public function ajax_save_address() {
        check_ajax_referer('expotodo_address_nonce', 'security');
        $user_id = get_current_user_id();
        wp_send_json_success('Dirección actualizada correctamente.');
    }

    /**
     * Genera el HTML de los items del carrito para el panel lateral
     */
    public static function get_cart_items_html() {
        ob_start();
        ?>
        <div class="cart-items flex-grow-1">
            <?php if ( function_exists('WC') && ! WC()->cart->is_empty() ) : ?>
                <ul class="list-unstyled p-3 mb-0">
                    <?php
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                            ?>
                            <li class="d-flex mb-3 pb-2 border-bottom align-items-center">
                                <div class="me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <?php 
                                    $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                                    echo str_replace('class="', 'class="img-fluid rounded shadow-sm ', $thumbnail);
                                    ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="mb-0 text-truncate fw-bold small">
                                        <?php
                                        $name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                                        if ( $product_permalink ) {
                                            echo sprintf( '<a href="%s" class="text-dark text-decoration-none">%s</a>', esc_url( $product_permalink ), esc_html($name) );
                                        } else {
                                            echo esc_html($name);
                                        }
                                        ?>
                                    </h6>
                                    <div class="text-muted small">
                                        <?php echo sprintf( '%d &times; %s', $cart_item['quantity'], WC()->cart->get_product_price( $_product ) ); ?>
                                    </div>
                                </div>
                                <div class="ms-2">
                                    <?php
                                    echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                                        '<a href="%s" class="remove_from_cart_button text-danger small" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="fas fa-trash-alt"></i></a>',
                                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                        esc_html__( 'Remove this item', 'woocommerce' ),
                                        esc_attr( $product_id ),
                                        esc_attr( $cart_item_key ),
                                        esc_attr( $_product->get_sku() )
                                    ), $cart_item_key );
                                    ?>
                                </div>
                            </li>
                        <?php }
                    } ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Devuelve los argumentos de consulta para la sección de "Colección" o "Productos Nuevos"
     * Centraliza la lógica para que se pueda cambiar desde un solo lugar.
     */
    public static function get_collection_query_args($count = 20) {
        return array(
            'post_type'      => 'product',
            'posts_per_page' => $count,
            'orderby'        => 'date',
            'order'          => 'DESC'
        );
    }

    /**
     * Guardar campos personalizados en la base de datos del pedido
     */
    public function save_custom_checkout_fields( $order_id ) {
        $custom_fields = array(
            'billing_number_exterior',
            'billing_number_interior',
            'billing_delegacion',
            'shipping_number_exterior',
            'shipping_number_interior',
            'shipping_delegacion'
        );
        foreach ( $custom_fields as $field ) {
            if ( ! empty( $_POST[$field] ) ) {
                update_post_meta( $order_id, '_' . $field, sanitize_text_field( $_POST[$field] ) );
            }
        }
    }

    /**
     * Mostrar campos personalizados de envío en la pantalla de administración del pedido
     */
    public function display_custom_fields_admin_shipping( $order ) {
        $ext = get_post_meta( $order->get_id(), '_shipping_number_exterior', true );
        $int = get_post_meta( $order->get_id(), '_shipping_number_interior', true );
        $del = get_post_meta( $order->get_id(), '_shipping_delegacion', true );
        if ( $ext ) echo '<p><strong>Número Exterior:</strong> ' . esc_html( $ext ) . '</p>';
        if ( $int ) echo '<p><strong>Número Interior:</strong> ' . esc_html( $int ) . '</p>';
        if ( $del ) echo '<p><strong>Delegación/Municipio:</strong> ' . esc_html( $del ) . '</p>';
    }

    /**
     * Mostrar campos personalizados de facturación en la pantalla de administración del pedido
     */
    public function display_custom_fields_admin_billing( $order ) {
        $ext = get_post_meta( $order->get_id(), '_billing_number_exterior', true );
        $int = get_post_meta( $order->get_id(), '_billing_number_interior', true );
        $del = get_post_meta( $order->get_id(), '_billing_delegacion', true );
        if ( $ext ) echo '<p><strong>Número Exterior:</strong> ' . esc_html( $ext ) . '</p>';
        if ( $int ) echo '<p><strong>Número Interior:</strong> ' . esc_html( $int ) . '</p>';
        if ( $del ) echo '<p><strong>Delegación/Municipio:</strong> ' . esc_html( $del ) . '</p>';
    }
}