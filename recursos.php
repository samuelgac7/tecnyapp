<?php
function display_resource($resource_type) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tecnyapp_' . $resource_type;

    $results = $wpdb->get_results("SELECT id, name, unit, price, source FROM {$table_name} LIMIT 25");
    echo "<input type='text' id='{$resource_type}_search' placeholder='Filtrar Por Nombre..' class='form-control mb-3'>";
    echo "<div id='aux_{$resource_type}'>";
    echo "<div class='table-responsive'>";
    echo "<table id='{$resource_type}Table' class='table table-bordered table-striped supertabla'>";
    echo "<thead class='thead-dark'>";
    echo "<tr><th>N°</th><th>Nombre</th><th>Unidad</th><th>Precio</th><th>Fuente</th><th>Actions</th></tr>";
    echo "</thead>";
    echo "<tbody>";

    foreach ($results as $row) {
        $formattedPrice = "$" . number_format($row->price, 0, ',', '.');
        echo "<tr id='resource-{$row->id}'>";
        echo "<td class='editable' data-field='id'>" . esc_html($row->id) . "</td>";
        echo "<td class='editable' data-field='name'>" . esc_html($row->name) . "</td>";
        echo "<td class='editable' data-field='unit'>" . esc_html($row->unit) . "</td>";
        echo "<td class='editable' data-field='price'>" . esc_html($formattedPrice) . "</td>";
        echo "<td class='editable' data-field='source'>" . esc_html($row->source) . "</td>";
        echo "<td>";
        echo "<button class='edit-button'>Edit</button>";
        echo "<button class='submit-button' style='display:none;'>Submit</button>";
        echo "<button class='cancel-button' style='display:none;'>Cancel</button>";
        echo "</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";  // Close 'table-responsive'
    echo "</div>";
}

add_action('wp_ajax_filter_resources', 'filter_resources_callback');

function filter_resources_callback() {
    // Verify nonce for security
    check_ajax_referer('dashboard_nonce', 'nonce');

    $resource_type = isset($_POST['resource_type']) ? sanitize_text_field($_POST['resource_type']) : '';
    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    // Database query to search for resources by name
    global $wpdb;
    $table_name = $wpdb->prefix . 'tecnyapp_' . $resource_type;
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE name LIKE %s LIMIT 50",
        '%' . $wpdb->esc_like($search_term) . '%'
    ));

    // Start capturing output with an output buffer
    ob_start();

    echo "<div class='table-responsive'>";
    echo "<table id='{$resource_type}Table' class='table table-bordered table-striped supertabla'>";
    echo "<thead class='thead-dark'>";
    echo "<tr><th>N°</th><th>Nombre</th><th>Unidad</th><th>Precio</th><th>Fuente</th><th>Actions</th></tr>";
    echo "</thead>";
    echo "<tbody>";

    foreach ($results as $row) {
        $formattedPrice = "$" . number_format($row->price, 0, ',', '.');
        echo "<tr id='resource-{$row->id}'>";
        echo "<td class='editable' data-field='id'>" . esc_html($row->id) . "</td>";
        echo "<td class='editable' data-field='name'>" . esc_html($row->name) . "</td>";
        echo "<td class='editable' data-field='unit'>" . esc_html($row->unit) . "</td>";
        echo "<td class='editable' data-field='price'>" . esc_html($formattedPrice) . "</td>";
        echo "<td class='editable' data-field='source'>" . esc_html($row->source) . "</td>";
        echo "<td>";
        echo "<button class='edit-button'>Edit</button>";
        echo "<button class='submit-button' style='display:none;'>Submit</button>";
        echo "<button class='cancel-button' style='display:none;'>Cancel</button>";
        echo "</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Get the contents from the output buffer and end buffering
    $output = ob_get_clean();

    echo $output; // send the output

    die();
}
