<?php
	// Add linked scenes graph 
	if( $book->isGameBook )
		include_once(WTRH_JS_DIR."/wtrh_jointjs_scenes.js.php");
		
	// "Storyboard" module exists ?
	if( class_exists("WH_Storyboard") ) {
		include(WTRH_STORYBOARD_DIR."/includes/pages/page_book_tab3.php");
	} else {
		echo "<p>";
		echo _e('You do not have the module "Storyboard"','wtr_helper');
		echo "<br>\n";
		echo _e('You can purchase it on our website','wtr_helper');
		echo " : <a href='".WTRH_WEBSITE."' target='_blank'>".WTRH_WEBSITE."</a>";
		echo "</p>";
	}
?>
