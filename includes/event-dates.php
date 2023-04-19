<?php

require_once plugin_dir_path(__FILE__) . '../includes/event-type.php';

/**
 * Effectue la fonction add_event_date_fields dans la boite de publication de l'événement
 */
add_action('post_submitbox_misc_actions', 'add_event_date_fields');

/**
 * Ajoute deux champs permetant de renseigner la date de début et la date de fin de l'événement
 * le champ de la date du début est obligatoire
 */
function add_event_date_fields()
{
    // Affichage des champs sur la page d'ajout/modification d'un événement
    ?>
    <div class="misc-pub-section">
        <label for="event-start-date">Date de début :</label>
        <input type="date" id="event-start-date" name="event_start_date"
            value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'event_start_date', true)); ?>" required>
    </div>
    <div class="misc-pub-section">
        <label for="event-end-date">Date de fin :</label>
        <input type="date" id="event-end-date" name="event_end_date"
            value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'event_end_date', true)); ?>">
    </div>
    <?php
}

/**
 * Quand l'événement est sauvegarder, la fonction save_event_date_fields est appelé
 */
add_action('save_post', 'save_event_date_fields');

/**
 * Enregistre les dates de début et de fin d'un événement
 */
function save_event_date_fields($post_id)
{
    // Vérification de la validité des données envoyées
    if (!isset($_POST['event_start_date']) || !isset($_POST['event_end_date'])) {
        return;
    }

    // Enregistrement des champs personnalisés
    update_post_meta($post_id, 'event_start_date', sanitize_text_field($_POST['event_start_date']));
    update_post_meta($post_id, 'event_end_date', sanitize_text_field($_POST['event_end_date']));
}

/**
 * Appel la fonction display_event_dates avec le contenu
 */
add_filter('the_content', 'display_event_dates');

/**
 * Affiche les dates de début et de fin dans la page d'un événement
 */
function display_event_dates($content)
{
    // Vérification qu'il s'agit bien d'un événement
    if (get_post_type() !== 'event') {
        return $content;
    }

    // Récupération des dates de début et de fin
    $start_date = get_post_meta(get_the_ID(), 'event_start_date', true);
    $end_date = get_post_meta(get_the_ID(), 'event_end_date', true);

    // Affichage des dates de début et de fin
    if (!empty($start_date) && !empty($end_date)) {
        $content = "<p><strong>Dates de l'événement :</strong> Du " . date('d-m-Y', strtotime($start_date)) . ' au ' . date('d-m-Y', strtotime($end_date)) . '</p> ' . $content;
    } elseif (!empty($start_date)) {
        $content = "<p><strong>Date de l'événement :</strong> Le " . date('d-m-Y', strtotime($start_date)) . '</p> ' . $content;
    }
    return $content;
}