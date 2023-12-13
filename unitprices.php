<?php
function display_unit_prices() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tecnyapp_unitprice';

    $results = $wpdb->get_results("SELECT id, name, date, notes, source, unit FROM {$table_name} LIMIT 25");
    echo "<input type='text' id='unitprice_search' placeholder='Filtrar Por Nombre..' class='form-control mb-3'>";
    echo "<div id=aux>";
    echo "<div class='table-responsive'>";
    echo "<table id='unitPriceTable' class='table table-bordered table-striped supertabla'>";  // Added 'table-bordered' and 'table-striped' for better visibility
    echo "<thead class='thead-dark'>";  // Added 'thead-dark' for dark header
    echo "<tr><th>N°</th><th>Nombre</th><th>Fecha</th><th>Fuente</th><th>Unidad</th><th>Precio Unitario</th><th>Comentarios</th></tr>";
    echo "</thead>";
    echo "<tbody>";

    foreach ($results as $row) {
        $unitPriceId = $row->id;
        $totalCost = calculate_precios($unitPriceId);

        // Modify the cost value
        $formattedCost = "$" . number_format($totalCost, 0, ',', '.');
        echo "<tr class='unitprice-row'>";
        echo "<td>" . esc_html($row->id) . "</td>";
        echo "<td>" . esc_html($row->name) . "</td>";
        echo "<td>" . esc_html($row->date) . "</td>";
        echo "<td>" . esc_html($row->source) . "</td>";
        echo "<td>" . esc_html($row->unit) . "</td>";
        echo "<td>" . esc_html($formattedCost) . "</td>"; 
        echo "<td>" . esc_html($row->notes) . "</td>";
        echo "</tr>";
            // Add an empty details <tr> beneath each main row
        echo "<tr class='details-row' id='details-" . esc_html($row->id) . "' style='display: none;'>";
        echo "<td colspan='7'>Loading...</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";  // Close 'table-responsive'
    echo "</div>";
}



function calculate_precios($unitPriceId){
    global $wpdb;
    
    // Compute the total cost for materials based on the new 'price' column
    $materialCost = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(upm.price) 
            FROM wp_tecnyapp_unitpricematerial upm
            WHERE upm.unit_price_id = %d", $unitPriceId));

    // Compute the total cost for labor based on the new 'price' column
    $laborCost = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(upl.price) 
            FROM wp_tecnyapp_unitpricelabor upl
            WHERE upl.unit_price_id = %d", $unitPriceId));

    // Compute the total cost for equipment based on the new 'price' column
    $equipmentCost = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(upe.price) 
            FROM wp_tecnyapp_unitpriceequipment upe
            WHERE upe.unit_price_id = %d", $unitPriceId));

    // Compute the total cost for others based on the new 'price' column
    $othersCost = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(upo.price) 
            FROM wp_tecnyapp_unitpriceothers upo
            WHERE upo.unit_price_id = %d", $unitPriceId));

    // Sum up the costs from all categories and return
    return $materialCost + $laborCost + $equipmentCost + $othersCost;
}

add_action('wp_ajax_filter_unit_prices', 'filter_unit_prices_callback');

function filter_unit_prices_callback() {
    // Verify nonce for security
    check_ajax_referer('dashboard_nonce', 'nonce');

    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    // Database query to search for unit prices by name
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM wp_tecnyapp_unitprice WHERE name LIKE %s LIMIT 50",
        '%' . $wpdb->esc_like($search_term) . '%'
    ));

    // Start capturing output with an output buffer
    ob_start();

    echo "<div class='table-responsive'>";
    echo "<table id='unitPriceTable' class='table table-bordered table-striped supertabla'>";
    echo "<thead class='thead-dark'>";
    echo "<tr><th>N°</th><th>Nombre</th><th>Fecha</th><th>Fuente</th><th>Unidad</th><th>Precio Unitario</th><th>Comentarios</th></tr>";
    echo "</thead>";
    echo "<tbody>";

    foreach ($results as $row) {
        $unitPriceId = $row->id;
        $totalCost = calculate_precios($unitPriceId);

        // Modify the cost value
        $formattedCost = "$" . number_format($totalCost, 0); // format cost with no decimals and prepend $

        echo "<tr>";
        echo "<td>" . esc_html($row->id) . "</td>";
        echo "<td>" . esc_html($row->name) . "</td>";
        echo "<td>" . esc_html($row->date) . "</td>";
        echo "<td>" . esc_html($row->source) . "</td>";
        echo "<td>" . esc_html($row->unit) . "</td>";
        echo "<td>" . esc_html($formattedCost) . "</td>";  // Display the total cost with 2 decimal places
        echo "<td>" . esc_html($row->notes) . "</td>";
        echo "</tr>";
            // Add an empty details <tr> beneath each main row
        echo "<tr class='details-row' id='details-" . esc_html($row->id) . "' style='display: none;'>";
        echo "<td colspan='7'>Loading...</td>";
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

function fetch_unit_price_details_callback() {
    global $wpdb;

    check_ajax_referer('dashboard_nonce', 'nonce');

    $unitPriceId = intval($_POST['unitPriceId']);
    $data = array();

    // Fetch materials for the unit price
    $materials = $wpdb->get_results($wpdb->prepare(
        "SELECT m.name, upm.quantity, upm.price, upm.single_price, m.unit 
         FROM wp_tecnyapp_unitpricematerial upm
         JOIN wp_tecnyapp_material m ON upm.material_id = m.id
         WHERE upm.unit_price_id = %d", 
         $unitPriceId));

    $data['Materials'] = array();
    foreach ($materials as $material) {
        $data['Materials'][] = array(
            'name' => $material->name,
            'quantity' => $material->quantity,
            'price' => $material->price,
            'single_price' => $material->single_price,
            'unit' => $material->unit
        );
    }

    // Fetch labor details for the unit price
    $labors = $wpdb->get_results($wpdb->prepare(
        "SELECT l.name, upl.quantity, upl.price, upl.single_price, l.unit 
         FROM wp_tecnyapp_unitpricelabor upl
         JOIN wp_tecnyapp_labor l ON upl.labor_id = l.id      
         WHERE upl.unit_price_id = %d", 
         $unitPriceId));

    $data['Labor'] = array();
    foreach ($labors as $labor) {
        $data['Labor'][] = array(
            'name' => $labor->name,
            'quantity' => $labor->quantity,
            'price' => $labor->price,
            'single_price' => $labor->single_price,
            'unit' => $labor->unit
        );
    }

    // Fetch equipment details for the unit price
    $equipments = $wpdb->get_results($wpdb->prepare(
        "SELECT e.name, upe.quantity, upe.price, upe.single_price, e.unit 
        FROM wp_tecnyapp_unitpriceequipment upe
        JOIN wp_tecnyapp_equipment e ON upe.equipment_id = e.id    
         WHERE upe.unit_price_id = %d", 
         $unitPriceId));

    $data['Equipment'] = array();
    foreach ($equipments as $equipment) {
        $data['Equipment'][] = array(
            'name' => $equipment->name,
            'quantity' => $equipment->quantity,
            'price' => $equipment->price,
            'single_price' => $equipment->single_price,
            'unit' => $equipment->unit
        );
    }

    // Fetch other costs for the unit price
    $others = $wpdb->get_results($wpdb->prepare(
        "SELECT o.name, upo.quantity, upo.price, upo.single_price, o.unit 
        FROM wp_tecnyapp_unitpriceothers upo
        JOIN wp_tecnyapp_others o ON upo.others_id = o.id      
         WHERE upo.unit_price_id = %d", 
         $unitPriceId));

    $data['Other Costs'] = array();
    foreach ($others as $other) {
        $data['Other Costs'][] = array(
            'name' => $other->name,
            'quantity' => $other->quantity,
            'price' => $other->price,
            'single_price' => $other->single_price,
            'unit' => $other->unit
        );
    }

    // Echo the data as a JSON string
    echo json_encode($data);
    die();
}

add_action('wp_ajax_fetch_unit_price_details', 'fetch_unit_price_details_callback');
