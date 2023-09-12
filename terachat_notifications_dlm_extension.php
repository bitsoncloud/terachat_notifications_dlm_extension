<?php
/*
Plugin Name: Terachat Notifications DLM Extension
Plugin URI: https://bitsoncloud.com
Description: Compatibilidad con Digital License Manager Pro en las Notificaciones de pedido para Woocommerce 
Version: 0.9.2
Author: Bits On Cloud LLC
Author URI: https://bitsoncloud.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined('ABSPATH') or die("Bye bye");
define('TERA_DLM_RUTA',plugin_dir_path(__FILE__));
define('TERA_DLM_URL',plugins_url('seisalmes-woo-licenses'));
define('TERA_DLM_NOMBRE','Terachat Notifications DLM Extension');
define('TERA_DLM_TYPE',0);

include_once TERA_DLM_RUTA."/funciones.php";

add_action('admin_init', 'tera_notif_dlm_check_required_plugins');
function tera_notif_dlm_check_required_plugins() {
    $required_plugins = array(
        'terachat_notifications/terachat_notifications.php', 
        'digital-license-manager-pro/digital-license-manager-pro.php', 
    );

    foreach ($required_plugins as $plugin) {
        if (!is_plugin_active($plugin)) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Para activar este plugin, necesitas tener activo el Plugin Requerido: ' . $plugin);
        }
    }
}

