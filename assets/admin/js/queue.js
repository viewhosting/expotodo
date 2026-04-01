/**
 * 📊 Boutique Queue: Lógica de Auditoría y Acciones de Correo
 */
function refreshGrid() {
    jQuery.post(ajaxurl, { action: 'expotodo_fetch_queue' }, function (res) {
        if (res.success) {
            jQuery('#email-grid-container').html(res.data);
            jQuery('#sync-indicator').html('✅ Actualizado ahora');
        }
    });
}

function showPreview(id) {
    let body = jQuery('#email-body-' + id).html();
    let sub = jQuery('#email-sub-' + id).text();
    jQuery('#previewTitle').text(sub);
    jQuery('#previewBody').html(body);
    jQuery('#previewModal').fadeIn(200);
}

function showLog(id) {
    let log = jQuery('#email-log-' + id).html();
    jQuery('#logContent').text(log || 'No hay detalles técnicos disponibles.');
    jQuery('#logModal').fadeIn(200);
}

function performAction(action, id) {
    if (!confirm('¿Seguro que deseas realizar esta acción Boutique?')) return;

    jQuery.post(ajaxurl, {
        action: 'expotodo_queue_action',
        email_action: action,
        id: id
    }, function (res) {
        if (res.success) {
            alert(res.data);
            refreshGrid();
        } else {
            alert('Error: ' + res.data);
        }
    });
}

function closeModal(id) {
    jQuery('#' + id).fadeOut(200);
}

// 🛰️ Boutique Auto-Sync: Refresco cada 10 segundos
jQuery(document).ready(function ($) {
    if ($('#email-grid-container').length) {
        setInterval(refreshGrid, 10000);
        refreshGrid();
    }
});
