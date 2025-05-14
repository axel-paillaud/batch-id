<?php

function batch_id_upgrade_database() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smart_batch_types';

    $index_name = $wpdb->get_var(
        $wpdb->prepare("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE table_schema = DATABASE()
              AND table_name = %s
              AND column_name = 'prefix'
              AND NON_UNIQUE = 0
        ", $table_name)
    );

    // Remove the index UNIQUE
    if ($index_name) {
        $wpdb->query("ALTER TABLE $table_name DROP INDEX $index_name;");
    }
}
