<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Claim a Batch ID for the current user.
 *
 * @param int $batch_id The Batch ID to claim.
 * @return array An array with 'success' and 'message' keys.
 */
function batch_id_claim_batch(int $batch_id) {
    global $wpdb;
    $user_id = get_current_user_id();
    $table_batch_ids = $wpdb->prefix . 'smart_batch_ids';
    $table_barcodes = $wpdb->prefix . 'smart_barcodes';

    $batch_id = isset($_POST['claim_batch_id']) ? trim($_POST['claim_batch_id']) : '';

    // Check if the Batch ID is valid (10 digits)
    if (!preg_match('/^\d{10}$/', $batch_id)) {
        return ['success' => false, 'message' => __('Invalid Batch ID format. Must be exactly 10 digits.', 'batch-id')];
    }

    // Check if the Batch ID exists in the database
    $batch = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_batch_ids WHERE batch_id = %s", $batch_id));

    if (!$batch) {
        return ['success' => false, 'message' => __('This Batch ID does not exist.', 'batch-id')];
    }

    // Check if the Batch ID is already assigned to a user
    if (!is_null($batch->customer_id)) {
        return ['success' => false, 'message' => __('This Batch ID is already assigned to a user.', 'batch-id')];
    }

    // Check if the Batch ID is of type "float" (prefix = 2)
    if (!str_starts_with($batch_id, '2')) {
        return ['success' => false, 'message' => __("Only `float` Batch IDs can be claimed.", 'batch-id')];
    }

    // Assign the Batch ID to the current user
    $wpdb->update($table_batch_ids, ['customer_id' => $user_id], ['batch_id' => $batch_id]);

    // Assign all associated barcodes to the user
    $wpdb->update($table_barcodes, ['customer_id' => $user_id], ['batch_id' => $batch_id]);

    return ['success' => true, 'message' => __('Batch ID successfully claimed!', 'batch-id')];
}

function batch_id_handle_claim_request() {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['claim_batch_id']) && is_user_logged_in()) {
        $batch_id = sanitize_text_field(trim($_POST['claim_batch_id']));
        $response = batch_id_claim_batch($batch_id);

        // Redirect after submission with a message in query string
        wp_redirect(add_query_arg([
            'batch_status' => $response['success'] ? 'success' : 'error',
            'batch_message' => urlencode($response['message'])
        ], wp_get_referer()));
        exit;
    }
}
add_action('admin_post_batch_id_claim', 'batch_id_handle_claim_request');

function batch_id_display_front_page() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        echo '<p>' . __('You must be logged in to view your Batch IDs.', 'batch-id') . '</p>';
        return;
    }

    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'smart_batch_ids';
    $table_barcodes = $wpdb->prefix . 'smart_barcodes';
    $table_batch_types = $wpdb->prefix . 'smart_batch_types';

    // Load batch types for CSS mapping
    $batch_types = $wpdb->get_results("SELECT * FROM $table_batch_types", OBJECT_K);
    // $batch_types_classes = [
    //     1 => 'batch-type-prepayed',
    //     2 => 'batch-type-float'
    // ];

    // Load Batch IDs linked to the user
    $batch_ids = $wpdb->get_results($wpdb->prepare(
        "SELECT batch_id, type_id FROM $table_batch_ids WHERE customer_id = %d ORDER BY id DESC",
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
                'barcodes' => $barcodes,
                'type_lang' => $batch_types[$batch->type_id]->lang ?? 'Unknown',
                'type_name' => $batch_types[$batch->type_id]->name ?? 'unknown',
                'color' => $batch_types[$batch->type_id]->color ?? '#cccccc',
            ];
        }
    }

    $total_batches = count($batch_data);

    // Extract batch status & message from query string
    $batch_status = isset($_GET['batch_status']) ? sanitize_text_field($_GET['batch_status']) : '';
    $batch_message = isset($_GET['batch_message']) ? stripslashes(urldecode($_GET['batch_message'])) : '';

    require plugin_dir_path(__FILE__) . '../templates/front-page.php';
}
