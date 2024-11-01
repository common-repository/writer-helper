<?php

/******************************
 **       DB functions       **
 ******************************/

// Create DB tables 
function wtr_createDB($reqList = array()) {
	global $wpdb, $dbCreateReqList;
	$msg = "";
	$wpdb->hide_errors();
	$res = true;
	
	if( count($reqList) == 0 )
		$reqList = $dbCreateReqList;
	
	//wtr_info_log(__METHOD__, print_r($reqList, true));
	
	foreach( $reqList as $req ) {
		
		//wtr_info_log(__METHOD__, "Req : " . sprintf($req, $wpdb->prefix));
		
		$result = $wpdb->query(sprintf($req, $wpdb->prefix));
		if( $result === false ) {
			$res = false;
			wtr_error_log(__METHOD__, $wpdb->last_error);
			break;
		}
		
	}
		
	$wpdb->show_errors();
	return $res;
}

// Delete DB tables
function wtr_deleteDB($tabList = array()) {
	global $wpdb;
	global $dbTablesList;
	$wpdb->hide_errors();
	$msg = "";
	$res = true;
	
	if( count($tabList) == 0 )
		$tabList = $dbTablesList;
	
	//wtr_info_log(__METHOD__, print_r($tabList, true));
	
	foreach( $tabList as $tab ) {
		
		$result = $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . $tab . "`;");
		if( $result === false ) {
			$res = false;
			wtr_error_log(__METHOD__, $msg);
		}
		
	}
	
	$wpdb->show_errors();
	return $res;
}

// get a row 
function wtr_getRow($query, $args = null, $output_type = OBJECT, $offset = 0) {
	global $wpdb;
	$result = false;
	
//	wtr_info_log(__METHOD__, "query:".$query);
	
	// No variable parts
	if( (is_null($args) )
	 || (is_array($args) && count($args) == 0)
	 || (!is_array($args) && strlen($args) == 0) ) {
//		if( $output_type == OBJECT )
			$result = $wpdb->get_row($query, $output_type, $offset);
/*		else
			$result = wtr_sanitize_fromDB($wpdb->get_row($query, $output_type, $offset));
	*/
	 } else { // prepare query
//		if( $output_type == OBJECT )
			$result = $wpdb->get_row(
							$wpdb->prepare($query, $args), 
							$output_type, 
							$offset);
	/*	else
			$result = wtr_sanitize_fromDB(
						$wpdb->get_row(
							$wpdb->prepare($query, $args), 
							$output_type, 
							$offset)
						);*/
	}
		
	return $result;
}
 
// get a set of rows
function wtr_getResults($query, $args = array(), $output_type = OBJECT) {
	global $wpdb;
	$result = array();

//	wtr_info_log(__METHOD__, "query:".$query." args=".print_r($args,true));
	
	// No variable parts
	if( (is_null($args) )
	 || (is_array($args) && count($args) == 0)
	 || (!is_array($args) && strlen($args) == 0) ) {

		$result = $wpdb->get_results($query, $output_type);

	 } else { // prepare query			
//wtr_info_log(__METHOD__,"query:".$wpdb->prepare($query, $args));
			$result = $wpdb->get_results(
							$wpdb->prepare($query, $args), 
							$output_type);

	}
//wtr_info_log(__METHOD__,"result:".print_r($result,true));

	return $result;
}
 
// insert or update a row 
// $values is an array with columns names as key and values as values
function wtr_setRow($tableName, $type, $values, $ids = array()) {
	global $wpdb;
	$result = false;

/*	wtr_info_log(__METHOD__, "type:".$type." table: ".$tableName.
								" values:".print_r($values,true).
								" ids:".print_r($ids,true));
// */
	// Insert a row
	if( $type == "insert" ) {
		$result = $wpdb->insert($tableName, 
								$values );	
	 } 

	 // Update a row
	 if( $type == "update" ) {		
		$result = $wpdb->update($tableName, 
								$values, 
								$ids );
	}
	
	if( $result === false ) {
		wtr_error_log(__METHOD__, "Request <".$wpdb->last_query."> :: Error message ".$wpdb->last_error);
	}
	
	return $result;
}
 
// delete rows
function wtr_deleteRow($tableName, $ids) {
	return wtr_deleteRows($tableName, $ids);
}
function wtr_deleteRows($tableName, $where_clauses) {
	global $wpdb;
	$result = $wpdb->delete($tableName, $where_clauses );
	
	if( $result === false )
		wtr_error_log(__METHOD__, "Table: ".$tableName." :: where_clauses: ".print_r($where_clauses,true));

	return $result;
}

// run query and return true if execution ok
function wtr_runQuery($query, $args = array()) {
	global $wpdb;
	$result = false;
	$wpdb->hide_errors();
	if( is_null($args) || ! is_array($args) || count($args) == 0 ) {
		$result = $wpdb->query($query);
	} else {
//wtr_info_log(__METHOD__,"query:".$wpdb->prepare($query, $args));
		$result = $wpdb->query($wpdb->prepare($query, $args));
	}
	
	if( $result === false )
		wtr_error_log(__METHOD__, "Error on query: ".$query." :: args: ".print_r($args,true));
	else {
		$result = true;
	}
	$wpdb->show_errors();
	return $result;
}
 
 
 
// convert old statuses
function wtr_convertOldStatuses() {
	global $wpdb;

	$query = "SELECT COUNT(*) FROM %s".
			 " WHERE status > 0 AND status < 10";
			 
	// convert books' statuses
	$table  = $wpdb->prefix.WH_DB_Book::tableName;
	$result = wtr_getRow(sprintf($query, $table), array(), ARRAY_N);
wtr_info_log(__METHOD__, $result[0]." rows to change in $table <br>");
	if( $result[0] > 0 ) {
		wtr_updateStatuses($table);
	}
			 
	// convert chapters' statuses
	$table  = $wpdb->prefix.WH_DB_Chapter::tableName;
	$result = wtr_getRow(sprintf($query, $table), array(), ARRAY_N);
wtr_info_log(__METHOD__, $result[0]." rows to change in $table <br>");
	if( $result[0] > 0 ) {
		wtr_updateStatuses($table);
	}
			 
	// convert scenes' statuses
	$table  = $wpdb->prefix.WH_DB_Scene::tableName;
	$result = wtr_getRow(sprintf($query, $table), array(), ARRAY_N);
wtr_info_log(__METHOD__, $result[0]." rows to change in $table <br>");
	if( $result[0] > 0 ) {
		wtr_updateStatuses($table);
	}
}

function wtr_updateStatuses($table) {
	
	$old_statuses = array( //0 => WH_Status::DRAFT    ,
	                       1 => WH_Status::TOEDIT   ,
	                       2 => WH_Status::EDITING  ,
	                       3 => WH_Status::EDITED   ,
	                       4 => WH_Status::TOPUBLISH,
	                       5 => WH_Status::PUBLISHED,
	                       6 => WH_Status::TRASHED
						  );
	
	foreach( $old_statuses as $old_status => $new_status ) {
		
		$query = "UPDATE ".$table.
				 " SET status = ".$new_status.
				 " WHERE status = ".$old_status;
				 
		if( ! wtr_runQuery($query) )
			wtr_error_log(__METHOD__, "Error update $table with status $old_status<br>");
	}
}


// Add column "bookworld_id" and "storyboard_id" in Books DB table
function wtr_updateBooksTable(&$errmsg) {
	global $wpdb;
	
	// list table columns
	$query = "DESCRIBE ".$wpdb->prefix.WH_DB_Book::tableName;
	$res = $wpdb->get_results($query);
	$cols = array();
	foreach( $res as $line )
		$cols[] = $line->Field;
	
	// if does not exist
	$res = true;
	if( ! in_array("bookworld_id", $cols) ) {
		
		$query1 = "ALTER TABLE ".$wpdb->prefix.WH_DB_Book::tableName.
					" ADD COLUMN ".
					"`bookworld_id` bigint(20) NOT NULL DEFAULT 0";
		$res = $wpdb->query($query1);
		if( $res === false )
			$errmsg = $wpdb->last_error;
	}
	
	if( ! in_array("storyboard_id", $cols) ) {
		
		$query2 = "ALTER TABLE ".$wpdb->prefix.WH_DB_Book::tableName.
				" ADD COLUMN ".
				"`storyboard_id` bigint(20) NOT NULL DEFAULT 0";
		$res = $wpdb->query($query2);
		if( $res === false )
			$errmsg = $wpdb->last_error;
	}
	
	return $res;
}


// Transform old users in new users with metadata
function wtr_upgradeUser(&$errmsg) {
	
	// get all writer helper users
	global $wpdb;
	$wpdb->hide_errors();
	$query    = "SELECT id, user_id, user_name, role, book_id".
				" FROM ".$wpdb->prefix.WH_DB_User::tableName;
	$order_by = " ORDER BY book_id asc, role asc ";
		
	$result   = wtr_getResults($query.$order_by);		
	$old_usr  = 0;
	
	$new_users = array();
	$old_user = null;
	$new_user = null;
	
	// for each user, create metadata
	foreach( $result as $usr ) {
		
		// read wh user object
		$old_user = new WH_User($usr->id);
		// delete old object
		$old_user->delete();

		// new user
		if( $usr->user_id != $old_usr ) {
			
			// If wh user object is set
			if( $new_user != null ) {
				$new_user->save();
			}
			
			$new_user = new WH_User(0, array('user_id'=>$usr->user_id));
			$new_user->id = 0;
			$new_user->save(); // generate a new id
			$old_usr = $new_user->user_id;
		}
		
		// add roles
		$new_user->addRole($usr->role);
		if( $usr->role == WTRH_ROLE_ADMIN ) {
			$new_user->addRole(WTRH_ROLE_AUTHOR);
			$new_user->addRole(WTRH_ROLE_EDITOR);
		}
		
		// add author/editor to book
		if( $usr->book_id != 0 ) {
			$book = new WH_Book($usr->book_id);
			// if book exists
			if( $book->isOk ) {
				if( $usr->role == WTRH_ROLE_AUTHOR )
					$book->add_Author($new_user->user_id, $usr->user_name);
				else
					$book->add_Editor($new_user->user_id, $usr->user_name);
				$book->save();
			} else
				wtr_error_log(__METHOD__,"Book does not exists (id: ".$usr->book_id.")");
		}
	}

	// Save last user
	if( $new_user != null ) {
		$new_user->save();
	}
	$wpdb->show_errors();
}

// Delete columns User.user_name, User.role, User_book_id
function wtr_dropUserColumns(&$errmsg) {
	
	global $wpdb;
	$ret = true;
	$table = $wpdb->prefix.WH_DB_User::tableName;
	$query = "ALTER TABLE ".$table.
			 " DROP COLUMN user_name,".
			 " DROP COLUMN role,".
			 " DROP COLUMN book_id";
			 
	$wpdb->hide_errors();
	$ret = wtr_runQuery($query);
	if( ! $ret )
		wtr_error_log(__METHOD__, "Error alter $table drop columns<br>");
	
	$wpdb->show_errors();
	return $ret;
}

?>