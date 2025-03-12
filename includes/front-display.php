
<?php
if (!defined('ABSPATH')) {
    exit;
}

function batch_id_display_front_page() {
    // Charger l'utilisateur connecté
    $user_id = get_current_user_id();
    if (!$user_id) {
        echo '<p>' . __('Vous devez être connecté pour voir vos Batch ID.', 'batch-id') . '</p>';
        return;
    }

    global $wpdb;
    $table_batch_ids = $wpdb->prefix . 'batch_ids';

    // Charger des données de test (Batch ID liés au client connecté)
    $batch_ids = $wpdb->get_results($wpdb->prepare(
        "SELECT batch_id FROM $table_batch_ids WHERE customer_id = %d ORDER BY id DESC",
        $user_id
    ));

    // Charger la vue et lui passer les données
    require plugin_dir_path(__FILE__) . '../templates/front-page.php';
}
