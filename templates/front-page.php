<?php
/**
 * @var string $batch_status
 * @var string $batch_message
 * @var array $batch_data
 * @var int $total_batches
 */

if (!defined('ABSPATH')) exit;
?>

<div class="batch-add">
    <h2><?php _e('Claim a Batch ID', 'batch-id'); ?></h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <label hidden for="claim_batch_id"><?php _e('Enter your Batch ID:', 'batch-id'); ?></label>
        <input
            class="batch-id-input"
            type="text"
            id="claim_batch_id"
            name="claim_batch_id"
            placeholder="Enter your 10 digits Batch ID"
            minlength="10"
            maxlength="10"
            required
        />
        <input type="hidden" name="action" value="batch_id_claim">
        <button type="submit" class="button"><?php _e('Claim', 'batch-id'); ?></button>
    </form>

    <?php if (!empty($batch_message)) : ?>
        <div class="batch-message <?php echo $batch_status === 'success' ? 'success' : 'error'; ?>">
            <?php echo $batch_message; ?>
        </div>
    <?php endif; ?>
</div>

<div class="batch-id-title">
    <h2>Your available barcodes</h2>
    <p><?php echo $total_batches; ?> batch IDs</p>
</div>

<div class="batch-search">
    <label hidden for="batch-search-input"><?php _e('Search a Batch ID:', 'batch-id'); ?></label>
    <span class="dashicons dashicons-search"></span>
    <input type="text" id="batch-search-input" placeholder="Search a Batch ID..." />
</div>

<?php if (!empty($batch_data)) : ?>
    <div class="batch-container">
        <?php foreach ($batch_data as $batch) : ?>
            <div class="batch-column" data-batch-id="<?php echo esc_attr($batch['batch_id']); ?>">
                <div class="batch-header"><?php echo esc_html("Batch ID " . $batch['batch_id']); ?></div>
                <?php foreach ($batch['barcodes'] as $barcode) : ?>
                    <div class="barcode-container">
                        <div class="barcode-code <?php echo $barcode->is_used ? 'used' : ''; ?>">
                            <?php echo esc_html($barcode->barcode); ?>
                        </div>
                        <div class="barcode <?php echo $barcode->is_used ? 'used' : ''; ?>">
                            <?php echo esc_html($barcode->barcode); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>Batch ID not found.</p>
<?php endif; ?>
