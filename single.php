<?php
get_header();
?>

<main class="flex-grow-1">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6">
                    <article class="product-card h-100" data-product-id="<?php echo get_the_ID(); ?>"
                        data-product-name="<?php the_title(); ?>" data-product-price="520">
                        <div class="product-image-container">
                            <?php 
                            if ( has_post_thumbnail() ) {
                                the_post_thumbnail('large', array('class' => 'product-image'));
                            } else {
                                echo '<img alt="' . get_the_title() . '" class="product-image" src="https://www.expotodo.com.mx/wp-content/uploads/2023/07/EXPO-TODO-34-400x400.png" />';
                            }
                            ?>
                        </div>
                    </article>
                </div>
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="product-content w-100">
                        <h1 class="product-title mb-3"><?php the_title(); ?></h1>
                        <div class="product-description mb-3">
                            <?php the_content(); ?>
                        </div>
                        
                        <!-- Static specs for now, ideally Custom Fields -->
                        <ul class="mb-4">
                            <li>Altura aproximada: 190 cm</li>
                            <li>Acabado: blanco mate</li>
                            <li>Base: cristal templado con soporte metálico</li>
                            <li>Uso recomendado: tiendas deportivas, vitrinas y exhibiciones especiales</li>
                        </ul>
                        
                        <div class="product-price mb-3">
                            <span class="price-label">Precio</span>
                            <span class="price">520€</span>
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                            <a class="btn-card btn btn-primary" href="#">
                                <i class="fas fa-shopping-cart me-2"></i>Agregar al carrito
                            </a>
                            <a class="btn btn-outline-secondary" href="<?php echo home_url('/productos'); ?>">
                                <i class="fas fa-arrow-left me-2"></i>Volver a productos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endwhile; endif; ?>

    <section class="featured-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Productos recomendados</h2>
            <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4">
                <!-- Static recommendations for now -->
                <div class="col product-grid-item"
                    data-category="maniquies-linea-deportiva maniquies-fibra-dama-caballero"
                    data-flags="nuevo mas-vendido" data-price="520">
                    <article class="product-card h-100">
                        <div class="product-image-container">
                            <div class="product-category">Linea Deportiva</div><button class="btn-add-wishlist"
                                title="Agregar a lista de deseos" type="button"><i
                                    class="far fa-heart"></i></button><img alt="Maniquí deportivo dama"
                                class="product-image"
                                src="https://www.expotodo.com.mx/wp-content/uploads/2023/07/EXPO-TODO-32-400x400.png" />
                        </div>
                        <div class="product-content p-3">
                            <h3 class="product-title">Maniquí deportivo dama</h3>
                            <p class="product-description">Maniquí de alta calidad para exhibición profesional.</p>
                            <div class="product-price mb-3"><span class="price new-price">520€</span></div><a
                                class="btn-card btn-primary" href="#"><i
                                    class="fas fa-eye me-2"></i> Ver detalles</a><a href="#" class="btn-card btn-primary mt-2">
                            <i class="fas fa-shopping-cart me-2"></i> Agregar al carrito
                        </a>
                        </div>
                    </article>
                </div>
                <div class="col product-grid-item"
                    data-category="maniquies-fibra-dama-caballero maniquies-linea-piel" data-flags="mas-vendido"
                    data-price="450">
                    <article class="product-card h-100">
                        <div class="product-image-container">
                            <div class="product-category">Fibra Dama Caballero</div><button class="btn-add-wishlist"
                                title="Agregar a lista de deseos" type="button"><i
                                    class="far fa-heart"></i></button><img alt="Maniquí fibra dama clásico"
                                class="product-image"
                                src="https://www.expotodo.com.mx/wp-content/uploads/2025/11/BUCHONES-34.png" />
                        </div>
                        <div class="product-content p-3">
                            <h3 class="product-title">Maniquí fibra dama clásico</h3>
                            <p class="product-description">Maniquí de alta calidad para exhibición profesional.</p>
                            <div class="product-price mb-3"><span class="price new-price">450€</span></div><a
                                class="btn-card btn-primary" href="#"><i
                                    class="fas fa-eye me-2"></i> Ver detalles</a><a href="#" class="btn-card btn-primary mt-2">
                            <i class="fas fa-shopping-cart me-2"></i> Agregar al carrito
                        </a>
                        </div>
                    </article>
                </div>
                <div class="col product-grid-item" data-category="bustos-modistos bustos-forrados-dama-caballero"
                    data-flags="mas-vendido" data-price="279">
                    <article class="product-card h-100">
                        <div class="product-image-container">
                            <div class="product-category">Bustos Modistos</div><button class="btn-add-wishlist"
                                title="Agregar a lista de deseos" type="button"><i
                                    class="far fa-heart"></i></button><img alt="Busto modisto dama"
                                class="product-image"
                                src="https://www.expotodo.com.mx/wp-content/uploads/2024/11/FOTOS-WEB-16.png" />
                        </div>
                        <div class="product-content p-3">
                            <h3 class="product-title">Busto modisto dama</h3>
                            <p class="product-description">Maniquí de alta calidad para exhibición profesional.</p>
                            <div class="product-price mb-3"><span class="price new-price">279€</span></div><a
                                class="btn-card btn-primary" href="#"><i
                                    class="fas fa-eye me-2"></i> Ver detalles</a><a href="#" class="btn-card btn-primary mt-2">
                            <i class="fas fa-shopping-cart me-2"></i> Agregar al carrito
                        </a>
                        </div>
                    </article>
                </div>
                <div class="col product-grid-item" data-category="ganchos accesorios-tienda"
                    data-flags="mas-vendido" data-price="1.5">
                    <article class="product-card h-100">
                        <div class="product-image-container">
                            <div class="product-category">Ganchos</div><button class="btn-add-wishlist"
                                title="Agregar a lista de deseos" type="button"><i
                                    class="far fa-heart"></i></button><img alt="Ganchos metálicos"
                                class="product-image"
                                src="https://www.expotodo.com.mx/wp-content/uploads/2024/04/NUEVO-ARTICULADOS-2-1.png" />
                        </div>
                        <div class="product-content p-3">
                            <h3 class="product-title">Ganchos metálicos</h3>
                            <p class="product-description">Maniquí de alta calidad para exhibición profesional.</p>
                            <div class="product-price mb-3"><span class="price new-price">1.5€</span></div><a
                                class="btn-card btn-primary" href="#"><i
                                    class="fas fa-eye me-2"></i> Ver detalles</a>
                                    <a href="#" class="btn-card btn-primary mt-2">
                            <i class="fas fa-shopping-cart me-2"></i> Agregar al carrito
                        </a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
