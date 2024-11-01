<?php
/************************************
 **      Category class      **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_activity.php");

class WH_DB_Category {
	const tableName = "wtr_category";
	const createReq = "CREATE TABLE IF NOT EXISTS `%s".WH_DB_Category::tableName."` (".
	       "`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,".
	       "`element` text NOT NULL,".
	       "`number` bigint(20) NOT NULL,".
	       "`title` text NOT NULL,".
	       "`description` text NOT NULL,".
	       "`parent_id` bigint(20) unsigned NOT NULL)";

	const tableCols= 'id, element, number, title, description, parent_id';
    const selectBaseReq= "SELECT ".WH_DB_Category::tableCols.
							  " FROM %s".WH_DB_Category::tableName;
    const selectParReq= "SELECT ".WH_DB_Category::tableCols.
							  " FROM %s".WH_DB_Category::tableName.
							  " WHERE parent_id=%s";
    const selectReq= "SELECT ".WH_DB_Category::tableCols.
							  " FROM %s".WH_DB_Category::tableName.
							  " WHERE id=%s";
	
	public $id;
	public $element;
	public $number;
	public $title;
	public $description;
	public $parent_id;
	
	public $isOk;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'element' => string
	*			'number' => int
	*			'title' => string
	*			'description' => string
	*			'parent_id' => int
	**/
    public function __construct($id, $args = array())    {
		$this->id          = isset($args['id'])         ? (int)$args['id']: (int)$id;
		$this->element     = isset($args['element'])    ? (string)$args['element']:'';
		$this->number      = isset($args['number'])     ? (int)$args['number']:0;
		$this->title       = isset($args['title'])      ? (string)$args['title']:'';
		$this->description = isset($args['description'])? (string)$args['description']:'';
		$this->parent_id   = isset($args['parent_id'])  ? (int)$args['parent_id']:0;
		
		$this->isOk = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
		} else {
			if( $id != 0 )
				$this->isOk = $this->getDB_Category($id);
			else
				$this->isOk = true;
		}
	}

	/* Update DB */
	public function save() {
		$result = false;
		
		if( $this->id == 0 ) { $result = $this->insertDB_Category(); }
		else {                 $result = $this->updateDB_Category(); }
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		
		if( $this->id == 0 )
			return true;

		return $this->deleteDB_Category();
	}
	
	/* Read DB */
	private function getDB_Category($id) {
		global $wpdb;
		
		$result = $wpdb->get_row(sprintf(WH_DB_Category::selectReq, $wpdb->prefix, $id), ARRAY_A);
		
		if( $result ) {
			$this->id           = $id;
			$this->element      = $result['element'];
			$this->number       = $result['number'];
			$this->title        = $result['title'];
			$this->description  = $result['description'];
			$this->parent_id    = $result['parent_id'];
			return true;
		} else {
			wtr_error_log(__METHOD__, "<".$wpdb->last_query."> : ".$wpdb->last_error);
		}
		return false;
	}
	
	/* Insert into DB */
	private function insertDB_Category() {
		global $wpdb;
		$ret = false;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Category::tableName, 
								"insert",
								array('element'     => $this->element,
									  'number'      => $this->number     , 
	                                  'title'       => $this->title,
									  'description' => $this->description,
									  'parent_id'   => $this->parent_id) );
		if( $result !== false ) {
			$ret = true;
			$this->id = $wpdb->insert_id;
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'action'      => "insert", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		} else {
			wtr_error_log(__METHOD__, "error on insert: ".$wpdb->last_error);
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'action'      => "insert", 
												'comment'     => $comment));
		}
		return $ret;
	}
	
	/* Update DB */
	private function updateDB_Category() {
		global $wpdb;
		$ret = true;

		$result = wtr_setRow($wpdb->prefix . WH_DB_Category::tableName, 
								"update",
		                        array('element'     => $this->element,
									  'number'      => $this->number     , 
	                                  'title'       => $this->title,
									  'description' => $this->description,
									  'parent_id'   => $this->parent_id),
								array('id' => $this->id) );
		if( $result === false ) {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'action'      => "update", 
												'comment'     => $comment));
			$ret = false;
		} else { 
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'action'      => "update", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		}
		return $ret;
	}
	
	/* Delete DB */
	private function deleteDB_Category() {
		global $wpdb;
		$ret = true;
		
		$result = wtr_deleteRow($wpdb->prefix . WH_DB_Category::tableName, 
								array('id' => $this->id) );
		if( $result === false ) {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'action'      => "delete", 
												'comment'     => $comment));
			$ret = false;
		} else {
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'action'      => "delete", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		}
		return $ret;
	}
	


	/* Get All DB */
	public static function getAllDB_Categories($element = "", $col = "element", $direction = "asc") {
		global $wpdb;
		$result   = array();
		$query    = sprintf(WH_DB_Category::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		if( trim($element) != "" ) {
			$where .= "element='%s'";
			$args[] = $element;
		} 
		
		if( count($args) == 0 )
			$where = "";
		
//wtr_info_log(__METHOD__,"query:".$query.$where.$order_by);
		$result = wtr_getResults($query.$where.$order_by, $args);
//wtr_info_log(__METHOD__,"result:".print_r($result,true));

		return $result;
	}
	
	/* Get one category */
	public static function getDB_CategoryByTitle($element, $title, $col = "element", $direction = "asc") {
		global $wpdb;
		$result   = array();
		$query    = sprintf(WH_DB_Category::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		if( trim($element) == "" ) 
			return false;
		
		if( trim($title) == "" ) 
			return false;
			
		$where .= "element='%s'";
		$args[] = $element;
		 
		$where .= " AND title='%s'";
		$args[] = $title;
		
		$result = wtr_getResults($query.$where.$order_by, $args);
//wtr_info_log(__METHOD__,"result:".print_r($result,true));

		return $result;
	}
	
	/* Get All DB */
	public static function getAllDB_CategoriesByParent($parent_id) {
		global $wpdb;		
		$result   = false;
		$query    = sprintf(WH_DB_Category::selectParReq, $wpdb->prefix, $parent_id);
		$args     = array();
		
		$result = wtr_getResults($query, $args);

		return $result;
	}
}
?>