<?php if (!defined('ABSPATH')) exit; ?>

<h2>Your barcodes</h2>

<?php if (!empty($batch_ids)) : ?>
    <ul>
        <?php foreach ($batch_ids as $batch) : ?>
            <li><?php echo esc_html($batch->batch_id); ?></li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <p>Batch ID not found.</p>
<?php endif; ?>
