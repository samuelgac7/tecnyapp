Plugin para crear tablas en db y usarlas

to enqueue you have to have this on functions.php of your theme

function enqueue_custom_dashboard_scripts() {
    if (is_page_template('dashboard-page.php')) {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    
    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');
    
    // Enqueue Popper.js
    wp_enqueue_script('popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js', array(), null, true); 

    // Enqueue Bootstrap JS - Now, it depends on 'jquery' AND 'popper'
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array(), '', true);

	// Remove registered jQuery version.
	wp_deregister_script('jquery');

	// Enqueue the desired jQuery version from the CDN.
	wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', array(), '3.6.0', true);
	
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_dashboard_scripts');