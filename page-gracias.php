<?php
/* Template Name: Gracias */
get_header();
?>

    <!-- Main Content -->
    <main class="py-5 bg-light d-flex align-items-center" style="min-height: 60vh;">
        <div class="container text-center">
            <div class="card shadow-sm mx-auto" style="max-width: 600px;">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h1 class="h2 mb-3">¡Gracias por tu compra!</h1>
                    <p class="text-muted mb-4">Hemos recibido tu pedido correctamente. Te hemos enviado un correo electrónico de confirmación.</p>
                    
                    <div class="bg-light p-3 rounded mb-4 border">
                        <p class="mb-1 small text-uppercase text-muted">Número de pedido</p>
                        <h3 class="mb-0 font-monospace">#ORD-2026-8834</h3>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-center">
                        <a href="<?php echo home_url('/seguimiento'); ?>" class="btn btn-outline-primary px-4">Seguimiento de Pedido</a>
                        <a href="<?php echo home_url(); ?>" class="btn btn-primary px-4">Volver a la Tienda</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php 
// Renderizar sugerencias reales usando el nuevo controlador centralizado
echo expotodo_render_collection(array(
    'title'          => 'También te puede interesar',
    'posts_per_page' => 4,
    'type'           => 'featured',
    'orderby'        => 'rand'
)); 
?>

<?php get_footer(); ?>
