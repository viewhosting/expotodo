<?php
get_header();
?>

    <main class="py-5 bg-light d-flex align-items-center" style="min-height: 60vh;">
        <div class="container text-center">
            <div class="display-1 fw-bold text-primary mb-4">404</div>
            <h1 class="h2 mb-4">Página no encontrada</h1>
            <p class="lead text-muted mb-5">Lo sentimos, la página que buscas no existe o ha sido movida.</p>
            <a href="<?php echo home_url(); ?>" class="btn btn-primary btn-lg px-5">Volver al Inicio</a>
        </div>
    </main>

<?php get_footer(); ?>
