<?php

if (!defined('ABSPATH')) {
    exit;
}

function batch_id_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Batch Types table
    $table_batch_types = $wpdb->prefix . 'batch_types';
    $sql_batch_types = "CREATE TABLE $table_batch_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        description TEXT DEFAULT NULL,
        prefix INT NOT NULL UNIQUE
    ) $charset_collate;";

    // Batch ID table
    $table_batch_ids = $wpdb->prefix . 'batch_ids';
    $sql_batch_ids = "CREATE TABLE $table_batch_ids (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        batch_id VARCHAR(20) NOT NULL UNIQUE,
        type_id INT NOT NULL,
        customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (batch_id),
        INDEX (customer_id)
    ) $charset_collate;";

    // Barcode table
    $table_barcodes = $wpdb->prefix . 'barcodes';
    $sql_barcodes = "CREATE TABLE $table_barcodes (
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
    dbDelta($sql_batch_types);
    dbDelta($sql_batch_ids);
    dbDelta($sql_barcodes);

    // Insert default types
    $wpdb->insert($table_batch_types, ['id' => 1, 'name' => 'Prepayed', 'description' => 'Prepaid Batch', 'prefix' => 1]);
    $wpdb->insert($table_batch_types, ['id' => 2, 'name' => 'Float', 'description' => 'Floating Batch', 'prefix' => 2]);
}
