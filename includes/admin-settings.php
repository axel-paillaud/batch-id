<?php
if (!defined('ABSPATH')) {
    exit;
}

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

function batch_id_admin_page() {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'batch_ids';
    $table_barcodes = $wpdb->prefix . 'barcodes';
    $response = [
        'success' => true,
        'message' => ''
    ];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['batch_id'])) {
        $batch_id = sanitize_text_field($_POST['batch_id']);
        $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : NULL;

        // Check if the Batch ID exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_batch_ids WHERE batch_id = %s", $batch_id));

        if (!preg_match('/^\d{9}$/', $batch_id)) {
            $response['message'] = '<div class="notice notice-error"><p>' . __('Invalid Batch ID format, should contain 9 digits.', 'batch-id') . '</p></div>';
            $response['success'] = false;
        }

        if ($exists) {
            $response['message'] = '<div class="notice notice-error"><p>' . __('This Batch ID already exists.', 'batch-id') . '</p></div>';
            $response['success'] = false;
        }

        if ($response['success']) {
            // Insert the Batch ID
            $wpdb->insert($table_batch_ids, [
            'batch_id' => $batch_id,
            'customer_id' => $customer_id
            ]);

            // Generate the 10 barcodes
            for ($i = 0; $i <= 9; $i++) {
                $barcode = $batch_id . $i;
                $wpdb->insert($table_barcodes, [
                'barcode' => $barcode,
                'batch_id' => $batch_id,
                'is_used' => 0,
                'customer_id' => $customer_id
                ]);
            }

            $response['message'] = '<div class="notice notice-success"><p>' . __('Batch ID and barcodes generated successfully!', 'batch-id') . '</p></div>';
        }
    }

    // Pagination
    $batch_per_page = 13;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $batch_per_page;

    $total_batches = $wpdb->get_var("SELECT COUNT(*) FROM $table_batch_ids");

    // Calculate the total number of pages
    $total_pages = ceil($total_batches / $batch_per_page);

    // Retrieve the Batch ID for the current page
    $batch_ids = $wpdb->get_results($wpdb->prepare(
        "SELECT batch_id, customer_id FROM $table_batch_ids ORDER BY id DESC LIMIT %d OFFSET %d",
        $batch_per_page,
        $offset
    ));

    // Prefill batch ID field with the next available ID
    // Ex: 2503 for March 2025
    $batch_prefix = date('y') . date('m');

    $last_batch_id = $wpdb->get_var("SELECT batch_id FROM {$table_batch_ids} ORDER BY id DESC LIMIT 1");

    if ($last_batch_id && preg_match('/^(\d{4})(\d{5})$/', $last_batch_id, $matches)) {
        // Year + month (ex: 2503)
        $last_prefix = $matches[1];
        // Numeric part (ex: 00034)
        $last_number = intval($matches[2]);

        // Check if the last Batch ID matches the current month/year
        if ($last_prefix === $batch_prefix) {
            // Increment and keep 5 digits
            $next_number = str_pad($last_number + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Restart at 00000
            $next_number = '00000';
        }
    } else {
        // No data in the database, start at 00000
        $next_number = '00000';
    }

    $next_batch_id = $batch_prefix . $next_number;

    require plugin_dir_path(__FILE__) . '../templates/admin-page.php';
}
