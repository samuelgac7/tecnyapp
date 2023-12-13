<?php

// Create the tables when the plugin is activated 
function create_files_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table for files management
    $table_name_files = $wpdb->prefix . 'tecnycon_files';

    $sql_files = "CREATE TABLE $table_name_files (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        file_name VARCHAR(255) NOT NULL,
        file_description TEXT NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        file_url VARCHAR(255) NOT NULL,
        user_id bigint(20) NOT NULL,
        upload_time datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_files );
}



function create_files_table_activation_hook() {
    create_files_table();
}

function display_file_upload_form() {
    ?>
 <form id="file-upload-form" method="post" enctype="multipart/form-data" class="mb-3">

        <div class="form-group">
            <input type="hidden" name="action" value="handle_file_upload">
            <label for="uploaded_file">Elegir Archivo:</label>
            <input type="file" name="uploaded_file" class="form-control-file">
        </div>
        <div class="form-group">
            <label for="file_description">Descripción del archivo:</label>
            <textarea name="file_description" class="form-control" required></textarea>
        </div>
        <input type="submit" name="upload_file_submit" value="Subir" class="btn btn-primary">
    </form>
    <?php
}

function handle_file_upload() {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $uploadedfile = $_FILES['uploaded_file'];

    // Check if the $_FILES array is empty (no file selected)
    if (empty($uploadedfile)) {
        echo "No file was selected.";
        wp_die();
    }

    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'tecnycon_files',
            array(
                'file_url' => $movefile['url'],
                'file_name' => $uploadedfile['name'],
                'file_description' => $_POST['file_description'],
                'file_type' => wp_check_filetype($uploadedfile['name'])['ext'],
                'user_id' => get_current_user_id(),   
                'upload_time' => current_time('mysql') 
            )
        );
        
        // Check if the insert was successful
        if ($wpdb->insert_id) {
            echo "File uploaded successfully.";
        } else {
            echo "Failed to write file info to database.";
        }
    } else {
        echo "Error: " . $movefile['error'];
    }

    wp_die(); 
}



add_action('wp_ajax_handle_file_upload', 'handle_file_upload');

function display_uploaded_files() {
    global $wpdb;

    $files = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "tecnycon_files ORDER BY id DESC", ARRAY_A);

    if ($files) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th scope="col">N°</th><th scope="col">Nombre del archivo</th><th scope="col">Descripción</th><th scope="col">Tipo de archivo</th><th scope="col">Link descarga</th></tr></thead>';
        echo '<tbody>';

        foreach ($files as $index => $file) {
            $icon = get_icon_for_file_type($file['file_type']);

            echo '<tr>';
            echo '<th scope="row">' . ($index + 1) . '</th>';
            echo '<td>' . esc_html($file['file_name']) . '</td>';
            echo '<td>' . esc_html($file['file_description']) . '</td>';

            echo '<td>' . $icon . '</td>';
            echo '<td><a href="' . esc_url($file['file_url']) . '" download class="btn btn-link">Descargar</a></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No se han subido archivos todavía.</p>';
    }
}

function get_icon_for_file_type($type) {
    switch ($type) {
        case 'pdf':
            return '<i class="fas fa-file-pdf text-danger"></i>';
        case 'doc':
        case 'docx':
            return '<i class="fas fa-file-word text-primary"></i>';
        case 'xls':
        case 'xlsx':
            return '<i class="fas fa-file-excel text-success"></i>';
        default:
            return '<i class="fas fa-file"></i>';
    }
}
