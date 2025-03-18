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
    $items['batch-id'] = __('Batch ID', 'batch-id');
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

// Add batch ID on back-office user page
function batch_id_display_user_batches($user) {
    if (!current_user_can('edit_users')) {
        return;
    }

    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'batch_ids';
    $table_barcodes = $wpdb->prefix . 'barcodes';

    $all_batches = $wpdb->get_results($wpdb->prepare(
        "SELECT batch_id FROM $table_batch_ids WHERE customer_id = %d ORDER BY created_at DESC",
        $user->ID
    ));

    $batch_ids = [];

    foreach ($all_batches as $batch) {
        // Check if at least one barcode is not used
        $unused_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_barcodes WHERE batch_id = %s AND is_used = 0",
            $batch->batch_id
        ));

        if ($unused_count > 0) {
            $batch_ids[] = $batch;
        }
    }

    require plugin_dir_path(__FILE__) . '../templates/user-batch-ids.php';
}
add_action('show_user_profile', 'batch_id_display_user_batches');
add_action('edit_user_profile', 'batch_id_display_user_batches');
