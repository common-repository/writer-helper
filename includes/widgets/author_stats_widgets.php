<?php 
/**
 ** Widgets for showing stats on author
 **/
include_once(WTRH_INCLUDE_DIR . '/classes/class_stat_author.php');
	
// Display all author stats
function wtrh_stat_author_dashboard_widget_render () {
	$as = new WH_Stat_Author(array("user_id"=>get_current_user_id()));
	$as->calc_stats();

	$words = 0;
	foreach( $as->word_count_books as $b )
		$words = $words + $b[1];

	echo "<table class='wh_dashboard_widget'>";
	echo "<tr><td><i class='dashicons dashicons-book-alt'></td>";
	echo "<td style='text-align:right'>".$as->count_books."</td><td colspan='2'>".
	(($as->count_books<2)?__('book','wtr_helper'):__('books','wtr_helper')).
		" ".__('including','wtr_helper')." ".$as->count_gameBooks." ".
	(($as->count_gameBooks<2)?__('game book','wtr_helper'):__('game books','wtr_helper')).
		"</td>";
	echo "</td></tr>";
	
	echo "<tr><td><i class='dashicons dashicons-admin-page'></td>";
	echo "<td style='text-align:right'>".$as->count_chapters."</td><td>".
	(($as->count_chapters<2)?__('chapter','wtr_helper'):__('chapters','wtr_helper'));
	
	echo "<td  rowspan='4'>&nbsp;&nbsp;&nbsp;</td>";
	echo "<td class='wh_dw_side' rowspan='4'>";
	echo "<b>".__("Statuses of your books",'wtr_helper')."</b><br>";
	echo "<table class='wh_dw_bs'>";
	foreach( $as->count_status_books as $s )
		echo "<tr><td style='text-align:center;".WH_Status::getStatusStyle($s[0])."'>".
			WH_Status::getStatusName($s[0])."</td><td>".$s[1]." ".
			(($s[1]<2)?__('book','wtr_helper'):__('books','wtr_helper'))."</td></tr>";
	echo "</table>";
	echo "</td></tr>";
	
	echo "<tr><td><i class='dashicons dashicons-media-text'></td>";
	echo "<td style='text-align:right'>".$as->count_scenes."</td><td>".
	(($as->count_scenes<2)?__('scene','wtr_helper'):__('scenes','wtr_helper'))."</td></tr>";
	
	echo "<tr><td><i class='dashicons dashicons-editor-spellcheck'></td>";
	echo "<td style='text-align:right'>".$words."</td><td>".__('words','wtr_helper')."</td></tr>";
	echo "</table>";
}
function init_wtrh_stat_author() {
  wp_add_dashboard_widget(
        'wtrh_stat_author_dashboard_widget',               // Widget slug.
        esc_html__( 'Writer Helper - Statistics', 'wtr_helper' ),   // Title.
        'wtrh_stat_author_dashboard_widget_render'         // Display function.
    ); 
}
add_action('wp_dashboard_setup', 'init_wtrh_stat_author');


?>