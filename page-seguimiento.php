<?php
/* Template Name: Seguimiento */
get_header();
?>

<!-- Main Content -->
<main class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Rastrear Pedido</h2>
                        <p class="text-center text-muted mb-4">Introduce tu número de pedido y correo electrónico para ver el estado.</p>
                        
                        <form id="trackingForm">
                            <div class="mb-3">
                                <label for="orderId" class="form-label">Número de Pedido</label>
                                <input type="text" class="form-control" id="orderId" placeholder="Ej. ORD-2026-8834" required>
                            </div>
                            <div class="mb-4">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" placeholder="tu@email.com" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Rastrear</button>
                        </form>
                    </div>
                </div>

                <!-- Status Result (Hidden by default) -->
                <div id="trackingResult" class="card shadow-sm d-none">
                    <div class="card-body p-4">
                        <h4 class="mb-3">Estado del Pedido <span class="text-primary" id="resultOrderId"></span></h4>
                        
                        <div class="position-relative m-4">
                            <div class="progress" style="height: 2px;">
                                <div class="progress-bar" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-primary rounded-pill" style="width: 2rem; height:2rem;">1</div>
                            <div class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-primary rounded-pill" style="width: 2rem; height:2rem;">2</div>
                            <div class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-secondary rounded-pill" style="width: 2rem; height:2rem;">3</div>
                        </div>
                        
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Recibido</span>
                            <span>En Proceso</span>
                            <span>Enviado</span>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded">
                            <p class="mb-1"><strong>Estado actual:</strong> <span class="text-success">En preparación</span></p>
                            <p class="mb-0 text-muted small">Tu pedido está siendo empaquetado y pronto será enviado.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Sección de Colección Centralizada -->
<?php get_template_part('template-parts/product-collection'); ?>

<?php get_footer(); ?>
