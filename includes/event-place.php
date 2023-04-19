<?php

require_once plugin_dir_path(__FILE__) . '../includes/event-type.php';

/**
 * Effectue la fonction register_event_place à l'initialisation de wordpress
 */
add_action( 'init', 'register_event_place' );

/**
 * Créer un attribut lieu personalisé
 */
function register_event_place() {
    $labels = array(
        'name'              => _x( 'Lieux d\'événements', 'taxonomy general name' ),
        'singular_name'     => _x( 'Lieu d\'événement', 'taxonomy singular name' ),
        'search_items'      => __( 'Rechercher un lieu d\'événement' ),
        'all_items'         => __( 'Tous les lieux d\'événements' ),
        'parent_item'       => __( 'Lieu parent' ),
        'parent_item_colon' => __( 'Lieu parent :' ),
        'edit_item'         => __( 'Modifier le lieu d\'événement' ),
        'update_item'       => __( 'Mettre à jour le lieu d\'événement' ),
        'add_new_item'      => __( 'Ajouter un nouveau lieu d\'événement' ),
        'new_item_name'     => __( 'Nom du nouveau lieu d\'événement' ),
        'menu_name'         => __( 'Lieux d\'événements' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'event_place' ),
    );

    register_taxonomy( 'event_place', 'event', $args );
}

/**
 * Appel la fonction display_event_place avec le contenu
 */
add_filter('the_content', 'display_event_place');

/**
 * Affiche le lieu de l'événement dans la page d'un événement
 */
function display_event_place($content)
{
    // Vérification qu'il s'agit bien d'un événement
    if (get_post_type() !== 'event') {
        return $content;
    }

    // Récupération des termes de Lieu (ici tous les lieux)
    $terms = get_the_terms( get_the_ID(), 'event_place' );

    // Vérifie s'il y a des termes et retourne le nom du premier terme
    $event_place_text = "<p><strong>Lieu de l'événement : </strong>";
    if ( $terms && ! is_wp_error( $terms ) ) {
        $i = 0;
        foreach($terms as $term)
        {
            $event_place_name = $term->name;
            if($i === count($terms)-1)
            {
                $event_place_text .= $event_place_name;
            }
            else
            {
                $event_place_text .= $event_place_name . " - ";
            }
            $i++;
        }
        $event_place_text .= '</p>';
        return $event_place_text . $content;
    }

    return $content;
}