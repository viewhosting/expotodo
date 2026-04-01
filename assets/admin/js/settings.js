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

    // 🖼️ Selector de Medios y Previsualización para el Fondo del Footer Premium
    const $footerBgInput = $('#footer_bg');
    const $footerPreviewWrapper = $('#footer-preview-wrapper');
    const $footerPreviewBox = $('#footer_bg_preview_img');

    function updateFooterPreview() {
        const url = $footerBgInput.val();
        const align = $('input[name="footer_bg_align"]:checked').val() || 'center center';

        if (url) {
            $footerPreviewWrapper.show();
            let safeUrl = url.replace(/'/g, "\\'");
            $footerPreviewBox.css({
                'background-image': "url('" + safeUrl + "')",
                'background-position': align
            });
        } else {
            $footerPreviewWrapper.hide();
        }
    }

    $footerBgInput.on('input change', updateFooterPreview);
    // Escuchar el cambio en cualquier radio button de la grid
    $('input[name="footer_bg_align"]').on('change', updateFooterPreview);

    $('#btn_upload_footer_bg').on('click', function (e) {
        e.preventDefault();

        const frame = wp.media({
            title: 'Seleccionar Fondo para Footer',
            button: { text: 'Usar como fondo' },
            multiple: false
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            $footerBgInput.val(attachment.url).trigger('change');
        });

        frame.open();
    });
});
