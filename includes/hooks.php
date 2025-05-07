<?php
if (!defined('ABSPATH')) {
    exit;
}

function batch_id_enqueue_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_batch-id-settings' && $hook !== 'user-edit.php' && $hook !== 'profile.php') {
        return;
    }

    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_script('jquery-ui-autocomplete');

    wp_enqueue_script('batch-id-admin-js', $plugin_url . '../assets/js/admin.js', ['jquery', 'jquery-ui-autocomplete'], false, true);

}
add_action('admin_enqueue_scripts', 'batch_id_enqueue_admin_scripts');

function batch_id_enqueue_admin_styles() {
    wp_enqueue_style('batch-id-admin-css', plugin_dir_url(__FILE__) . '../assets/css/admin.css');
}
add_action('admin_enqueue_scripts', 'batch_id_enqueue_admin_styles');

function batch_id_enqueue_front_styles() {
    $css_file = plugin_dir_path(__FILE__) . '../assets/css/front.css';
    $css_url = plugin_dir_url(__FILE__) . '../assets/css/front.css';

    // Vérifie si le fichier CSS existe et récupère sa date de modification
    $version = file_exists($css_file) ? filemtime($css_file) : time();

    wp_enqueue_style('batch-id-front-css', $css_url, [], $version);
}
add_action('wp_enqueue_scripts', 'batch_id_enqueue_front_styles');

function batch_id_enqueue_front_scripts() {
    if (is_account_page() && is_wc_endpoint_url('batch-id')) {
        $js_file = plugin_dir_path(__FILE__) . '../assets/js/front.js';
        $js_url = plugin_dir_url(__FILE__) . '../assets/js/front.js';

        $version = file_exists($js_file) ? filemtime($js_file) : time();

        wp_enqueue_script(
            'batch-id-front-js',
            $js_url,
            [],
            $version,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'batch_id_enqueue_front_scripts');

// Add admin menu page
function batch_id_add_admin_menu() {
    add_menu_page(
        __('Batch ID Admin Page', 'batch-id'),
        __('Batch ID', 'batch-id'),
        'manage_options',
        'batch-id-settings',
        'batch_id_admin_page',
        'dashicons-media-spreadsheet',
        20
    );
}
add_action('admin_menu', 'batch_id_add_admin_menu');

// Add a new "Batch ID" tab in My Account
add_filter('woocommerce_account_menu_items', 'batch_id_add_account_tab', 40);
function batch_id_add_account_tab($items) {
    $items['batch-id'] = __('Batch ID', 'batch-id');
    return $items;
}

// Declare the "batch-id" endpoint for WooCommerce
add_action('init', function() {
    add_rewrite_endpoint('batch-id', EP_ROOT | EP_PAGES);
});

add_filter('woocommerce_get_query_vars', function($vars) {
    $vars['batch-id'] = 'batch-id';
    return $vars;
});

// Handle the display of content on the "Batch ID" tab
add_action('woocommerce_account_batch-id_endpoint', 'batch_id_display_front_page');

function batch_id_search_customers() {
    global $wpdb;
    $search_term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';

    // Retrieve customers that match the search term
    $customers = $wpdb->get_results($wpdb->prepare("
        SELECT ID, display_name
        FROM {$wpdb->prefix}users
        WHERE display_name LIKE %s
        ORDER BY display_name ASC
        LIMIT 10", '%' . $wpdb->esc_like($search_term) . '%'
    ));

    // Transform into JSON format for jQuery UI
    $results = [];
    foreach ($customers as $customer) {
        $results[] = [
            'value' => $customer->ID,
            'label' => $customer->display_name
        ];
    }

    wp_send_json($results);
}

add_action('wp_ajax_batch_id_search_customers', 'batch_id_search_customers');

// Add batch ID on back-office user page
add_action('show_user_profile', 'batch_id_display_user_batches');
add_action('edit_user_profile', 'batch_id_display_user_batches');
