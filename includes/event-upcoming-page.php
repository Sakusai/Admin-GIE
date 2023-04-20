<?php

/**
 * Appel la fonction create_new_page quand wordpress est initialisé
 */
$events_page = get_option( 'events_page_auto', false);
if($events_page === true){
    add_action( 'init', 'create_event_page' );
}


/**
 * Crée une page Evénement qui fait appel au shortcode upcoming_events
 * Ajoute cette page dans le menu de base
 */
function create_event_page() {

    // Vérifie si la page existe déjà
    $page_query = new WP_Query( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'pagename'       => 'event_upcoming'
    ));
  
    // Si la page n'existe pas, on la crée
    if ( ! $page_query->have_posts() ) {
        // Crée la page
        $page_id = wp_insert_post(
            array(
                'post_title'    => 'Événement',
                'post_content'  => '[upcoming_events]', // Appel du shortcode permettant d'afficher les événements par mois
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => 'event_upcoming'
            )
        );
  
        // Vérifie si la page a été créée avec succès
        if ( $page_id ) {
            echo 'Page created successfully!';

        // Ajoute la page au menu
        $menu_item_data = array(
            'menu-item-object-id' => $page_id,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-title' => get_the_title( $page_id ),
            'menu-item-status' => 'publish'
        );
        wp_update_nav_menu_item( 2, 0, $menu_item_data );
        }
    }
}
  