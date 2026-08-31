# Roadmap de Implementación: Sistema Especializado de Cotizaciones (Expotodo)

Este documento detalla el estado actual, los avances logrados y las tareas pendientes necesarias para el desarrollo del **Plugin Especializado en Cotizaciones (Presupuestos)** para **Expotodo** (diseño y fabricación de maniquíes y sistemas de exhibición).

Está diseñado como una guía de relevo técnico para que otra Inteligencia Artificial o desarrollador retome el proyecto de forma inmediata y estructurada.

---

## 1. Contexto y Filosofía del Proyecto

### El Objetivo
Construir una herramienta autónoma de presupuestos y cotizaciones para la plataforma comercial de Expotodo. Permite a los agentes comerciales (o a los propios clientes, según se decida) crear cotizaciones complejas, desglosar IVA, aplicar descuentos, guardarlas en base de datos y exportarlas en formato PDF premium para envío por correo.

### Portabilidad Total (Independencia del Tema)
El plugin debe ser completamente desacoplado del tema de WordPress. Si en el futuro Expotodo decide cambiar su plantilla o tema, **el cotizador debe seguir funcionando y viéndose idéntico**. 

*   **Aislamiento CSS:** Todo el código visual debe estar encapsulado en el ámbito `.et-cotizador-wrapper`.
*   **Aislamiento CSS Global:** No usar estilos a etiquetas HTML globales sin este prefijo.
*   **Dependencias:** El plugin debe proveer sus propias dependencias o librerías de estilos y scripts de manera encolada (`wp_enqueue_script` / `wp_enqueue_style`) sin asumir que el tema las provee.

---

## 2. Avances Logrados (Qué está hecho)

### A. Auditoría de Diseño y Estilos del Tema
Se analizaron los archivos estilísticos del tema activo de Expotodo (`main.css`, `pagina_checkout.css` y `testimonials.css`) para identificar los siguientes elementos de marca:
*   **Tipografía oficial:** `'Avenir Next'` para títulos y `'Roboto'` para textos.
*   **Colores primarios y acentos:** Verde Limón (`#b0d443`), Verde Oliva Obscuro (`#8ca835`), Verde Bosque (`#354213`), Gris Slate (`#1e293b`), y Gris de Fondo (`#f8fafc`).
*   **Tratamiento de campos de entrada:** Campos de `52px` de alto, bordes redondeados de `12px` (`#e2e8f0`), foco con resplandor elástico verde limón (`rgba(176, 212, 67, 0.15)`).
*   **Alineación de elementos:** Uso de tarjetas redondeadas de `20px` (`.et-card`) con sombras tenues y grids adaptables de CSS.

### B. Especificación Visual y Guía de Estilos
Se creó la guía detallada de estilos del plugin en la raíz del proyecto:
📄 **[GUIA_ESTILOS_COTIZACIONES.md](file:///Users/emanueru/apps/php/expotodo/expotodo/GUIA_ESTILOS_COTIZACIONES.md)**

Esta guía provee las clases CSS exactas listas para ser copiadas en la hoja de estilos del plugin (`assets/css/cotizador.css`), garantizando que la interfaz resultante sea idéntica al checkout y pasarelas premium de la web actual de Expotodo.

### C. Optimización y Edición de Reseñas de Clientes (ReviewsController)
Se implementaron mejoras críticas en el módulo preexistente de reseñas del tema:
*   **Soporte para Correos Anónimos (`****@****.com`):** Se modificaron los campos de entrada de correo en frontend (`page-escribir-resena.php`) y backend (`ReviewsController.php`) a `type="text"` para omitir las restricciones de formato nativo de los navegadores. En el servidor PHP, si el correo contiene asteriscos (`*`), se realiza una sanitización personalizada mediante una expresión regular segura (`/[^a-zA-Z0-9@\.\-_*]/`), evitando que `sanitize_email()` destruya los asteriscos de privacidad.
*   **Saltos de Línea Permitidos (`<br>` y `<p>`):** Se reconfiguró el almacenamiento y renderizado de las opiniones. En lugar de limpiar todas las etiquetas HTML usando `sanitize_textarea_field()`, se utiliza `wp_kses()` con un esquema estricto que admite solo `<br>` y `<p>`, protegiendo el sitio contra XSS. Las salidas en el slider público (`footer.php`) y en la administración ahora renderizan las etiquetas correspondientes de forma nativa.
*   **Sistema de Edición Completo en Administración:** Se añadió un botón "Editar" a cada fila en el panel administrativo. Este botón transfiere los metadatos al nuevo modal de edición interactivo mediante jQuery. El guardado se ejecuta de manera asíncrona mediante AJAX a través de la acción `expotodo_edit_review_ajax`, permitiendo modificar el nombre, correo, cuerpo de la reseña, estado y actualizar la foto de perfil en el mismo formulario.

### D. Corrección en la Pantalla de Pago de Pedidos (order-pay)
*   **Visibilidad de Errores Críticos:** Se corrigió un estilo en [pagina_checkout.css](file:///Users/emanueru/apps/php/expotodo/expotodo/assets/css/pagina_checkout.css) que ocultaba globalmente los errores de WooCommerce (`.woocommerce-error`). Al habilitarlos de nuevo, se reveló la notificación de bloqueo de pago de WooCommerce.
*   **Acceso Universal Seguro Mediante Enlace Directo:** WooCommerce restringe por defecto el pago de pedidos de clientes registrados a menos que hayan iniciado sesión. Se implementó un filtro sobre el hook `user_has_cap` en [WooCommerceController.php](file:///Users/emanueru/apps/php/expotodo/expotodo/inc/controllers/WooCommerceController.php) que permite pagar a cualquier persona (incluido invitados) siempre que tengan la URL de pago con la clave del pedido (`key=wc_order_...`) única y válida. Esto soluciona el error *"Este pedido no se ha podido pagar"* permitiendo que el link del plugin funcione de manera universal y segura.

### E. Rediseño Estético y Funcional del Inicio de Sesión (Login)
*   **Inicio de Sesión de Administración (`wp-login.php`):**
    *   Se implementaron hooks en [AdminController.php](file:///Users/emanueru/apps/php/expotodo/expotodo/inc/controllers/AdminController.php) (`login_enqueue_scripts`, `login_headerurl` y `login_headertext`) para personalizar completamente la pantalla de inicio de sesión oficial de WordPress.
    *   **Diseño Premium y Limpio:** Reemplazo del logotipo de WordPress por el logotipo oficial de Expotodo, centrado y con dimensiones responsivas.
    *   **Estilización de Inputs y Botón:** Los inputs ahora tienen bordes suaves redondeados y cambian a foco verde oliva (`#8ca835`) con resplandor suave. El botón de acceso se rediseñó en Verde Limón (`#b0d443`) con texto en Gris Slate, elevándose e iluminándose en hover.
    *   **Enlaces y Fondos:** El fondo gris azulado por defecto fue reemplazado por un fondo gris claro limpio (`#f8fafc`), y los enlaces inferiores fueron centrados y configurados para cambiar a color verde oliva en hover. El enlace del logotipo ahora redirige al Home de la tienda.
*   **Inicio de Sesión Frontend (Cliente):**
    *   **Alineación de Marca:** Se configuraron las variables CSS en [pagina_cuenta.css](file:///Users/emanueru/apps/php/expotodo/expotodo/assets/css/pagina_cuenta.css) para usar la paleta oficial de Expotodo: Gris Slate (`#1e293b`) como color principal, Gris de Fondo (`#f8fafc`) y Verde Limón (`#b0d443`) para los acentos interactivos y hover.
    *   **Campos de Entrada Modernizados:** Se rediseñaron los campos de usuario y contraseña en [page-cuenta.php](file:///Users/emanueru/apps/php/expotodo/expotodo/page-cuenta.php) y [header.php](file:///Users/emanueru/apps/php/expotodo/expotodo/header.php) utilizando grupos de Bootstrap con iconos descriptivos integrados (`far fa-user` y `fas fa-lock`).
    *   **Conmutador de Visibilidad de Contraseña:** Se añadió un botón con icono de ojo (`far fa-eye`) dentro de los campos de contraseña y se programó un script interactivo en [expotodo-login.js](file:///Users/emanueru/apps/php/expotodo/expotodo/assets/js/expotodo-login.js) para alternar dinámicamente la visibilidad del texto ingresado sin romper el inicio de sesión.
    *   **Efectos Hover y Micro-animaciones:** Los botones principales de inicio de sesión (`.btn-login-submit-modern`) y las tarjetas (`.login-card-modern`) ahora cuentan con animaciones fluidas de elevación (`translateY`) y sombras difuminadas con el color verde limón cuando el cursor pasa sobre ellos.
    *   **Carga de Estilos en Plantilla Personalizada:** Se ajustó [ThemeController.php](file:///Users/emanueru/apps/php/expotodo/expotodo/inc/controllers/ThemeController.php) para asegurar la carga del archivo de estilos boutique `pagina_cuenta.css` en la plantilla de página personalizada `page-cuenta.php`.

### F. Ocultación de la Categoría Default ("uncategorized" / "sin-categoria")
*   **Filtro Global de Consultas de Términos:** Se implementó una exclusión a nivel de sistema mediante el hook `get_terms` en [WooCommerceController.php](file:///Users/emanueru/apps/php/expotodo/expotodo/inc/controllers/WooCommerceController.php).
*   **Compatibilidad Multilingüe y Soporte Multiobjeto:** Filtra y elimina dinámicamente del listado de categorías los términos cuyos slugs coincidan con `uncategorized` (WooCommerce/WordPress por defecto) y `sin-categoria` (traducción al español).
*   **Seguridad y Optimización:** El filtro valida que no estemos en la administración de WordPress (`!is_admin()`) para que el administrador del sitio aún pueda gestionar productos en esta categoría desde el panel, y soporta tanto arreglos de objetos WP_Term como consultas optimizadas que retornan arreglos de IDs numéricos.

### G. Cortafuegos de Seguridad (WAF) y Panel de Control de IPs
*   **Inspección Temprana de Peticiones (`init`):** Se implementó una clase controladora autónoma [FirewallController.php](file:///Users/emanueru/apps/php/expotodo/expotodo/inc/controllers/FirewallController.php) que intercepta todas las peticiones públicas para detectar intentos de ataque.
*   **Reglas de Detección de Amenazas (RegEx):**
    *   **SQL Injection (SQLi):** Escanea e identifica patrones maliciosos como `UNION SELECT`, `INFORMATION_SCHEMA`, etc.
    *   **Cross-Site Scripting (XSS):** Bloquea inyecciones de código HTML/JS (etiquetas `<script>`, controladores de eventos como `onload`, etc.).
    *   **Directory Traversal / LFI:** Previene accesos relativos y lecturas de archivos del núcleo (ej. `wp-config.php`, `.env`).
    *   **Bot Traps:** Detecta y bloquea instantáneamente escaneos automáticos de vulnerabilidades orientados a archivos sensibles.
*   **Sistema de Strikes y Baneos con Transientes:** Las IPs maliciosas reciben strikes (advertencias) almacenados en transitorios de WordPress. Al llegar a 3 strikes (o con un solo intento de escaneo de Bot Trap), la IP queda bloqueada automáticamente por 24 horas y redirigida a una plantilla HTTP 403 Forbidden estilizada con el logo corporativo.
*   **Base de Datos Dedicada:** Se creó la tabla `wp_expotodo_firewall_log` para registrar eventos de seguridad y advertencias con un mecanismo de limpieza automática que limita la tabla a los 1000 registros más recientes.
*   **Panel de Control AJAX en el Backend:** 
    *   Se diseñó una página administrativa interactiva bajo el menú principal "Expotodo" con tarjetas de métricas integradas y tablas dinámicas.
    *   El administrador puede habilitar/deshabilitar el cortafuegos, bloquear/desbloquear IPs manualmente, y declararlas inmunes (Lista Blanca) en tiempo real mediante llamadas asíncronas optimizadas con el script [firewall-admin.js](file:///Users/emanueru/apps/php/expotodo/expotodo/assets/js/firewall-admin.js).

---

## 3. Faltantes y Roadmap de Desarrollo (Qué falta por hacer)

El proyecto del plugin debe dividirse en 5 fases lógicas:

### 📦 Fase 1: Inicialización del Plugin (Plugin Bootstrap)
*   **Crear archivo base del plugin:** `expotodo-cotizador.php` en la carpeta `/wp-content/plugins/expotodo-cotizador/`.
*   **Estructura de Directorios recomendada:**
    ```text
    expotodo-cotizador/
    ├── expotodo-cotizador.php     # Archivo principal del plugin (Headers, inicialización)
    ├── includes/
    │   ├── class-et-cotizador.php  # Controlador principal
    │   ├── class-et-db.php         # Manejo de base de datos
    │   ├── class-et-pdf.php        # Generador de PDF
    │   └── class-et-ajax.php       # Controladores de peticiones AJAX
    ├── templates/
    │   ├── cotizador-view.php      # Vista HTML del panel público/privado
    │   └── pdf-template.php        # Estructura HTML para exportar a PDF
    ├── assets/
    │   ├── css/
    │   │   └── cotizador.css       # Contiene todo el CSS de GUIA_ESTILOS_COTIZACIONES.md
    │   └── js/
    │       └── cotizador.js        # Lógica AJAX, cálculos en tiempo real y buscador
    └── vendor/                     # Librerías (por ejemplo, Dompdf)
    ```

### 🗄️ Fase 2: Arquitectura de Base de Datos
*   **Decisión Técnica:** Definir si usar un **Custom Post Type (CPT)** llamado `et_cotizacion` o **tablas personalizadas SQL** (`wp_et_cotizaciones` y `wp_et_cotizaciones_items`). 
    *   *Recomendación:* Usar tablas personalizadas para cotizaciones complejas debido a que permite un desglose ultra-rápido de ítems (productos, cantidades, precios unitarios, IVA, descuentos) sin sobrecargar la tabla de metadatos de WordPress (`wp_postmeta`).
*   **Estructura de la tabla `wp_et_cotizaciones` (Cabecera):**
    *   `id` (BIGINT, Llave Primaria, Auto_increment)
    *   `cliente_nombre` (VARCHAR 255)
    *   `cliente_email` (VARCHAR 255)
    *   `cliente_telefono` (VARCHAR 50)
    *   `validez_dias` (INT)
    *   `subtotal` (DECIMAL 10,2)
    *   `descuento` (DECIMAL 10,2)
    *   `iva` (DECIMAL 10,2)
    *   `total` (DECIMAL 10,2)
    *   `estado` (VARCHAR 50: borrador, enviado, aceptado, rechazado)
    *   `fecha_creacion` (DATETIME)
    *   `fecha_vencimiento` (DATETIME)
    *   `hash_seguridad` (VARCHAR 64 - Para ver cotizaciones online de forma segura)

### 🖥️ Fase 3: Interfaz del Cotizador y Buscador AJAX
*   **Formulario de Entrada:** Implementar la interfaz HTML definida en la Sección 10 de `GUIA_ESTILOS_COTIZACIONES.md`.
*   **Buscador Integrado con WooCommerce:** 
    *   Un input buscador que realice consultas AJAX a la base de datos de WooCommerce (`wp_posts` de tipo `product`).
    *   Al seleccionar un maniquí o sistema de exhibición, el script de JS debe obtener su SKU, imagen miniatura, y precio activo (regular o rebajado).
*   **Cálculos en Tiempo Real (JavaScript):**
    *   Multiplicar Cantidad × Precio Unitario.
    *   Sumar subtotales dinámicamente en el DOM.
    *   Calcular IVA (16% en México) de forma automática.
    *   Opción para aplicar un porcentaje o monto fijo de descuento.

### 📄 Fase 4: Motor de Generación de PDF (PDF Engine)
*   **Librería:** Integrar **Dompdf** o **FPDF** dentro de la carpeta `/vendor/` para renderizar código HTML/CSS directamente a PDF de manera rápida.
*   **Hoja de Estilos del PDF:** Crear una hoja de estilos para impresión (`templates/pdf-template.php`) que use exactamente los mismos colores de la marca para mantener la coherencia corporativa de Expotodo:
    *   Cabecera formal con logotipo de Expotodo.
    *   Bloque de información del cliente y validez de la cotización.
    *   Tabla de desglose alineada con la tabla de medidas del sitio.
    *   Pie de página con información de contacto, términos de pago y datos bancarios para transferencia.

### ✉️ Fase 5: Módulo de Envío por Correo Electrónico
*   **Envío Directo:** Implementar una función en PHP utilizando `wp_mail()` para enviar la cotización por correo electrónico.
*   **Adjunto Dinámico:** El correo electrónico del cliente debe recibir un mensaje formal con el PDF generado adjunto directamente.
*   **Notificaciones:** Alertas de administración cuando una cotización sea generada o consultada online.

---

## 4. Instrucciones de Relevo para la Próxima IA

Cuando retomes este proyecto, sigue esta secuencia detallada:

1.  **Lee la Guía de Estilos:** Abre [GUIA_ESTILOS_COTIZACIONES.md](file:///Users/emanueru/apps/php/expotodo/expotodo/GUIA_ESTILOS_COTIZACIONES.md) para comprender la estructura exacta del código visual y los tokens de diseño.
2.  **Genera los Archivos del Plugin:** Inicia creando el archivo de inicialización del plugin `expotodo-cotizador.php` y los archivos de assets CSS y JS. Copia las reglas de estilos de la guía directamente en la hoja de estilos de tu plugin.
3.  **Implementa el Instalador de BD:** Escribe la función de activación del plugin (`register_activation_hook`) para crear la base de datos de cabecera y desglose de ítems mediante `dbDelta()`.
4.  **Crea el Controlador AJAX:** Escribe los endpoints para buscar productos de WooCommerce y devolver sus precios e imágenes en formato JSON.
5.  **Asegura el Ámbito Visual:** Recuerda envolver siempre tus vistas en el contenedor `.et-cotizador-wrapper` para asegurar que el diseño no se rompa si se instala otro tema de WordPress en el futuro.

---

## 5. Análisis del Proyecto Expotodo (Tema WordPress + WooCommerce)

### 🔍 Descripción General
Este es un **tema personalizado de WordPress para WooCommerce** diseñado para Expotodo, una empresa dedicada a la venta de maniquíes y sistemas de exhibición.

---

### 📁 Estructura del Proyecto
```
expotodo/
├── assets/
│   ├── css/          # Estilos del tema
│   ├── images/       # Imágenes (logo, fondo, etc.)
│   └── js/         # Scripts JavaScript
├── inc/
│   ├── controllers/  # Controladores modulares (arquitectura MVC)
│   └── class-expotodo-loader.php  # Cargador de controladores
├── template-parts/   # Plantillas reutilizables
├── woocommerce/      # Plantillas personalizadas de WooCommerce
├── *.php             # Páginas del tema (single, archive, etc.)
└── GUIA_ESTILOS_COTIZACIONES.md  # Guía de diseño
```

---

### 🧩 Controladores Principales

| Controlador | Función |
|-------------|---------|
| `ThemeController` | Configuración del tema, encolado de assets, búsqueda en vivo, menú Bootstrap |
| `ReviewsController` | Sistema de reseñas/testimonios con tabla personalizada en BD |
| `WishlistController` | Lista de deseos para usuarios autenticados |
| `AdminController` | Menú de administración de Expotodo |
| `UserController` | Inicio de sesión AJAX y gestión de perfiles |
| `WooCommerceController` | **Extenso**: checkout personalizado, campos de dirección MX, filtros de productos, envíos, Mercado Pago |
| `ContactController` | Formulario de contacto con almacenamiento en BD |

---

### 🗄️ Tablas Personalizadas en la Base de Datos
1. `wp_expotodo_reviews` - Almacena reseñas de clientes
2. `wp_expotodo_messages` - Almacena mensajes del formulario de contacto

---

### ✨ Características Destacadas

1. **Checkout Personalizado para México**
   - Campos específicos: Calle, Nº Ext/Int, Colonia, Delegación/Municipio
   - Restricción de envíos a BC, BS y CH
   - Recogida local por defecto

2. **Sistema de Reseñas**
   - Formulario público para que clientes envíen reseñas
   - Panel de administración para aprobar/gestionar
   - Soporte para fotos de perfil

3. **Filtros y Búsqueda**
   - Filtro AJAX por categorías y precio
   - Búsqueda en vivo de productos

4. **Wishlist**
   - Lista de deseos para usuarios autenticados
   - Panel lateral y vista de cuadrícula
   - Página de administración con insights

5. **Integración Mercado Pago**
   - URLs de retorno personalizadas
   - Redirección forzada al punto de pago

---

### 🛠️ Tecnologías Utilizadas
- WordPress
- WooCommerce
- Bootstrap 5
- Font Awesome
- jQuery
- Fancybox
- Swiper
- Mercado Pago

---

### 🔐 Seguridad
- Nonces en formularios AJAX
- Sanitización de entradas
- Validación de permisos de usuario
- Campo honeypot anti-spam en formulario de contacto
