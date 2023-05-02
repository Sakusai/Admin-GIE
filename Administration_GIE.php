<?php
/*
Plugin Name: Administration GIE
Plugin URI: http://wp.gie-convergence.fr/
Description: Administration des modules GIE - événements, annuaire, numéros utlies, élus.
Version: 2.0
Author: GIE Convergence - Cyril NITZKI - Lydie MARTINET - Erwann MATON
Author URI: https://wp.gie-convergence.fr
License: -
D'après : https://www.eprojet.fr/cours/wordpress/12-wordpress-developpement-de-plugin-wordpress
*/

require_once plugin_dir_path(__FILE__) . 'includes/Event/event-place.php';
require_once plugin_dir_path(__FILE__) . 'includes/Event/event-hours.php';
require_once plugin_dir_path(__FILE__) . 'includes/Event/event-dates.php';
require_once plugin_dir_path(__FILE__) . 'includes/Event/event-glance.php';
require_once plugin_dir_path(__FILE__) . 'includes/Map/map-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/Map/map-place.php';
require_once plugin_dir_path(__FILE__) . 'includes/Article/article-slider.php';
require_once plugin_dir_path(__FILE__) . 'includes/Article/article-generate-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/Article/article-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/Alert/alert-menu.php';