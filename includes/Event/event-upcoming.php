
<?php

/**
 * Création d'un shortcorde utilisable avec [upcoming_events] créé par la fonction upcoming_events_shortcode
 */
add_shortcode( 'upcoming_events', 'upcoming_events_shortcode' );

/**
 * Shortcode pour afficher les événements par mois
 */
function upcoming_events_shortcode() {
  ob_start(); // Met en mémoire tampon la sortie du code

  // Liste des mois de l'année
  $monthNames = [
    1 => "Janvier",
    2 => "Février",
    3 => "Mars",
    4 => "Avril",
    5 => "Mai",
    6 => "Juin",
    7 => "Juillet",
    8 => "Août",
    9 => "Septembre",
    10 => "Octobre",
    11 => "Novembre",
    12 => "Décembre"
    ];

  // On récupère le mois et l'année choisis en utilisant un explode pour séparer le mois et l'année dans un tableau
  $currentMonthYear = isset($_GET['monthYear']) ? explode('-', $_GET['monthYear']) : [intval(date('m')), intval(date('Y'))]; 
  $currentMonth = $currentMonthYear[0]; // On récupère le mois courant
  $currentYear = $currentMonthYear[1]; // On récupère l'année courante

  $prevMonth = $currentMonth - 1; // Calcul du mois précédent
  $prevYear = $currentYear; // Calcul de l'année précédente
  if ($prevMonth < 1) {
      $prevMonth = 12;
      $prevYear--;
  }

  $nextMonth = $currentMonth + 1; // Calcul du mois prochain
  $nextYear = $currentYear; // Calcul de l'année prochaine
  if ($nextMonth > 12) {
      $nextMonth = 1;
      $nextYear++;
  }

  $currentmonthName = $monthNames[$currentMonth]; // Nom du mois courant
  $prevmonthName = $monthNames[$prevMonth]; // Nom du mois précédent
  $nextmonthName = $monthNames[$nextMonth]; // Nom du mois prochain
  ?>
  <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__) . '../CSS/style.css'; ?>">
  <!-- Affichage des deux boutons de navigation et affichage du mois et de l'année séléctionnée -->
  <div class="month-navigation">
    <button id="prev-month" onclick="location.href='?monthYear=<?php echo $prevMonth.'-'.$prevYear; ?>'"><?php echo $prevmonthName?></button>
    <p id="month-display"><?php echo $currentmonthName . ' ' . $currentYear; ?></p>
    <button id="next-month" onclick="location.href='?monthYear=<?php echo $nextMonth.'-'.$nextYear; ?>'"><?php echo $nextmonthName?></button>
  </div>

  <?php
  // Fusion de l'année et le mois
  $year_month = date('Y-m', strtotime($currentYear.'-'.$currentMonth));
  // réduire le nb de résultat de la requête
  $args = array(
    'post_type'      => 'event',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'meta_key'       => 'event_start_date',
    'order'          => 'ASC'
  );

  $events = new WP_Query( $args ); // Exécution de la requête
  ?>
  <div class="upcoming-events">
    <!-- Affichage du mois séléctionné, date_i18n permet de traduire automatiquement la date -->
    <h2>Événements <?php echo ucfirst(date_i18n( 'F Y', strtotime( $currentYear . '-' . $currentMonth . '-01' ) ) ); ?></h2>
    <?php
  // Si la requête retourne des événements alors il les affiche
  if ( $events->have_posts() ) { 

    ?>
    <ul>
    <?php while ( $events->have_posts() ) {
      $events->the_post(); // Séléctionne puis enlève le premier événement de la liste 
      $event_start_date = get_post_meta( get_the_ID(), 'event_start_date', true ); // Récupère la date du début
      $event_start_month = date('Y-m', strtotime($event_start_date)); // Récupère le mois et l'année de début
      $event_end_date = get_post_meta( get_the_ID(), 'event_end_date', true ); // Récupère la date de fin
      if (!$event_end_date ) // si il n'y a pas de date de fin alors date de fin = date de début
      {
        $event_end_date = $event_start_date;
      }
      $event_end_month = date('Y-m', strtotime($event_end_date)); // Récupère le mois de fin
      // $event_start_month = mois de la date du début de l'événement
      // $year_month = année et mois choisis
      if ( ($event_start_month === $year_month  && $event_end_month === $year_month ) // Commence et finit dans le mois
        || ($event_start_month < $year_month  && $event_end_month === $year_month ) // Commence avant le premier du mois et finit dans le mois
        || ($event_start_month < $year_month  && $event_end_month > $year_month ) // Commence anat le premier du mois et finit apres le dernier du mois
        || ($event_start_month === $year_month  && $event_end_month > $year_month ))  // Commende dans le mois et finit après le mois
        {?>
        <li class="event-thumbnail">
        <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__) . '../CSS/style.css'; ?>">
          <!-- Si l'événement à une vignette, alors on l'affiche -->
          <?php if ( has_post_thumbnail() ) : ?>
            <a class="event-thumbnail img" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( array( 300, 300 ) ); ?></a>
          <?php endif; ?>
          <div class="event-details">
            <a class="event-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              <span class="event-date"><?php 
                echo date_i18n( 'd-m-Y', strtotime( $event_start_date ) );
                if($event_end_date != $event_start_date)
                {
                  echo ' - ' . date_i18n( 'd-m-Y', strtotime( $event_end_date ) );
                }
                echo ' - ' . str_replace(":", "h", get_post_meta(get_the_ID(), 'event_start_hour', true));
              ?>
            </span>
            <span class="event-desc">
              <?php
              // Récupérer l'ID de l'événement
              $event_id = get_the_ID();

              // Récupérer le contenu de l'événement
              $content = get_the_content($event_id);
              $apercu_description = substr($content, 0, 80);
              echo $apercu_description."...";

              // Récupérer l'URL de l'événement
              $event_url = get_permalink($event_id);
              ?>
              <br>
              <button id='event-readmore' class="button-upcoming" onclick="location.href='<?php echo $event_url; ?>'">Lire la suite</button>
            </span>
        </div>
        </li>
        <hr>
      <?php 
      }}?>
    </ul>
    <?php }
    else {
      echo "Il n'y a pas d'événements à venir pour ce mois.";
    }
    ?> 
  </div>
  <?php
  wp_reset_postdata();
  return ob_get_clean();
}