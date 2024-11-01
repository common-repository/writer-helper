<?php
/************************************
 **         Activity class         **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");

class WH_DB_Activity {
	const tableName = "wtr_activity";
	const createReq = "CREATE TABLE IF NOT EXISTS `%s".WH_DB_Activity::tableName."` (".
	       "`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,".
	       "`user_id` bigint(20) unsigned NOT NULL,".
	       "`element` text NOT NULL,".
	       "`element_id` bigint(20) unsigned NOT NULL DEFAULT 0,".
	       "`book_id` bigint(20) unsigned NOT NULL DEFAULT 0,".
	       "`action` text NOT NULL,".
	       "`comment` text,".
	       "`activity_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,".
	       "`action_seen` boolean DEFAULT false,".
	       "`action_done` boolean DEFAULT false);";

	const tableCols= 'id, user_id, element, element_id, action,'.
	                 ' comment, activity_datetime, action_seen, action_done';
    const selectBaseReq = "SELECT ".WH_DB_Activity::tableCols.
	                          " FROM %s".WH_DB_Activity::tableName;
    const selectReq    = "SELECT ".WH_DB_Activity::tableCols.
	                          " FROM %s".WH_DB_Activity::tableName.
							  " WHERE id=%s";
	
	public $id;
	public $user_id;
	public $element;
	public $element_id;
	public $book_id;
	public $action;
	public $comment;
	public $action_seen;
	public $action_done;
	
	public $isOk;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'user_id' => int
	*			'element' => string
	*			'element_id' => int
	*			'book_id' => int
	*			'action' => string
	*			'comment' => string
	*			'action_seen' => boolean
	*			'action_done' => boolean
	**/
    public function __construct($id, $args = array())    {
		$this->id            = isset($args['id'])         ? (int)$args['id']:(int)$id;
		$this->user_id       = isset($args['user_id'])    ? (int)$args['user_id']:0;
		$this->element       = isset($args['element'])    ? (string)$args['element']:'';
		$this->element_id    = isset($args['element_id']) ? (int)$args['element_id']:0;
		$this->book_id       = isset($args['book_id'])    ? (int)$args['book_id']:0;
		$this->action        = isset($args['action'])     ? (string)$args['action']:'';
		$this->comment       = isset($args['comment'])    ? (string)$args['comment']:'';
		$this->action_seen   = isset($args['action_seen'])? (boolean)$args['action_seen']:false;
		$this->action_done   = isset($args['action_done'])? (boolean)$args['action_done']:false;
		
		if( is_array($this->comment) )
			$this->comment = json_encode($this->comment);
		
		$this->isOk = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
		} else {
			if( $id != 0 ) 
				$this->isOk = $this->getDB_Activity($id);
			else
				$this->isOk = true;
		}
	}

	/* Update DB */
	public function save() {
		$result = false;
		
		if( $this->id == 0 ) { $result = $this->insertDB_Activity(); }
		else {                 $result = $this->updateDB_Activity();	}
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		
		if( $this->id == 0 )
			return true;

		return $this->deleteDB_Activity();
	}
	
	/* Read DB */
	private function getDB_Activity($id) {
		global $wpdb;
		
		$result = $wpdb->get_row(sprintf(WH_DB_Activity::selectReq, $wpdb->prefix, $id), ARRAY_A);
		
		if( $result ) {
			$this->id            = $id;
			$this->user_id       = $result['user_id'];
			$this->element       = $result['element'];
			$this->element_id    = $result['element_id'];
			$this->book_id       = $result['book_id'];
			$this->action        = $result['action'];
			$this->comment       = $result['comment'];
			$this->action_seen   = $result['action_seen'];
			$this->action_done   = $result['action_done'];
			$this->isOk = true;
		} else {
			wtr_error_log(__METHOD__, "<".$wpdb->last_query."> : ".$wpdb->last_error);
			$this->isOk = false;
		}
		return $this->isOk;
	}
	
	/* Insert into DB */
	private function insertDB_Activity() {
		global $wpdb;
		$ret = false;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Activity::tableName, 
								"insert",
		                        array('user_id'      => $this->user_id     ,  
		                              'element'      => $this->element, 
		                              'element_id'   => $this->element_id  , 
		                              'book_id'      => $this->book_id  , 
		                              'action'       => $this->action,
		                              'comment'      => $this->comment,
		                              'action_seen'  => $this->action_seen ,
		                              'action_done'  => $this->action_done  ) );
		if( $result !== false ) {
			$this->id = $wpdb->insert_id;
			$ret = true;
		}
		return $ret;
	}
	
	/* Update DB */
	private function updateDB_Activity() {
		global $wpdb;
		$ret = true;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Activity::tableName, 
								"update",
		                        array('user_id'      => $this->user_id     ,  
		                              'element'      => $this->element, 
		                              'element_id'   => $this->element_id  , 
		                              'book_id'      => $this->book_id  , 
		                              'action'       => $this->action,
		                              'comment'      => $this->comment,
		                              'action_seen'  => $this->action_seen ,
		                              'action_done'  => $this->action_done  ),
								array('id' => $this->id) );
		if( $result === false ) {
			$ret = false;
		}
		return $ret;
	}
	
	/* Delete DB */
	private function deleteDB_Activity() {
		global $wpdb;
		$ret = true;
		$result = wtr_deleteRow($wpdb->prefix . WH_DB_Activity::tableName, array('id' => $this->id) );
		if( $result === false ) {
			$ret = false;
		}
		return $ret;
	}
	
	/* Get All DB */
	public static function getAllDB_Activities($col = "id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Activity::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		
		if( count($args) == 0 )
			$where = "";
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/* Get User's activities */
	public static function getAllDB_ActivitiesByUser($user_id, $col = "id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_ArchetypJourney::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		if( $user_id != 0 ) {
			$where .= "user_id=%s";
			$args[] = $user_id;
		} 
		
		if( count($args) == 0 )
			$where = "";
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/* Get Book's activities */
	public static function getAllDB_ActivitiesByBook($book_id, $col = "id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_ArchetypJourney::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		$where .= "book_id=%d";
		$args[] = $book_id;
		
		if( count($args) == 0 )
			$where = "";
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/* Get Book's activities */
	// $elt_array : list of element
	// $days      : number of days 
	public static function getAllDB_ActivitiesByElements($elt_array, $days = 0, $col = "id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_ArchetypJourney::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		
		$elts = array();
		$where .= "element IN (";
		foreach( $elt_array as $elt ) {
			
			if( count($args) > 0 )
				$where .= ",";
			
			$where .= "'%s'";
			$args[] = $elt;
		}
		$where .= ")";

		// get the last X days of activity
		if( $days != 0 )
			$and = " AND timestampdiff(DAY,activity_datetime,sysdate()) < ".$days;
			
		if( count($args) == 0 )
			$where = "";
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/*********************************
	 ***       Add an activity     ***
	 *********************************/
	/* comment : array()
	             if slq error
				    array( 'type'      => 'error',
							'data'     => get_object_vars($this),
							'request'  => $wpdb->last_query,
							'msg'      => $wpdb->last_error)
	             if info
				    array( 'type'      => 'info',
							'data'     => get_object_vars($this),
							'msg'      => '')
	*/
    public static function addActivity($args, $user_id = 0){
		
		if( $user_id == 0 ) {
			// get user_id from WordPress
			$uid = get_current_user_id();
			if( $uid == 0 ) { // usr not logged in
				wtr_error_log(__METHOD__, 
									"user not logged in");
				return false;
			}
			$user_id = $uid;
		} else {
			if( get_user_by('ID', $user_id) === false ){ // unknown usr
				wtr_error_log(__METHOD__, 
									"user unknown id=".$user_id);
				return false;
			}
		}		
		
		$args['user_id'] = $user_id;
		
		$my_activity = new WH_DB_Activity(0, $args);
		
		if( $my_activity->isOk ) {
			if( ! $my_activity->save() ) {
				wtr_error_log(__METHOD__, 
				"action save WH_DB_Activity KO <".print_r($my_activity,true).">");
				return false;
			}
		} else {
			wtr_error_log(__METHOD__, 
							"action new WH_DB_Activity KO");
				return false;
		}
		return true;		
	}
	
	/*********************************
	 ***       Get an activity     ***
	 *********************************/
    public static function getActivity($element, $element_id, $col = "id", $direction = "asc"){		
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Activity::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		$where .= "element=%s";
		$args[] = $element;
		$where .= " AND element_id=%d";
		$args[] = $element_id;
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/*********************************
	 ***       Purge activities    ***
	 *********************************/
	/* Purge done activities 
	   or (seen activities over 30 days)
	*/
    public static function purgeDBActivities($days = 30){
		global $wpdb;
		$result   = false;
		$query    = "DELETE FROM ".$wpdb->prefix.WH_DB_Activity::tableName;
		$where    = " WHERE ";
		$args     = array();
		
		$where .= "action_done = true";
		$where .= " OR ";
		$where .= "(action_seen = true and ";
		$where .= "timestampdiff(DAY,activity_datetime,sysdate()) > %d)";
		$args[] = $days;
		
		$result = wtr_runQuery($query.$where, $args);

		return $result;
	}
}
?>