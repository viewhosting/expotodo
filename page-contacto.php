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
                            <!-- Contenedor del Mapa oficial de Google -->
                            <div id="map-sucursales" class="rounded-3 shadow-sm border" style="background: #f8f9fa;"></div>
                            
                            <script>
                                function initMap() {
                                    var locations = [
                                        { 
                                            lat: 19.4306729, 
                                            lng: -99.1383525, 
                                            title: "Matriz Centro",
                                            address: "República de Uruguay 37, Centro Histórico de la Cdad. de México, Centro, Cuauhtémoc, 06000 Ciudad de México, CDMX"
                                        },
                                        { 
                                            lat: 19.4312882, 
                                            lng: -99.1491636, 
                                            title: "Sucursal Ayuntamiento",
                                            address: "Ayuntamiento 132, Centro, Cuauhtémoc, 06040, Ciudad de México"
                                        },
                                        { 
                                            lat: 21.1194371, 
                                            lng: -101.6775147, 
                                            title: "Sucursal León",
                                            address: "5 de Febrero 515, Centro, 37000 León, Gto."
                                        }
                                    ];

                                    var map = new google.maps.Map(document.getElementById('map-sucursales'), {
                                        zoom: 17,
                                        center: { lat: 19.4306729, lng: -99.1383525 },
                                        scrollwheel: true,
                                        styles: [
                                            { "featureType": "administrative", "elementType": "labels.text.fill", "stylers": [{ "color": "#444444" }] },
                                            { "featureType": "landscape", "elementType": "all", "stylers": [{ "color": "#f2f2f2" }] },
                                            { "featureType": "poi", "elementType": "all", "stylers": [{ "visibility": "off" }] }
                                        ]
                                    });

                                    var bounds = new google.maps.LatLngBounds();
                                    var infoWindow = new google.maps.InfoWindow();

                                    locations.forEach(function(loc, index) {
                                        var marker = new google.maps.Marker({
                                            position: { lat: loc.lat, lng: loc.lng },
                                            map: map,
                                            title: loc.title,
                                            animation: google.maps.Animation.DROP
                                        });

                                        marker.addListener('click', function() {
                                            infoWindow.setContent('<strong>' + loc.title + '</strong><br>' + loc.address);
                                            infoWindow.open(map, marker);
                                        });

                                        if (index < 2) {
                                            bounds.extend(marker.getPosition());
                                        }
                                    });

                                    // Ajustar el mapa para que se vean todos los marcadores
                                    map.fitBounds(bounds);
                                }
                            </script>
                            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDD0onsVecR7aXcELdaTPrPzSvOgm6Ei9I&callback=initMap" async defer></script>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                                    <div>
                                        <h5 class="h6 fw-bold">Matriz Centro</h5>
                                        <p class="small text-muted mb-0">República de Uruguay 37-Loc.A, Centro, Cuauhtémoc, 06000 Ciudad de México</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                                    <div>
                                        <h5 class="h6 fw-bold">Sucursal Ayuntamiento</h5>
                                        <p class="small text-muted mb-0">Ayuntamiento 132, Centro, Cuauhtémoc, 06040, Ciudad de México</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                                    <div>
                                        <h5 class="h6 fw-bold">Sucursal León</h5>
                                        <p class="small text-muted mb-0">5 de Febrero 515, Centro, 37000 León, Gto.</p>
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

    <!-- Sección de Colección Centralizada -->
    <?php get_template_part('template-parts/product-collection'); ?>

<?php get_footer(); ?>