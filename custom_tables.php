<?php


// Include the necessary files
include_once plugin_dir_path( __FILE__ ) . 'includes/custom-table-functions.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/ajax-functions.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/shortcode-functions.php';



// Enqueue cs file and localize it
function enqueue_my_plugin_styles() {
    // Register the style for the plugin.
    wp_register_style('my-plugin-style', plugins_url('assets/css/my-plugin-styles.css', __FILE__), array(), '1.0.0', 'all');

    // Enqueue the style.
    wp_enqueue_style('my-plugin-style');
}

// Hook into the 'wp_enqueue_scripts' action.
add_action('wp_enqueue_scripts', 'enqueue_my_plugin_styles');


//create the Tables when the pluging is activated 
function create_custom_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table for Subcontratistas
    $table_name_subcontratistas = $wpdb->prefix . 'subcontratistas';

    $sql_subcontratistas = "CREATE TABLE $table_name_subcontratistas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        Nombre VARCHAR(255) NOT NULL,
        Empresa VARCHAR(255) NOT NULL,
        Especialidad VARCHAR(255) NOT NULL,
        Telefono VARCHAR(20) NOT NULL,
        Email VARCHAR(100) NOT NULL,
        Comentarios TEXT,
        Hora DATETIME DEFAULT CURRENT_TIMESTAMP,
        Usuario VARCHAR(100) NOT NULL,
        PRIMARY KEY  (id)
      ) $charset_collate;";
      
      // Table for Proveedores
      $table_name_proveedores = $wpdb->prefix . 'proveedores';
      
      $sql_proveedores = "CREATE TABLE $table_name_proveedores (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        Nombre VARCHAR(255) NOT NULL,
        Empresa VARCHAR(255) NOT NULL,
        Especialidad VARCHAR(255) NOT NULL,
        Telefono VARCHAR(20) NOT NULL,
        Email VARCHAR(100) NOT NULL,
        Comentarios TEXT,
        Hora DATETIME DEFAULT CURRENT_TIMESTAMP,
        Usuario VARCHAR(100) NOT NULL,
        PRIMARY KEY  (id)
      ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_subcontratistas );
    dbDelta( $sql_proveedores );
}
function create_tables_activation_hook() {
    create_custom_tables();
}
