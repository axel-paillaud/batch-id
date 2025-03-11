<?php
if (!defined('ABSPATH')) {
    exit;
}

function batch_id_enqueue_admin_scripts($hook) {
    // Check if we are on the plugin page
    if ($hook !== 'toplevel_page_batch-id-settings') {
        return;
    }

    // Retrieve the plugin URL
    $plugin_url = plugin_dir_url(__FILE__);

    // Register and enqueue the admin script
    wp_enqueue_script('batch-id-admin-js', $plugin_url . '../assets/js/admin.js', [], false, true);
}
add_action('admin_enqueue_scripts', 'batch_id_enqueue_admin_scripts');
