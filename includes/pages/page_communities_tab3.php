<br><br>
<?php 
	if( file_exists(WTRH_COMMUNITIES_DIR."/writerhelper_communities.php") ) {
		include(WTRH_COMMUNITIES_DIR."/writerhelper_communities.php");
		include(WTRH_COMMUNITIES_DIR."/includes/pages/page_communities_tab3.php");
	} else {
		echo "<p>";
		echo _e('You do not have the module "Communities"','wtr_helper');
		echo "<br>";
		echo _e('You can purchase it on our website','wtr_helper');
		echo " : <a href='".WTRH_WEBSITE."' target='_blank'>".WTRH_WEBSITE."</a>";
		echo "</p>";
	}
?> 