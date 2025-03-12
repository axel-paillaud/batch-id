<?php
if (!defined('ABSPATH')) {
    exit;
}

function batch_id_enqueue_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_batch-id-settings') {
        return;
    }

    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_script('jquery-ui-autocomplete');

    wp_enqueue_script('batch-id-admin-js', $plugin_url . '../assets/js/admin.js', ['jquery', 'jquery-ui-autocomplete'], false, true);

    wp_enqueue_style('batch-id-admin-css', $plugin_url . '../assets/css/admin.css');
}

add_action('admin_enqueue_scripts', 'batch_id_enqueue_admin_scripts');

function batch_id_enqueue_front_styles() {
    wp_enqueue_style('batch-id-front-css', plugin_dir_url(__FILE__) . '../assets/css/front.css');
}
add_action('wp_enqueue_scripts', 'batch_id_enqueue_front_styles');

// Add a new "Batch ID" tab in My Account
add_filter('woocommerce_account_menu_items', 'batch_id_add_account_tab', 40);
function batch_id_add_account_tab($items) {
    $items['batch-id'] = __('Batch ID', 'batch-id'); // Add the tab
    return $items;
}

// Declare the "batch-id" endpoint for WooCommerce
add_action('init', function() {
    add_rewrite_endpoint('batch-id', EP_ROOT | EP_PAGES);
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

function batch_id_delete() {
    global $wpdb;
    $batch_id = isset($_POST['batch_id']) ? sanitize_text_field($_POST['batch_id']) : '';

    if (empty($batch_id)) {
        wp_send_json_error("Batch ID invalide.");
    }

    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}batch_ids WHERE batch_id = %s", $batch_id));

    if (!$exists) {
        wp_send_json_error("Le Batch ID n'existe pas.");
    }

    // Delete the Batch ID (the barcodes are deleted in cascade)
    $wpdb->delete("{$wpdb->prefix}batch_ids", ['batch_id' => $batch_id]);

    wp_send_json_success("Batch ID supprimé.");
}

add_action('wp_ajax_batch_id_delete', 'batch_id_delete');
