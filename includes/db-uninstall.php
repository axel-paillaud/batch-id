<?php

if (!defined('ABSPATH')) {
    exit;
}

// Delete tables on plugin deactivation
function batch_id_remove_tables() {
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}barcodes;");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}batch_ids;");
}
