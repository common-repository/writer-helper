<?php
/************************************
 **        DB Book class           **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_activity.php");

class WH_DB_Metadata {
	const tableName = "wtr_metadata";
	const createReq = "CREATE TABLE IF NOT EXISTS `%s".WH_DB_Metadata::tableName."` (".
	       "`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,".
	       "`obj_id` bigint(20) unsigned NOT NULL,".
	       "`meta_obj` varchar(50) NOT NULL,".
	       "`meta_key` text NOT NULL,".
	       "`meta_value` text NOT NULL,".
	       "`user_id` bigint(20) NOT NULL DEFAULT 0,\n".
		   "INDEX(obj_id),\n".
		   "INDEX(meta_obj),\n".
		   "INDEX(user_id));";

	const tableCols= 'meta_id, obj_id, meta_obj, meta_key, meta_value, user_id';
	const selectBaseReq= "SELECT ".WH_DB_Metadata::tableCols.
	                             " FROM %s".WH_DB_Metadata::tableName;
	const selectByObj  = "SELECT ".WH_DB_Metadata::tableCols.
	                             " FROM %1\$s".WH_DB_Metadata::tableName.
								 " WHERE obj_id = %2\$s".
								 " AND meta_obj = '%3\$s'";
	const selectByObjKey= "SELECT ".WH_DB_Metadata::tableCols.
	                             " FROM %1\$s".WH_DB_Metadata::tableName.
								 " WHERE obj_id =  %2\$s".
								 " AND meta_obj = '%3\$s'".
								 " AND meta_key = '%4\$s'";
	const selectByUsrObj= "SELECT ".WH_DB_Metadata::tableCols.
	                             " FROM %1\$s".WH_DB_Metadata::tableName.
								 " WHERE user_id = %2\$s".
								 "  AND meta_obj = '%3\$s'";
	const selectByUOI   = "SELECT ".WH_DB_Metadata::tableCols.
	                             " FROM %1\$s".WH_DB_Metadata::tableName.
								 " WHERE user_id = %2\$s".
								 "  AND obj_id   = %3\$s".
								 "  AND meta_obj = '%4\$s'";
	const selectByOUK  = "SELECT ".WH_DB_Metadata::tableCols.
	                             " FROM %1\$s".WH_DB_Metadata::tableName.
								 " WHERE obj_id = %2\$s".
								 " AND meta_obj = '%3\$s'".
								 " AND meta_key = '%4\$s'".
								 " AND user_id  = %4\$s";
	const selectReq   = "SELECT ".WH_DB_Metadata::tableCols.
	                             " FROM %s".WH_DB_Metadata::tableName.
								 " WHERE meta_id=%s";
	const deleteOReq  = "DELETE    FROM %1\$s".WH_DB_Metadata::tableName.
								 " WHERE obj_id = %2\$s".
								 " AND meta_obj = '%3\$s'";
	const deleteOKReq = "DELETE    FROM %1\$s".WH_DB_Metadata::tableName.
								 " WHERE obj_id = %2\$s".
								 " AND meta_obj = '%3\$s'".
								 " AND meta_key = '%4\$s'";
	
	public $id;
	public $obj_id;
	public $meta_obj;
	public $meta_key;
	public $meta_value;
	public $user_id;
	
	public $isOk;
	/**
	* Class constructor 
	*  $args : array()
	*			'obj_id'     => integer
	*			'meta_obj'   => string
	*			'meta_key'   => string
	*			'meta_value' => string
	*			'user_id'    => integer
	**/
    public function __construct($id, $args = array())    {
		$this->id          = isset($args['id'])          ? (int)$args['id']:(int)$id;
		$this->obj_id      = isset($args['obj_id'])      ? (int)$args['obj_id']:0;
		$this->meta_obj    = isset($args['meta_obj'])    ? (string)$args['meta_obj']:'';
		$this->meta_key    = isset($args['meta_key'])    ? (string)$args['meta_key']:'';
		$this->meta_value  = isset($args['meta_value'])  ? (string)$args['meta_value']:'';
		$this->user_id     = isset($args['user_id'])     ? (int)$args['user_id']:0;
		
		$this->isOk = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
		} else {
			if( $id != 0 )
				$this->isOk = $this->getDB_Metadata($id);
			else
				$this->isOk = true;
		}
		return $this->isOk;
	}

	/* Update DB */
	public function save() {
		$result = false;
		
		if( $this->id == 0 ) { $result = $this->insertDB_Metadata(); }
		else {                 $result = $this->updateDB_Metadata(); }
		 
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		
		if( $this->id == 0 )
			return true;

		return $this->deleteDB_Metadata();
	}
	
	/* Read DB */
	private function getDB_Metadata($id) {
		global $wpdb;
		
		$result = $wpdb->get_row(sprintf(WH_DB_Metadata::selectReq, $wpdb->prefix, $id), ARRAY_A);
		
		if( $result ) {
			$this->id              = $id;
			$this->obj_id          = $result['obj_id'];
			$this->meta_obj        = $result['meta_obj'];
			$this->meta_key        = $result['meta_key'];
			$this->meta_value      = $result['meta_value'];
			$this->user_id         = $result['user_id'];
			$this->isOk = true;
		} else {
			wtr_error_log(__METHOD__, "<".$wpdb->last_query."> : ".$wpdb->last_error);
			$this->isOk = false;
		}
		return $this->isOk;
	}
	
	/* Insert into DB */
	private function insertDB_Metadata() {
		global $wpdb;
		$ret = false;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Metadata::tableName, 
								"insert",
		                        array('obj_id'       => $this->obj_id, 
		                              'meta_obj'     => $this->meta_obj,
		                              'meta_key'     => $this->meta_key, 
		                              'meta_value'   => $this->meta_value, 
		                              'user_id'      => $this->user_id) );
		if( $result !== false ) {
			$this->id = $wpdb->insert_id;
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => 0, 
												'action'      => "insert", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
			$ret = true;
		} else {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => 0, 
												'action'      => "insert", 
												'comment'     => $comment));
		}
		return $ret;
	}
	
	/* Update DB */
	private function updateDB_Metadata() {
		global $wpdb;
		$ret = true;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Metadata::tableName, 
								"update",
								array('obj_id'        => $this->obj_id, 
		                              'meta_obj'      => $this->meta_obj, 
		                              'meta_key'      => $this->meta_key, 
		                              'meta_value'    => $this->meta_value, 
		                              'user_id'       => $this->user_id),
								array('meta_id' => $this->id) );
		if( $result === false ) {
			wtr_error_log(__METHOD__, "Update METADATA error ".$wpdb->last_error);
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => 0, 
												'action'      => "update", 
												'comment'     => $comment));
			$ret = false;
		} else {
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => 0, 
												'action'      => "update", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		}
		return $ret;
	}
	
	/* Delete DB */
	private function deleteDB_Metadata() {
		global $wpdb;
		$ret = true;
		$result = wtr_deleteRow($wpdb->prefix . WH_DB_Metadata::tableName, array('meta_id' => $this->id) );
		if( $result === false ) {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => 0, 
												'action'      => "delete", 
												'comment'     => $comment));
			$ret = false;
		} else {
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => 0, 
												'action'      => "delete", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		}
		return $ret;
	}
	
	
	
	/* Get All DB Metadatas for an array of args
	*  $args : array()
	*			'obj_id'     => integer
	*			'meta_obj'   => string
	*			'meta_key'   => string
	*			'user_id'    => integer
	*/
	public static function getAllDB_Metadatas($args = array(),
										  $col = "meta_id", $direction = "asc") {
		global $wpdb;
		
		if( count($args) == 0 )
			return array();
		
		$result   = false;
		$query    = sprintf(WH_DB_Metadata::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		
		if( isset($args['obj_id']) )
			$where .= " obj_id=".$args['obj_id'];
		
		if( isset($args['meta_obj']) ){
			if( strlen(trim($where)) > 5 )
				$where .= " AND ";		
			$where .= " meta_obj='".$args['meta_obj']."'";
		}
		
		if( isset($args['meta_key']) ){
			if( strlen(trim($where)) > 5 )
				$where .= " AND ";		
			$where .= " meta_key='".$args['meta_key']."'";
		}
		
		if( isset($args['user_id']) ) {
			if( strlen(trim($where)) > 5 )
				$where .= " AND ";		
			$where .= " user_id=".$args['user_id'];
		}
		
		$result = wtr_getResults($query.$where.$order_by);

		return $result;
	}
	
	/* Delete Metadatas for an object
	*/
	public static function deleteDB_ObjectMetadatas($meta_obj, $obj_id) {
		global $wpdb;
		
		if( ! is_numeric($obj_id) )
			return false;
		if( $obj_id == 0 )
			return false;
		if( strlen($meta_obj) == 0 )
			return false;
		
		$query    = sprintf(WH_DB_Metadata::deleteOReq, 
							$wpdb->prefix, 
							$obj_id, 
							$meta_obj);
		
		return wtr_runQuery($query);
	}
	
	/* Delete Metadatas for an object and a key
	*/
	public static function deleteDB_ObjectKeyMetadatas($meta_obj, $obj_id, $meta_key) {
		global $wpdb;
		
		if( ! is_numeric($obj_id) )
			return false;
		if( $obj_id == 0 )
			return false;
		if( strlen($meta_obj) == 0 )
			return false;
		
		$query    = sprintf(WH_DB_Metadata::deleteOKReq, 
							$wpdb->prefix, 
							$obj_id, 
							$meta_obj,
							$meta_key);
		
		return wtr_runQuery($query);
	}
	
	
	public static function metadataTableExists() {
		global $wpdb;
		return wtr_runQuery(sprintf(WH_DB_Metadata::selectBaseReq, $wpdb->prefix));
	}
}
?>