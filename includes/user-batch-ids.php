<?php
if (!defined('ABSPATH')) exit;

/**
 * Fetch batch IDs for a given user that still have unused barcodes.
 *
 * @param int $user_id The ID of the user.
 * @global wpdb $wpdb WordPress database global object.
 * @return array List of batch IDs with unused barcodes.
 */

function batch_id_get_user_batches($user_id) {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'batch_ids';
    $table_barcodes = $wpdb->prefix . 'barcodes';

    // Retrieve all Batch IDs assigned to this user
    $all_batches = $wpdb->get_results($wpdb->prepare(
        "SELECT batch_id FROM $table_batch_ids WHERE customer_id = %d ORDER BY created_at DESC",
        $user_id
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

    return $batch_ids;
}

/**
 * Display batch IDs on the back-office user profile page.
 *
 * @param WP_User $user The current user object.
 */
function batch_id_display_user_batches($user) {
    if (!current_user_can('edit_users')) {
        return;
    }

    $batch_ids = batch_id_get_user_batches($user->ID);

    require plugin_dir_path(__FILE__) . '../templates/user-batch-ids.php';
}
