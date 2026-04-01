# Expotodo Boutique Theme 🛍️✨

![Estado](https://img.shields.io/badge/Estado-Activo-b0d443?style=flat-square)
![WordPress](https://img.shields.io/badge/WordPress-Compatible-blue?style=flat-square)
![WooCommerce](https://img.shields.io/badge/WooCommerce-Optimizado-purple?style=flat-square)

Expotodo Boutique es un ecosistema digital avanzado en forma de tema personalizado para WordPress y WooCommerce. Diseñado meticulosamente para una Boutique de maniquíes y visual merchandising, este proyecto eleva la experiencia de usuario (UX) y la estética a través de patrones de diseño premium, como **Glassmorphism**, menús interactivos y un panel de administración corporativo personalizado.

## 🌟 Características Principales

*   **Identidad Premium (Glassmorphism):** Componentes visuales como barras laterales fuera del lienzo (Off-Canvas) y pies de página dinámicos que utilizan desenfoque de fondo en tiempo real (`backdrop-filter`) para una sensación flotante de cristal esmerilado.
*   **Paneles Laterales AJAX Dinámicos:** Sistema inteligente `right-sidebar` que maneja el Carrito de WooCommerce y el Panel de Búsqueda sin recargar la página, ofreciendo retroalimentación con toasts inmersivos.
*   **Aesthetic Footer Dinámico:** Un esquema de pie de página inyectado mediante variables CSS que permite cambiar fondos desde el administrador, aplicando automáticamente filtros de inversión, degradados de contraste y control posicional mediante un grid 3x3 avanzado. 
*   **Dashboard de Control Exclusivo:** Un back-office (`AdminController.php`) que trasciende las opciones nativas de WordPress, ofreciendo configuración de integraciones y previsualización de componentes en la propia administración (Live Preview).
*   **Motor SMTP Nativo:** Integración y gestión autónoma de envíos de correo por SMTP diseñada específicamente para los flujos transaccionales de la tienda (`EmailController.php`).

## ⚙️ Requisitos del Sistema

Para garantizar el correcto funcionamiento y renderizado del ecosistema Expotodo, el entorno anfitrión debe cumplir:

*   **PHP:** Versión `7.4` o superior (Recomendado `8.1+`).
*   **Navegadores:** Soporte para CSS moderno (Flexbox, CSS Variables, `backdrop-filter`).
*   **WordPress:** Versión `6.0` o superior.
*   **Plugins Obligatorios:** 
    *   `WooCommerce` (Activo y configurado).

## 🚀 Instalación y Despliegue

Sigue estos pasos para implementar la rama principal en tu entorno:

1.  Clona el repositorio en la carpeta `wp-content/themes/` de tu instalación WordPress:
    ```bash
    git clone https://github.com/viewhosting/expotodo.git expotodo
    ```
2.  Accede a tu panel de administración de WordPress > **Apariencia > Temas**.
3.  Activa el tema **Expotodo**.
4.  Dirígete al nuevo menú en el administrador **"Ajustes Expotodo"**.
5.  Configura las pestañas de identidad visual (fondo de footer) y los servidores SMTP para habilitar el comercio.

## 🏗️ Arquitectura del Proyecto

Este tema utiliza un patrón Modelo-Vista-Controlador (MVC) parcial adaptado a la API de WordPress para separar la lógica de negocio de la vista.

*   `/inc/controllers/` - Contiene la lógica profunda.
    *   `AdminController.php` - Maneja la UI de ajustes propia del administrador de WP.
    *   `EmailController.php` - Enruta correos mediante el protocolo SMTP.
    *   `WooCommerceController.php` - Secuestra y mejora las funciones y fragmentos de AJAX de WC.
*   `/assets/` - Recursos compilados estáticos (Motor UI).
    *   `/css/main.css` - Identidad estilística central y tokens Boutique.
    *   `/admin/js/settings.js` - Sistema de renderizado en vivo y control multimedia para el back-office.
*   `functions.php` - Gateway de inicialización; instancia los controladores.
*   `footer.php` / `header.php` / `index.php` - Plantillas visuales (Vistas).

## 🛡️ Estándares de Diseño (UI/UX)

*   **Colores de Marca:**
    *   Primario: `#b0d443` (Verde Limón Expotodo)
    *   Oscuro/Texto: `#1a1a1a`
    *   Luz Absoluta: `var(--text-white)` `#ffffff`
*   **Tipografía Primaria:** `Avenir Next` / Sistema Nativo para máxima legibilidad.

---

*Diseñado con pasión por el detalle. Ingeniería y Arte digital orientada a conversión comercial.*
