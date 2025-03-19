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
    $table_batch_ids = $wpdb->prefix . 'batch_ids';

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
    if (!$batch->customer_id === null || (int) $batch->customer_id !== 0) {
        return ['success' => false, 'message' => __('This Batch ID is already assigned to a user.', 'batch-id')];
    }

    // Check if the Batch ID is of type "float" (prefix = 2)
    if (!str_starts_with($batch_id, '2')) {
        return ['success' => false, 'message' => __('Only "float" Batch IDs can be claimed.', 'batch-id')];
    }

    // Assign the Batch ID to the current user
    $wpdb->update($table_batch_ids, ['customer_id' => $user_id], ['batch_id' => $batch_id]);

    return ['success' => true, 'message' => __('Batch ID successfully claimed!', 'batch-id')];

    return null;
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

    $total_batches = count($batch_data);

    require plugin_dir_path(__FILE__) . '../templates/front-page.php';
}
