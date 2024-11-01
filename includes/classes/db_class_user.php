<?php
/************************************
 **      User class      **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_activity.php");

class WH_DB_User {
	// Metadata access keys
	const meta_obj_usr  = "User";
		
	const meta_key_cap  = "capabilities";
	const meta_key_author = WTRH_ROLE_AUTHOR;
	const meta_key_editor = WTRH_ROLE_EDITOR;
	const meta_key_reader = WTRH_ROLE_READER;
	const meta_key_readerp= WTRH_ROLE_READERP;
	
	// DB info
	const tableName = "wtr_users";
	const createReq = "CREATE TABLE IF NOT EXISTS `%1\$s".WH_DB_User::tableName.
		   "` \n(".
	       "`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,\n".
	       "`user_id` bigint(20) unsigned NOT NULL,\n".
		   "INDEX(user_id));\n";

	const tableCols= 'id, user_id';
    const selectBaseReq = "SELECT ".WH_DB_User::tableCols.
							  " FROM %s".WH_DB_User::tableName;
    const selectReq  = "SELECT ".WH_DB_User::tableCols.
							  " FROM %s".WH_DB_User::tableName.
							  " WHERE id=%s";
    const selectUReq = "SELECT ".WH_DB_User::tableCols.
							  " FROM %s".WH_DB_User::tableName.
							  " WHERE user_id=%s";
    const deleteBUReq= "DELETE  FROM %s".WH_DB_User::tableName.
							  " WHERE book_id=%s";
	
	public $id;
	public $user_id;
	
	public $roles;			/* array( WH_DB_Metadata) */
	public $roles_array;	/* array( array(role, meta)) */
	public $capabilities;	/* WH_DB_Metadata */
	public $capabilities_array;
	
	public $isOk;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'user_id'   => int
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id        = isset($args['id'])       ? (int)$args['id']: (int)$id;
		$this->user_id   = isset($args['user_id'])  ? (int)$args['user_id']:0;

		$this->roles        = array();
		$this->roles_array  = array();
		$this->capabilities = null;
		$this->capabilities_array = array();
		
		$this->isOk = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
		} else {
			$this->isOk = $this->getDB_User();
			
			if( $cascade ) { // read metadata
				$this->readRoles();
				$this->readCapabilities();
			}
		}
	}

	/* Update DB */
	public function save() {
		$result = false;
		
		// save metadata
		foreach( $this->roles as $i => $r ) {
			$this->roles[$i]->meta_value = json_encode($this->roles_array[$i]['meta']);
			$this->roles[$i]->save();
		}
		if( $this->capabilities != null ) {
			$this->capabilities->meta_value = json_encode($this->capabilities_array);
			$this->capabilities->save();
		}
		
		// save user
		if( $this->id == 0 ) { $result = $this->insertDB_User(); }
		else {                 $result = $this->updateDB_User();	}
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		
		if( $this->id == 0 )
			return true;
		
		// delete metadata
		if( $this->roles == null )
			$this->readRoles();
		foreach( $this->roles as $r )
			$r->delete();
		$this->roles = array();
		$this->roles_array  = array();
		
		if( $this->capabilities == null )
			$this->readCapabilities();
		if( $this->capabilities != null )
			$this->capabilities->delete();
		$this->capabilities = null;
		$this->capabilities_array = array();

		// delete all metadatas
		WH_DB_Metadata::deleteDB_ObjectMetadatas(WH_DB_User::meta_obj_usr, $this->id);
		
		// delete object
		return $this->deleteDB_User();
	}
	
	/* Read DB */
	private function getDB_User() {
		global $wpdb;
		
		// if existing user
		if( $this->id != 0 ) {
			
			$query = sprintf(WH_DB_User::selectReq, $wpdb->prefix, $this->id);
			$result = wtr_getRow($query, null, ARRAY_A);
			
			if( $result ) {
				$this->id        = $result['id'];
				$this->user_id   = $result['user_id'];
				return true;
			} else {
				wtr_error_log(__METHOD__, "<".$wpdb->last_query."> : ".$wpdb->last_error);
			}
			
		} else {
			
			// new user ?
			if( $this->user_id != 0 ) {
				$query = sprintf(WH_DB_User::selectUReq, 
								 $wpdb->prefix, $this->user_id);
				$result = wtr_getRow($query, null, ARRAY_A);
				if( $result ) {
					$this->id        = $result['id'];
					$this->user_id   = $result['user_id'];
				}
				return true;
			}
		}
		return false;
	}
	
	/* Insert into DB */
	private function insertDB_User() {
		global $wpdb;
		$ret = false;
		$result = wtr_setRow($wpdb->prefix . WH_DB_User::tableName, 
								"insert",
								array('user_id'   => $this->user_id) );
		if( $result !== false ) {
			$this->id = $wpdb->insert_id;
			$ret = true;
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'action'      => "insert", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		} else {
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
	private function updateDB_User() {
		global $wpdb;
		$ret = true;
		$result = wtr_setRow($wpdb->prefix . WH_DB_User::tableName, 
								"update",
								array('user_id'   => $this->user_id),
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
	private function deleteDB_User() {
		global $wpdb;
		$ret = true;
		
		$result = wtr_deleteRow($wpdb->prefix . WH_DB_User::tableName, 
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
	
	
	/* Read user roles */
	public function readRoles() {
		
		if( ! is_numeric($this->id) )
			return array();
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_User::meta_obj_usr, 
							'obj_id'   => $this->id,
							'user_id'  => $this->user_id
						));

		$this->roles       = array();
		$this->roles_array = array();
		foreach( $result as $role ) {
			if( $role->meta_key != WH_DB_User::meta_key_cap ) {
				$this->roles[]       = new WH_DB_Metadata($role->meta_id);
				$this->roles_array[] = array('role' => $role->meta_key,
											 'meta' => json_decode($role->meta_value, true)
											 );
			}
		}
	}
	
	/* Read user capabilities */
	public function readCapabilities() {
		
		if( ! is_numeric($this->id) )
			return array();
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_User::meta_obj_usr, 
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_User::meta_key_cap
						));

		$this->capabilities       = null;
		$this->capabilities_array = array();
		if( isset($result[0]) ) {
			$this->capabilities       = new WH_DB_Metadata($result[0]->meta_id);
			$this->capabilities_array = json_decode($result[0]->meta_value, true);
			// no capabilities found
			if( ! is_array($this->capabilities_array) )
				$this->capabilities_array = array();
		}
	}
	
	
	/* Add user role */
	public function addRole($role, $meta_array) {
		
		if( $role !=  WTRH_ROLE_ADMIN &&
		    $role !=  WTRH_ROLE_AUTHOR &&
		    $role !=  WTRH_ROLE_EDITOR &&
		    $role !=  WTRH_ROLE_READER &&
		    $role !=  WTRH_ROLE_READERP )
			return false;
			
		$new_meta = new WH_Metadata(0,
						array(
							'meta_obj' => WH_DB_User::meta_obj_usr, 
							'obj_id'   => $this->id,
							'user_id'  => $this->user_id,
							'meta_key' => $role,
							'meta_value' => json_encode($meta_array)
						));
		$new_meta->save();
		
		$this->roles[]       = $new_meta;
		$this->roles_array[] = array('role' => $role,
									 'meta' => $meta_array);
		return true;
	}
	
	/* Add user capabilities */
	public function addCapabilities($meta_array) {
		
		if( $this->capabilities == null )
			$this->capabilities = new WH_Metadata(0, array(
									'meta_obj' => WH_DB_User::meta_obj_usr, 
									'obj_id'   => $this->id,
									'user_id'  => $this->user_id,
									'meta_key' => WH_DB_User::meta_key_cap
									));
		
		$this->capabilities->meta_value = json_encode($meta_array);
		$this->capabilities_array       = $meta_array;
		return true;
	}
	
	
	/* Delete user role */
	public function deleteRole($role) {
		
		if( $role !=  WTRH_ROLE_ADMIN &&
		    $role !=  WTRH_ROLE_AUTHOR &&
		    $role !=  WTRH_ROLE_EDITOR &&
		    $role !=  WTRH_ROLE_READER &&
		    $role !=  WTRH_ROLE_READERP )
			return false;
			
		// if role exists already for user
		$found = false;
		foreach( $this->roles_array as $i => $r ) {
			if( $r['role'] == $role ) {
				$this->DB_User->roles[$i]->delete();
				unset($this->roles[$i]);
				unset($this->roles_array[$i]);
				$found = true;
				break;
			}
		}
		return true;
	}
	
	/* Delete user capabilities */
	public function deleteCapabilities() {
		
		$this->capabilities->meta_value = array();
		$this->capabilities_array       = array();
		return true;
	}
	
	
	
	/* Get All DB */
	public static function getAllDB_Users($col = "user_id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_User::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE user_id > 0";
		$order_by = " ORDER BY ".$col." ".$direction;
		
		$result = wtr_getResults($query.$where.$order_by);		
		
		return $result;
	}
	
	
		/* Get All DB users & roles */
	public static function getAllDB_UsersAndRoles($roles = array(),
										$col = "id", $direction = "asc") {
		global $wpdb;
		
		$result   = false;
		$query    = "SELECT id, meta_key, meta_value, b.user_id ".
					"FROM ".$wpdb->prefix.WH_DB_User::tableName." a,".
					$wpdb->prefix.WH_DB_Metadata::tableName." b";
		$where    = " WHERE a.user_id > 0 ".
		            "   AND a.id=b.obj_id".
		            "   AND b.meta_obj='".WH_DB_User::meta_obj_usr."'";
				
		if( count($roles) > 0 ) {
			$where .= " AND b.meta_key in (";
			foreach( $roles as $i => $role ) {
				if( $i > 0 )
					$where .= ",";				
				$where .= "'".$role."'";
			}
			$where .= ")";
		}
		$order_by = " ORDER BY ".$col." ".$direction;
		
		$result = wtr_getResults($query.$where.$order_by);
		
		
		return $result;
	}
	
}
?>