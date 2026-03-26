document.addEventListener('DOMContentLoaded', function() {
    const trackingForm = document.getElementById('trackingForm');
    if (trackingForm) {
        trackingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const orderId = document.getElementById('orderId').value;
            const resultDiv = document.getElementById('trackingResult');
            const resultIdSpan = document.getElementById('resultOrderId');
            
            // Simular carga
            const btn = this.querySelector('button');
            const originalText = btn.textContent;
            btn.textContent = 'Buscando...';
            btn.disabled = true;

            setTimeout(() => {
                if (resultIdSpan) resultIdSpan.textContent = '#' + orderId;
                if (resultDiv) resultDiv.classList.remove('d-none');
                btn.textContent = originalText;
                btn.disabled = false;
            }, 1000);
        });
    }
});
