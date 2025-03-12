<?php
if (!defined('ABSPATH')) {
    exit;
}

function batch_id_display_front_page() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        echo '<p>' . __('You must be logged in to view your Batch IDs.', 'batch-id') . '</p>';
        return;
    }

    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'batch_ids';
    $table_barcodes = $wpdb->prefix . 'barcodes';

    // Load Batch IDs linked to the user
    $batch_ids = $wpdb->get_results($wpdb->prepare(
        "SELECT batch_id FROM $table_batch_ids WHERE customer_id = %d ORDER BY id DESC",
        $user_id
    ));

    $batch_data = [];
    foreach ($batch_ids as $batch) {
        $barcodes = $wpdb->get_results($wpdb->prepare(
            "SELECT barcode, is_used FROM $table_barcodes WHERE batch_id = %s ORDER BY barcode ASC",
            $batch->batch_id
        ));

        // Check if all barcodes are used
        $all_used = array_reduce($barcodes, function($carry, $item) {
            return $carry && ($item->is_used == 1);
        }, true);

        // If all barcodes are used, don't add it
        if (!$all_used) {
            $batch_data[] = [
                'batch_id' => $batch->batch_id,
                'barcodes' => $barcodes
            ];
        }
    }

    require plugin_dir_path(__FILE__) . '../templates/front-page.php';
}
