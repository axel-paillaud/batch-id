
<?php

if (!defined('ABSPATH')) {
    exit;
}

function batch_id_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Batch ID table
    $table_batch_ids = $wpdb->prefix . 'batch_ids';
    $sql1 = "CREATE TABLE $table_batch_ids (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        batch_id VARCHAR(20) NOT NULL UNIQUE,
        customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX (batch_id),
        INDEX (customer_id)
    ) $charset_collate;";

    // Barcode table
    $table_barcodes = $wpdb->prefix . 'barcodes';
    $sql2 = "CREATE TABLE $table_barcodes (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        barcode VARCHAR(30) NOT NULL UNIQUE,
        batch_id VARCHAR(20) NOT NULL,
        is_used TINYINT(1) DEFAULT 0,
        customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX (batch_id),
        INDEX (customer_id),
        FOREIGN KEY (batch_id) REFERENCES $table_batch_ids(batch_id) ON DELETE CASCADE
    ) $charset_collate;";

    // Execute SQL queries with dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql1);
    dbDelta($sql2);
}
