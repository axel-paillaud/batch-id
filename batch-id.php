<?php
/*
 * Plugin Name:       Batch ID
 * Description:       Barcode batch management by customer.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Axel Paillaud
 * Author URI:        https://axelweb.fr/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       batch-id
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
     exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/db-install.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/front-display.php';
require_once plugin_dir_path(__FILE__) . 'includes/hooks.php';

function batch_id_load_textdomain() {
    load_plugin_textdomain('batch-id', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'batch_id_load_textdomain');

register_activation_hook(__FILE__, 'batch_id_activate');

register_deactivation_hook(__FILE__, 'batch_id_deactivate');

register_uninstall_hook(__FILE__, 'batch_id_uninstall');

function batch_id_activate() {
    // Activation code here
}

function batch_id_deactivate() {
    // Deactivation code here
}

function batch_id_uninstall() {
    // Uninstallation code here
}

function batch_id_init() {
    // Initialization code here
}
