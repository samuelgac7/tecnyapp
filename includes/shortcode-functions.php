<?php
//display subcontratistas
function display_subcontratistas_shortcode() {
    ob_start();
    display_data('subcontratistas');
    return ob_get_clean();
}
add_shortcode('display_subcontratistas', 'display_subcontratistas_shortcode');
//display proveedores
function display_proveedores_shortcode() {
    ob_start();
    display_data('proveedores');
    return ob_get_clean();
}
add_shortcode('display_proveedores', 'display_proveedores_shortcode');

function subcontratista_form_shortcode() {
    ob_start();
    ?>
    <form id="myform" method="post" action="" class="form-inline">
        <input type="hidden" name="form_type" value="subcontratistas">

        <div class="form-group mx-sm-3 mb-2">
            <label for="Nombre" class="sr-only">Nombre:</label>
            <input type="text" class="form-control" name="Nombre" placeholder="Nombre" required>
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Empresa" class="sr-only">Empresa:</label>
            <input type="text" class="form-control" name="Empresa" placeholder="Empresa" required>
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Especialidad" class="sr-only">Especialidad:</label>
            <input type="text" class="form-control" name="Especialidad" placeholder="Especialidad" required>
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Telefono" class="sr-only">Telefono:</label>
            <input type="text" class="form-control" name="Telefono" placeholder="Telefono">
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Email" class="sr-only">Email:</label>
            <input type="email" class="form-control" name="Email" placeholder="Email">
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Comentarios" class="sr-only">Comentarios:</label>
            <textarea class="form-control" name="Comentarios" placeholder="Comentarios"></textarea>
        </div>

        <button type="submit" class="btn btn-primary mb-2">
            <i class="fas fa-plus"></i>
        </button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('subcontratista_form', 'subcontratista_form_shortcode');

function proveedor_form_shortcode() {
    ob_start();
    ?>
    <form id="myform" method="post" action="" class="form-inline">
        <input type="hidden" name="form_type" value="proveedores">

        <div class="form-group mx-sm-3 mb-2">
            <label for="Nombre" class="sr-only">Nombre:</label>
            <input type="text" class="form-control" name="Nombre" placeholder="Nombre" required>
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Empresa" class="sr-only">Empresa:</label>
            <input type="text" class="form-control" name="Empresa" placeholder="Empresa" required>
        </div>
        
        <div class="form-group mx-sm-3 mb-2">
            <label for="Especialidad" class="sr-only">Especialidad:</label>
            <input type="text" class="form-control" name="Especialidad" placeholder="Especialidad" required>
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Telefono" class="sr-only">Telefono:</label>
            <input type="text" class="form-control" name="Telefono" placeholder="Telefono">
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Email" class="sr-only">Email:</label>
            <input type="email" class="form-control" name="Email" placeholder="Email">
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="Comentarios" class="sr-only">Comentarios:</label>
            <textarea class="form-control" name="Comentarios" placeholder="Comentarios"></textarea>
        </div>

        <button type="submit" class="btn btn-primary mb-2">
            <i class="fas fa-plus"></i>
        </button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('proveedor_form', 'proveedor_form_shortcode');

function filter_especialidad_shortcode() {
    ob_start(); // Start output buffering
    ?>
    
    <div class="form-group">
        <input type="text" class="form-control" id="filterEspecialidad" placeholder="Filtrar por Especialidad">
    </div>
    
    <?php
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('filter_especialidad', 'filter_especialidad_shortcode');

function resource_form_shortcode($atts) {
        // Extract the attributes
        $atts = shortcode_atts(array(
            'type' => 'default' // default value if not provided
        ), $atts, 'resource_form');
    
        $resource_type = $atts['type'];
    
        ob_start();
        ?>
        <form id="resourceForm_<?php echo $resource_type; ?>" data-resource-type="<?php echo $resource_type; ?>" method="post" action="" class="form-inline">
        <input type="hidden" name="form_type" value="<?php echo $resource_type; ?>">


        <div class="form-group mx-sm-3 mb-2">
            <label for="name" class="sr-only">Nombre:</label>
            <input type="text" class="form-control" name="name" placeholder="Nombre" required>
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="unit" class="sr-only">Unidad:</label>
            <input type="text" class="form-control" name="unit" placeholder="Unidad" required>
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="price" class="sr-only">Precio:</label>
            <input type="number" step="0.01" class="form-control" name="price" placeholder="Precio" required>
        </div>

        <div class="form-group mx-sm-3 mb-2">
            <label for="source" class="sr-only">Fuente:</label>
            <input type="text" class="form-control" name="source" placeholder="Fuente">
        </div>

        <button type="submit" class="btn btn-primary mb-2">
            <i class="fas fa-plus"></i>
        </button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('resource_form', 'resource_form_shortcode');

