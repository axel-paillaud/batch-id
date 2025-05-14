<?php
if (!defined('ABSPATH')) {
    exit;
}

function batch_id_admin_page() {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'smart_batch_ids';
    $table_batch_types = $wpdb->prefix . 'smart_batch_types';

    $response = ['success' => true, 'message' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['batch_id'])) {
            $response = batch_id_handle_create($_POST);
        } elseif (isset($_POST['delete_batch_id'])) {
            $response = batch_id_handle_delete($_POST);
        } elseif (isset($_POST['add_batch_type'])) {
            $response = batch_type_handle_create($_POST);
        } elseif (isset($_POST['delete_batch_type_id'])) {
            $response = batch_type_handle_delete($_POST);
        }
    }

    $types = $wpdb->get_results("SELECT id, name, lang, prefix, color FROM $table_batch_types ORDER BY prefix ASC");

    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $batch_info = batch_id_get_admin_batches($current_page);

    $last_batch_id = $wpdb->get_var("SELECT batch_id FROM {$table_batch_ids} ORDER BY id DESC LIMIT 1");
    $next_batch_id = batch_id_generate_next_id($last_batch_id);

    require plugin_dir_path(__FILE__) . '../templates/admin-page.php';
}

function batch_id_handle_create($data) {
    $batch_id = trim($data['batch_id']);
    $type_id = intval($data['type_id']);
    $customer_id = isset($data['customer_id']) && $data['customer_id'] !== '' ? intval($data['customer_id']) : null;
    $quantity = isset($data['quantity']) ? max(1, intval($data['quantity'])) : 1;

    return batch_id_create($batch_id, $type_id, $customer_id, $quantity);
}

function batch_id_handle_delete($data) {
    $batch_id = sanitize_text_field($data['delete_batch_id']);
    return batch_id_delete($batch_id);
}

function batch_type_handle_create($data) {
    global $wpdb;
    $table_batch_types = $wpdb->prefix . 'smart_batch_types';

    $name = sanitize_title($data['batch_name']);
    $lang = sanitize_text_field($data['batch_lang']);
    $prefix = intval($data['batch_prefix']);
    $color = sanitize_hex_color($data['batch_color']);

    $name_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_batch_types WHERE name = %s", $name));
    if ($name_exists) {
        return [
            'success' => false,
            'message' => __('This slug is already in use.', 'batch-id'),
        ];
    }

    $wpdb->insert($table_batch_types, [
        'name'   => $name,
        'lang'   => $lang,
        'prefix' => $prefix,
        'color'  => $color ?: null,
    ]);

    return [
        'success' => true,
        'message' => __('New batch type added.', 'batch-id'),
    ];
}

function batch_type_handle_delete($data) {
    global $wpdb;
    $table_batch_types = $wpdb->prefix . 'smart_batch_types';

    $type_id = intval($data['delete_batch_type_id']);
    $deleted = $wpdb->delete($table_batch_types, ['id' => $type_id]);

    return [
        'success' => (bool) $deleted,
        'message' => $deleted
            ? __('Batch type and all related Batch IDs deleted.', 'batch-id')
            : __('Deletion failed.', 'batch-id')
    ];
}

function batch_id_generate_next_id($last_batch_id = null) {
    $batch_prefix = date('y') . date('m');

    if ($last_batch_id && preg_match('/^\d(\d{4})(\d{5})$/', $last_batch_id, $matches)) {
        $last_prefix = $matches[1];
        $last_number = intval($matches[2]);
        $next_number = ($last_prefix === $batch_prefix)
            ? str_pad($last_number + 1, 5, '0', STR_PAD_LEFT)
            : '00000';
    } else {
        $next_number = '00000';
    }

    return $batch_prefix . $next_number;
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

function batch_id_get_admin_batches($page = 1, $per_page = 13) {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'smart_batch_ids';
    $table_barcodes = $wpdb->prefix . 'smart_barcodes';

    $offset = ($page - 1) * $per_page;

    // Retrieve batch IDs with pagination
    $batch_results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_batch_ids ORDER BY id DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));

    $batch_data = [];
    foreach ($batch_results as $batch) {
        // Get barcodes for this batch
        $barcodes = $wpdb->get_results($wpdb->prepare(
            "SELECT barcode, is_used FROM $table_barcodes WHERE batch_id = %s",
            $batch->batch_id
        ));

        // Get customer name if assigned
        $customer_name = __('Non attribué', 'batch-id');
        if (!is_null($batch->customer_id)) {
            $customer_data = get_userdata($batch->customer_id);
            if ($customer_data) {
                $customer_name = '<a href="' . esc_url(admin_url("user-edit.php?user_id=" . $batch->customer_id)) . '" target="_blank">' . esc_html($customer_data->display_name) . '</a>';
            }
        }

        $batch_data[] = [
            'batch_id'     => $batch->batch_id,
            'customer_name' => $customer_name,
            'barcodes'     => $barcodes,
            'is_fully_used' => count(array_filter($barcodes, fn($b) => $b->is_used == 0)) === 0
        ];
    }

    // Get total batch count for pagination
    $total_batches = $wpdb->get_var("SELECT COUNT(*) FROM $table_batch_ids");
    $total_pages = ceil($total_batches / $per_page);

    return [
        'batches'       => $batch_data,
        'total_batches' => $total_batches,
        'total_pages'   => $total_pages,
        'current_page'  => $page
    ];
}
