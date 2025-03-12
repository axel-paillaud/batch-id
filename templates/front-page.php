<?php
/**
 * @var object[] $batch_data
 * @var int $total_batches
 */

if (!defined('ABSPATH')) exit;
?>

<div class="batch-id-title">
    <h2>Your available barcodes</h2>
    <p><?php echo $total_batches; ?> batch ID</p>
</div>

<?php if (!empty($batch_data)) : ?>
    <div class="batch-container">
        <?php foreach ($batch_data as $batch) : ?>
            <div class="batch-column">
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
