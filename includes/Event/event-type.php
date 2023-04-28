<?php

require_once plugin_dir_path(__FILE__) . '../Event/event-type-category.php';
require_once plugin_dir_path(__FILE__) . '../Event/event-sort-by-dates.php';
require_once plugin_dir_path(__FILE__) . '../Event/event-upcoming.php';
require_once plugin_dir_path(__FILE__) . '../Event/event-upcoming-page.php';
require_once plugin_dir_path(__FILE__) . '../Event/event-slider.php';
require_once plugin_dir_path(__FILE__) . '../Event/event-settings.php';
/**
 *  Effectue la fonction register_event_post_type à l'initialisation de WordPress
 */
add_action('init', 'register_event_post_type');

/**
 * Enregistrement du type de publication 'event'
 */
function register_event_post_type()
{
    // Création du type personalisé Event
    register_post_type(
        'event',
        array(
            'labels' => array(
                'name' => __('GIE Événements', 'textdomain'),
                'singular_name' => __('GIE Événement', 'textdomain'),
                'name_admin_bar' => __('GIE Événements', 'textdomain'),
		        'parent_item_colon' => __('Parent Événements:', 'textdomain'),
                'all_items' => __('Tous les événements', 'textdomain'),
                'add_new_item' => __('Ajouter un événement', 'textdomain'), // Modifie le texte d'ajout d'un événement
                'new_item' => __('Nouvel événement', 'textdomain' ), // Modifie le texte pour un nouvel événement
                'edit_item' => __("Modifier l'événement", 'textdomain'), // Modifie le texte de modification d'un événement
                'update_item' => __("Mettre à jour l'événement", 'textdomain'), // Modifie le texte de mise à jour d'un événement
                'view_item' => __("Voir l'événement", 'textdomain'),
                'view_items' => __('Voir les événements', 'textdomain'),
		        'search_items' => __('Rechercher des événements', 'textdomain'),
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt',
            ),
            'taxonomies' => array(
                'post_tag',
            ),
            'show_in_nav_menus' => true, // Affiche les événements dans la création de menu
            'menu_icon' => 'dashicons-calendar-alt',  // URL de l'image
        )
    );
}
/**
 * Effevtue la fonction add_back_button dans le contenu de l'événement
 */
add_action('the_content', 'add_back_button');

/**
 * Ajoute un bouton de retour dans le contenu
 */
function add_back_button($content) {
    if (is_singular('event')) { // Vérifier que c'est une page d'événement
        $back_button = "<button onclick='javascript:history.back()'>Retour</button>"; // Créer le bouton retour
        $content .= $back_button; // Ajouter le bouton à la fin du contenu
    }
    return $content;
}

/*
add_action( 'pre_get_posts', 'event_admin_pagination' );

function event_admin_pagination( $query ) {
    global $pagenow;

    if ( is_admin() && $pagenow == 'edit.php' && $query->query_vars['post_type'] == 'event' ) {
        $query->set( 'posts_per_page', 10 ); // Nombre d'événements par page
        $query->set( 'orderby', 'date' ); // Tri par date de création
        $query->set( 'order', 'DESC' ); // Tri descendant
    }
}
*/