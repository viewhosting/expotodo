<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador del Cortafuegos de Seguridad (WAF) y Bloqueador de IP
 * Detecta inyecciones SQL, XSS, Path Traversal, Bot Traps, gestiona bloqueos y ofrece panel de control.
 */
class FirewallController {

    public function __construct() {
        // Inicializar la tabla de base de datos
        add_action( 'after_setup_theme', array( $this, 'setup_table' ) );

        // Ejecutar el WAF antes que cualquier otra cosa en peticiones públicas
        add_action( 'init', array( $this, 'check_request' ), 1 );

        // Encolar assets de administración solo en nuestra página
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Endpoints AJAX para el panel de control
        add_action( 'wp_ajax_expotodo_firewall_action', array( $this, 'ajax_handle_action' ) );
    }

    /**
     * Crea la tabla de base de datos para los registros de firewall
     */
    public function setup_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_firewall_log';
        $charset_collate = $wpdb->get_charset_collate();

        $this->write_debug_log("setup_table() running. Table: $table_name");

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ip varchar(45) NOT NULL,
            request_uri varchar(255) NOT NULL,
            attack_type varchar(50) NOT NULL,
            user_agent varchar(255) DEFAULT '',
            payload text DEFAULT '',
            status varchar(20) NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            KEY ip (ip)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Verificar si la tabla existe después de dbDelta
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        $this->write_debug_log("setup_table() finished. Table exists: " . ($table_exists ? 'YES' : 'NO'));
    }

    /**
     * Encola los scripts y estilos necesarios para el panel del administrador
     */
    public function enqueue_admin_assets() {
        if ( isset($_GET['page']) && $_GET['page'] === 'expotodo-firewall' ) {
            wp_enqueue_script( 'expotodo-firewall-admin-js', get_template_directory_uri() . '/assets/js/firewall-admin.js', array('jquery'), '1.0.0', true );
            wp_localize_script( 'expotodo-firewall-admin-js', 'expotodo_firewall_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'expotodo_firewall_nonce' )
            ));
        }
    }

    /**
     * Obtiene la dirección IP real del visitante manejando Cloudflare y Proxies
     */
    private function get_visitor_ip() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ( $ip_headers as $header ) {
            if ( ! empty( $_SERVER[$header] ) ) {
                $ips = explode( ',', $_SERVER[$header] );
                $ip  = trim( $ips[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Guarda un evento de seguridad en la base de datos y mantiene la tabla limpia
     */
    private function log_firewall_event( $ip, $uri, $type, $payload, $status ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_firewall_log';

        $this->write_debug_log("log_firewall_event() inserting: IP=$ip, Type=$type, Status=$status, URI=$uri");

        $result = $wpdb->insert( $table_name, array(
            'ip'          => $ip,
            'request_uri' => substr( $uri, 0, 255 ),
            'attack_type' => $type,
            'user_agent'  => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
            'payload'     => substr( $payload, 0, 1000 ), // limitar tamaño
            'status'      => $status,
            'created_at'  => current_time('mysql')
        ));

        if ( $result === false ) {
            $this->write_debug_log("log_firewall_event() FAILED. Error: " . $wpdb->last_error);
        } else {
            $this->write_debug_log("log_firewall_event() SUCCESS. Inserted ID: " . $wpdb->insert_id);
        }

        // Limpieza automática periódica: conservar solo los últimos 1000 registros
        if ( rand(1, 100) <= 10 ) {
            $max_id = $wpdb->get_var( "SELECT id FROM $table_name ORDER BY id DESC LIMIT 1 OFFSET 1000" );
            if ( $max_id ) {
                $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id <= %d", $max_id ) );
            }
        }
    }

    /**
     * Intercepta y analiza las peticiones públicas
     */
    public function check_request() {
        $ip = $this->get_visitor_ip();
        $uri_raw = $_SERVER['REQUEST_URI'];
        $this->write_debug_log("check_request() entered. IP: $ip, URI: $uri_raw");

        if ( is_admin() ) {
            // No loguear solicitudes de admin para no llenar el log
            return;
        }

        // Evitar autobloqueos: omitir si es un administrador logueado
        if ( current_user_can('manage_options') ) {
            $this->write_debug_log("Bypassing check: User is an Administrator (manage_options).");
            return;
        }

        // Omitir si el cortafuegos está desactivado
        if ( get_option( 'expotodo_firewall_enabled', 'yes' ) !== 'yes' ) {
            $this->write_debug_log("Bypassing check: Firewall is disabled in options.");
            return;
        }

        // 1. Validar lista blanca
        $whitelist = get_option( 'expotodo_firewall_whitelist', array() );
        if ( in_array( $ip, $whitelist, true ) ) {
            $this->write_debug_log("Bypassing check: IP $ip is whitelisted.");
            return;
        }

        // 2. Validar lista negra (bloqueos manuales permanentes)
        $blacklist = get_option( 'expotodo_firewall_blacklist', array() );
        if ( in_array( $ip, $blacklist, true ) ) {
            $this->write_debug_log("Blocking: IP $ip is blacklisted. Rendering block page.");
            $this->log_firewall_event( $ip, $_SERVER['REQUEST_URI'], 'Blacklist Manual', '', 'blocked' );
            $this->render_blocked_page( $ip, 'Dirección IP bloqueada manualmente por el administrador.' );
        }

        // 3. Validar bloqueos temporales (Transients)
        if ( get_transient( 'expotodo_ban_' . $ip ) ) {
            $this->write_debug_log("Blocking: IP $ip is temporarily banned. Rendering block page.");
            $this->render_blocked_page( $ip, 'Bloqueo automático tras múltiples intentos sospechosos.' );
        }

        // 4. Analizar la petición para detectar ataques
        $is_attack = false;
        $attack_type = '';
        $payload = '';

        $uri   = urldecode( $_SERVER['REQUEST_URI'] );
        $query = urldecode( $_SERVER['QUERY_STRING'] );

        $scan_data = array(
            'URI'   => $uri,
            'Query' => $query,
        );

        if ( ! empty($_POST) ) {
            $scan_data['POST'] = json_encode($_POST);
        }

        // Firmas y patrones de ataque
        $rules = array(
            'SQL Injection' => array(
                '/union\s+(all\s+)?select/i',
                '/select\s+.*\s+from/i',
                '/insert\s+into/i',
                '/update\s+.*\s+set/i',
                '/delete\s+from/i',
                '/information_schema/i',
                '/group_concat/i',
            ),
            'Cross-Site Scripting (XSS)' => array(
                '/<script/i',
                '/javascript:/i',
                '/onload\s*=/i',
                '/onerror\s*=/i',
                '/alert\(/i',
                '/eval\(/i',
                '/base64_decode/i',
            ),
            'Directory Traversal / File Inclusion' => array(
                '/\.\.\//',
                '/\.\.\\\\/',
                '/etc\/passwd/i',
                '/wp-config\.php/i',
                '/\.env/i',
            ),
        );

        // Trampas para bots (Escaneos de archivos que los humanos no consultan)
        $bot_traps = array(
            '/\.env$/i',
            '/\.git\//i',
            '/wp-config\.php\.bak/i',
            '/composer\.json$/i',
            '/xmlrpc\.php$/i',
        );

        foreach ( $bot_traps as $pattern ) {
            if ( preg_match( $pattern, $uri ) ) {
                $is_attack = true;
                $attack_type = 'Escaneo de Archivos (Bot Trap)';
                $payload = $uri;
                break;
            }
        }

        if ( ! $is_attack ) {
            foreach ( $rules as $type => $patterns ) {
                foreach ( $patterns as $pattern ) {
                    foreach ( $scan_data as $key => $val ) {
                        if ( preg_match( $pattern, $val ) ) {
                            $is_attack = true;
                            $attack_type = $type;
                            $payload = $key . ': ' . $val;
                            break 3;
                        }
                    }
                }
            }
        }

        if ( $is_attack ) {
            $this->write_debug_log("ATTACK DETECTED: Type: $attack_type | Payload: $payload");
            // Incrementar contador de strikes
            $strikes_key = 'expotodo_strikes_' . $ip;
            $strikes = (int) get_transient( $strikes_key );
            $strikes++;

            // Si es un escaneo de archivos sensible (bot trap) bloqueamos al instante. 
            // Si es SQLi/XSS, permitimos 3 avisos antes del baneo de 24 horas.
            if ( $strikes >= 3 || $attack_type === 'Escaneo de Archivos (Bot Trap)' ) {
                $this->write_debug_log("Banning IP $ip due to strikes ($strikes) or Bot Trap.");
                set_transient( 'expotodo_ban_' . $ip, true, 24 * HOUR_IN_SECONDS );
                delete_transient( $strikes_key );

                $this->log_firewall_event( $ip, $uri, $attack_type, $payload, 'blocked' );
                $this->render_blocked_page( $ip, $attack_type );
            } else {
                $this->write_debug_log("Strike registered for IP $ip. Total strikes: $strikes");
                set_transient( $strikes_key, $strikes, 1 * HOUR_IN_SECONDS );
                $this->log_firewall_event( $ip, $uri, $attack_type, $payload, 'warned' );
            }
        } else {
            $this->write_debug_log("Request is safe. No attack detected.");
        }
    }

    /**
     * Renderiza la plantilla HTTP 403 Forbidden estilizada con marca Expotodo
     */
    private function render_blocked_page( $ip, $reason ) {
        status_header( 403 );
        nocache_headers();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Acceso Denegado - Expotodo</title>
            <style>
                body {
                    background-color: #0f172a;
                    color: #f8fafc;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                    padding: 20px;
                    box-sizing: border-box;
                }
                .card {
                    background-color: #1e293b;
                    border: 1px solid rgba(255, 255, 255, 0.05);
                    border-radius: 20px;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                    width: 100%;
                    max-width: 500px;
                    padding: 40px 30px;
                    text-align: center;
                    box-sizing: border-box;
                }
                .logo {
                    max-width: 160px;
                    height: auto;
                    margin-bottom: 25px;
                }
                .icon {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    background-color: rgba(239, 68, 68, 0.1);
                    color: #ef4444;
                    font-size: 32px;
                    font-weight: bold;
                    margin-bottom: 20px;
                }
                h1 {
                    font-size: 22px;
                    font-weight: 700;
                    margin: 0 0 15px 0;
                    color: #f1f5f9;
                }
                p {
                    color: #94a3b8;
                    font-size: 14px;
                    line-height: 1.6;
                    margin: 0 0 25px 0;
                }
                .info-box {
                    background-color: #0f172a;
                    border-radius: 10px;
                    padding: 15px;
                    text-align: left;
                    font-family: monospace;
                    font-size: 13px;
                    margin-bottom: 30px;
                    border: 1px solid rgba(255, 255, 255, 0.02);
                }
                .info-row {
                    display: flex;
                    margin-bottom: 8px;
                }
                .info-row:last-child {
                    margin-bottom: 0;
                }
                .info-label {
                    color: #64748b;
                    width: 90px;
                    flex-shrink: 0;
                }
                .info-value {
                    color: #cbd5e1;
                    word-break: break-all;
                }
                .btn {
                    display: inline-block;
                    background-color: #b0d443;
                    color: #1e293b;
                    text-decoration: none;
                    padding: 12px 25px;
                    border-radius: 8px;
                    font-weight: 700;
                    font-size: 14px;
                    transition: all 0.2s ease;
                }
                .btn:hover {
                    background-color: #8ca835;
                    color: #ffffff;
                }
            </style>
        </head>
        <body>
            <div class="card">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="Expotodo" class="logo">
                <div class="icon">✕</div>
                <h1>Acceso Denegado (403)</h1>
                <p>Tu dirección IP ha sido bloqueada temporalmente por nuestro cortafuegos al detectar peticiones que violan las políticas de seguridad.</p>
                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Tu IP:</span>
                        <span class="info-value"><?php echo esc_html( $ip ); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Detección:</span>
                        <span class="info-value"><?php echo esc_html( $reason ); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha (UTC):</span>
                        <span class="info-value"><?php echo gmdate('Y-m-d H:i:s'); ?></span>
                    </div>
                </div>
                <a href="mailto:<?php echo esc_attr( get_option('admin_email') ); ?>?subject=IP%20Bloqueada%20<?php echo esc_attr( $ip ); ?>" class="btn">Contactar Soporte</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Renderiza el Panel de Control del Cortafuegos en el backend
     */
    public static function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_firewall_log';

        // 1. Obtener opciones de configuración
        $enabled   = get_option( 'expotodo_firewall_enabled', 'yes' );
        $blacklist = get_option( 'expotodo_firewall_blacklist', array() );
        $whitelist = get_option( 'expotodo_firewall_whitelist', array() );

        // 2. Obtener estadísticas e historial
        $recent_logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC LIMIT 50" );
        $total_attacks = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'blocked'" );
        $total_warnings = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'warned'" );

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Centro de Seguridad Expotodo (WAF)</h1>
            <hr class="wp-header-end">

            <!-- Estado y Configuración General -->
            <div class="notice notice-info inline" style="margin: 15px 0; padding: 15px; display: flex; align-items: center; justify-content: space-between; border-radius: 6px;">
                <div>
                    <h3 style="margin: 0 0 5px 0;">Estado del Cortafuegos: 
                        <span id="firewall-status-badge" style="padding: 3px 8px; border-radius: 4px; font-size: 13px; color: #fff; background-color: <?php echo ($enabled === 'yes') ? '#46b450' : '#dc3232'; ?>;">
                            <?php echo ($enabled === 'yes') ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </h3>
                    <p style="margin: 0; color: #64748b;">El cortafuegos inspecciona en tiempo real peticiones en búsqueda de SQLi, XSS, Path Traversal e IP Bans.</p>
                </div>
                <div>
                    <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                        <input type="checkbox" id="toggle-firewall-state" value="yes" <?php checked($enabled, 'yes'); ?> style="opacity: 0; width: 0; height: 0;">
                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo ($enabled === 'yes') ? '#46b450' : '#ccc'; ?>; transition: .4s; border-radius: 34px;"></span>
                    </label>
                </div>
            </div>

            <!-- Grid de Estadísticas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div class="card" style="padding: 20px; margin: 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #b0d443;">
                    <h3 style="margin: 0; color: #64748b; font-size: 14px;">IPs Bloqueadas Permanentes</h3>
                    <p style="font-size: 28px; font-weight: bold; margin: 10px 0 0 0;" id="count-blacklist"><?php echo count($blacklist); ?></p>
                </div>
                <div class="card" style="padding: 20px; margin: 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #2ea2cc;">
                    <h3 style="margin: 0; color: #64748b; font-size: 14px;">Total de Bloqueos Registrados</h3>
                    <p style="font-size: 28px; font-weight: bold; margin: 10px 0 0 0;" id="count-total-attacks"><?php echo (int) $total_attacks; ?></p>
                </div>
                <div class="card" style="padding: 20px; margin: 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #dba617;">
                    <h3 style="margin: 0; color: #64748b; font-size: 14px;">Total de Advertencias (Strikes)</h3>
                    <p style="font-size: 28px; font-weight: bold; margin: 10px 0 0 0;" id="count-total-warnings"><?php echo (int) $total_warnings; ?></p>
                </div>
                <div class="card" style="padding: 20px; margin: 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #46b450;">
                    <h3 style="margin: 0; color: #64748b; font-size: 14px;">IPs en Lista Blanca</h3>
                    <p style="font-size: 28px; font-weight: bold; margin: 10px 0 0 0;" id="count-whitelist"><?php echo count($whitelist); ?></p>
                </div>
            </div>
             <!-- Fila para Gestión Manual de IPs (3 columnas) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <!-- Bloqueador Manual -->
                <div class="card" style="padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 0;">
                    <h3 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">Gestión Manual de IPs</h3>
                    <form id="form-firewall-add-ip">
                        <div style="margin-bottom: 12px;">
                            <label for="manual-ip" style="display: block; font-weight: bold; margin-bottom: 6px;">Dirección IP (IPv4 / IPv6)</label>
                            <input type="text" id="manual-ip" class="regular-text" placeholder="Ej. 187.142.229.108" style="width: 100%;" required>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="button button-primary" id="btn-manual-block" style="flex: 1; background: #dc3232; border-color: #dc3232;">Bloquear IP</button>
                            <button type="button" class="button button-secondary" id="btn-manual-whitelist" style="flex: 1; border-color: #46b450; color: #46b450;">Lista Blanca</button>
                        </div>
                    </form>
                </div>

                <!-- IPs Bloqueadas (Blacklist) -->
                <div class="card" style="padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 0;">
                    <h3 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">Lista Negra (Baneadas)</h3>
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="wp-list-table widefat" style="border: none; box-shadow: none;">
                            <tbody id="blacklist-tbody">
                                <?php if ( $blacklist ) : foreach ( $blacklist as $ip ) : ?>
                                    <tr id="blacklist-row-<?php echo esc_attr( str_replace('.', '-', $ip) ); ?>">
                                        <td><strong><?php echo esc_html($ip); ?></strong></td>
                                        <td style="text-align: right;">
                                            <button class="button button-small btn-unblock-ip" data-ip="<?php echo esc_attr($ip); ?>">Desbloquear</button>
                                        </td>
                                    </tr>
                                <?php endforeach; else : ?>
                                    <tr class="no-ips"><td colspan="2" style="color: #64748b; font-style: italic;">No hay IPs bloqueadas permanentemente.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- IPs de Confianza (Whitelist) -->
                <div class="card" style="padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 0;">
                    <h3 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">Lista Blanca (Inmunes)</h3>
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="wp-list-table widefat" style="border: none; box-shadow: none;">
                            <tbody id="whitelist-tbody">
                                <?php if ( $whitelist ) : foreach ( $whitelist as $ip ) : ?>
                                    <tr id="whitelist-row-<?php echo esc_attr( str_replace('.', '-', $ip) ); ?>">
                                        <td><strong><?php echo esc_html($ip); ?></strong></td>
                                        <td style="text-align: right;">
                                            <button class="button button-small btn-unwhitelist-ip" data-ip="<?php echo esc_attr($ip); ?>">Quitar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; else : ?>
                                    <tr class="no-ips"><td colspan="2" style="color: #64748b; font-style: italic;">No hay IPs en lista blanca.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Contenido del Panel (Historial de Logs 100% de Ancho) -->
            <div style="margin-bottom: 25px;">
                <div class="card" style="padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 0; width: 100%; max-width: none; box-sizing: border-box;">
                    <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">Historial de Solicitudes y Ataques Recientes (Últimas 50)</h2>
                    <div style="overflow-x: auto;">
                        <table class="wp-list-table widefat fixed striped" style="border: none;">
                            <thead>
                                <tr>
                                    <th width="140">Fecha</th>
                                    <th width="130">Dirección IP</th>
                                    <th width="150">Ataque Detectado</th>
                                    <th>Petición URI / Carga Útil (Payload)</th>
                                    <th width="110">Estado</th>
                                    <th width="90" style="text-align: right;">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="firewall-logs-tbody">
                                <?php if ( $recent_logs ) : foreach ( $recent_logs as $log ) : 
                                    $is_banned = in_array( $log->ip, $blacklist, true );
                                ?>
                                    <tr>
                                        <td><small><?php echo date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at) ); ?></small></td>
                                        <td>
                                            <strong><?php echo esc_html( $log->ip ); ?></strong>
                                        </td>
                                        <td><span class="badge" style="background-color: #f3f4f6; color: #374151; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo esc_html($log->attack_type); ?></span></td>
                                        <td>
                                            <code style="word-break: break-all; font-size: 11px;"><?php echo esc_html($log->request_uri); ?></code>
                                            <?php if ( $log->payload ) : ?>
                                                <div style="margin-top: 4px; font-size: 10px; color: #64748b; max-height: 50px; overflow-y: auto;">
                                                    <strong>Payload:</strong> <code><?php echo esc_html($log->payload); ?></code>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-weight: bold; color: <?php echo ($log->status === 'blocked') ? '#dc3232' : '#dba617'; ?>;">
                                                <?php echo ($log->status === 'blocked') ? 'Bloqueado ✕' : 'Advertencia ⚠'; ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php if ( ! $is_banned && ! in_array( $log->ip, $whitelist, true ) ) : ?>
                                                <button class="button button-small btn-block-ip" data-ip="<?php echo esc_attr($log->ip); ?>">Bloquear</button>
                                            <?php else : ?>
                                                <span style="color: #64748b; font-size: 11px; font-style: italic;">Sin acción</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; else : ?>
                                    <tr><td colspan="6" class="text-center">No se han registrado incidentes de seguridad aún.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

           
        </div>

        <style>
            .wrap .card {
                max-width: none;
            }
            .switch input:checked + .slider { background-color: #46b450; }
            .switch input:focus + .slider { box-shadow: 0 0 1px #46b450; }
            .switch input:checked + .slider:before { transform: translateX(24px); }
            .slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
        </style>
        <?php
    }

    /**
     * AJAX endpoint para procesar todas las solicitudes del panel de control
     */
    public function ajax_handle_action() {
        check_ajax_referer( 'expotodo_firewall_nonce', 'nonce' );

        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error( array( 'message' => 'No tienes permisos suficientes.' ) );
        }

        $firewall_action = isset( $_POST['firewall_action'] ) ? sanitize_key( $_POST['firewall_action'] ) : '';

        switch ( $firewall_action ) {
            case 'toggle_firewall':
                $enabled = ( isset($_POST['enabled']) && $_POST['enabled'] === 'yes' ) ? 'yes' : 'no';
                update_option( 'expotodo_firewall_enabled', $enabled );
                wp_send_json_success( array( 
                    'message' => 'Cortafuegos ' . ($enabled === 'yes' ? 'Activado' : 'Desactivado') . ' correctamente.',
                    'status'  => $enabled
                ) );
                break;

            case 'block_ip':
                $ip = isset($_POST['ip']) ? sanitize_text_field(wp_unslash($_POST['ip'])) : '';
                if ( filter_var($ip, FILTER_VALIDATE_IP) ) {
                    $blacklist = get_option( 'expotodo_firewall_blacklist', array() );
                    if ( ! in_array($ip, $blacklist, true) ) {
                        $blacklist[] = $ip;
                        update_option( 'expotodo_firewall_blacklist', $blacklist );

                        // Eliminar de lista blanca e strikes temporales si existieran
                        $whitelist = get_option( 'expotodo_firewall_whitelist', array() );
                        if ( ($key = array_search($ip, $whitelist)) !== false ) {
                            unset($whitelist[$key]);
                            update_option( 'expotodo_firewall_whitelist', array_values($whitelist) );
                        }

                        delete_transient( 'expotodo_ban_' . $ip );
                        delete_transient( 'expotodo_strikes_' . $ip );

                        $this->log_firewall_event( $ip, 'Panel Administración', 'Bloqueo Manual', '', 'blocked' );

                        wp_send_json_success( array( 
                            'message'   => "La dirección IP $ip ha sido bloqueada permanentemente.",
                            'ip'        => $ip,
                            'row_id'    => str_replace('.', '-', $ip),
                            'blacklist' => get_option( 'expotodo_firewall_blacklist', array() ),
                            'whitelist' => get_option( 'expotodo_firewall_whitelist', array() )
                        ) );
                    }
                    wp_send_json_error( array( 'message' => 'Esta dirección IP ya se encuentra bloqueada.' ) );
                }
                wp_send_json_error( array( 'message' => 'La dirección IP ingresada no es válida.' ) );
                break;

            case 'unblock_ip':
                $ip = isset($_POST['ip']) ? sanitize_text_field(wp_unslash($_POST['ip'])) : '';
                $blacklist = get_option( 'expotodo_firewall_blacklist', array() );
                $removed = false;

                if ( ($key = array_search($ip, $blacklist)) !== false ) {
                    unset($blacklist[$key]);
                    update_option( 'expotodo_firewall_blacklist', array_values($blacklist) );
                    $removed = true;
                }

                // También limpiar strikes y transitorios de ban temporal
                if ( get_transient( 'expotodo_ban_' . $ip ) ) {
                    delete_transient( 'expotodo_ban_' . $ip );
                    $removed = true;
                }
                delete_transient( 'expotodo_strikes_' . $ip );

                if ( $removed ) {
                    wp_send_json_success( array( 
                        'message'   => "La dirección IP $ip ha sido desbloqueada.",
                        'ip'        => $ip,
                        'blacklist' => get_option( 'expotodo_firewall_blacklist', array() )
                    ) );
                }
                wp_send_json_error( array( 'message' => 'La dirección IP no está en la lista de bloqueos.' ) );
                break;

            case 'whitelist_ip':
                $ip = isset($_POST['ip']) ? sanitize_text_field(wp_unslash($_POST['ip'])) : '';
                if ( filter_var($ip, FILTER_VALIDATE_IP) ) {
                    $whitelist = get_option( 'expotodo_firewall_whitelist', array() );
                    if ( ! in_array($ip, $whitelist, true) ) {
                        $whitelist[] = $ip;
                        update_option( 'expotodo_firewall_whitelist', $whitelist );

                        // Remover de lista negra si estuviera bloqueado
                        $blacklist = get_option( 'expotodo_firewall_blacklist', array() );
                        if ( ($key = array_search($ip, $blacklist)) !== false ) {
                            unset($blacklist[$key]);
                            update_option( 'expotodo_firewall_blacklist', array_values($blacklist) );
                        }
                        
                        delete_transient( 'expotodo_ban_' . $ip );
                        delete_transient( 'expotodo_strikes_' . $ip );

                        wp_send_json_success( array( 
                            'message'   => "La dirección IP $ip ha sido declarada inmune (Lista Blanca).",
                            'ip'        => $ip,
                            'row_id'    => str_replace('.', '-', $ip),
                            'blacklist' => get_option( 'expotodo_firewall_blacklist', array() ),
                            'whitelist' => get_option( 'expotodo_firewall_whitelist', array() )
                        ) );
                    }
                    wp_send_json_error( array( 'message' => 'La dirección IP ya se encuentra en la lista blanca.' ) );
                }
                wp_send_json_error( array( 'message' => 'La dirección IP ingresada no es válida.' ) );
                break;

            case 'unwhitelist_ip':
                $ip = isset($_POST['ip']) ? sanitize_text_field(wp_unslash($_POST['ip'])) : '';
                $whitelist = get_option( 'expotodo_firewall_whitelist', array() );

                if ( ($key = array_search($ip, $whitelist)) !== false ) {
                    unset($whitelist[$key]);
                    update_option( 'expotodo_firewall_whitelist', array_values($whitelist) );
                    wp_send_json_success( array( 
                        'message'   => "IP $ip removida de la lista blanca con éxito.",
                        'ip'        => $ip,
                        'whitelist' => get_option( 'expotodo_firewall_whitelist', array() )
                    ) );
                }
                wp_send_json_error( array( 'message' => 'La dirección IP no está en la lista blanca.' ) );
                break;

            case 'get_logs':
                global $wpdb;
                $table_name = $wpdb->prefix . 'expotodo_firewall_log';
                $recent_logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC LIMIT 50" );
                $blacklist = get_option( 'expotodo_firewall_blacklist', array() );
                $whitelist = get_option( 'expotodo_firewall_whitelist', array() );
                $total_attacks = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'blocked'" );
                $total_warnings = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE status = 'warned'" );

                ob_start();
                if ( $recent_logs ) {
                    foreach ( $recent_logs as $log ) {
                        $is_banned = in_array( $log->ip, $blacklist, true );
                        $is_whitelisted = in_array( $log->ip, $whitelist, true );
                        ?>
                        <tr>
                            <td><small><?php echo date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at) ); ?></small></td>
                            <td>
                                <strong><?php echo esc_html( $log->ip ); ?></strong>
                            </td>
                            <td><span class="badge" style="background-color: #f3f4f6; color: #374151; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo esc_html($log->attack_type); ?></span></td>
                            <td>
                                <code style="word-break: break-all; font-size: 11px;"><?php echo esc_html($log->request_uri); ?></code>
                                <?php if ( $log->payload ) : ?>
                                    <div style="margin-top: 4px; font-size: 10px; color: #64748b; max-height: 50px; overflow-y: auto;">
                                        <strong>Payload:</strong> <code><?php echo esc_html($log->payload); ?></code>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-weight: bold; color: <?php echo ($log->status === 'blocked') ? '#dc3232' : '#dba617'; ?>;">
                                    <?php echo ($log->status === 'blocked') ? 'Bloqueado ✕' : 'Advertencia ⚠'; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <?php if ( ! $is_banned && ! $is_whitelisted ) : ?>
                                    <button class="button button-small btn-block-ip" data-ip="<?php echo esc_attr($log->ip); ?>">Bloquear</button>
                                <?php else : ?>
                                    <span style="color: #64748b; font-size: 11px; font-style: italic;">Sin acción</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr><td colspan="6" class="text-center">No se han registrado incidentes de seguridad aún.</td></tr>
                    <?php
                }
                $html = ob_get_clean();

                wp_send_json_success( array(
                    'html' => $html,
                    'stats' => array(
                        'blacklist_count' => count($blacklist),
                        'whitelist_count' => count($whitelist),
                        'total_attacks'   => (int) $total_attacks,
                        'total_warnings'  => (int) $total_warnings
                    )
                ) );
                break;

            default:
                wp_send_json_error( array( 'message' => 'Operación no reconocida.' ) );
        }
    }

    /**
     * Escribir mensaje de depuración en el archivo log.log del tema
     */
    private function write_debug_log( $message ) {
        $log_file = get_template_directory() . '/log.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents( $log_file, "[$timestamp] $message\n", FILE_APPEND );
    }
}
