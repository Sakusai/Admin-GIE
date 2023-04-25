<?php

/**
 * Appel la fonction event_order quand on affiche les événements
 */
add_action('pre_get_posts', 'event_order');

/**
 * Tri les événements par leurs dates de début
 */
function event_order($query) {
    if (is_tax('event_category')) { // Vérifier si on est sur une page de catégorie d'événement
        $query->set('meta_key', 'event_start_date'); // Clé de la requête
        $query->set('orderby', 'meta_value'); // Tri sur la valeur de la clé
        $query->set('order', 'ASC'); // tri ascendant
    }
}