<?php 
	// if uninstall.php is not called by WordPress, die
	if (!defined('WP_UNINSTALL_PLUGIN')) {
		die;
	}

	// delete DB tables
	global $wpdb;
	$wpdb->hide_errors();
	$msg = "";
	$res = true;
	

	$tb_list = array("wtr_activity",
	                 "wtr_book",
	                 "wtr_bookworld",
	                 "wtr_category",
	                 "wtr_chapter",
	                 "wtr_metadata",
	                 "wtr_scene",
	                 "wtr_users"	);
	
	foreach( $tb_list as $tab ) {
		
		$result = $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . $tab . "`;");
		if( $result === false ) {
			$res = false;
			wtr_error_log(__METHOD__, $msg);
		}
		
	}
	
	$wpdb->show_errors();

	// delete transient
	delete_transient( 'wtr_plugin_update' );
?>