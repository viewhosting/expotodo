/**
 * 📋 Boutique Templates: Lógica del Gestor de Plantillas
 */
function sendTest(type) {
    let content = '';
    if (window.tinyMCE && window.tinyMCE.get('template_body')) {
        content = window.tinyMCE.get('template_body').getContent();
    } else {
        content = jQuery('#template_body').val();
    }

    let email = jQuery('#target_test_email').val();
    if (!confirm('¿Deseas enviar una prueba de "' + type + '"?')) return;

    jQuery.post(ajaxurl, {
        action: 'expotodo_test_template',
        template_type: type,
        template_content: content,
        test_email: email
    }, function (res) {
        alert(res.data);
    });
}

function insertTag(tag) {
    if (window.tinyMCE && window.tinyMCE.get('template_body')) {
        window.tinyMCE.get('template_body').insertContent(tag);
    } else {
        let text = jQuery('#template_body').val();
        jQuery('#template_body').val(text + tag);
    }
}
