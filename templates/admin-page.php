<?php
/**
 * @var array $response
 * @var object[] $batch_ids
 * @var wpdb $wpdb
 * @var string $table_barcodes
 * @var int $total_pages
 * @var int $current_page
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php _e('Batch ID Settings', 'batch-id'); ?></h1>

    <?php echo $response['message']; ?>

    <!-- TODO: create CSS class instead of inline styles -->
    <div id="batch-id-message" style="display: none;"></div>

    <form method="post" id="batch-id-form">
        <label for="batch_id"><?php _e('Enter a Batch ID:', 'batch-id'); ?></label>
        <input type="text" id="batch_id" name="batch_id" required />

        <label for="customer"><?php _e('Associate a customer (optional):', 'batch-id'); ?></label>
        <input type="text" id="customer" name="customer" placeholder="Search customer..." />
        <input type="hidden" id="customer_id" name="customer_id" />

        <button type="submit" class="button button-primary"><?php _e('Generate', 'batch-id'); ?></button>
    </form>

    <hr>

    <h2><?php _e('Existing Batch IDs', 'batch-id'); ?></h2>

    <?php if (!empty($batch_ids)) : ?>
        <table id="batch-id-table" class="widefat fixed">
            <thead>
                <tr>
                    <th><?php _e('Batch ID', 'batch-id'); ?></th>
                    <th><?php _e('Customer', 'batch-id'); ?></th>
                    <th><?php _e('Barcodes', 'batch-id'); ?></th>
                    <th><?php _e('Actions', 'batch-id'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batch_ids as $batch) :
                    $unused_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_barcodes WHERE batch_id = %s AND is_used = 0", $batch->batch_id));
                    $batch_style = ($unused_count == 0) ? 'style="text-decoration: line-through; color: red;"' : '';

                    $customer_name = 'Non attribué';
                    if (!is_null($batch->customer_id)) {
                        $customer_data = get_userdata($batch->customer_id);
                        if ($customer_data) {
                            $customer_name = '<a href="' . esc_url(admin_url("user-edit.php?user_id=" . $batch->customer_id)) . '" target="_blank">' . esc_html($customer_data->display_name) . '</a>';
                        }
                    }
                ?>
                    <tr>
                        <td <?php echo $batch_style; ?>><?php echo esc_html($batch->batch_id); ?></td>
                        <td><?php echo $customer_name; ?></td>
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
                        <td>
                            <button class="delete-batch button button-link-delete aw-icon-flex" data-batch="<?php echo esc_attr($batch->batch_id); ?>" title="<?php _e('Supprimer ce Batch ID', 'batch-id'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Next and previous buttons -->
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php if ($total_pages > 1) : ?>
                    <span class="pagination-links">
                        <?php if ($current_page > 1) : ?>
                            <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', $current_page - 1)); ?>">&larr; <?php _e('Précédent', 'batch-id'); ?></a>
                        <?php endif; ?>

                        <span class="paging-input">
                            <?php printf(__('Page %d sur %d', 'batch-id'), $current_page, $total_pages); ?>
                        </span>

                        <?php if ($current_page < $total_pages) : ?>
                            <a class="next-page button" href="<?php echo esc_url(add_query_arg('paged', $current_page + 1)); ?>"><?php _e('Suivant', 'batch-id'); ?> &rarr;</a>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

    <?php else : ?>
        <p><?php _e('No Batch IDs generated.', 'batch-id'); ?></p>
    <?php endif; ?>
</div>
