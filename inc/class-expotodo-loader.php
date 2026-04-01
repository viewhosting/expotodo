<?php
if (!defined('ABSPATH')) exit;

/**
 * Maestro de carga de módulos de Expotodo
 * Escanea el directorio de controladores y los inicializa automáticamente
 */
class Expotodo_Loader {

    public function __construct() {
        $this->load_all_modules();
    }

    private function load_all_modules() {
        $controller_path = get_template_directory() . '/inc/controllers/';
        
        // Buscamos todos los archivos PHP en la carpeta de controladores
        $controllers = glob($controller_path . '*.php');
        error_log('EXPOTODO LOADER: Found controllers: ' . print_r($controllers, true));
        if (empty($controllers)) return;

        foreach ($controllers as $file) {
            require_once $file;
            
            // Obtenemos el nombre de la clase a partir del nombre del archivo
            $class_name = str_replace('.php', '', basename($file));
            
            // Si la clase existe, la instanciamos
            if (class_exists($class_name)) {
                new $class_name();
            }
        }
    }
}
