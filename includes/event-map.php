<?php

add_shortcode('my_map', 'my_map_shortcode');


function my_map_shortcode($atts) {
    // Attributs par défaut
    $atts = shortcode_atts(array(
        'lat' => '49.564402344613605',
        'lon' => '3.619300577651194',
        'zoom' => '13',
        'marker_lat' => '49.56459863808388',
        'marker_lon' => '3.620358908793717',
        'marker_title' => 'GIE Convergence',
        'marker_desc' => 'Assistance et services informatiques à Laon'
    ), $atts, 'my_map');

    // Enqueue Leaflet CSS file
    wp_enqueue_style( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.css', array(), '1.7.1' );

    // Génération du contenu HTML de la carte
    $content = '<div id="mapid" style="height: 400px;"></div>';
    $content .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js"></script>';
    $content .= '<script>
                    var mymap = L.map("mapid").setView([' . $atts['lat'] . ', ' . $atts['lon'] . '], ' . $atts['zoom'] . ');
                    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                        attribution: "Map data © <a href=\'https://openstreetmap.org\'>OpenStreetMap</a> contributors",
                        maxZoom: 18,
                    }).addTo(mymap);
                    var markerOptions = {
                        icon: L.icon({
                            iconUrl: "https://cdn-icons-png.flaticon.com/512/4703/4703650.png",
                            iconSize: [30, 30],
                            iconAnchor: [15, 30],
                            popupAnchor: [0, -30]
                        })
                    };
                    var marker = L.marker([' . $atts['marker_lat'] . ', ' . $atts['marker_lon'] . '], markerOptions).addTo(mymap);
                    marker.bindPopup("<b>' . $atts['marker_title'] . '</b><br>' . $atts['marker_desc'] . '").openPopup();
                 </script>';

    // Retourne le contenu HTML généré
    return $content;
}
