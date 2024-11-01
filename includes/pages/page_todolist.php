<h1><?php _e('My To Do List','wtr_helper');?></h1>
<?php 
	if( file_exists(WTRH_MODULES_DIR."/todolist/index.php") ) {
		include(WTRH_MODULES_DIR."/todolist/index.php");
	} else {
		echo "<p>";
		echo _e('You do not have the module "To Do List"','wtr_helper');
		echo "<br>";
		echo _e('You can purchase it on our website','wtr_helper');
		echo " : <a href='".WTRH_WEBSITE."' target='_blank'>".WTRH_WEBSITE."</a>";
		echo "</p>";
	}
?> 