/**
 * ⚙️ Boutique Settings: Lógica de Configuración y Pruebas SMTP
 */
jQuery(document).ready(function ($) {
    $('#btn_test_smtp').on('click', function () {
        const email = $('#test_smtp_email').val();
        if (!email) return alert('Ingresa un correo');
        const btn = $(this);
        btn.prop('disabled', true).text('⏳ Enviando...');
        $('#test_smtp_status').html(' Procesando...').css('color', '#666');

        jQuery.post(ajaxurl, {
            action: 'expotodo_test_smtp',
            email: email,
            _ajax_nonce: expotodo_admin_params.test_smtp_nonce
        }, function (res) {
            btn.prop('disabled', false).text('Realizar Prueba');
            if (res.success) $('#test_smtp_status').html(' ✅ Éxito').css('color', '#059669');
            else $('#test_smtp_status').html(' ❌ Error: ' + res.data).css('color', '#dc2626');
        });
    });
});
