jQuery(document).ready(function ($) {
    // Verificar que los parámetros globales de firewall estén disponibles
    if (typeof expotodo_firewall_params === 'undefined') {
        console.error('expotodo_firewall_params no está definido.');
        return;
    }

    const ajaxUrl = expotodo_firewall_params.ajax_url;
    const nonce = expotodo_firewall_params.nonce;

    // Helper para mostrar notificaciones simples en el panel
    function showNotification(message, isError = false) {
        // Remover notificaciones anteriores
        $('.firewall-notice').remove();

        const noticeClass = isError ? 'notice-error' : 'notice-success';
        const noticeHtml = `
            <div class="notice ${noticeClass} is-dismissible firewall-notice" style="margin: 15px 0;">
                <p><strong>${message}</strong></p>
                <button type="button" class="notice-dismiss" onclick="jQuery(this).parent().fadeOut();"><span class="screen-reader-text">Descartar</span></button>
            </div>
        `;
        $('.wp-heading-inline').parent().after(noticeHtml);

        // Auto ocultar después de 4 segundos
        setTimeout(function () {
            $('.firewall-notice').fadeOut(function () {
                $(this).remove();
            });
        }, 4000);
    }

    // 1. Activar / Desactivar Cortafuegos
    $('#toggle-firewall-state').on('change', function () {
        const isChecked = $(this).is(':checked');
        const enabledState = isChecked ? 'yes' : 'no';
        const $slider = $(this).siblings('.slider');

        $slider.css('opacity', '0.5');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'expotodo_firewall_action',
                firewall_action: 'toggle_firewall',
                enabled: enabledState,
                nonce: nonce
            },
            success: function (response) {
                $slider.css('opacity', '1');
                if (response.success) {
                    showNotification(response.data.message);
                    const $badge = $('#firewall-status-badge');
                    if (enabledState === 'yes') {
                        $badge.text('Activo').css('background-color', '#46b450');
                        $slider.css('background-color', '#46b450');
                    } else {
                        $badge.text('Inactivo').css('background-color', '#dc3232');
                        $slider.css('background-color', '#ccc');
                    }
                } else {
                    showNotification(response.data.message || 'Error al cambiar estado.', true);
                    // Revertir checkbox
                    $('#toggle-firewall-state').prop('checked', !isChecked);
                }
            },
            error: function () {
                $slider.css('opacity', '1');
                showNotification('Error de conexión al servidor.', true);
                $('#toggle-firewall-state').prop('checked', !isChecked);
            }
        });
    });

    // 2. Bloquear IP Manualmente
    $('#btn-manual-block').on('click', function (e) {
        e.preventDefault();
        const ipInput = $('#manual-ip').val().trim();
        if (!ipInput) {
            showNotification('Por favor, ingresa una IP.', true);
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'expotodo_firewall_action',
                firewall_action: 'block_ip',
                ip: ipInput,
                nonce: nonce
            },
            success: function (response) {
                $btn.prop('disabled', false).text('Bloquear IP');
                if (response.success) {
                    showNotification(response.data.message);
                    $('#manual-ip').val('');
                    updateBlacklistTable(response.data.blacklist);
                    updateWhitelistTable(response.data.whitelist);
                    updateLogsTableButtons(ipInput, true);
                } else {
                    showNotification(response.data.message, true);
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('Bloquear IP');
                showNotification('Error de conexión.', true);
            }
        });
    });

    // 3. Añadir a Lista Blanca Manualmente
    $('#btn-manual-whitelist').on('click', function (e) {
        e.preventDefault();
        const ipInput = $('#manual-ip').val().trim();
        if (!ipInput) {
            showNotification('Por favor, ingresa una IP.', true);
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'expotodo_firewall_action',
                firewall_action: 'whitelist_ip',
                ip: ipInput,
                nonce: nonce
            },
            success: function (response) {
                $btn.prop('disabled', false).text('Lista Blanca');
                if (response.success) {
                    showNotification(response.data.message);
                    $('#manual-ip').val('');
                    updateBlacklistTable(response.data.blacklist);
                    updateWhitelistTable(response.data.whitelist);
                    updateLogsTableButtons(ipInput, true);
                } else {
                    showNotification(response.data.message, true);
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('Lista Blanca');
                showNotification('Error de conexión.', true);
            }
        });
    });

    // 4. Desbloquear IP (Blacklist)
    $(document).on('click', '.btn-unblock-ip', function () {
        const ip = $(this).data('ip');
        const $btn = $(this);
        $btn.prop('disabled', true).text('...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'expotodo_firewall_action',
                firewall_action: 'unblock_ip',
                ip: ip,
                nonce: nonce
            },
            success: function (response) {
                if (response.success) {
                    showNotification(response.data.message);
                    updateBlacklistTable(response.data.blacklist);
                    updateLogsTableButtons(ip, false);
                } else {
                    $btn.prop('disabled', false).text('Desbloquear');
                    showNotification(response.data.message, true);
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('Desbloquear');
                showNotification('Error de conexión.', true);
            }
        });
    });

    // 5. Quitar IP (Whitelist)
    $(document).on('click', '.btn-unwhitelist-ip', function () {
        const ip = $(this).data('ip');
        const $btn = $(this);
        $btn.prop('disabled', true).text('...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'expotodo_firewall_action',
                firewall_action: 'unwhitelist_ip',
                ip: ip,
                nonce: nonce
            },
            success: function (response) {
                if (response.success) {
                    showNotification(response.data.message);
                    updateWhitelistTable(response.data.whitelist);
                    updateLogsTableButtons(ip, false);
                } else {
                    $btn.prop('disabled', false).text('Quitar');
                    showNotification(response.data.message, true);
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('Quitar');
                showNotification('Error de conexión.', true);
            }
        });
    });

    // 6. Bloquear IP desde la tabla de logs
    $(document).on('click', '.btn-block-ip', function () {
        const ip = $(this).data('ip');
        const $btn = $(this);
        $btn.prop('disabled', true).text('...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'expotodo_firewall_action',
                firewall_action: 'block_ip',
                ip: ip,
                nonce: nonce
            },
            success: function (response) {
                if (response.success) {
                    showNotification(response.data.message);
                    updateBlacklistTable(response.data.blacklist);
                    updateWhitelistTable(response.data.whitelist);
                    updateLogsTableButtons(ip, true);
                } else {
                    $btn.prop('disabled', false).text('Bloquear');
                    showNotification(response.data.message, true);
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('Bloquear');
                showNotification('Error de conexión.', true);
            }
        });
    });

    // --- Helpers de Renderizado Dinámico ---

    function updateBlacklistTable(blacklist) {
        const $tbody = $('#blacklist-tbody');
        $('#count-blacklist').text(blacklist.length);
        $tbody.empty();

        if (blacklist && blacklist.length > 0) {
            blacklist.forEach(function (ip) {
                const rowId = ip.replace(/\./g, '-');
                $tbody.append(`
                    <tr id="blacklist-row-${rowId}">
                        <td><strong>${ip}</strong></td>
                        <td style="text-align: right;">
                            <button class="button button-small btn-unblock-ip" data-ip="${ip}">Desbloquear</button>
                        </td>
                    </tr>
                `);
            });
        } else {
            $tbody.append('<tr class="no-ips"><td colspan="2" style="color: #64748b; font-style: italic;">No hay IPs bloqueadas permanentemente.</td></tr>');
        }
    }

    function updateWhitelistTable(whitelist) {
        const $tbody = $('#whitelist-tbody');
        $('#count-whitelist').text(whitelist.length);
        $tbody.empty();

        if (whitelist && whitelist.length > 0) {
            whitelist.forEach(function (ip) {
                const rowId = ip.replace(/\./g, '-');
                $tbody.append(`
                    <tr id="whitelist-row-${rowId}">
                        <td><strong>${ip}</strong></td>
                        <td style="text-align: right;">
                            <button class="button button-small btn-unwhitelist-ip" data-ip="${ip}">Quitar</button>
                        </td>
                    </tr>
                `);
            });
        } else {
            $tbody.append('<tr class="no-ips"><td colspan="2" style="color: #64748b; font-style: italic;">No hay IPs en lista blanca.</td></tr>');
        }
    }

    function updateLogsTableButtons(ip, isBannedOrWhitelisted) {
        $('#firewall-logs-tbody tr').each(function () {
            const $row = $(this);
            const rowIp = $row.find('td:nth-child(2) strong').text().trim();
            if (rowIp === ip) {
                const $btnCell = $row.find('td:last-child');
                if (isBannedOrWhitelisted) {
                    $btnCell.html('<span style="color: #64748b; font-size: 11px; font-style: italic;">Sin acción</span>');
                } else {
                    $btnCell.html(`<button class="button button-small btn-block-ip" data-ip="${ip}">Bloquear</button>`);
                }
            }
        });
    }

    // 7. Polling automático del historial de logs (cada 30 segundos)
    function pollLogs() {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'expotodo_firewall_action',
                firewall_action: 'get_logs',
                nonce: nonce
            },
            success: function (response) {
                if (response.success) {
                    // Actualizar el cuerpo de la tabla
                    $('#firewall-logs-tbody').html(response.data.html);

                    // Actualizar estadísticas en tiempo real
                    $('#count-blacklist').text(response.data.stats.blacklist_count);
                    $('#count-whitelist').text(response.data.stats.whitelist_count);
                    $('#count-total-attacks').text(response.data.stats.total_attacks);
                    $('#count-total-warnings').text(response.data.stats.total_warnings);
                }
            },
            error: function () {
                console.error('Error al actualizar el historial de logs.');
            }
        });
    }

    // Ejecutar el polling cada 30 segundos
    setInterval(pollLogs, 30000);
});