<?php
/**
 * @var object[] $batch_data
 */

if (!defined('ABSPATH')) exit;
?>

<h2>Your available barcodes</h2>

<?php if (!empty($batch_data)) : ?>
    <div class="batch-container">
        <?php foreach ($batch_data as $batch) : ?>
            <div class="batch-column">
                <div class="batch-header"><?php echo esc_html("Batch ID " . $batch['batch_id']); ?></div>
                <?php foreach ($batch['barcodes'] as $barcode) : ?>
                    <div class="barcode"><?php echo esc_html($barcode); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>Batch ID not found.</p>
<?php endif; ?>
