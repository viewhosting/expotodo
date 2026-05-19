document.addEventListener('DOMContentLoaded', function () {
    var trackingForm = document.getElementById('trackingForm');
    if (trackingForm) {
        trackingForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var orderId = document.getElementById('orderId').value;
            var resultDiv = document.getElementById('trackingResult');
            var resultIdSpan = document.getElementById('resultOrderId');

            // Simular carga
            var btn = this.querySelector('button');
            var originalText = btn.textContent;
            btn.textContent = 'Buscando...';
            btn.disabled = true;

            setTimeout(function () {
                if (resultIdSpan) resultIdSpan.textContent = '#' + orderId;
                if (resultDiv) resultDiv.classList.remove('d-none');
                btn.textContent = originalText;
                btn.disabled = false;
            }, 1000);
        });
    }
});
