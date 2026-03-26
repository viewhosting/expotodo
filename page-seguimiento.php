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

<!-- Sección de Productos Destacados -->
<section class="featured-section py-5" id="coleccion">
    <div class="container">
        <h2 class="section-title text-center mb-5">Nuestra Colección</h2>
        
        <div class="row g-4 mb-5">
            <!-- Producto 1 -->
            <div class="col-md-3">
                <article class="product-card h-100" data-product-id="maniqui-lux" data-product-name="Maniquí Profesional Serie LUX" data-product-price="450">
                    <div class="product-image-container">
                        <div class="product-category">Maniquíes de Línea</div>
                        <button type="button" class="btn-add-wishlist" title="Agregar a lista de deseos">
                            <i class="far fa-heart"></i>
                        </button>
                        <img src="https://www.expotodo.com.mx/wp-content/uploads/2024/11/FOTOS-WEB-17.png" 
                             class="product-image" 
                             alt="Maniquí profesional de torso completo">
                    </div>
                    <div class="product-content p-3">
                        <h3 class="product-title">Maniquí Profesional Serie LUX</h3>
                        <p class="product-description">
                            Torso completo con ajuste micrométrico y base de aluminio pulido. Ideal para alta costura.
                        </p>
                        <div class="product-price mb-3">
                            Desde <span class="price">450€</span>
                        </div>
                        <a href="<?php echo home_url('/detalle-producto'); ?>" class="btn-card btn-primary">
                            <i class="fas fa-eye me-2"></i> Ver especificaciones
                        </a>
                    </div>
                </article>
            </div>
            
            <!-- Producto 2 -->
            <div class="col-md-3">
                <article class="product-card h-100" data-product-id="sistema-zen" data-product-name="Sistema Modular de Perchas ZEN" data-product-price="280">
                    <div class="product-image-container">
                        <div class="product-category">Sistemas de Exhibición</div>
                        <button type="button" class="btn-add-wishlist" title="Agregar a lista de deseos">
                            <i class="far fa-heart"></i>
                        </button>
                        <img src="https://www.expotodo.com.mx/wp-content/uploads/2024/11/FOTOS-WEB-18.png" 
                             class="product-image" 
                             alt="Ganchos y perchas de diseño minimalista">
                    </div>
                    <div class="product-content p-3">
                        <h3 class="product-title">Sistema Modular de Perchas ZEN</h3>
                        <p class="product-description">
                            Conjunto de ganchos y racks en madera de haya y metal. Diseño flexible y minimalista.
                        </p>
                        <div class="product-price mb-3">
                            Kit desde <span class="price">280€</span>
                        </div>
                        <a href="<?php echo home_url('/detalle-producto'); ?>" class="btn-card btn-primary">
                            <i class="fas fa-eye me-2"></i> Ver especificaciones
                        </a>
                    </div>
                </article>
            </div>
            
            <!-- Producto 3 -->
            <div class="col-md-3">
                <article class="product-card h-100" data-product-id="busto-clasico-1" data-product-name="Busto Modisto Clásico" data-product-price="279">
                    <div class="product-image-container">
                        <div class="product-category sale">Oferta</div>
                        <div class="product-category">Bustos Modistos</div>
                        <button type="button" class="btn-add-wishlist" title="Agregar a lista de deseos">
                            <i class="far fa-heart"></i>
                        </button>
                        <img src="https://www.expotodo.com.mx/wp-content/uploads/2024/11/FOTOS-WEB-19.png" 
                             class="product-image" 
                             alt="Busto modisto para sastrería">
                    </div>
                    <div class="product-content p-3">
                        <h3 class="product-title">Busto Modisto Clásico</h3>
                        <p class="product-description">
                            Forma anatómica precisa para el ajuste de chaquetas y blusas. Acabado en lino beige.
                        </p>
                        <div class="product-price mb-3">
                            <span class="old-price">320€</span>
                            <span class="price new-price">279€</span>
                        </div>
                        <a href="<?php echo home_url('/detalle-producto'); ?>" class="btn-card btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i> Agregar al carrito
                        </a>
                    </div>
                </article>
            </div>

            <!-- Producto 4 -->
            <div class="col-md-3">
                <article class="product-card h-100" data-product-id="busto-clasico-2" data-product-name="Busto Modisto Clásico" data-product-price="279">
                    <div class="product-image-container">
                        <div class="product-category sale">Oferta</div>
                        <div class="product-category">Bustos Modistos</div>
                        <button type="button" class="btn-add-wishlist" title="Agregar a lista de deseos">
                            <i class="far fa-heart"></i>
                        </button>
                        <img src="https://www.expotodo.com.mx/wp-content/uploads/2024/11/FOTOS-WEB-20.png" 
                             class="product-image" 
                             alt="Busto modisto para sastrería">
                    </div>
                    <div class="product-content p-3">
                        <h3 class="product-title">Busto Modisto Clásico</h3>
                        <p class="product-description">
                            Forma anatómica precisa para el ajuste de chaquetas y blusas. Acabado en lino beige.
                        </p>
                        <div class="product-price mb-3">
                            <span class="old-price">320€</span>
                            <span class="price new-price">279€</span>
                        </div>
                        <a href="<?php echo home_url('/detalle-producto'); ?>" class="btn-card btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i> Agregar al carrito
                        </a>
                    </div>
                </article>
            </div>
        </div>
        
        <div class="text-center">
            <a href="<?php echo home_url('/productos'); ?>" class="btn btn-outline-dark btn-lg px-5">
                Ver Catálogo Completo
            </a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
