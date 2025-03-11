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
            $message = '<div class="notice notice-error"><p>' . __('This Batch ID already exists.', 'batch-id') . '</p></div>';
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

            $message = '<div class="notice notice-success"><p>' . __('Batch ID and barcodes generated successfully!', 'batch-id') . '</p></div>';
        }
    }

    // Fetch existing Batch IDs
    $batch_ids = $wpdb->get_results("SELECT batch_id FROM $table_batch_ids ORDER BY id DESC");

    ?>
    <div class="wrap">
        <h1><?php _e('Batch ID Settings', 'batch-id'); ?></h1>

        <?php echo $message; ?>

        <form method="post">
            <label for="batch_id"><?php _e('Enter a Batch ID:', 'batch-id'); ?></label>
            <input type="text" id="batch_id" name="batch_id" required />
            <button type="submit" class="button button-primary"><?php _e('Generate', 'batch-id'); ?></button>
        </form>

        <hr>

        <h2><?php _e('Existing Batch IDs', 'batch-id'); ?></h2>

        <?php if (!empty($batch_ids)) : ?>
            <table class="widefat fixed">
                <thead>
                    <tr>
                        <th><?php _e('Batch ID', 'batch-id'); ?></th>
                        <th><?php _e('Barcodes', 'batch-id'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($batch_ids as $batch) :
                        // Vérifier si tous les barcodes du batch sont utilisés
                        $unused_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_barcodes WHERE batch_id = %s AND is_used = 0", $batch->batch_id));
                        $batch_style = ($unused_count == 0) ? 'style="text-decoration: line-through; color: red;"' : '';
                    ?>
                        <tr>
                            <td <?php echo $batch_style; ?>><?php echo esc_html($batch->batch_id); ?></td>
                            <td>
                                <button class="toggle-barcodes button" data-batch="<?php echo esc_attr($batch->batch_id); ?>">
                                    <?php _e('Voir les barcodes', 'batch-id'); ?>
                                </button>
                                <div class="barcodes-list" data-batch="<?php echo esc_attr($batch->batch_id); ?>" style="display:none;">
                                    <ul>
                                        <?php
                                        $barcodes = $wpdb->get_results($wpdb->prepare("SELECT barcode, is_used FROM $table_barcodes WHERE batch_id = %s", $batch->batch_id));
                                        foreach ($barcodes as $barcode) {
                                            $barcode_style = ($barcode->is_used == 1) ? 'style="text-decoration: line-through; color: red;"' : '';
                                            echo '<li ' . $barcode_style . '>' . esc_html($barcode->barcode) . '</li>';
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php _e('No Batch IDs generated.', 'batch-id'); ?></p>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".toggle-barcodes").forEach(button => {
            button.addEventListener("click", function() {
                let batchId = this.getAttribute("data-batch");
                let barcodeList = document.querySelector(".barcodes-list[data-batch='" + batchId + "']");
                if (barcodeList.style.display === "none") {
                    barcodeList.style.display = "block";
                    this.textContent = "<?php _e('Masquer les barcodes', 'batch-id'); ?>";
                } else {
                    barcodeList.style.display = "none";
                    this.textContent = "<?php _e('Voir les barcodes', 'batch-id'); ?>";
                }
            });
        });
    });
    </script>

    <?php
}
