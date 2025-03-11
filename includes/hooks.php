<?php
if (!defined('ABSPATH')) {
    exit;
}

function batch_id_enqueue_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_batch-id-settings') {
        return;
    }

    $plugin_url = plugin_dir_url(__FILE__);

    // Load jQuery UI Autocomplete
    wp_enqueue_script('jquery-ui-autocomplete');

    // Load our script JS
    wp_enqueue_script('batch-id-admin-js', $plugin_url . '../assets/js/admin.js', ['jquery', 'jquery-ui-autocomplete'], false, true);
}

add_action('admin_enqueue_scripts', 'batch_id_enqueue_admin_scripts');

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
