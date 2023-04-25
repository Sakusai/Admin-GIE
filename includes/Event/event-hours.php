<?php

require_once plugin_dir_path(__FILE__) . '../Event/event-type.php';

/**
 * Effectue la fonction add_event_hour_fields dans la boite de publication de l'événement
 */
add_action('post_submitbox_misc_actions', 'add_event_hour_fields');

/**
 * Ajoute deux champs permetant de renseigner l'heure de début et l'heure de fin de l'événement
 * le champ de l'heure du début est obligatoire
 */
function add_event_hour_fields()
{
    // Affichage des champs sur la page d'ajout/modification d'un événement
    ?>
    <!-- Champ de l'heure de début -->
    <div class="misc-pub-section">
        <label for="event-start-hour">Heure de début :</label>
        <input type="time" id="event-start-hour" name="event_start_hour"
            value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'event_start_hour', true)); ?>" required>
    </div>
    <!-- Champ de l'heure de fin -->
    <div class="misc-pub-section">
        <label for="event-end-hour">Heure de fin :</label>
        <input type="time" id="event-end-hour" name="event_end_hour"
            value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'event_end_hour', true)); ?>">
    </div>
    <?php
}

/**
 * Quand l'événement est sauvegarder, la fonction save_event_hour_fields est appelé
 */
add_action('save_post', 'save_event_hour_fields');

/**
 * Enregistre les horaires de début et de fin d'un événement
 */
function save_event_hour_fields($post_id)
{
    // Vérification de la validité des données envoyées
    if (!isset($_POST['event_start_hour']) || !isset($_POST['event_end_hour'])) {
        return;
    }

    // Enregistrement des champs personnalisés
    update_post_meta($post_id, 'event_start_hour', sanitize_text_field($_POST['event_start_hour']));
    update_post_meta($post_id, 'event_end_hour', sanitize_text_field($_POST['event_end_hour']));
}

/**
 * Appel la fonction display_event_hours avec le contenu
 */
add_filter('the_content', 'display_event_hours');

/**
 * Affiche les heures de début et de fin dans la page d'un événement
 */
function display_event_hours($content)
{
    // Vérification qu'il s'agit bien d'un événement
    if (get_post_type() !== 'event') {
        return $content;
    }

    // Récupération des heures de début et de fin
    $start_hour = get_post_meta(get_the_ID(), 'event_start_hour', true);
    $end_hour = get_post_meta(get_the_ID(), 'event_end_hour', true);

    // Affichage des heures de début et de fin
    if (!empty($start_hour) && !empty($end_hour)) {
        $content = "<p><strong>Heures de l'événement :</strong> De " . str_replace(":", "h", $start_hour) . ' à ' . str_replace(":", "h", $end_hour) . '</p> ' . $content;
    } elseif (!empty($start_hour)) {
        $content = "<p><strong>Heure de l'événement :</strong> A partir de " . str_replace(":", "h", $start_hour) . '</p> ' . $content;
    }
    return $content;
}