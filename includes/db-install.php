<?php

if (!defined('ABSPATH')) {
    exit;
}

function batch_id_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Batch Types table
    $table_batch_types = $wpdb->prefix . 'smart_batch_types';
    $sql_batch_types = "CREATE TABLE $table_batch_types (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        lang TEXT DEFAULT NULL,
        prefix INT NOT NULL,
        color VARCHAR(9) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Batch ID table
    $table_batch_ids = $wpdb->prefix . 'smart_batch_ids';
    $sql_batch_ids = "CREATE TABLE $table_batch_ids (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        batch_id VARCHAR(20) NOT NULL UNIQUE,
        type_id INT NOT NULL,
        customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_batch_id (batch_id),
        KEY idx_customer_id (customer_id),
        KEY idx_type_id (type_id)
    ) $charset_collate;";

    // Barcode table
    $table_barcodes = $wpdb->prefix . 'smart_barcodes';
    $sql_barcodes = "CREATE TABLE $table_barcodes (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        barcode VARCHAR(30) NOT NULL UNIQUE,
        batch_id VARCHAR(20) NOT NULL,
        is_used TINYINT(1) DEFAULT 0,
        customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_batch_id (batch_id),
        KEY idx_customer_id (customer_id)
    ) $charset_collate;";

    // Execute SQL queries with dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_batch_types);
    dbDelta($sql_batch_ids);
    dbDelta($sql_barcodes);

    // Insert default batch types
    $default_types = [
        [
            'id'     => 1,
            'name'   => 'float',
            'lang'   => 'Floating',
            'prefix' => 0,
            'color'  => '#ffffff'
        ],
        [
            'id'     => 2,
            'name'   => 'plasmid-amplicon',
            'lang'   => 'Plasmid Amplicon',
            'prefix' => 1,
            'color'  => '#caedfb'
        ],
        [
            'id'     => 3,
            'name'   => 'genome',
            'lang'   => 'Genome',
            'prefix' => 2,
            'color'  => '#ffcccc'
        ],
        [
            'id'     => 4,
            'name'   => 'sanger-premixed',
            'lang'   => 'Sanger Premixed',
            'prefix' => 3,
            'color'  => '#fbe2d5'
        ],
        [
            'id'     => 5,
            'name'   => 'sanger-premium',
            'lang'   => 'Sanger Premium',
            'prefix' => 4,
            'color'  => '#fbe2d5'
        ],
        [
            'id'     => 6,
            'name'   => 'meta-16s',
            'lang'   => 'Meta 16S',
            'prefix' => 5,
            'color'  => '#daf2d0'
        ],
        [
            'id'     => 7,
            'name'   => 'specific-project',
            'lang'   => 'Specific Project',
            'prefix' => 6,
            'color'  => '#f2ceef'
        ],
    ];

    foreach ($default_types as $type) {
        $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_batch_types WHERE id = %d", $type['id']
        ));
        if (!$exists) {
            $wpdb->insert($table_batch_types, $type);
        }
    }
}
