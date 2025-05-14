<?php

function batch_id_upgrade_database() {
    global $wpdb;
    $table_batch_types = $wpdb->prefix . 'smart_batch_types';
    $table_barcodes = $wpdb->prefix . 'smart_barcodes';
    $table_batch_ids = $wpdb->prefix . 'smart_batch_ids';
    $users_table = $wpdb->users;

    // Supprime l'index UNIQUE sur prefix si existant
    $index_name = $wpdb->get_var(
        $wpdb->prepare("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE table_schema = DATABASE()
              AND table_name = %s
              AND column_name = 'prefix'
              AND NON_UNIQUE = 0
        ", $table_batch_types)
    );

    if ($index_name) {
        $wpdb->query("ALTER TABLE $table_batch_types DROP INDEX $index_name;");
    }

    // Ajout des clés étrangères
    batch_id_add_foreign_key($table_batch_ids, 'customer_id', $users_table, 'ID', 'fk_customer_id', 'SET NULL', 'CASCADE');
    batch_id_add_foreign_key($table_batch_ids, 'type_id', $table_batch_types, 'id', 'fk_type_id');
    batch_id_add_foreign_key($table_barcodes, 'batch_id', $table_batch_ids, 'batch_id', 'fk_barcode_batch_id');
}

function batch_id_add_foreign_key($table, $column, $referenced_table, $referenced_column, $constraint_name, $on_delete = 'CASCADE', $on_update = 'CASCADE') {
    global $wpdb;

    $constraint_exists = $wpdb->get_var($wpdb->prepare("
        SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = %s
          AND COLUMN_NAME = %s
          AND REFERENCED_TABLE_NAME = %s
    ", $table, $column, $referenced_table));

    if (!$constraint_exists) {
        $wpdb->query("
            ALTER TABLE {$table}
            ADD CONSTRAINT {$constraint_name}
            FOREIGN KEY ({$column}) REFERENCES {$referenced_table}({$referenced_column})
            ON DELETE {$on_delete} ON UPDATE {$on_update}
        ");
    }
}
