<?php
/*
* Actions to do when plugin has been activated or updated
*/


/******************************************************
 **         UPDATE ACTIONS
 ******************************************************/
 
// Look for Writer Helper plugin
// @param $upgrader_object Array
// @param $options Array
function wtrh_upgrade_completed( $upgrader_object, $options ) {
	$wh_plugin = plugin_basename(WTRH_PLUGIN_FILE);
	
wtr_info_log(__METHOD__,"options ".print_r($options,true));
	// If an update has taken place and the updated type is plugins and the plugins element exists
	if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
		// Iterate through the plugins being updated and check if ours is there
		foreach( $options['plugins'] as $plugin ) {
wtr_info_log(__METHOD__,"plugin updated ".$plugin);
			if( $plugin == $wh_plugin ) {
wtr_info_log(__METHOD__,"plugin in list of updated plugins");
				// Set a transient to record that the plugin has just been updated
				set_transient(WTRH_UPDATE_TRANSIENT, 1);
			}
		}
	}
}


// Show a notice to anyone who has just updated this plugin
function wtrh_display_update_notice() {
	// Check the transient to see if we've just updated the plugin
	if( get_transient( WTRH_UPDATE_TRANSIENT ) ) {
wtr_info_log(__METHOD__,"plugin has been updated");

		$errmsg = "";
		
		// apply changes
		if( wtrh_update_changes($errmsg) )
			echo '<div class="notice notice-success">' . 
				__( 'Thanks for updating', 'wtr_helper' ) . '</div>';
		else
			echo '<div class="notice notice-failure">' . $errmsg . '</div>';

		delete_transient( WTRH_UPDATE_TRANSIENT );
	}
}


// Actions required because of new plugin functionnalities
function wtrh_update_changes(&$errmsg) {

wtr_info_log(__METHOD__,"apply changes after update");
	global $wpdb;
	$errmsg = "";
	$ret = true;
	
	// Update old statuses
	// -----------------------------------------
wtr_info_log(__METHOD__,"convert old status");
	wtr_convertOldStatuses();
	
	// Create Metadata table 
	// -----------------------------------------
wtr_info_log(__METHOD__,"create metadata table");
	$result = $wpdb->query(sprintf(WH_DB_Metadata::createReq, $wpdb->prefix));
	if( $result === false ) {
		$errmsg = $wpdb->last_error;
		wtr_error_log(__METHOD__, $errmsg);
		$ret = false;
		goto wtrh_update_changes_end;
	}
	
	// Add column "bookworld_id" and "storyboard_id" in Books DB table
	// -----------------------------------------
wtr_info_log(__METHOD__,"add column bookworld_id and storyboard_id to wtr_book");
	$ret = wtr_updateBooksTable($errmsg);
	if( $ret === false )
		wtr_error_log(__METHOD__, $errmsg);
	
	
	// Add books' settings
	$bs = WH_Category::get_BooksSettings();
	// if no books' settings
wtr_info_log(__METHOD__,"add book settings nb=".count($bs));
	if( count($bs) == 0 ) {
		$tit  = "Authors";
		$desc = array('nbBooks'       => -1,
					  'useBookworld'  => false,
					  'nbBookworlds'  => 0,
					  'useStoryboard' => false, 
					  'useStatistics' => false, 
					  'useToDoList'   => false);
		$cat  = new WH_Category(0, array('element' => WTRH_CAT_BOOKSETTINGS,
										 'number'  => 1,
										 'title'   => $tit,
										 'description' => json_encode($desc)));
		if( $cat->id == 0 && ! $cat->save() ) {
			wtr_error_log(__METHOD__, 
						  "add new category '".WTRH_CAT_BOOKSETTINGS."' ");
			$ret = false;
		}
		
		$tit = "Editors";
		$desc = array('editAllBooks'  => false, 
					  'useStatistics' => false, 
					  'useToDoList'   => false);
		$cat = new WH_Category(0, array('element' => WTRH_CAT_BOOKSETTINGS,
										'number'  => 2,
										'title'   => $tit,
										 'description' => json_encode($desc)));
		if( $cat->id == 0 && ! $cat->save() ) {
			wtr_error_log(__METHOD__, 
						  "add new category '".WTRH_CAT_BOOKSETTINGS."'");
			$ret = false;
		}
	}

	// Change Users
	// -----------------------------------------
wtr_info_log(__METHOD__,"upgrade users");
	$ret = wtr_upgradeUser($errmsg);
	if( $ret === false )
		wtr_error_log(__METHOD__, $errmsg);

wtr_info_log(__METHOD__,"drop users columns");
	$ret = wtr_dropUserColumns($errmsg);
	if( $ret === false )
		wtr_error_log(__METHOD__, $errmsg);

	
wtrh_update_changes_end:	
	return $ret;
}



/******************************************************
 **         ACTIVATE ACTIONS
 ******************************************************/
 

// Show a notice to anyone who has just installed the plugin for the first time
function wtrh_display_install_notice() {
	// Check the transient to see if we've just activated the plugin
	if( get_transient( WTRH_ACTIVATE_TRANSIENT ) ) {
		echo '<div class="notice notice-success">' . __( 'Thanks for installing', 'wtr_helper' ) . '</div>';
		
		// Delete the transient so we don't keep displaying the activation message
		delete_transient( WTRH_ACTIVATE_TRANSIENT );
	}
}
add_action( 'admin_notices', 'wtrh_display_install_notice' );


/**
 * Run this on activation
 * Set a transient so that we know we've just activated the plugin
 */
function wtrh_activate() {
	set_transient( WTRH_ACTIVATE_TRANSIENT, 1 );
}
register_activation_hook( __FILE__, 'wtrh_activate' );

?>