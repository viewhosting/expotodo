# Guía de Diseño y Estilos para el Plugin de Cotizaciones (Expotodo)

Esta guía define de manera exhaustiva el sistema de diseño, la tipografía, los colores y las estructuras HTML/CSS que deben utilizarse para la creación del **Plugin Especializado en Cotizaciones (Presupuestos)**. 

Para garantizar que el plugin sea **completamente independiente del tema actual** y siga funcionando con el mismo aspecto premium incluso si en el futuro se cambia a un tema completamente distinto, toda la arquitectura visual del plugin se encapsulará bajo su propio espacio de nombres en CSS.

---

## 1. Estrategia de Aislamiento y Encapsulación (Namespacing)

Para evitar colisiones con otros temas u otros plugins, **nunca** se deben redefinir elementos globales de HTML (como `input`, `select`, `table` o `button`) directamente. En su lugar, todas las vistas, formularios e interfaces generadas por el plugin de cotizaciones deben estar envueltas en un contenedor con la clase de ámbito principal: `.et-cotizador-wrapper`.

### Regla de Oro
Todos los estilos del plugin se escribirán anteponiendo esta clase base:
```css
/* CORRECTO: Aislado dentro de la aplicación de cotizaciones */
.et-cotizador-wrapper .btn-primary { ... }
.et-cotizador-wrapper .form-input { ... }

/* INCORRECTO: Afectaría a todo el sitio web o chocaría con el tema */
.btn-primary { ... }
input[type="text"] { ... }
```

---

## 2. Paleta de Colores (Design Tokens)

El plugin debe incorporar sus propias variables CSS para asegurar la portabilidad de los colores. Estos colores se extraen directamente del diseño actual de Expotodo:

```css
.et-cotizador-wrapper {
    /* Paleta Oficial Expotodo */
    --et-primary: #b0d443;             /* Verde Limón Claro */
    --et-primary-dark: #8ca835;        /* Verde Oliva Obscuro */
    --et-primary-deep: #354213;        /* Verde Bosque Elegante */
    --et-rosa: #d443b0;                /* Rosa Accent */
    --et-accent: #e74c3c;              /* Rojo de Alerta */
    
    /* Colores de Texto */
    --et-text-dark: #1a1a1a;           /* Texto Principal Obscuro */
    --et-text-muted: #475569;          /* Gris para Subtítulos/Labels */
    --et-text-light: #94a3b8;          /* Gris Claro para Placeholders */
    --et-text-white: #ffffff;          /* Blanco */
    
    /* Fondos y Bordes */
    --et-bg-page: #f8fafc;             /* Fondo de Página Ultra-Claro */
    --et-bg-card: #ffffff;             /* Fondo de Tarjetas */
    --et-bg-input: #f8fafc;            /* Fondo de Inputs */
    --et-border: #e2e8f0;              /* Borde Gris Suave */
    --et-border-hover: #cbd5e1;        /* Borde Gris Medio al pasar el cursor */
    
    /* Sombras y Efectos */
    --et-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.02);
    --et-shadow-md: 0 10px 25px rgba(0, 0, 0, 0.03);
    --et-shadow-lg: 0 15px 35px rgba(140, 168, 53, 0.06); /* Sombra con resplandor verde oliva tenue */
    --et-shadow-focus: 0 0 0 4px rgba(176, 212, 67, 0.15); /* Resplandor verde limón para foco */
}
```

---

## 3. Tipografía y Jerarquía Visual

Para alinearse con el tema actual de Expotodo sin depender de que este cargue las fuentes, el plugin utilizará la siguiente pila tipográfica modular (cargando Roboto y Avenir Next si están disponibles, o cayendo a fuentes del sistema de alta legibilidad):

- **Tipografía de Títulos (`h1` a `h6`):** `'Avenir Next', -apple-system, BlinkMacSystemFont, sans-serif`
- **Tipografía de Texto y Formularios (`body`, `input`, `select`, `p`):** `'Roboto', 'Segoe UI', system-ui, sans-serif`

```css
.et-cotizador-wrapper {
    font-family: 'Roboto', 'Segoe UI', system-ui, sans-serif;
    color: var(--et-text-muted);
    line-height: 1.6;
}

.et-cotizador-wrapper h1,
.et-cotizador-wrapper h2,
.et-cotizador-wrapper h3,
.et-cotizador-wrapper h4,
.et-cotizador-wrapper h5,
.et-cotizador-wrapper h6 {
    font-family: 'Avenir Next', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--et-text-dark);
    font-weight: 700;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
```

---

## 4. Estilo de Botones (Buttons System)

El template de Expotodo utiliza dos tratamientos de bordes en botones: rectos (para el catálogo y tarjetas de producto) y redondeados premium (para la pasarela de pago y checkout). Para el Cotizador, se recomienda el uso del esquema premium redondeado para inspirar confianza y suavidad en la cotización.

### A. Botón Principal (Primary Action Button)
Se utiliza para "Generar Cotización", "Agregar Producto al Presupuesto" o "Enviar por PDF".
```css
.et-cotizador-wrapper .et-btn-primary {
    background-color: var(--et-primary) !important;
    color: var(--et-text-dark) !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 16px 28px !important;
    font-family: 'Avenir Next', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.95rem !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 10px 20px rgba(176, 212, 67, 0.25) !important;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
}

.et-cotizador-wrapper .et-btn-primary:hover {
    background-color: var(--et-primary-dark) !important;
    color: var(--et-text-white) !important;
    transform: translateY(-3px) !important;
    box-shadow: 0 15px 30px rgba(140, 168, 53, 0.35) !important;
}
```

### B. Botón Secundario / Contorno (Outline Button)
Se utiliza para acciones complementarias como "Limpiar Campos", "Atrás" o "Agregar Otro Elemento".
```css
.et-cotizador-wrapper .et-btn-outline {
    background: transparent !important;
    color: var(--et-text-dark) !important;
    border: 2px solid var(--et-text-dark) !important;
    border-radius: 12px !important;
    padding: 14px 26px !important;
    font-family: 'Avenir Next', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease !important;
}

.et-cotizador-wrapper .et-btn-outline:hover {
    background-color: var(--et-text-dark) !important;
    color: var(--et-text-white) !important;
    transform: translateY(-2px) !important;
}
```

---

## 5. Estilo Detallado de Formularios y Controles

Los formularios son el núcleo de un cotizador. Esta sección describe de forma milimétrica cómo deben lucir los campos de entrada de texto, las áreas de texto y los selectores desplegables para mantener una paridad exacta con el diseño premium de Expotodo:

### A. Estructura de Fila y Campo (Layout de Formulario)
Cada campo debe estar encapsulado en un contenedor de control con etiqueta y espaciado consistente.
```css
.et-cotizador-wrapper .et-form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
    width: 100%;
}

.et-cotizador-wrapper .et-form-label {
    display: block !important;
    font-size: 0.85rem !important;
    font-weight: 600 !important;
    color: var(--et-text-muted) !important;
    margin-bottom: 8px !important;
    text-align: left;
}
```

### B. Campos de Texto y Área de Texto (`input[type="text"]`, `textarea`)
Tienen un fondo ligeramente tintado en gris-azul, bordes suaves y una animación elástica al recibir el foco:
```css
.et-cotizador-wrapper .et-form-input,
.et-cotizador-wrapper .et-form-textarea {
    width: 100% !important;
    height: 52px !important;
    padding: 12px 18px !important;
    border: 1.5px solid var(--et-border) !important;
    border-radius: 12px !important;
    background-color: var(--et-bg-input) !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    color: var(--et-text-dark) !important;
    box-sizing: border-box !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.01) !important;
}

.et-cotizador-wrapper .et-form-textarea {
    height: 120px !important;
    resize: vertical !important;
}

/* Efecto hover (Al pasar el cursor) */
.et-cotizador-wrapper .et-form-input:hover,
.et-cotizador-wrapper .et-form-textarea:hover {
    border-color: var(--et-border-hover) !important;
    background-color: var(--et-bg-card) !important;
}

/* Efecto focus (Al dar clic en el campo) */
.et-cotizador-wrapper .et-form-input:focus,
.et-cotizador-wrapper .et-form-textarea:focus {
    border-color: var(--et-primary) !important;
    background-color: var(--et-bg-card) !important;
    outline: none !important;
    box-shadow: var(--et-shadow-focus) !important;
}

/* Estilo del Placeholder */
.et-cotizador-wrapper .et-form-input::placeholder,
.et-cotizador-wrapper .et-form-textarea::placeholder {
    color: var(--et-text-light) !important;
    font-weight: 400 !important;
}
```

### C. Selector Desplegable Personalizado (`select`)
Los navegadores renderizan los elementos `<select>` de formas muy inconsistentes. Expotodo implementa una flecha minimalista en SVG codificada directamente en CSS para anular la apariencia nativa y brindar un look unificado:
```css
.et-cotizador-wrapper .et-form-select {
    width: 100% !important;
    height: 52px !important;
    padding: 12px 40px 12px 18px !important; /* Más espacio a la derecha para no pisar la flecha */
    border: 1.5px solid var(--et-border) !important;
    border-radius: 12px !important;
    background-color: var(--et-bg-input) !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    color: var(--et-text-dark) !important;
    box-sizing: border-box !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    
    /* Anular flecha nativa */
    appearance: none !important;
    -webkit-appearance: none !important;
    
    /* Flecha personalizada en SVG */
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 15px center !important;
    background-size: 16px !important;
}

.et-cotizador-wrapper .et-form-select:hover {
    border-color: var(--et-border-hover) !important;
    background-color: var(--et-bg-card) !important;
}

.et-cotizador-wrapper .et-form-select:focus {
    border-color: var(--et-primary) !important;
    background-color: var(--et-bg-card) !important;
    outline: none !important;
    box-shadow: var(--et-shadow-focus) !important;
}
```

---

## 6. Estilos de Tablas de Desglose de Productos

Toda cotización requiere una tabla donde se Listen los productos, cantidades, precios unitarios e importes parciales. La estética de la tabla está alineada con el diseño limpio y los degradados de la sección de medidas del template:

```css
.et-cotizador-wrapper .et-table-responsive {
    width: 100%;
    overflow-x: auto;
    border-radius: 16px;
    box-shadow: var(--et-shadow-sm);
    border: 1px solid var(--et-border);
    margin-bottom: 25px;
}

.et-cotizador-wrapper .et-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background-color: var(--et-bg-card);
}

/* Cabecera de la Tabla */
.et-cotizador-wrapper .et-table thead tr {
    background-color: var(--et-primary-dark) !important;
    color: var(--et-text-white) !important;
}

.et-cotizador-wrapper .et-table th {
    padding: 16px 20px !important;
    font-family: 'Avenir Next', -apple-system, BlinkMacSystemFont, sans-serif;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--et-border);
    text-align: left;
}

/* Alineaciones específicas */
.et-cotizador-wrapper .et-table th.text-center,
.et-cotizador-wrapper .et-table td.text-center {
    text-align: center;
}

.et-cotizador-wrapper .et-table th.text-right,
.et-cotizador-wrapper .et-table td.text-right {
    text-align: right;
}

/* Celdas de la Tabla */
.et-cotizador-wrapper .et-table td {
    padding: 14px 20px !important;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
    color: var(--et-text-muted);
    vertical-align: middle;
}

/* Efecto hover sobre las filas de productos */
.et-cotizador-wrapper .et-table tbody tr {
    transition: background-color 0.2s ease;
}

.et-cotizador-wrapper .et-table tbody tr:hover {
    background-color: #f8fafc;
}

/* Quitar borde en la última fila para redondear bordes inferiores */
.et-cotizador-wrapper .et-table tbody tr:last-child td {
    border-bottom: none;
}
```

---

## 7. Tarjetas y Paneles Informativos (Cards)

Las tarjetas sirven para separar lógicamente los bloques funcionales (Datos del Cliente, Selección de Maniquíes, y Totales). Utilizan el estilo de tarjetas de testimoniales con sombras ultra suaves y bordes redondeados orgánicos:

```css
.et-cotizador-wrapper .et-card {
    background: var(--et-bg-card);
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 20px;
    padding: 35px;
    box-shadow: var(--et-shadow-md);
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    margin-bottom: 30px;
}

.et-cotizador-wrapper .et-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--et-shadow-lg);
    border-color: rgba(176, 212, 67, 0.4); /* Resplandor del verde primario */
}

/* Título de la Tarjeta */
.et-cotizador-wrapper .et-card-title {
    font-size: 1.15rem;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--et-primary);
    display: inline-block;
}
```

---

## 8. Notificaciones de Éxito / Error e Indicadores

### A. Mensajes Inline (Feedback de Formulario)
Para notificar cuando una cotización fue creada o hubo un error en un campo:
```css
.et-cotizador-wrapper .et-alert {
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.et-cotizador-wrapper .et-alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.et-cotizador-wrapper .et-alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
```

### B. Toasts Flotantes Premium
Para avisar al usuario dinámicamente cuando un producto es añadido al borrador:
```css
.et-toast-container {
    position: fixed;
    top: 30px;
    right: 30px;
    z-index: 999999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 380px;
    width: calc(100% - 60px);
}

.et-toast {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px) saturate(180%);
    -webkit-backdrop-filter: blur(12px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-left: 5px solid var(--et-primary);
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    color: var(--et-text-dark);
    font-family: 'Avenir Next', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    animation: etToastIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    cursor: pointer;
}

@keyframes etToastIn {
    from { opacity: 0; transform: translateX(100%) scale(0.9); }
    to { opacity: 1; transform: translateX(0) scale(1); }
}
```

---

## 9. Sistema de Grid y Maquetación Responsiva

Para evitar el uso de frameworks pesados de CSS dentro del plugin, se proporciona una estructura de rejilla modular nativa mediante **CSS Grid**, con soporte completo para pantallas táctiles y móviles:

```css
.et-grid {
    display: grid !important;
    gap: 20px;
    width: 100%;
}

/* Variaciones de columnas */
.et-grid-2 { grid-template-columns: repeat(2, 1fr); }
.et-grid-3 { grid-template-columns: repeat(3, 1fr); }
.et-grid-4 { grid-template-columns: repeat(4, 1fr); }

.et-col-span-2 { grid-column: span 2; }
.et-col-span-3 { grid-column: span 3; }

/* Adaptación a dispositivos móviles (Breakpoints) */
@media (max-width: 992px) {
    .et-grid-3, .et-grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .et-grid-2, .et-grid-3, .et-grid-4 {
        grid-template-columns: 1fr;
    }
    .et-col-span-2, .et-col-span-3 {
        grid-column: span 1;
    }
    
    .et-cotizador-wrapper .et-card {
        padding: 20px;
    }
}
```

---

## 10. Plantilla HTML Sugerida para una Vista del Plugin

A continuación se muestra una plantilla de estructura HTML recomendada para el formulario de presupuestos, utilizando los estilos documentados:

```html
<div class="et-cotizador-wrapper">
    <div class="et-grid et-grid-3">
        
        <!-- Columna Izquierda/Centro: Formulario de Datos y Selección (Ocupa 2/3 columnas en escritorio) -->
        <div class="et-col-span-2">
            
            <!-- Tarjeta 1: Datos del Solicitante -->
            <div class="et-card">
                <h3 class="et-card-title">Datos del Presupuesto</h3>
                
                <div class="et-grid et-grid-2">
                    <div class="et-form-group">
                        <label class="et-form-label" for="et-cliente">Nombre del Cliente</label>
                        <input class="et-form-input" type="text" id="et-cliente" placeholder="Escribe el nombre de la empresa o persona">
                    </div>
                    <div class="et-form-group">
                        <label class="et-form-label" for="et-validez">Validez de la Cotización</label>
                        <select class="et-form-select" id="et-validez">
                            <option value="15">15 Días Naturales</option>
                            <option value="30" selected>30 Días Naturales</option>
                            <option value="60">60 Días Naturales</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta 2: Detalle de Productos -->
            <div class="et-card">
                <h3 class="et-card-title">Productos Seleccionados</h3>
                
                <div class="et-table-responsive">
                    <table class="et-table">
                        <thead>
                            <tr>
                                <th>Maniquí / Sistema</th>
                                <th class="text-center" style="width: 100px;">Cant</th>
                                <th class="text-right" style="width: 140px;">Precio Unit.</th>
                                <th class="text-right" style="width: 140px;">Subtotal</th>
                                <th class="text-center" style="width: 80px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Maniquí Dama Silueta Premium</strong> - Color Blanco Mate</td>
                                <td class="text-center">5</td>
                                <td class="text-right">$2,450.00 MXN</td>
                                <td class="text-right">$12,250.00 MXN</td>
                                <td class="text-center">
                                    <button class="et-btn-outline" style="padding: 6px 12px; font-size: 0.75rem; border-radius: 6px;">Quitar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <button class="et-btn-outline">+ Agregar Producto</button>
            </div>
            
        </div>
        
        <!-- Columna Derecha: Resumen de Totales y Exportación -->
        <div class="et-col-span-1">
            <div class="et-card" style="position: sticky; top: 90px;">
                <h3 class="et-card-title">Resumen</h3>
                
                <div style="margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem;">
                        <span>Subtotal Neto:</span>
                        <span style="font-weight: 600; color: var(--et-text-dark);">$12,250.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem;">
                        <span>I.V.A (16%):</span>
                        <span style="font-weight: 600; color: var(--et-text-dark);">$1,960.00</span>
                    </div>
                    <hr style="border: 0; border-top: 1.5px solid var(--et-border); margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-family: 'Avenir Next', sans-serif; font-weight: 800; text-transform: uppercase; font-size: 0.9rem;">Total Cotizado:</span>
                        <span style="font-size: 1.6rem; font-weight: 800; color: var(--et-primary-dark);">$14,210.00</span>
                    </div>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 12px; width: 100%;">
                    <button class="et-btn-primary" style="width: 100%;">
                        Generar Cotización (PDF)
                    </button>
                    <button class="et-btn-outline" style="width: 100%;">
                        Enviar por Correo
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>
```
