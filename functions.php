<?php
/**
 * ExpoTodo Theme Functions
 */

// Basic Setup
function expotodo_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'woocommerce' );
    
    // WooCommerce Gallery Features
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'expotodo_setup' );

// Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'expotodo_scripts' );
function expotodo_scripts() {
    // Bootstrap CSS
    wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0' );
    
    // FontAwesome
    wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0' );
    
    // Google Fonts
    wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Open+Sans:wght@400;600&display=swap', array(), null );
    
    // Fancybox 5 (Lightbox)
    wp_enqueue_style( 'fancybox-css', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css', array(), '5.0' );
    wp_enqueue_script( 'fancybox-js', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js', array(), '5.0', true );

    // Main CSS
    wp_enqueue_style( 'expotodo-style', get_template_directory_uri() . '/assets/css/main.css', array('bootstrap', 'fancybox-css'), '1.0.0' );

    // Filtro CSS
    wp_enqueue_style( 'expotodo-filtro', get_template_directory_uri() . '/assets/css/filtro.css?ver='.rand(1,9999), array('expotodo-style'), '1.0.0' );

    // CSS para Mi Cuenta
    if ( is_page_template('page-cuenta.php') || is_account_page() ) {
        wp_enqueue_style( 'expotodo-cuenta', get_template_directory_uri() . '/assets/css/cuenta.css?ver='.rand(1,9999), array('expotodo-style'), '1.0.0' );
    }

    // CSS para Página de Carrito
    if ( is_cart() || is_page_template('page-cart.php') ) {
        wp_enqueue_style( 'expotodo-carrito', get_template_directory_uri() . '/assets/css/pagina_carrito.css?ver='.rand(1,9999), array('expotodo-style'), '1.0.0' );
    }
    
    // Bootstrap JS
    wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true );
    
    // Custom JS
    wp_enqueue_script( 'expotodo-script', get_template_directory_uri() . '/assets/js/script.js?ver='.rand(1,9999), array('jquery'), '1.0.0', true );
    
    // Localize main script
    wp_localize_script( 'expotodo-script', 'expotodo_globals', array(
        'ajax_url'    => admin_url( 'admin-ajax.php' ),
        'login_nonce' => wp_create_nonce( 'expotodo_login_nonce' ),
        'profile_nonce' => wp_create_nonce( 'expotodo_profile_nonce' ),
        'redirect_url'=> get_permalink( get_option('woocommerce_myaccount_page_id') ),
        'checkout_url' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/'),
        'checkout_nonce' => wp_create_nonce( 'woocommerce-process_checkout' ),
    ));

    // Custom Login JS (separado para evitar caché y mejor organización)
    wp_enqueue_script( 'expotodo-login-js', get_template_directory_uri() . '/assets/js/expotodo-login.js?ver=' . time(), array('jquery', 'expotodo-script'), '1.0.0', true );

    // Product Filters JS (Shop & List Page)
    if ( is_shop() || is_product_category() || is_page_template('page-lista-productos.php') || is_page('lista-productos') || is_page('productos') ) {
        wp_enqueue_script( 'expotodo-productos-js', get_template_directory_uri() . '/assets/js/productos.js?ver='.rand(1,9999), array('jquery'), '1.0.0', true );
        
        // Localize script to pass data to JS
        wp_localize_script( 'expotodo-productos-js', 'expotodo_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ));
    }

    // Custom Checkout JS (solo en checkout)
    if ( is_checkout() ) {
        wp_enqueue_script( 'expotodo-checkout-custom', get_template_directory_uri() . '/assets/js/checkout-custom.js?ver='.rand(1,9999), array('jquery'), '1.0.0', true );
        
        // Localizar script para checkout
        wp_localize_script( 'expotodo-checkout-custom', 'expotodo_checkout_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'expotodo_checkout_nonce' ),
        ));
    }
}

/**
 * Modificar campos del checkout
 */
add_filter( 'woocommerce_checkout_fields', 'expotodo_custom_checkout_fields' );
function expotodo_custom_checkout_fields( $fields ) {
    // 1. Ocultar campo empresa
    $fields['billing']['billing_company']['class'][] = 'd-none';
    $fields['billing']['billing_company']['label'] = 'Nombre de la empresa / Razón Social';
    $fields['billing']['billing_company']['required'] = false;

    // 2. Agregar campos personalizados para Factura
    $fields['billing']['billing_invoice_required'] = array(
        'type'        => 'checkbox',
        'label'       => '¿Deseas factura?',
        'class'       => array('form-row-wide'),
        'priority'    => 25,
    );

    $fields['billing']['billing_rfc'] = array(
        'type'        => 'text',
        'label'       => 'RFC',
        'class'       => array('form-row-first', 'd-none', 'invoice-field'),
        'required'    => false,
        'priority'    => 35,
    );

    $fields['billing']['billing_cfdi'] = array(
        'type'        => 'select',
        'label'       => 'Uso de CFDI',
        'class'       => array('form-row-last', 'd-none', 'invoice-field'),
        'required'    => false,
        'options'     => array(
            'G01' => 'G01 - Adquisición de mercancías',
            'G03' => 'G03 - Gastos en general',
            'P01' => 'P01 - Por definir',
        ),
        'priority'    => 36,
    );
    
    $fields['billing']['billing_payment_method'] = array(
        'type'        => 'select',
        'label'       => 'Método de Pago',
        'class'       => array('form-row-first', 'd-none', 'invoice-field'),
        'required'    => false,
        'options'     => array(
            'PUE' => 'PUE - Pago en una sola exhibición',
            'PPD' => 'PPD - Pago en parcialidades o diferido',
        ),
        'priority'    => 37,
    );

    $fields['billing']['billing_payment_form'] = array(
        'type'        => 'select',
        'label'       => 'Forma de Pago',
        'class'       => array('form-row-last', 'd-none', 'invoice-field'),
        'required'    => false,
        'options'     => array(
            '01' => '01 - Efectivo',
            '03' => '03 - Transferencia electrónica de fondos',
            '04' => '04 - Tarjeta de crédito',
            '28' => '28 - Tarjeta de débito',
            '99' => '99 - Por definir',
        ),
        'priority'    => 38,
    );

    // 3. Ocultar País (Solo México) y agregar clase para ocultarlo visualmente
    $fields['billing']['billing_country']['class'][] = 'd-none';
    $fields['billing']['billing_country']['required'] = false;
    
    return $fields;
}

// AJAX Handler para obtener ciudades por estado
add_action( 'wp_ajax_expotodo_get_cities', 'expotodo_get_cities_by_state' );
add_action( 'wp_ajax_nopriv_expotodo_get_cities', 'expotodo_get_cities_by_state' );

function expotodo_get_cities_by_state() {
    $state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
    
    // Array de ciudades por estado (Claves ISO 3166-2 para México usadas por Woo)
    $cities_by_state = array(
        'AG' => array('Aguascalientes', 'Asientos', 'Calvillo', 'Cosío', 'Jesús María', 'Pabellón de Arteaga', 'Rincón de Romos', 'San José de Gracia', 'Tepezalá', 'El Llano', 'San Francisco de los Romo'),
        'BC' => array('Ensenada', 'Mexicali', 'Tecate', 'Tijuana', 'Playas de Rosarito', 'San Quintín', 'San Felipe'),
        'BS' => array('La Paz', 'Los Cabos', 'Comondú', 'Loreto', 'Mulegé'),
        'CM' => array('Campeche', 'Carmen', 'Champotón', 'Escárcega', 'Calkiní', 'Hecelchakán', 'Hopelchén', 'Palizada', 'Tenabo', 'Candelaria', 'Calakmul'),
        'CO' => array('Saltillo', 'Torreón', 'Monclova', 'Piedras Negras', 'Acuña', 'Matamoros', 'San Pedro', 'Ramos Arizpe', 'Frontera', 'Múzquiz'),
        'CL' => array('Colima', 'Manzanillo', 'Tecomán', 'Villa de Álvarez', 'Armería', 'Coquimatlán', 'Cuauhtémoc', 'Ixtlahuacán', 'Minatitlán', 'Comala'),
        'JA' => array('Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tonalá', 'Puerto Vallarta', 'Tlajomulco de Zúñiga', 'Lagos de Moreno', 'Tepatitlán de Morelos', 'Ciudad Guzmán', 'Ocotlán'),
        'MX' => array('Ecatepec de Morelos', 'Nezahualcóyotl', 'Toluca de Lerdo', 'Naucalpan de Juárez', 'Chimalhuacán', 'Tlalnepantla de Baz', 'Cuautitlán Izcalli', 'Tecámac', 'Ixtapaluca', 'Atizapán de Zaragoza'),
        'MI' => array('Morelia', 'Uruapan', 'Zamora', 'Lázaro Cárdenas', 'Zitácuaro', 'Apatzingán', 'La Piedad', 'Pátzcuaro', 'Sahuayo', 'Maravatío'),
        'MO' => array('Cuernavaca', 'Jiutepec', 'Cuautla', 'Temixco', 'Yautepec', 'Emiliano Zapata', 'Zacatepec', 'Xochitepec', 'Tlaltizapán', 'Jojutla'),
        'NA' => array('Tepic', 'Xalisco', 'Santiago Ixcuintla', 'Bahía de Banderas', 'Compostela', 'Ixtlán del Río', 'Tecuala', 'San Blas', 'Acaponeta', 'Tuxpan'),
        'NL' => array('Monterrey', 'Guadalupe', 'Apodaca', 'San Nicolás de los Garza', 'General Escobedo', 'Santa Catarina', 'Juárez', 'García', 'San Pedro Garza García', 'Cadereyta Jiménez'),
        'OA' => array('Oaxaca de Juárez', 'San Juan Bautista Tuxtepec', 'Salina Cruz', 'Juchitán de Zaragoza', 'Santa Cruz Xoxocotlán', 'Huajuapan de León', 'Santo Domingo Tehuantepec', 'Loma Bonita', 'Miahuatlán de Porfirio Díaz', 'Puerto Escondido'),
        'PU' => array('Puebla', 'Tehuacán', 'Cholula', 'Atlixco', 'San Martín Texmelucan'),
        'QE' => array('Santiago de Querétaro', 'San Juan del Río', 'El Marqués', 'Corregidora', 'Tequisquiapan'),
        'QR' => array('Cancún', 'Playa del Carmen', 'Chetumal', 'Cozumel', 'Tulum'),
        'SL' => array('San Luis Potosí', 'Soledad de Graciano Sánchez', 'Ciudad Valles', 'Matehuala', 'Rioverde'),
        'SI' => array('Culiacán', 'Mazatlán', 'Los Mochis', 'Guasave', 'Guamúchil'),
        'SO' => array('Hermosillo', 'Ciudad Obregón', 'Nogales', 'San Luis Río Colorado', 'Navojoa'),
        'TB' => array('Villahermosa', 'Cárdenas', 'Comalcalco', 'Huimanguillo', 'Macuspana'),
        'TM' => array('Reynosa', 'Matamoros', 'Nuevo Laredo', 'Tampico', 'Ciudad Victoria'),
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

/**
 * AJAX Filter Products
 */
add_action( 'wp_ajax_expotodo_filter_products', 'expotodo_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_expotodo_filter_products', 'expotodo_ajax_filter_products' );

function expotodo_ajax_filter_products() {
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
    $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 9999999;
    
    // Flags: nuevo, oferta, mas-vendido
    $flags = isset($_POST['flags']) ? (array) $_POST['flags'] : array();

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 12,
        'status' => 'publish',
        'meta_query' => array('relation' => 'AND'),
        'tax_query' => array('relation' => 'AND'),
    );

    // Filtro Categoría
    if ( !empty($category) && $category !== 'all' ) {
        if ( is_string($category) ) {
             $category = array($category);
        }
        if ( is_array($category) ) {
             $category = array_map('sanitize_text_field', $category);
             $category = array_diff($category, array('all'));
             
             if ( !empty($category) ) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $category,
                );
             }
        }
    }

    // Filtro Precio
    if ( $min_price > 0 || $max_price < 9999999 ) {
        $args['meta_query'][] = array(
            'key'     => '_price',
            'value'   => array( $min_price, $max_price ),
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        );
    }

    // Filtro Flags
    if ( in_array('oferta', $flags) ) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key'     => '_sale_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC'
            ),
            array(
                'key'     => '_min_variation_sale_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC'
            )
        );
    }
    
    if ( in_array('nuevo', $flags) ) {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }

    if ( in_array('mas-vendido', $flags) ) {
        $args['meta_key'] = 'total_sales';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
    }

    $loop = new WP_Query( $args );

    ob_start();

    if ( $loop->have_posts() ) :
        while ( $loop->have_posts() ) : $loop->the_post();
            global $product;
            ?>
            <div class="col product-grid-item"
                data-category="<?php echo esc_attr( implode(' ', wp_list_pluck( get_the_terms( $product->get_id(), 'product_cat' ), 'slug' ) ) ); ?>"
            >
                <div class="card h-100 product-card border-0 shadow-sm">
                    <div class="product-image-container position-relative overflow-hidden">
                        <?php 
                        if ( has_post_thumbnail() ) {
                            echo '<img src="' . get_the_post_thumbnail_url() . '" alt="' . get_the_title() . '" class="product-image">';
                        } else {
                            echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" class="product-image">';
                        }
                        ?>
                    </div>
                    <div class="product-content p-3">
                        <h3 class="product-title"><?php the_title(); ?></h3>
                        <p class="product-description"><?php echo wp_trim_words( get_the_excerpt(), 10 ); ?></p>
                        <div class="product-price mb-3">
                            <span class="price new-price"><?php echo $product->get_price_html(); ?></span>
                        </div>
                        <a class="btn-card btn-primary" href="<?php the_permalink(); ?>">
                            <i class="fas fa-eye me-2"></i> Ver detalles
                        </a>
                        <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" 
                            class="btn-card btn-primary mt-2 <?php echo $product->is_type('simple') ? 'ajax_add_to_cart' : ''; ?>" 
                            data-quantity="1" 
                            data-product_id="<?php echo get_the_ID(); ?>"
                            data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                            aria-label="Agregar “<?php the_title_attribute(); ?>” al carrito"
                            rel="nofollow">
                            <i class="fas fa-shopping-cart me-2"></i> Agregar
                        </a>
                    </div>
                </div>
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<div class="col-12 text-center py-5">No se encontraron productos.</div>';
    endif;

    $content = ob_get_clean();

    wp_send_json_success( array(
        'html' => $content,
        'max_pages' => $loop->max_num_pages
    ) );
    
    wp_die();
}

/**
 * AJAX Login Handler
 */
add_action( 'wp_ajax_expotodo_login', 'expotodo_ajax_login' );
add_action( 'wp_ajax_nopriv_expotodo_login', 'expotodo_ajax_login' );

function expotodo_ajax_login() {
    check_ajax_referer( 'expotodo_login_nonce', 'security' );

    $info = array();
    $info['user_login']    = isset($_POST['username']) ? sanitize_text_field( $_POST['username'] ) : '';
    $info['user_password'] = isset($_POST['password']) ? $_POST['password'] : ''; 
    $info['remember']      = true;

    $user_signon = wp_signon( $info, false );

    if ( is_wp_error( $user_signon ) ) {
        wp_send_json_error( array( 'message' => $user_signon->get_error_message() ) );
    } else {
        wp_send_json_success( array( 'message' => 'Inicio de sesión exitoso. Redirigiendo...' ) );
    }
}

/**
 * AJAX Update Profile Handler
 */
add_action( 'wp_ajax_expotodo_update_profile', 'expotodo_update_profile' );

function expotodo_update_profile() {
    check_ajax_referer( 'expotodo_profile_nonce', 'security' );

    $current_user_id = get_current_user_id();
    if ( ! $current_user_id ) {
        wp_send_json_error( array( 'message' => 'No tienes permiso para realizar esta acción.' ) );
    }

    $first_name = isset($_POST['first_name']) ? sanitize_text_field( $_POST['first_name'] ) : '';
    $last_name  = isset($_POST['last_name']) ? sanitize_text_field( $_POST['last_name'] ) : '';

    if ( empty($first_name) || empty($last_name) ) {
        wp_send_json_error( array( 'message' => 'Por favor, completa todos los campos requeridos.' ) );
    }

    $user_data = array(
        'ID'         => $current_user_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'display_name' => $first_name . ' ' . $last_name,
    );

    $user_id = wp_update_user( $user_data );

    if ( is_wp_error( $user_id ) ) {
        wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
    } else {
        wp_send_json_success( array( 'message' => 'Perfil actualizado correctamente.' ) );
    }
}

/**
 * AJAX Live Search Handler
 */
add_action( 'wp_ajax_expotodo_live_search', 'expotodo_ajax_live_search' );
add_action( 'wp_ajax_nopriv_expotodo_live_search', 'expotodo_ajax_live_search' );

function expotodo_ajax_live_search() {
    $query = isset($_POST['query']) ? sanitize_text_field( $_POST['query'] ) : '';

    if ( empty($query) || strlen($query) < 2 ) {
        wp_send_json_success( array( 'html' => '' ) );
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 8,
        'status'         => 'publish',
        's'              => $query,
    );

    $loop = new WP_Query( $args );

    ob_start();

    if ( $loop->have_posts() ) :
        while ( $loop->have_posts() ) : $loop->the_post();
            global $product;
            ?>
            <a href="<?php the_permalink(); ?>" class="search-result-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                <div class="search-result-image me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                    <?php 
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail( array(50, 50), array( 'class' => 'rounded shadow-sm w-100 h-100', 'style' => 'object-fit: cover;' ) );
                    } else {
                        echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="50" height="50" class="rounded shadow-sm w-100 h-100" style="object-fit: cover;">';
                    }
                    ?>
                </div>
                <div class="search-result-info overflow-hidden">
                    <h6 class="mb-0 text-dark text-truncate"><?php the_title(); ?></h6>
                    <small class="text-muted d-block mt-1">
                        <?php if ( $product->get_sku() ) : ?>
                            <span class="badge bg-light text-dark border me-1"><?php echo esc_html( $product->get_sku() ); ?></span>
                        <?php endif; ?>
                        <?php echo $product->get_price_html(); ?>
                    </small>
                </div>
            </a>
            <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<div class="p-4 text-center text-muted">No se encontraron productos para "' . esc_html($query) . '".</div>';
    endif;

    $content = ob_get_clean();

    wp_send_json_success( array( 'html' => $content ) );
    wp_die();
}

/**
 * WooCommerce Get Cart Items HTML
 */
function expotodo_get_cart_items_html() {
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
 * WooCommerce Cart AJAX Fragments
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'expotodo_cart_fragments' );
function expotodo_cart_fragments( $fragments ) {
    $fragments['span.cart-count'] = '<span class="cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
    $fragments['div.cart-items'] = expotodo_get_cart_items_html();
    $fragments['span.cart-subtotal'] = '<span class="cart-subtotal">' . WC()->cart->get_cart_subtotal() . '</span>';
    $fragments['span.cart-total'] = '<span class="cart-total">' . WC()->cart->get_total() . '</span>';
    
    // Visibilidad del mensaje de vacío
    if ( WC()->cart->is_empty() ) {
        $fragments['div.cart-empty-message'] = '<div class="cart-empty-message text-muted small p-4 text-center">Tu carrito está vacío.</div>';
    } else {
        $fragments['div.cart-empty-message'] = '<div class="cart-empty-message d-none"></div>';
    }

    return $fragments;
}

/**
 * AJAX para obtener datos de Checkout (Métodos de Pago)
 */
add_action( 'wp_ajax_expotodo_get_checkout_data', 'expotodo_get_checkout_data' );
add_action( 'wp_ajax_nopriv_expotodo_get_checkout_data', 'expotodo_get_checkout_data' );

function expotodo_get_checkout_data() {
    if ( ! WC()->cart ) {
        wp_send_json_error( array( 'message' => 'Carrito no inicializado' ) );
    }

    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
    
    ob_start();
    if ( ! empty( $available_gateways ) ) {
        echo '<ul class="list-group list-group-flush payment-methods-list border-0 woocommerce-checkout-payment-methods">';
        foreach ( $available_gateways as $gateway ) {
            ?>
            <li class="list-group-item p-0 mb-3 border-0 bg-transparent wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                <input type="radio" class="btn-check input-radio" name="payment_method" id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" value="<?php echo esc_attr( $gateway->id ); ?>" autocomplete="off" <?php checked( $gateway->chosen, true ); ?>>
                <label class="btn btn-outline-dark w-100 text-start d-flex align-items-center p-3 payment-method-option" for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                    <div class="flex-grow-1">
                        <div class="fw-bold h6 mb-1"><?php echo $gateway->get_title(); ?></div>
                    </div>
                    <?php if ( $gateway->get_icon() ) : ?>
                        <div class="ms-2 payment-icon">
                            <?php echo $gateway->get_icon(); ?>
                        </div>
                    <?php endif; ?>
                </label>
                
                <?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
                    <div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?> mt-2 p-3 bg-light rounded" style="display: none; border: 1px solid #eee;">
                        <?php $gateway->payment_fields(); ?>
                    </div>
                <?php endif; ?>
            </li>
            <?php
        }
        echo '</ul>';
    } else {
        echo '<div class="alert alert-warning small">No hay métodos de pago disponibles. Por favor, contacta con soporte.</div>';
    }
    $html = ob_get_clean();

    wp_send_json_success( array(
        'html'  => $html,
        'total' => WC()->cart->get_total(),
        'count' => WC()->cart->get_cart_contents_count()
    ) );
}

/**
 * Disparadores de prueba manual: Agregado por URL (?test_toast=1) o por AJAX
 */
add_action( 'init', 'expotodo_manual_test_toast' );
function expotodo_manual_test_toast() {
    if ( isset( $_GET['test_toast'] ) ) {
        wc_add_notice( '¡Prueba de Toast exitosa! El sistema funciona.', 'success' );
        wp_redirect( remove_query_arg( 'test_toast' ) );
        exit;
    }
}

add_action( 'wp_ajax_expotodo_add_notice', 'expotodo_ajax_add_notice' );
add_action( 'wp_ajax_nopriv_expotodo_add_notice', 'expotodo_ajax_add_notice' );
function expotodo_ajax_add_notice() {
    wc_add_notice( '¡Mensaje vía AJAX exitoso!', 'success' );
    wp_send_json_success();
}

/**
 * Disparador de prueba manual: Agregado por URL (?test_success=1)
 */
add_action( 'init', 'expotodo_debug_trigger_notice' );
function expotodo_debug_trigger_notice() {
    if ( isset( $_GET['test_success'] ) ) {
        wc_add_notice( '¡Victoria! El sistema de avisos está vivo y deja pasar mensajes legítimos.', 'success' );
    }
}

/**
 * EXTERMINIO 360° - NIVEL PHP (Nacimiento y Entrega)
 * Bloqueamos cualquier mensaje de "Zona" o "México" para que ni siquiera llegue 
 * al sistema de Toasts.
 */
add_filter( 'woocommerce_add_notice', 'expotodo_kill_zone_notices_at_birth', 999, 1 );
function expotodo_kill_zone_notices_at_birth( $notice ) {
    if ( isset($notice['notice']) && (stripos($notice['notice'], 'Zona') !== false || stripos($notice['notice'], 'México') !== false) ) {
        return false;
    }
    return $notice;
}

add_filter( 'woocommerce_get_notices', 'expotodo_kill_zone_notices_at_delivery', 999, 1 );
function expotodo_kill_zone_notices_at_delivery( $notices ) {
    if ( empty( $notices ) ) return $notices;
    $categories = array( 'success', 'notice', 'error' );
    foreach ( $categories as $cat ) {
        if ( ! empty( $notices[$cat] ) ) {
            foreach ( $notices[$cat] as $key => $notice ) {
                if ( isset($notice['notice']) && (stripos($notice['notice'], 'Zona') !== false || stripos($notice['notice'], 'México') !== false) ) {
                    unset( $notices[$cat][$key] );
                }
            }
            $notices[$cat] = array_values( $notices[$cat] );
        }
    }
    return $notices;
}
