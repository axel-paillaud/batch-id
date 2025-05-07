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
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        lang TEXT DEFAULT NULL,
        prefix INT NOT NULL UNIQUE,
        color VARCHAR(9) DEFAULT NULL
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
        INDEX (batch_id),
        INDEX (customer_id),
        INDEX (type_id),
        FOREIGN KEY (type_id) REFERENCES $table_batch_types(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (customer_id) REFERENCES $wpdb->users(ID) ON DELETE SET NULL ON UPDATE CASCADE
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
        INDEX (batch_id),
        INDEX (customer_id),
        FOREIGN KEY (batch_id) REFERENCES $table_batch_ids(batch_id) ON DELETE CASCADE
    ) $charset_collate;";

    // Execute SQL queries with dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_batch_types);
    dbDelta($sql_batch_ids);
    dbDelta($sql_barcodes);

    // Insert default batch types
    $wpdb->insert(
        $table_batch_types, [
            'id'     => 1,
            'name'   => 'float',
            'lang'   => 'Floating',
            'prefix' => 0,
            'color'  => '#ffffff'
        ]
    );
    $wpdb->insert(
        $table_batch_types, [
            'id'     => 2,
            'name'   => 'plasmid-amplicon',
            'lang'   => 'Plasmid Amplicon',
            'prefix' => 1,
            'color'  => '#caedfb'
        ]
    );
    $wpdb->insert(
        $table_batch_types, [
            'id'     => 3,
            'name'   => 'genome',
            'lang'   => 'Genome',
            'prefix' => 2,
            'color'  => '#ffcccc'
        ]
    );
    $wpdb->insert(
        $table_batch_types, [
            'id'     => 4,
            'name'   => 'sanger-premixed',
            'lang'   => 'Sanger Premixed',
            'prefix' => 3,
            'color'  => '#fbe2d5'
        ]
    );
    $wpdb->insert(
        $table_batch_types, [
            'id'     => 5,
            'name'   => 'sanger-premium',
            'lang'   => 'Sanger Premium',
            'prefix' => 4,
            'color'  => '#fbe2d5'
        ]
    );
    $wpdb->insert(
        $table_batch_types, [
            'id'     => 6,
            'name'   => 'meta-16s',
            'lang'   => 'Meta 16S',
            'prefix' => 5,
            'color'  => '#daf2d0'
        ]
    );
    $wpdb->insert(
        $table_batch_types, [
            'id'     => 7,
            'name'   => 'specific-project',
            'lang'   => 'Specific Project',
            'prefix' => 6,
            'color'  => '#f2ceef'
        ]
    );
}
