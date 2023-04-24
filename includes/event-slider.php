<?php

/**
 * Création d'un shortcorde utilisable avec [slider_events] créé par la fonction slider_events_shortcode
 */
add_shortcode( 'slider_events', 'slider_events_shortcode' );

/**
 * Shortcode pour afficher une sorte de carrousel qui affiche les événements d'une certaine période donnée
 */
function slider_events_shortcode() {
    ob_start(); // Met en mémoire tampon la sortie du code

  
    $today = date('Y-m-d'); // Date d'ajourd'hui
    // Création de la requête qui chercher tous les événements
    $args = array(
        'post_type'      => 'event',
        'posts_per_page' => 10,
        'meta_query'     => array(
            array(
                'key'     => 'event_start_date',
                'compare' => '>=',
                'value'   => $today,
                'type'    => 'DATE'
            )
        ),
        'orderby'        => 'meta_value',
        'meta_key'       => 'event_start_date',
        'order'          => 'ASC'
    );
    
    $events = new WP_Query( $args ); // Exécution de la requête
    ?>

    <link rel="stylesheet" href="<?php echo plugins_url( 'slick/slick-theme.css', __FILE__ ); ?>"/> <!-- Lien vers le fichier theme css de slick -->
    <link rel="stylesheet" href="<?php echo plugins_url( 'slick/slick.css', __FILE__ ); ?>"/> <!-- Lien vers le fichier css de slick -->
    <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__) . 'CSS/style.css'; ?>"> <!-- Lien vers notre fichier css -->
    <div class="responsive">
    <?php
    // Boucle qui permet d'afficher les événements
    while ( $events->have_posts() ) {
        $events->the_post(); // Séléctionne puis enlève le premier événement de la liste 
        $event_id = get_the_ID(); // Récupère l'ID de l'événement séléctionné
        $event_start_date =  date('d-m-Y', strtotime(get_post_meta( $event_id, 'event_start_date', true ))); // Date de début de l'événement
        $event_day = date('d', strtotime(get_post_meta( $event_id, 'event_start_date', true ))); // Jour de l'événement
        $event_date = new DateTime(get_post_meta($event_id, 'event_start_date', true)); // Date de début de l'événement en type DateTime
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE); // Création du format de la date de notre choix, ici on précise en français
        $formatter->setPattern('MMM'); // Choix du format, ici on veut afficher les trois premières lettres du mois
        $event_month = $formatter->format($event_date); // Mois de l'événement sous forme : avr. par exemple
        $event_start_hour = get_post_meta( $event_id, 'event_start_hour', true ); // Heure de début de l'événement
        $slides_to_show = get_option( 'events_slides_to_show', 4 ); // Nombre de slide à afficher
        $content = get_the_content($event_id); // Contenu de l'événement
        $apercu_description = strip_tags(substr($content, 0, 480/$slides_to_show)); // Contenu coupé selon le nombre de slide afficher, qui donne une petite description
        $terms = get_the_terms( get_the_ID(), 'event_place' ); //Récuperation de tous les lieux

        $event_place_text = ""; // Texte vide pour afficher les lieux
        if ( $terms && ! is_wp_error( $terms ) ) // Vérifie qu'il a bien des lieux et vérifie que la requête ne provoque pas d'erreur
        {
            $i = 0;
            foreach($terms as $term)
            {
                $event_place_name = $term->name; // Récupère le nom du lieu
                if($i === count($terms)-1)
                {
                    $event_place_text .= $event_place_name; // Affiche le lieu si il y en a qu'un seul
                }
                else
                {
                    $event_place_text .= $event_place_name . " - "; // Affiche les lieux séparés avec un - si il y en a plusieur
                }
                $i++;
            }
        }
        $slides_format = get_option( 'events_slides_format', 1 ); // Récupère le format de slide choisi
        $background_color = get_option( 'events_background_color', '#ffffff' ); // Récupère la couleur de slide choisi
        $font_family = get_option('events_font_family', 'Arial'); // Récupère la police d'écriture du titre choisi
        if ($slides_format == 1)
        {
        ?>
        <!-- Affichage du slide format 1 -->
        <div class="slider" style="height: <?php echo ($slides_to_show*70/3)+290?>px; background: <?php echo $background_color ?>;">
            <p class="img">
                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( array( 300, 300 ) ); ?></a>
            </p>
            <div class="date-box">
                <h3 class="date"><strong><?php echo $event_day; ?></strong></h3>
                <h3 class="date"><strong><?php echo $event_month; ?></strong></h3>
            </div>
            <h1 style="font-family:<?php echo $font_family ?>; "><a href="<?php the_permalink(); ?>" class="color-a"><?php the_title();?></a></h1>
            <h2 class="hour"><strong><?php echo str_replace(":", "h", $event_start_hour); ?></strong></h2>
            <h2 class="place"><?php echo $event_place_text;?></h2>
            <p class="read-more"><a href="<?php the_permalink(); ?>" class="color-a">Lire la suite</a></p>
        </div>

        

    <?php }
    
    else{
        ?>
         <!-- Affichage du slide format 2 -->
        <div class="slider2">
            <p class="img2">
                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( array( 300, 180 ) ); ?></a>
            </p>
            <h1 class="h1slid2">    
                <a href="<?php the_permalink(); ?>"> <strong><?php the_title();?> </strong> </a>
            </h1>
            <h2 class="date2 h2slid2"> <?php echo $event_start_date . " à " . str_replace(":", "h", $event_start_hour)  ; ?> </h2>
            <h2 class="h2slid2"><strong><?php echo $event_place_text?></strong></h2>
            <p><?php echo $apercu_description . "..."; ?></p>
            <p class="read-more2">
                <a  href="<?php the_permalink(); ?>">Lire la suite</a>
            </p>
        </div>
    <?php
    }
    }
    $slides_speed = get_option('events_slides_speed', 3000); // Récupère la vitesse de défilement automatique choisi
    $slides_speed_pass = get_option('events_slides_speed_pass', 300); //Récupère la vitesse de défilement entre deux slides choisi
    $slides_auto = get_option('events_slides_auto'); // Récupère le choix du défilement automatique
    ?>
    </div>
    <!-- On Récupère ici tous les scripts js nécessaires pour le slider -->
    <script type="text/javascript" src="<?php echo plugins_url( 'JS/jquery-3.6.4.min.js', __FILE__ ); ?>"></script>
    <script type="text/javascript" src="<?php echo plugins_url( 'JS/jquery-migrate-1.4.1.min.js', __FILE__ ); ?>"></script>
    <script type="text/javascript" src="<?php echo plugins_url( 'slick/slick.min.js', __FILE__ ); ?>"></script>
        <!-- Début du script slick (carrousel)-->
        <script type="text/javascript">
            $('.responsive').slick({
                dots: true, //Affichage ou non des points en bas du slider
                infinite: true, //Boucle infini ou non des slides
                speed: <?php echo $slides_speed_pass ?>, //Vitesse de défilement entre deux slides
                autoplay: <?php echo $slides_auto ?>, // Défilement automatique
                autoplaySpeed:<?php echo $slides_speed ?>, // Vitesse du défilement automatique
                slidesToShow: <?php echo $slides_to_show ?>, // Nombre de slide à afficher
                slidesToScroll: 1, // Nombre de slide qui défile
                responsive: [ // Adaptation avec des écrans plus petits
                    {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: false
                        }
                        },
                        {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                        },
                        {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                        }
                    ]
                    });
        </script>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}