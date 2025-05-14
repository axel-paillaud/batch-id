<?php

if (!defined('ABSPATH')) {
    exit;
}

define('BATCH_ID_TABLE', $wpdb->prefix . 'smart_batch_ids');
define('BATCH_TYPES_TABLE', $wpdb->prefix . 'smart_batch_types');
define('BARCODES_TABLE', $wpdb->prefix . 'smart_barcodes');
