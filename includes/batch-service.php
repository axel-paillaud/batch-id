<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Process Batch ID creation.
 *
 * @global wpdb $wpdb WordPress database global object.
 * @param string $batch_id The Batch ID to create.
 * @param int $type_id The batch type ID (default is 1).
 * @param int|null $customer_id The customer ID (optional).
 * @return array Response message and success status.
 */
function batch_id_create($batch_id, $type_id = 1, $customer_id = null, $quantity = 1) {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'smart_batch_ids';
    $table_barcodes = $wpdb->prefix . 'smart_barcodes';
    $table_batch_types = $wpdb->prefix . 'smart_batch_types';

    // Validate Batch ID format
    if (!preg_match('/^\d{9}$/', $batch_id)) {
        return [
            'success' => false,
            'message' => __('Invalid Batch ID format, should contain 9 digits.', 'batch-id')
        ];
    }

    // Validate quantity
    if ($quantity < 1) {
        return [
            'success' => false,
            'message' => __('Quantity must be at least 1.', 'batch-id')
        ];
    }

    // Get batch type
    $batch_type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_batch_types WHERE id = %d", $type_id));

    if (!$batch_type) {
        return [
            'success' => false,
            'message' => __('Invalid batch type selected.', 'batch-id')
        ];
    }

    $batch_prefix = substr($batch_id, 0, 4); // ex: 2503
    $numeric_part = (int)substr($batch_id, 4); // ex: 00034

    // Already existing batch IDs
    $existing_batch_ids = $wpdb->get_col("SELECT batch_id FROM $table_batch_ids");

    $created_batches = [];

    for ($i = 0; $i < $quantity; $i++) {
        $new_batch_id = $batch_type->prefix . $batch_prefix . str_pad($numeric_part + $i, 5, '0', STR_PAD_LEFT);

        // Check if the Batch ID already exists
        if (in_array($new_batch_id, $existing_batch_ids)) {
            continue;
        }

        // Insert the Batch ID
        $wpdb->insert($table_batch_ids, ['batch_id' => $new_batch_id, 'type_id' => $batch_type->id, 'customer_id' => $customer_id]);

        // Generate 10 barcodes linked to this Batch ID
        $barcodes = [];
        for ($j = 0; $j <= 9; $j++) {
            $barcode = $new_batch_id . $j;
            $wpdb->insert($table_barcodes, [
                'barcode' => $barcode,
                'batch_id' => $new_batch_id,
                'is_used' => 0,
                'customer_id' => $customer_id
            ]);
        }

        $created_batches[] = $new_batch_id;
    }

    if (empty($created_batches)) {
        return [
            'success' => false,
            'message' => __('No new Batch ID created. Some already existed.', 'batch-id')
        ];
    }

    return [
        'success' => true,
        'message' => sprintf(__('Successfully created %d Batch IDs.', 'batch-id'), count($created_batches))
    ];
}

/**
 * Process Batch ID deletion.
 *
 * @global wpdb $wpdb WordPress database global object.
 * @param string $batch_id The Batch ID to delete.
 * @return array Response message and success status.
 */
function batch_id_delete($batch_id) {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'smart_batch_ids';

    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_batch_ids WHERE batch_id = %s", $batch_id));

    if ($exists) {
        $wpdb->delete($table_batch_ids, ['batch_id' => $batch_id]);
        return [
            'success' => true,
            'message' => __('Batch ID deleted successfully.', 'batch-id')
        ];
    }

    return [
        'success' => false,
        'message' => __('This Batch ID does not exist.', 'batch-id')
    ];
}
