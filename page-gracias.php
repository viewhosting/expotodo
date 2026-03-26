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
                            <a href="#" class="btn-card btn-primary">
                                <i class="fas fa-shopping-cart me-2"></i> Añadir al carrito
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
                            <a href="#" class="btn-card btn-primary">
                                <i class="fas fa-shopping-cart me-2"></i> Añadir al carrito
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
