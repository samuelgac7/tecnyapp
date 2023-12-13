<?php
/**
 * Plugin Name: Tecnycon dashboard
 * Description: dashboard para Tecnycon, contiene listado contratistas, plantillas empresariales
 * Author: Dario
 * Version: 1.0
 */

 // Include the Composer autoloader
require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Include the necessary files
include_once plugin_dir_path(__FILE__) . 'custom_tables.php'; 
include_once plugin_dir_path(__FILE__) . 'files.php'; 
include_once plugin_dir_path(__FILE__) . 'unitprices.php'; 
include_once plugin_dir_path(__FILE__) . 'recursos.php'; 

register_activation_hook(__FILE__, 'create_tables_activation_hook');
register_activation_hook(__FILE__, 'create_files_table_activation_hook');

//removes the wp bar
add_filter('show_admin_bar', '__return_false');

// get user info


// Enqueue dashboard-specific scripts and styles
function enqueue_dashboard_resources() {

    // Fonts
    if ( is_page( 'dashboard' ) ) {

    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap', array(), null);

    // Icons and other styles
    wp_enqueue_style('boxicons', plugin_dir_url(__FILE__) . 'assets/vendor/fonts/boxicons.css', array(), null);
    wp_enqueue_style('core-css', plugin_dir_url(__FILE__) . 'assets/vendor/css/rtl/core.css', array(), null);
    wp_enqueue_style('theme-default-css', plugin_dir_url(__FILE__) . 'assets/vendor/css/rtl/theme-default.css', array(), null);
    wp_enqueue_style('demo-css', plugin_dir_url(__FILE__) . 'assets/css/demo.css', array(), null);
    wp_enqueue_style('perfect-scrollbar-css', plugin_dir_url(__FILE__) . 'assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css', array(), null);

    // Scripts
    wp_enqueue_script('helpers-js', plugin_dir_url(__FILE__) . 'assets/vendor/js/helpers.js', array(), null, true);
    //swp_enqueue_script('template-customizer-js', plugin_dir_url(__FILE__) . 'assets/vendor/js/template-customizer.js', array(), null, true);
    wp_enqueue_script('config-js', plugin_dir_url(__FILE__) . 'assets/js/config.js', array('helpers-js'), null, true);
    wp_enqueue_script('perfect-scrollbar', plugin_dir_url(__FILE__) . 'assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', array(), null, true);
    wp_enqueue_script('hammer', plugin_dir_url(__FILE__) . 'assets/vendor/libs/hammer/hammer.js', array(), null, true);
    wp_enqueue_script('menu-js', plugin_dir_url(__FILE__) . 'assets/vendor/js/menu.js', array(), null, true);
    wp_enqueue_script('main-js', plugin_dir_url(__FILE__) . 'assets/js/main.js', array(), null, true);
    wp_enqueue_script('bootstrap-js', plugin_dir_url(__FILE__) . 'assets/vendor/js/bootstrap.js', array(), null, true);
    wp_enqueue_script('dropdown-hover', plugin_dir_url(__FILE__) . 'assets/vendor/js/dropdown-hover.js', array(), null, true);



    // Your existing enqueued scripts and styles
    wp_enqueue_script('dashboard-ajax', plugin_dir_url(__FILE__) . 'assets/js/dashboard.js', array('jquery'), null, true);
    wp_enqueue_style('dashboard-styles', plugin_dir_url(__FILE__) . 'assets/css/dashboard.css');

    $localization_data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dashboard_nonce')
    );

    wp_localize_script('dashboard-ajax', 'ajax_params', $localization_data);
    }
}

add_action('wp_enqueue_scripts', 'enqueue_dashboard_resources');

function custom_menu_item($items, $args) {
  if (is_user_logged_in()) {
      foreach ($items as $key => $item) {
          if ($item->url == home_url('/login/')) {
              $items[$key]->url = home_url('/dashboard/');
          }
      }
  }
  return $items;
}
add_filter('wp_get_nav_menu_items', 'custom_menu_item', 10, 2);

function restrict_dashboard_access() {
  if (is_page('dashboard') && !is_user_logged_in()) {
      wp_redirect(home_url('/login/'));
      exit;
  }
}
add_action('template_redirect', 'restrict_dashboard_access');



function load_content() {
  global $wpdb;

  $content_type = $_POST['content_type'];

  $content_handlers = array(
      'proveedores' => function() {
          echo do_shortcode('[filter_especialidad]');
          echo do_shortcode('[proveedor_form]');
          echo do_shortcode('[display_proveedores]');
      },
      'subcontratistas' => function() {
          echo do_shortcode('[filter_especialidad]');
          echo do_shortcode('[subcontratista_form]');
          echo do_shortcode('[display_subcontratistas]');
      },
      'plantillas' => function() {
          display_file_upload_form();
          display_uploaded_files();
      },
      'unitprices' => function() {
          display_unit_prices();
      },
      'materials' => function() {
        echo do_shortcode('[resource_form type="material"]');
        display_resource("material");
          //
      },
      'labors' => function() {
        echo do_shortcode('[resource_form type="labor"]');
        display_resource("labor");
          //echo do_shortcode('[display_labors]');
      },
      'equipment' => function() {
        echo do_shortcode('[resource_form type="equipment"]');
        display_resource("equipment");
          //echo do_shortcode('[display_equipment]');
      },
      'Others' => function() {
        echo do_shortcode('[resource_form type="others"]');
        display_resource("others");
        //echo do_shortcode('[display_equipment]');
    }
  );

  if (isset($content_handlers[$content_type])) {
      $content_handlers[$content_type]();
  } else {
      // Handle unknown content type if necessary
      echo "Unknown content type: " . esc_html($content_type);
  }

  die();
}

add_action('wp_ajax_load_content', 'load_content'); 

function tecnycon_dashboard_shortcode() {
    global $current_user;
    wp_get_current_user();
    $account_page_link = home_url( '/account/' );

    ob_start();

    // The HTML structure for the dashboard
    ?>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="#" class="app-brand-link">
              <?php  
              echo '<img class= "tecnycon-logo" src="' . plugin_dir_url(__FILE__) . 'assets/img/branding/Tecnycon_Tranparente.png" alt="Tecnycon" />';
              ?>

            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="bx menu-toggle-icon d-none d-xl-block fs-4 align-middle"></i>
              <i class="bx bx-x d-block d-xl-none bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-divider mt-0"></div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            <!-- Page -->
            <li class="menu-item active">
              <a href="#" class="menu-link" data-content-type="">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Page 1">Dashboard</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="#" class="menu-link load-content" data-content-type="subcontratistas">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div data-i18n="Page 2">Listado de Subcontratistas</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="#" class="menu-link load-content" data-content-type="proveedores">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div data-i18n="Page 3">Listado de Proveedores</div>
              </a>
            </li >
            <li class="menu-item">
            <a href="#" class="menu-link load-content" data-content-type="plantillas">
              <i class="menu-icon tf-icons bx bx-detail"></i>
              <div data-i18n="Page 4">Plantillas Tecnycon</div>
            </a>
          </li>
          <li class="menu-item">
          <a href="#" class="menu-link load-content" data-content-type="unitprices">
            <i class="menu-icon tf-icons bx bx-detail"></i>
            <div data-i18n="Page 5">Listado de P.U</div>
          </a>
        </li>
        <li class="menu-item">
            <a href="#" class="menu-link" data-toggle="collapse" data-target="#resourcesSubmenu" aria-expanded="false">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div data-i18n="Resources">Resources</div>
            </a>
            <ul class="collapse list-unstyled" id="resourcesSubmenu">
                <li>
                    <a href="#" class="menu-link load-content" data-content-type="materials">Materiales</a>
                </li>
                <li>
                    <a href="#" class="menu-link load-content" data-content-type="labors">Mano de obra</a>
                </li>
                <li>
                    <a href="#" class="menu-link load-content" data-content-type="equipment">Equipos</a>
                </li>
                <li>
                    <a href="#" class="menu-link load-content" data-content-type="Others">Otros</a>
                </li>
            </ul>
        </li>

          </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
            <div class="container-fluid">
              <div class="navbar-nav align-items-center">
                <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                  <i class="bx bx-sm"></i>
                </a>
              </div>

              <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                  <i class="bx bx-menu bx-sm"></i>
                </a>
              </div>

              <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                <ul class="navbar-nav flex-row align-items-center ms-auto">
                  <!-- User -->
                  <?php
                  echo 'Hola ' . esc_html( $current_user->display_name ) . '!';
                  ?>
                  <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                                <?php echo get_avatar( $current_user->ID, 40 ); ?>
                            </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" href="#">
                          <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                                <?php echo get_avatar( $current_user->ID, 40 ); ?>
                            </div>
                            </div>
                            <div class="flex-grow-1">
                              <span class="fw-semibold d-block lh-1"> <?php echo esc_html( $current_user->display_name );?>.</span>
                              <small>Admin</small>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                      </li>
                      <li>
                        <a class="dropdown-item" href="<?php echo esc_url( $account_page_link ); ?>">
                          <i class="bx bx-user me-2"></i>
                          <span class="align-middle">My Profile</span>
                        </a>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                      </li>
                      <li>
                        <a class="dropdown-item" href="<?php echo wp_logout_url(); ?>">
                          <i class="bx bx-power-off me-2"></i>
                          <span class="align-middle">Log Out</span>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <!--/ User -->
                </ul>
              </div>
            </div>
          </nav>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
                      <div id="myToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
              <div class="toast-header">
                  <strong class="mr-auto">Notification</strong>
                  <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="toast-body">
                  <!-- The message will be inserted here by the showToast function -->
              </div>
          </div>

            <div id ="mainContainer" class="container-xxl flex-grow-1 container-p-y">
              <h4 class="py-3 breadcrumb-wrapper mb-4">Page 1</h4>
              <p>
                Sample page.<br />For more layout options refer
                <a
                  href="https://demos.pixinvent.com/frest-html-admin-template/documentation//layouts.html"
                  target="_blank"
                  class="fw-bold"
                  >Layout docs</a
                >.
              </p>
            </div>
            <!-- / Content -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->




    <!-- Page JS -->
  <?php
    return ob_get_clean();
}
add_shortcode('tecnycon_dashboard', 'tecnycon_dashboard_shortcode');
