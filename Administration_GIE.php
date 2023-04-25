<?php
/*
Plugin Name: Administration GIE
Plugin URI: http://wp.gie-convergence.fr/
Description: Administration des modules GIE - événements, annuaire, numéros utlies, élus.
Version: 2.0
Author: GIE Convergence - Cyril NITZKI - Lydie Martinet - Erwann MATON
Author URI: https://wp.gie-convergence.fr
License: -
D'après : https://www.eprojet.fr/cours/wordpress/12-wordpress-developpement-de-plugin-wordpress
*/
require_once plugin_dir_path(__FILE__) . 'includes/Event/event-place.php';
require_once plugin_dir_path(__FILE__) . 'includes/Event/event-hours.php';
require_once plugin_dir_path(__FILE__) . 'includes/Event/event-dates.php';
require_once plugin_dir_path(__FILE__) . 'includes/Event/event-glance.php';
