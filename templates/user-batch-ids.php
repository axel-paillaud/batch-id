<?php
/**
 * @var array $batch_data List of batch IDs with associated barcodes.
 */

if (!defined('ABSPATH')) exit;
?>

<h2><?php _e('User Batch IDs', 'batch-id'); ?></h2>

<?php if (!empty($batch_data)) : ?>
    <table class="widefat fixed" id="batch-id-table">
        <thead>
            <tr>
                <th><?php _e('Batch ID', 'batch-id'); ?></th>
                <th><?php _e('Barcodes', 'batch-id'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($batch_data as $batch) : ?>
                <tr>
                    <td><?php echo esc_html($batch['batch_id']); ?></td>
                    <td>
                        <button
                            class="toggle-barcodes button"
                            data-batch="<?php echo esc_attr($batch['batch_id']); ?>"
                            type="button"
                        >
                            <?php _e('Voir les barcodes', 'batch-id'); ?>
                        </button>
                        <div class="barcodes-list" data-batch="<?php echo esc_attr($batch['batch_id']); ?>" style="display:none;">
                            <ul>
                                <?php foreach ($batch['barcodes'] as $barcode) : ?>
                                    <li class="<?php echo ($barcode->is_used == 1) ? 'used' : ''; ?>">
                                        <?php echo esc_html($barcode->barcode); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p><?php _e('No Batch IDs assigned to this user.', 'batch-id'); ?></p>
<?php endif; ?>
