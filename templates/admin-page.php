<?php
/**
 * @var array $batch_info
 * @var array $response
 * @var object $batch_ids
 * @var object $types
 * @var int $total_pages
 * @var int $current_page
 * @var string $next_batch_id
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1><?php _e('Batch ID Settings', 'batch-id'); ?></h1>

    <?php echo $response['message']; ?>

    <div id="batch-id-message" style="display: none;"></div>

    <form method="post" id="batch-id-form">

        <div>
            <label for="type_id"><?php _e('Batch Type:', 'batch-id'); ?></label>
            <select
                id="type_id"
                name="type_id"
                title="<?php _e('Select a batch id type, which will be added as an identifier at the beginning of the batch ID.', 'batch-id'); ?>"
            >
                <?php
                foreach ($types as $type) {
                    echo '<option value="' . esc_attr($type->prefix) . '">' . esc_html($type->lang) . '</option>';
                }
                ?>
            </select>
        </div>

        <div>
            <label for="batch_id" ">
                <?php _e('Enter a Batch ID:', 'batch-id'); ?>
            </label>
            <input
                title="<?php _e('Enter a Batch ID without batch type prefix.', 'batch-id'); ?>"
                type="text"
                id="batch_id"
                name="batch_id"
                value="<?php echo esc_attr($next_batch_id); ?>"
                maxlength="9"
                required
            />
        </div>

        <div>
            <label for="quantity"><?php _e('Quantity:', 'batch-id'); ?></label>
            <input type="number" id="quantity" name="quantity" min="1" value="1" required />
        </div>

        <div>
            <label for="customer"><?php _e('Associate a customer (optional):', 'batch-id'); ?></label>
            <input type="text" id="customer" name="customer" placeholder="Search customer..." />
        </div>
        <input type="hidden" id="customer_id" name="customer_id" />

        <button type="submit" class="button button-primary"><?php _e('Generate', 'batch-id'); ?></button>
    </form>

    <hr>

    <div class="batch-id-existing">
        <h2><?php _e('Existing Batch IDs', 'batch-id'); ?></h2>
        <p><?= '(' . $batch_info['total_batches'] . ' in total)'; ?></p>
    </div>

    <?php if (!empty($batch_info['batches'])) : ?>
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
                <?php foreach ($batch_info['batches'] as $batch) : ?>
                    <tr>
                        <td class="<?php echo $batch['is_fully_used'] ? 'used' : ''; ?>"><?php echo esc_html($batch['batch_id']); ?></td>
                        <td><?php echo $batch['customer_name']; ?></td>
                        <td>
                            <button class="toggle-barcodes button" data-batch="<?php echo esc_attr($batch['batch_id']); ?>">
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
                        <td>
                            <form method="post" class="batch-id-delete-form" onsubmit="return confirm('Are you sure you want to delete this Batch ID?');">
                                <input type="hidden" name="delete_batch_id" value="<?php echo esc_attr($batch['batch_id']); ?>" />
                                <button type="submit" class="button button-link-delete">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Next and previous buttons -->
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php if ($batch_info['total_pages'] > 1) : ?>
                    <span class="pagination-links">
                        <?php if ($batch_info['current_page'] > 1) : ?>
                            <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', $batch_info['current_page'] - 1)); ?>">&larr; <?php _e('Précédent', 'batch-id'); ?></a>
                        <?php endif; ?>

                        <span class="paging-input">
                            <?php printf(__('Page %d sur %d', 'batch-id'), $batch_info['current_page'], $batch_info['total_pages']); ?>
                        </span>

                        <?php if ($batch_info['current_page'] < $batch_info['total_pages']) : ?>
                            <a class="next-page button" href="<?php echo esc_url(add_query_arg('paged', $batch_info['current_page'] + 1)); ?>"><?php _e('Suivant', 'batch-id'); ?> &rarr;</a>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

    <?php else : ?>
        <p><?php _e('No Batch IDs generated.', 'batch-id'); ?></p>
    <?php endif; ?>
</div>
