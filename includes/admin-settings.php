<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Process Batch ID creation.
 *
 * @global wpdb $wpdb WordPress database global object.
 * @param string $batch_id The Batch ID to create.
 * @param int|null $customer_id The customer ID (optional).
 * @return array Response message and success status.
 */
function batch_id_create($batch_id, $customer_id = null, $quantity = 1) {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'batch_ids';
    $table_barcodes = $wpdb->prefix . 'barcodes';

    // Validate Batch ID format
    if (!preg_match('/^\d{9}$/', $batch_id)) {
        return [
            'success' => false,
            'message' => '<div class="notice notice-error"><p>' . __('Invalid Batch ID format, should contain 9 digits.', 'batch-id') . '</p></div>'
        ];
    }

    // Validate quantity
    if ($quantity < 1) {
        return [
            'success' => false,
            'message' => '<div class="notice notice-error"><p>' . __('Quantity must be at least 1.', 'batch-id') . '</p></div>'
        ];
    }

    $batch_prefix = substr($batch_id, 0, 4); // ex: 2503 (année + mois)
    $numeric_part = (int)substr($batch_id, 4); // ex: 00034

    // Already existing batch IDs
    $existing_batch_ids = $wpdb->get_col("SELECT batch_id FROM $table_batch_ids");

    $created_batches = [];

    for ($i = 0; $i < $quantity; $i++) {
        $new_batch_id = $batch_prefix . str_pad($numeric_part + $i, 5, '0', STR_PAD_LEFT);

        // Check if the Batch ID already exists
        if (in_array($new_batch_id, $existing_batch_ids)) {
            continue;
        }

        // Insert the Batch ID
        $wpdb->insert($table_batch_ids, ['batch_id' => $new_batch_id, 'customer_id' => $customer_id]);

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
            'message' => '<div class="notice notice-error"><p>' . __('No new Batch ID created. Some already existed.', 'batch-id') . '</p></div>'
        ];
    }

    return [
        'success' => true,
        'message' => '<div class="notice notice-success"><p>' . sprintf(__('Successfully created %d Batch IDs.', 'batch-id'), count($created_batches)) . '</p></div>'
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
    $table_batch_ids = $wpdb->prefix . 'batch_ids';

    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_batch_ids WHERE batch_id = %s", $batch_id));

    if ($exists) {
        $wpdb->delete($table_batch_ids, ['batch_id' => $batch_id]);
        return ['success' => true, 'message' => '<div class="notice notice-success"><p>' . __('Batch ID deleted successfully.', 'batch-id') . '</p></div>'];
    }

    return ['success' => false, 'message' => '<div class="notice notice-error"><p>' . __('This Batch ID does not exist.', 'batch-id') . '</p></div>'];
}

function batch_id_get_admin_batches($page = 1, $per_page = 13) {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'batch_ids';
    $table_barcodes = $wpdb->prefix . 'barcodes';

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

function batch_id_admin_page() {
    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'batch_ids';

    $response = ['success' => true, 'message' => ''];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['batch_id'])) {
            // Process new Batch ID creation
            $batch_id = sanitize_text_field($_POST['batch_id']);
            $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : NULL;
            $quantity = !empty($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            $response = batch_id_create($batch_id, $customer_id, $quantity);
        } elseif (isset($_POST['delete_batch_id'])) {
            // Process Batch ID deletion
            $batch_id_to_delete = sanitize_text_field($_POST['delete_batch_id']);
            $response = batch_id_delete($batch_id_to_delete);
        }
    }

    // Get pagination info
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $batch_info = batch_id_get_admin_batches($current_page);

    // Generate the next available Batch ID
    $batch_prefix = date('y') . date('m');
    $last_batch_id = $wpdb->get_var("SELECT batch_id FROM {$table_batch_ids} ORDER BY id DESC LIMIT 1");

    if ($last_batch_id && preg_match('/^(\d{4})(\d{5})$/', $last_batch_id, $matches)) {
        $last_prefix = $matches[1];
        $last_number = intval($matches[2]);
        $next_number = ($last_prefix === $batch_prefix) ? str_pad($last_number + 1, 5, '0', STR_PAD_LEFT) : '00000';
    } else {
        $next_number = '00000';
    }

    $next_batch_id = $batch_prefix . $next_number;

    // Pass data to template
    require plugin_dir_path(__FILE__) . '../templates/admin-page.php';
}
