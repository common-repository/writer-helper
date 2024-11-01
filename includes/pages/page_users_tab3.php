<br><br>
<?php 
	if( file_exists(WTRH_MODULES_DIR."/writers_editors/index.php") ) {
		include(WTRH_MODULES_DIR."/writers_editors/index.php");
	} else {
		echo "<p>";
		echo _e('You do not have the module "Writers & Editors"','wtr_helper');
		echo "<br>";
		echo _e('You can purchase it on our website','wtr_helper');
		echo " : <a href='".WTRH_WEBSITE."' target='_blank'>".WTRH_WEBSITE."</a>";
		echo "</p>";
	}
?> 