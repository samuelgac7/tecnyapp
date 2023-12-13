<?php
// The function that will handle the AJAX request
function update_data() {
    global $wpdb; // Global database variable

    // Check for nonce security
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'dashboard_nonce')) {
        wp_send_json_error('Nonce value not verified.');
    }
    error_log(print_r($_POST, true)); // Log the POST data

    // Sanitize input
    $id = intval($_POST['id']);
    $Nombre = sanitize_text_field($_POST['Nombre']);
    $Empresa = sanitize_text_field($_POST['Empresa']);
    $Especialidad = sanitize_text_field($_POST['Especialidad']);
    $Telefono = sanitize_text_field($_POST['Telefono']);
    $Email = sanitize_email($_POST['Email']);
    $Comentarios = sanitize_text_field($_POST['Comentarios']);
    $form_type = sanitize_text_field($_POST['form_type']);

    // get what table i am updating/inserting
    $table_name = $wpdb->prefix . $form_type;
    //get the current user
    $current_user = wp_get_current_user();
    $Usuario = $current_user->user_login;
    //update data
    update_table($id, $Nombre,$Empresa, $Especialidad, $Telefono, $Email, $Comentarios,$table_name,$Usuario); 
}
function update_table($id, $Nombre,$Empresa, $Especialidad, $Telefono, $Email, $Comentarios,$table_name,$Usuario) {
    global $wpdb;

    $updated = $wpdb->update(
        $table_name, 
        array(
            'Nombre' => $Nombre,
            'Empresa' => $Empresa,
            'Especialidad' => $Especialidad,
            'Telefono' => $Telefono,
            'Email' => $Email,
            'Comentarios' => $Comentarios,
            'Usuario' => $Usuario
        ), 
        array('id' => $id) // WHERE clause
    );

    if ($updated) {
        wp_send_json_success(array('message' => 'Successfully updated data.'));
    } else {
        $error_message = $wpdb->last_error ? $wpdb->last_error : 'Failed to update data.';
        wp_send_json_error($error_message);
    }
}

add_action('wp_ajax_update_data', 'update_data');

function handle_form_data() {
    global $wpdb; // Global database variable
    // Check for nonce security
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'dashboard_nonce')) {
        wp_send_json_error('Nonce value not verified.');
    }
    error_log(print_r($_POST, true)); // Log the POST data for debugging

    // Sanitize input
    $Nombre = sanitize_text_field($_POST['Nombre']);
    $Empresa = sanitize_text_field($_POST['Empresa']);
    $Especialidad = sanitize_text_field($_POST['Especialidad']);
    $Telefono = sanitize_text_field($_POST['Telefono']);
    $Email = sanitize_email($_POST['Email']);
    $Comentarios = sanitize_text_field($_POST['Comentarios']);
    $form_type = sanitize_text_field($_POST['form_type']);
    
    // Get the current user's username
    $current_user = wp_get_current_user();
    $Usuario = $current_user->user_login;

    // Get the table name based on form_type
    $table_name = $wpdb->prefix . $form_type;  // Assuming your table name is like wp_subcontratistas, etc.

    // Insert data
    $result = $wpdb->insert(
        $table_name,
        array(
            'Nombre' => $Nombre,
            'Empresa' => $Empresa,
            'Especialidad' => $Especialidad,
            'Telefono' => $Telefono,
            'Email' => $Email,
            'Comentarios' => $Comentarios,
            'Usuario' => $Usuario
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s') // Data format
    );

    if ($result) {
        wp_send_json_success(array('message' => 'Data inserted successfully!'));
    } else {
        wp_send_json_error(array('message' => 'Failed to insert data. Please try again.'));
    }
}

add_action('wp_ajax_submit_form_data', 'handle_form_data');

function handle_resource_data() {
    global $wpdb; // Global database variable

    // Check for nonce security
    if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'dashboard_nonce')) {
        wp_send_json_error('Nonce value not verified.');
    }

    $resourceType = sanitize_text_field($_POST['form_type']);

    // Sanitize input
    $name = sanitize_text_field($_POST['name']);
    $unit = sanitize_text_field($_POST['unit']);
    $price = sanitize_text_field($_POST['price']);
    $source = sanitize_text_field($_POST['source']);

    // Get the table name based on resource type
    $table_name = $wpdb->prefix . 'tecnyapp_' . $resourceType;

    // Insert data
    $result = $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
            'unit' => $unit,
            'price' => $price,
            'source' => $source
        ),
        array('%s', '%s', '%s', '%s') // Data format
    );

    if ($result) {
        
        wp_send_json_success(array('message' => 'Data inserted successfully!'));
    } else {
        wp_send_json_error(array('message' => 'Failed to insert data. Please try again.'));
    }
}

add_action('wp_ajax_submit_resource_data', 'handle_resource_data');
