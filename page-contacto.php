<?php
/*
Template Name: Contacto
*/
get_header();
?>

<main class="flex-grow-1">
    <section class="py-5 bg-light">
        <div class="container">
            <h1 class="text-center mb-5 section-title">Contáctanos</h1>
            <div class="row g-5">
                <!-- Mapa y Sucursales -->
                <div class="col-lg-6">
                    <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                        <h3 class="mb-4">Nuestras Sucursales</h3>
                        <div class="ratio ratio-4x3 mb-4">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d60200.70636886737!2d-99.17557356616606!3d19.432607699999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1sexpotodo%20sucursales!5e0!3m2!1ses!2smx!4v1709660000000!5m2!1ses!2smx" 
                                    style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                                    <div>
                                        <h5 class="h6 fw-bold">Matriz Centro</h5>
                                        <p class="small text-muted mb-0">República de Uruguay 123, Centro Histórico, CDMX</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                                    <div>
                                        <h5 class="h6 fw-bold">Sucursal Norte</h5>
                                        <p class="small text-muted mb-0">Av. Insurgentes Norte 456, Lindavista, CDMX</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Contacto -->
                <div class="col-lg-6">
                    <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                        <h3 class="mb-4">Envíanos un mensaje</h3>
                        <form id="expotodo-contact-form" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="name" id="nombre" placeholder="Tu nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="tucorreo@ejemplo.com" required>
                                </div>
                                <div class="col-12">
                                    <label for="asunto" class="form-label">Asunto</label>
                                    <input type="text" class="form-control" name="subject" id="asunto" placeholder="Asunto del mensaje" required>
                                </div>
                                <div class="col-12">
                                    <label for="mensaje" class="form-label">Mensaje</label>
                                    <textarea class="form-control" name="message" id="mensaje" rows="5" placeholder="¿En qué podemos ayudarte?" required></textarea>
                                </div>
                                
                                <!-- Honeypot anti-spam -->
                                <div style="display:none;">
                                    <input type="text" name="hp_field" value="">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <span class="btn-text">Enviar Mensaje</span>
                                        <span class="btn-loading d-none"><i class="fas fa-spinner fa-spin"></i> Enviando...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="contact-response" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php 
// Renderizar la colección usando el nuevo controlador centralizado
echo expotodo_render_collection(array(
    'title'          => 'Nuestra Colección',
    'posts_per_page' => 4,
    'type'           => 'featured',
    'orderby'        => 'rand'
)); 
?>

<?php get_footer(); ?>