<?php
if (!defined('ABSPATH')) {
    exit;
}

function batch_id_add_admin_menu() {
    add_menu_page(
        __('Batch ID Admin Page', 'batch-id'),
        __('Batch ID', 'batch-id'),
        'manage_options',
        'batch-id-settings',
        'batch_id_admin_page',
        'dashicons-media-spreadsheet',
        20
    );
}

add_action('admin_menu', 'batch_id_add_admin_menu');

function batch_id_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Batch ID Settings', 'batch-id'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('batch_id_options_group');
            do_settings_sections('batch-id');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'batch_id_register_settings');

function batch_id_register_settings() {
    register_setting('batch_id_options_group', 'batch_id_option_name');

    add_settings_section(
        'batch_id_settings_section',
        __('Batch ID Settings Section', 'batch-id'),
        'batch_id_settings_section_callback',
        'batch-id'
    );

    add_settings_field(
        'batch_id_option_name',
        __('Batch ID Option', 'batch-id'),
        'batch_id_option_name_callback',
        'batch-id',
        'batch_id_settings_section'
    );
}

function batch_id_settings_section_callback() {
    echo __('Configure your Batch ID settings below:', 'batch-id');
}

function batch_id_option_name_callback() {
    $option = get_option('batch_id_option_name');
    ?>
    <input type="text" name="batch_id_option_name" value="<?php echo esc_attr($option); ?>" />
    <?php
}
