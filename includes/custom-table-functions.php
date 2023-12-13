<?php
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
function get_data($type) {
    global $wpdb;
    $table_name = $wpdb->prefix.$type;
    return $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);
}

function display_data($type) {

    $items = get_data($type);
    
    // Added the 'table' and 'table-striped' classes for Bootstrap styling
    $export_url = admin_url('admin-post.php?action=export_to_excel&type=' . $type);
    echo '<button id="exportToExcel" onclick="window.location.href=\'' . $export_url . '\'">Export to Excel</button>';    
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped">';
    echo '<thead>'; // Added thead for proper table styling
    echo '<tr><th>N°</th><th>Nombre</th><th>Empresa</th><th>Especialidad</th><th>Telefono</th><th>Email</th><th>Comentarios</th><th>Usuario</th><th>Editar</th></tr>';
    echo '</thead>';
    echo '<tbody>'; // Added tbody for proper table styling
    foreach ($items as $item) {
        echo '<tr id="row-' . esc_html($item['id']) . '">';
        echo '<td>' . esc_html($item['id']) . '</td>';
        echo '<td class="editable" data-field="Nombre">' . esc_html($item['Nombre']) . '</td>';
        echo '<td class="editable" data-field="Empresa">' . esc_html($item['Empresa']) . '</td>';
        echo '<td class="editable" data-field="Especialidad">' . esc_html($item['Especialidad']) . '</td>';
        echo '<td class="editable" data-field="Telefono">' . esc_html($item['Telefono']) . '</td>';
        echo '<td class="editable" data-field="Email">' . esc_html($item['Email']) . '</td>';
        $display_comment = (strlen($item['Comentarios']) > 30) ? substr($item['Comentarios'],0,27).'...' : $item['Comentarios'];
        echo '<td class="editable comentario-tooltip" data-field="Comentarios" data-toggle="tooltip" data-placement="top" title="' . esc_attr($item['Comentarios']) . '">' . esc_html($display_comment) . '</td>';
        echo '<td>' . esc_html($item['Usuario']) . '</td>';
        echo '<td>';
        echo '<input type="hidden" name="form_type" value="' . esc_html($type) . '">'; // Hidden input for type

        // Adding Bootstrap button classes
        echo '<button class="edit-button btn btn-warning btn-sm" data-id="' . esc_html($item['id']) . '">✎</button>';
        echo '<button class="cancel-button btn btn-danger btn-sm" style="display: none;">X</button>';
        echo '<button class="submit-button btn btn-success btn-sm" style="display: none;">✓</button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>'; // Closing tbody
    echo '</table>';
    echo '</div>';
}


function export_to_excel() {
    global $wpdb;

    $type = $_GET['type'];
    $table_name = $wpdb->prefix . $type;
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    if (empty($results)) {
        return;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    //hides the gridlines
    $sheet->setShowGridlines(false);

    // Set headers
    $headers = array_keys($results[0]);
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '2', $header); // Headers are now in the second row
        $col++;
    }

    // Auto-size columns and get the highest column
    $highestColumn = $sheet->getHighestColumn();
    for ($column = 'A'; $column <= $highestColumn; $column++) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Set title
    $title = "Listado " . ucfirst($type) . " TECNYCON";
    $sheet->setCellValue('A1', $title);
    $sheet->mergeCells('A1:' . $highestColumn . '1');

    // Apply style to title
    $titleStyle = [
        'font' => [
            'bold' => true,
            'size' => 16
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ];
    $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray($titleStyle);

    // Apply style to headers
    $headerStyle = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => 'D3D3D3'], // Light gray background
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
    $sheet->getStyle('A2:' . $highestColumn . '2')->applyFromArray($headerStyle);

    // Set data and apply borders
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
    $rowNum = 3; // Start data from the third row
    foreach ($results as $row) {
        $col = 'A';
        foreach ($row as $value) {
            $sheet->setCellValue($col . $rowNum, $value);
            $col++;
        }
        $sheet->getStyle('A' . $rowNum . ':' . $highestColumn . $rowNum)->applyFromArray($dataStyle);
        $rowNum++;
    }

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $filename = $type . '_export_' . date('Ymd_His') . '.xlsx';
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer->save('php://output');
    die();
}



add_action('admin_post_export_to_excel', 'export_to_excel'); 
