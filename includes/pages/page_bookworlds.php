<?php 
	if( class_exists("WH_Bookworld") ) {
		include(WTRH_BOOKWORLDS_DIR."/includes/pages/page_bookworlds.php");
	} else {
?>
<h1><?php _e('My Bookworlds','wtr_helper');?></h1>
<br>
<?php
		echo "<p>";
		echo _e('You do not have the module "Bookworld"','wtr_helper');
		echo "<br>";
		echo _e('You can purchase it on our website','wtr_helper');
		echo " : <a href='".WTRH_WEBSITE."' target='_blank'>".WTRH_WEBSITE."</a>";
		echo "</p>";
	}
?> 