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
    $message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['batch_id'])) {
        $batch_id = sanitize_text_field($_POST['batch_id']);

        // Check if the Batch ID exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_batch_ids WHERE batch_id = %s", $batch_id));

        if ($exists) {
            $message = '<div class="notice notice-error"><p>' . __('Ce Batch ID existe déjà.', 'batch-id') . '</p></div>';
        } else {
            // Insert the Batch ID
            $wpdb->insert($table_batch_ids, ['batch_id' => $batch_id, 'customer_id' => NULL]);

            // Generate the 10 barcodes
            for ($i = 0; $i <= 9; $i++) {
                $barcode = $batch_id . $i;
                $wpdb->insert($table_barcodes, [
                    'barcode' => $barcode,
                    'batch_id' => $batch_id,
                    'is_used' => 0,
                    'customer_id' => NULL
                ]);
            }

            $message = '<div class="notice notice-success"><p>' . __('Batch ID et codes-barres générés avec succès !', 'batch-id') . '</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1><?php _e('Batch ID Settings', 'batch-id'); ?></h1>

        <?php echo $message; ?>

        <form method="post">
            <label for="batch_id"><?php _e('Saisissez un Batch ID :', 'batch-id'); ?></label>
            <input type="text" id="batch_id" name="batch_id" required />
            <button type="submit" class="button button-primary"><?php _e('Générer', 'batch-id'); ?></button>
        </form>
    </div>
    <?php
}
