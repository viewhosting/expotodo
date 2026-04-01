<?php
if (!defined('ABSPATH')) exit;

/**
 * Biblioteca de Diseños Boutique Predeterminados
 * Provee el HTML premium inicial para cada una de las 13 plantillas.
 */
class TemplateDefaults {

    public static function get_default($slug) {
        $brand_color = '#b0d443';
        $methods = array(
            'new_order'                 => 'get_new_order_design',
            'customer_processing_order' => 'get_processing_design',
            'customer_completed_order'  => 'get_completed_design',
            'cancelled_order'           => 'get_cancelled_design',
            'failed_order'              => 'get_failed_design',
            'customer_on_hold_order'    => 'get_on_hold_design',
            'customer_refunded_order'   => 'get_refunded_design',
            'customer_invoice'          => 'get_invoice_design',
            'customer_note'             => 'get_note_design',
            'customer_new_account'      => 'get_new_account_design',
            'customer_reset_password'   => 'get_reset_password_design',
            'contact'                   => 'get_contact_design'
        );

        if (isset($methods[$slug])) {
            return self::{$methods[$slug]}($brand_color);
        }

        return '';
    }

    private static function get_new_order_design($c) {
        return '
        <div style="text-align:center; padding-bottom:30px;">
            <p style="text-transform:uppercase; letter-spacing:2px; font-size:12px; color:#64748b; font-weight:800; margin:0;">Aviso de Sistema</p>
            <h1 style="font-size:32px; margin:10px 0; color:#1a1a1a;">🛍️ Nuevo Pedido # {order_number}</h1>
            <p style="color:#64748b;">Se ha registrado una nueva venta en la boutique.</p>
        </div>
        <div style="background:#f8fafc; border-radius:12px; padding:25px; border:1px solid #e2e8f0;">
            <h3 style="margin-top:0; color:#1a1a1a;">Detalles del Cliente</h3>
            <p style="margin-bottom:0;"><strong>Nombre:</strong> {customer_name}<br><strong>Total:</strong> <span style="color:'.$c.'; font-weight:800;">{order_total}</span></p>
        </div>
        <div style="margin-top:30px;">
            <h3 style="color:#1a1a1a; border-bottom:2px solid #f1f5f9; padding-bottom:10px;">Productos</h3>
            {order_items}
        </div>';
    }

    private static function get_processing_design($c) {
        return '
        <div style="text-align:center;">
            <h1 style="font-size:28px; color:#1a1a1a;">✨ Estamos preparando tu pedido</h1>
            <p style="color:#64748b; font-size:16px;">¡Hola {customer_name}! Tu pedido <strong>#{order_number}</strong> ha sido recibido y nuestro equipo está trabajando en él.</p>
        </div>
        <div style="margin:40px 0; padding:30px; background:'.$c.'10; border-radius:100px; text-align:center; border:1px dashed '.$c.';">
            <span style="color:'.$c.'; font-weight:800; font-size:14px; text-transform:uppercase; letter-spacing:1px;">Estado: Procesando con cuidado</span>
        </div>
        <div>
            <h3 style="color:#1a1a1a;">Resumen de tu selección:</h3>
            {order_items}
            <div style="text-align:right; margin-top:20px; font-size:18px;">
                <strong>Total: <span style="color:'.$c.';">{order_total}</span></strong>
            </div>
        </div>';
    }

    private static function get_completed_design($c) {
        return '
        <div style="text-align:center;">
            <div style="font-size:50px; margin-bottom:20px;">📦</div>
            <h1 style="font-size:28px; color:#1a1a1a;">¡Tu pedido ha sido completado!</h1>
            <p style="color:#64748b;">Hola {customer_name}, excelentes noticias: tu pedido <strong>#{order_number}</strong> ya está finalizado o en camino.</p>
        </div>
        <div style="margin:30px 0; padding:30px; background:#f8fafc; border-radius:16px;">
            <p style="margin:0; color:#1a1a1a; line-height:1.6;">Esperamos que disfrutes tus productos boutique. Si tienes cualquier duda, estamos a un clic de distancia.</p>
        </div>';
    }

    private static function get_cancelled_design($c) {
        return '
        <div style="text-align:center;">
            <h1 style="font-size:24px; color:#ef4444;">Pedido Cancelado #{order_number}</h1>
            <p style="color:#64748b;">Hola {customer_name}, te informamos que tu pedido ha sido cancelado y no será procesado.</p>
        </div>';
    }

    private static function get_failed_design($c) {
        return '
        <div style="text-align:center;">
            <h1 style="font-size:24px; color:#ef4444;">⚠️ Pago Fallido</h1>
            <p style="color:#64748b;">Lo sentimos, el pago de tu pedido <strong>#{order_number}</strong> no pudo procesarse correctamente.</p>
            <a href="{payment_link}" style="display:inline-block; background:'.$c.'; color:#1a1a1a; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:800; margin-top:20px;">Reintentar Pago Ahora</a>
        </div>';
    }

    private static function get_on_hold_design($c) {
        return '
        <div style="text-align:center;">
            <h1 style="font-size:24px; color:#f59e0b;">⏳ Pedido en Espera</h1>
            <p style="color:#64748b;">Hemos recibido tu pedido <strong>#{order_number}</strong>, pero estamos esperando la confirmación de tu pago.</p>
        </div>
        <div style="background:#fff7ed; border-radius:12px; padding:25px; border:1px solid #ffedd5; margin-top:20px;">
            <h4 style="margin:0 0 10px 0; color:#9a3412;">Instrucciones de Pago:</h4>
            <div style="color:#c2410c; font-size:14px;">{payment_instructions}</div>
        </div>';
    }

    private static function get_refunded_design($c) {
        return '
        <div style="text-align:center;">
            <h1 style="font-size:24px; color:#1a1a1a;">Confirmación de Reembolso</h1>
            <p style="color:#64748b;">Se ha procesado un reembolso de <strong>{refund_amount}</strong> para tu pedido #{order_number}.</p>
        </div>';
    }

    private static function get_invoice_design($c) {
        return '
        <h2 style="color:#1a1a1a;">Detalles de tu Factura Boutique</h2>
        <p>Hola {customer_name}, adjuntamos o enlazamos la información de depósito para tu pedido #{order_number}.</p>
        <div style="text-align:center; margin:40px 0;">
            <a href="{invoice_link}" style="background:'.$c.'; color:#1a1a1a; padding:18px 40px; border-radius:12px; text-decoration:none; font-weight:800;">Descargar Factura / Detalles</a>
        </div>';
    }

    private static function get_note_design($c) {
        return '
        <div style="background:#f1f5f9; padding:30px; border-radius:16px; border-left:6px solid '.$c.';">
            <p style="margin:0; font-size:14px; color:#475569; text-transform:uppercase; letter-spacing:1px;">Nueva nota en tu pedido #{order_number}:</p>
            <div style="margin-top:15px; font-size:18px; color:#1e293b; line-height:1.6; font-style:italic;">"{note_content}"</div>
        </div>';
    }

    private static function get_new_account_design($c) {
        return '
        <div style="text-align:center;">
            <h1 style="font-size:32px; color:#1a1a1a;">💎 Bienvenido a la Élite</h1>
            <p style="color:#64748b; font-size:18px;">Tu cuenta en <strong>Expotodo Boutique</strong> ha sido creada con éxito.</p>
        </div>
        <div style="margin:40px 0; padding:30px; background:#f8fafc; border-radius:16px; text-align:center;">
            <p style="margin-bottom:20px;">Tu nombre de usuario es: <strong>{user_login}</strong></p>
            <a href="{set_pass_link}" style="background:'.$c.'; color:#1a1a1a; padding:15px 30px; border-radius:8px; text-decoration:none; font-weight:800;">Establecer mi Contraseña</a>
        </div>';
    }

    private static function get_reset_password_design($c) {
        return '
        <h2 style="color:#1a1a1a;">Solicitud de Cambio de Contraseña</h2>
        <p>Alguien ha solicitado restablecer la contraseña para la cuenta <strong>{user_login}</strong>.</p>
        <p>Si no fuiste tú, ignora este mensaje. Si deseas continuar, haz clic en el botón:</p>
        <div style="text-align:center; margin:30px 0;">
            <a href="{reset_link}" style="background:'.$c.'; color:#1a1a1a; padding:15px 35px; border-radius:8px; text-decoration:none; font-weight:800;">Restablecer Contraseña</a>
        </div>';
    }

    private static function get_contact_design($c) {
        return '
        <div style="border-bottom:2px solid #f1f5f9; padding-bottom:20px; margin-bottom:20px;">
            <h2 style="margin:0; color:#1a1a1a;">Respuesta a tu consulta</h2>
            <p style="color:#64748b; margin:5px 0;">Referencia: {subject}</p>
        </div>
        <p>Hola <strong>{name}</strong>,</p>
        <div style="font-size:16px; color:#1e293b; line-height:1.6; margin:20px 0;">
            {message}
        </div>
        <p style="color:#64748b; font-size:14px; margin-top:40px;">Gracias por contactar con la Boutique de Expotodo.</p>';
    }
}
