<?php
/************************************
 **         Activity class         **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_activity.php");

class WH_Activity {
	
	public $id;
	public $user_id;
	public $element;
	public $element_id;
	public $action;
	public $comment;
	public $action_seen;
	public $action_done;
	
	public $isOk;
	
	private $DB_Activity;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'user_id' => int
	*			'element' => string
	*			'element_id' => int
	*			'action' => string
	*			'comment' => string
	*			'action_seen' => boolean
	*			'action_done' => boolean
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id            = isset($args['id'])         ? (int)$args['id']:(int)$id;
		$this->user_id       = isset($args['user_id'])    ? (int)$args['user_id']:0;
		$this->element       = isset($args['element'])    ? (string)$args['element']:'';
		$this->element_id    = isset($args['element_id']) ? (int)$args['element_id']:0;
		$this->action        = isset($args['action'])     ? (string)$args['action']:'';
		$this->comment       = isset($args['comment'])    ? (string)$args['comment']:'';
		$this->action_seen   = isset($args['action_seen'])? (boolean)$args['action_seen']:false;
		$this->action_done   = isset($args['action_done'])? (boolean)$args['action_done']:false;
		
		$this->isOk = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
		} else {
			if( $id != 0 ) 
				$this->get_Activity($id);
			else {
				$this->DB_Activity = new WH_DB_Activity(0, $args);
				$this->isOk = $this->DB_Activity->isOk;
			}
		}
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = $this->DB_Activity->save();
		if( $result ) // copy id
			$this->id = $this->DB_Activity->id;
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$this->updateDB_Object();
		return $this->DB_Activity->delete();
	}
	
	/* Read DB */
	private function getActivity($id) {
		$this->DB_Activity = new WH_DB_Activity($id);
		
		if( $result ) {
			$this->id            = $this->DB_Activity->id         ;
			$this->user_id       = $this->DB_Activity->user_id    ;
			$this->element       = $this->DB_Activity->element    ;
			$this->element_id    = $this->DB_Activity->element_id ;
			$this->action        = $this->DB_Activity->action     ;
			$this->comment       = $this->DB_Activity->comment    ;
			$this->action_seen   = $this->DB_Activity->action_seen;
			$this->action_done   = $this->DB_Activity->action_done;
			$this->isOk = true;
		} else {
			$this->isOk = false;
		}
	}
	
	/* Update DB object */
	private function updateDB_Object() {
		$this->DB_Activity->id          = $this->id          ;
		$this->DB_Activity->user_id     = $this->user_id     ;
		$this->DB_Activity->element     = $this->element     ;
		$this->DB_Activity->element_id  = $this->element_id  ;
		$this->DB_Activity->action      = $this->action      ;
		$this->DB_Activity->comment     = $this->comment     ;
		$this->DB_Activity->action_seen = $this->action_seen ;
		$this->DB_Activity->action_done = $this->action_done ;
	}
	
	/* Get All Activities */
	public static function getAll_Activities($col = "id", $direction = "asc") {
		$myActivities = array();
		$dbActivities  = WH_DB_Activity::getAllDB_Activities($col, $direction);
		
		foreach( $dbActivities as $ac ) {
			$myActivities[] = new WH_Activity(0, get_object_vars($ac));
		}
		                  
		return $myActivities;
	}
	
	/* Get All Activities from an element */
	public static function getAll_ActivitiesByElement($element, $element_id, $col = "id", $direction = "asc") {
		$myActivities = array();

		foreach(WH_DB_Activity::getActivity($element, $element_id, $col, $direction) as $act) {
			$myActivities[] = new WH_Activity(0, get_object_vars($act));
		}
		                  
		return $myActivities;
	}
	
	/* Get All Activities from an element and an action */
	public static function getAll_ActivitiesByElementAction($element, $element_id, $action, $col = "id", $direction = "asc") {
		$myActivities = array();

		foreach(WH_DB_Activity::getActivity($element, $element_id, $col, $direction) as $act) {
			if( $act->action == $action )
				$myActivities[] = new WH_Activity(0, get_object_vars($act));
		}
		                  
		return $myActivities;
	}
	
	/* Get User's activities */
	public static function getAll_ActivitiesByUser($user_id, $col = "id", $direction = "asc") {
		$myActivities = array();
		$dbActivities  = WH_DB_Activity::getAllDB_ActivitiesByUser($user_id, $col, $direction);
		
		foreach( $dbActivities as $ac ) {
			$myActivities[] = new WH_Activity(0, get_object_vars($ac));
		}
		                  
		return $myActivities;
	}
	
	/* Get Book's activities */
	public static function getAll_ActivitiesByBook($book_id, $col = "id", $direction = "asc") {
		$myActivities = array();
		$dbActivities  = WH_DB_Activity::getAllDB_ActivitiesByBook($book_id, $col, $direction);
		
		foreach( $dbActivities as $ac ) {
			$myActivities[] = new WH_Activity(0, get_object_vars($ac));
		}
		               
		return $myActivities;
	}
	
	/* Get Lastest Book's activities */
	public static function getAll_LastestBooksActivities($days) {
		$myActivities = array();
		$dbActivities  = WH_DB_Activity::getAllDB_ActivitiesByElements(
												array('Book','Chapter','Scene'), 
												$days, "activity_datetime", "DESC");
		
		foreach( $dbActivities as $ac ) {
			$myActivities[] = new WH_Activity(0, get_object_vars($ac));
		}
		               
		return $myActivities;
	}

	
	// Change status of a book, a chapter or a scene
    public static function changeStatus($element, $element_id, $new_status){
		$ret     = true;
		$book_id = 0;
		
		// Alert only on book or chapter status changes
		if( strtolower($element) == "scene" )
			return $ret;
		
		// search of book id
		if( strtolower($element) == "book" )
			$book_id = $element_id;
		if( strtolower($element) == "chapter" ) {
			$chapter = new WH_Chapter($element_id);
			$book_id = $chapter->book_id;
		}
		
		// Alert users when status changed (authors, editors, readers)
		$roles = array();
		if( $new_status == WH_Status::DRAFT ) // alert authors
			$roles[] = WTRH_ROLE_AUTHOR;
		//if( $new_status == WH_Status::TRASHED ) // do nothing
		if( $new_status == WH_Status::TOEDIT ) // alert editors
			$roles[] = WTRH_ROLE_EDITOR;
		//if( $new_status == WH_Status::EDITING ) // do nothing
		if( $new_status == WH_Status::EDITED ) // alert authors
			$roles[] = WTRH_ROLE_AUTHOR;
		if( $new_status == WH_Status::TOPUBLISH ) // alert authors
			$roles[] = WTRH_ROLE_AUTHOR;
		if( $new_status == WH_Status::PUBLISHED ) { // alert readers
			$roles[] = WTRH_ROLE_READER;
			$roles[] = WTRH_ROLE_READERP;
		}
		
		foreach(WH_DB_Book::getAllDB_BookUsersId($book_id, $roles) as $user) {
			// Create an activity
			WH_DB_Activity::addActivity(array(  'element'     => $element,
												'element_id'  => $element_id, 
												'book_id'     => $book_id, 
												'action'      => "changeStatus", 
												'comment'     => $new_status, 
												'action_seen' => false,
												'action_done' => false),
										$user->user_id);
		}
		
		return $ret;
	}
	
	
	/* Update status activity */
    public static function updateActivity($id, $args){
		$my_activity = new WH_Activity($id);

		if( $my_activity->isOk ) {

			if( isset($args['action_seen']) && is_bool($args['action_seen']) )
				$my_activity->action_seen = $args['action_seen'];

			if( isset($args['action_done']) && is_bool($args['action_done']) )
				$my_activity->action_done = $args['action_done'];
			
			if( ! $my_activity->save() )
				wtr_error_log(__METHOD__, 
				"action save WH_Activity KO <".print_r($my_activity,true).">");
		} else
			wtr_error_log(__METHOD__, 
							"action new WH_DB_Activity KO");
	}
	
	/*********************************
	 ***       Purge activities    ***
	 *********************************/
	/* Purge done activities 
	   or (seen activities over 30 days)
	*/
	public static function purgeActivities($days = 30) {
		return WH_DB_Activity::purgeDBActivities($days);
	}
}
?>