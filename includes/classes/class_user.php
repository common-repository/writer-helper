<?php
/************************************
 **          User data             **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
if( file_exists(WTRH_INCLUDE_DIR . "/functions/user_functions.php") )
	include_once(WTRH_INCLUDE_DIR . "/functions/user_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_user.php");


/************************************
 **          User class            **
 ************************************/
class WH_User {
	
	public $id;
	public $user_id; // wp user id
	public $wp_user_name;
	
	public $isOk;
	
	public $roles;			/* array */
	public $capabilities;	/* array */
	
	private $DB_User;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'user_id'   => int
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id        = isset($args['id'])       ? (int)$args['id']: (int)$id;
		$this->user_id   = isset($args['user_id'])  ? (int)$args['user_id']:0;
		
		$this->roles        = array();
		$this->capabilities = array();
		
		$this->isOk     = false;
		
		if( ! is_numeric($id) )
			wtr_error_log(__METHOD__, "id incorrect : ".$user_id);
		else {
			// get writer helper user
			$this->get_User($cascade);
			
			// get wp user name
			$this->wp_user_name = WH_User::getWpUserName($this->user_id);
			
			if( $cascade ) {
				$this->readRoles();
				$this->readCapabilities();
			}
		}
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$ret = $this->DB_User->save();
		
		if( $ret )
			$this->id = $this->DB_User->id;
		
		return $ret;
	}
	
	/* Delete object from DB */
	public function delete() {
		$this->updateDB_Object();
		return $this->DB_User->delete();
	}
	
	/* Read DB */
	private function get_User() {
		$this->DB_User = new WH_DB_User($this->id, 
								array('user_id'=>$this->user_id));

		if( $this->DB_User->isOk ) {
			$this->id          = $this->DB_User->id;
			$this->user_id     = $this->DB_User->user_id;

			$this->isOk = true;
			
		} else
			$this->isOk = false;
	}
	
	/* Update DB object */
	private function updateDB_Object() {
		$this->DB_User->id      = $this->id ;
		$this->DB_User->user_id = $this->user_id ;
		
		$this->DB_User->roles_array        = $this->roles;
		$this->DB_User->capabilities_array = $this->capabilities;
	}
	
	/* Get User's roles */
	public function readRoles() {
		$this->roles = array();
		
		if( count($this->DB_User->roles) == 0 ) 
			$this->DB_User->readRoles();
			
		$this->roles = $this->DB_User->roles_array;
	}
	
	// Add a role to the user
	public function addRole($role) {
		
		if( strlen(trim($role)) == 0 ) 
			return false;
		
		if( $role !=  WTRH_ROLE_ADMIN &&
		    $role !=  WTRH_ROLE_AUTHOR &&
		    $role !=  WTRH_ROLE_EDITOR &&
		    $role !=  WTRH_ROLE_READER &&
		    $role !=  WTRH_ROLE_READERP )
			return false;
			
		// if role exists already for user
		$found = false;
		foreach( $this->roles as $i => $r ) 
			if( $r['role'] == $role ) {
				$found = true;
				break;
			}
			
		if( ! $found ) {
			// get books' settings
			$bs = WH_Category::get_BooksSettings();
			
			$mvalue = array();
							  
			if( $role == WTRH_ROLE_AUTHOR ) {
				
				// default values
				$mvalue['meta'] = array('nbBooks'         => -1, 
										  'UseBookworld'  => false, 
										  'UseStoryboard' => false, 
										  'useStatistics' => false, 
										  'useToDoList'   => false);
											  
				// look for authors' books settings
				foreach( $bs as $i => $s ) 
					if( $s->title == "Authors" ){
						$mvalue['meta'] = json_decode($bs[$i]->description, true);
						break;
					}
			}
			
			if( $role == WTRH_ROLE_EDITOR ) {
				
				// default values
				$mvalue['meta'] = array('editAllBooks'  => false, 
										'useStatistics' => false, 
										'useToDoList'   => false);
										
				// look for editors' books settings
				foreach( $bs as $i => $s ) 
					if( $s->title == "Editors" ){
						$mvalue['meta'] = json_decode($bs[$i]->description, true);
						break;
					} 
			}
			
			$this->DB_User->addRole($role, $mvalue);
			$this->roles = $this->DB_User->roles_array;
		} 
		
		return true;
	}
	
	// Delete a role from the user
	public function deleteRole($role) {
		
		$ret = $this->DB_User->deleteRole($role);
		$this->roles = $this->DB_User->roles_array;
		
		return $ret;
	}
	
	/* Update author's role */
	public function updateAuthorRole($args) {
		
		if( ! is_array($args) )
			return false;
		
		if( count($this->roles) == 0 )
			$this->readRoles();
		
		$found = false;
		foreach( $this->roles as $i => $r ) {
			if( $r['role'] == WTRH_ROLE_AUTHOR ) {
				foreach($args as $k => $meta ) 
					$this->roles[$i]['meta'][$k] = $meta;
				
				$found = true;
				break;
			}
		}
		
		if( ! $found )
			wtr_error_log(__METHOD__,"Author role not found for this user (".$this->id.")");
		
		return $found;
	}
	
	/* Update editor's role */
	public function updateEditorRole($args) {
		
		if( ! is_array($args) )
			return false;
		
		if( count($this->roles) == 0 )
			$this->readRoles();
		
		$found = false;
		foreach( $this->roles as $i => $r ) {
			if( $r['role'] == WTRH_ROLE_EDITOR ) {
				foreach($args as $k => $meta ) 
					$this->roles[$i]['meta'][$k] = $meta;
				
				$found = true;
				break;
			}
		}		
		
		if( ! $found )
			wtr_error_log(__METHOD__,"Editor role not found for this user (".$this->id.")");
		
		return $found;
	}
	
	/* Get User's capabilities */
	public function readCapabilities() {
		$this->capabilities = array();
		
		if( $this->DB_User->capabilities == null ) 
			$this->DB_User->readCapabilities();
			
		$this->capabilities = $this->DB_User->capabilities_array;
	}
	
	/* Add User's capabilities */
	public function addCapabilities($meta_array) {
		$this->DB_User->addCapabilities($meta_array);			
		$this->capabilities = $this->DB_User->capabilities_array;
	}
	
	/* Delete User's capabilities */
	public function deleteCapabilities() {
		$this->DB_User->deleteCapabilities();
		$this->capabilities = $this->DB_User->capabilities_array;
	}
	
	
	
	/* Get All User */
	public static function getAll_Users($col = "id", $direction = "asc") {
		$myUsers = array();
		$dbUsers  = WH_DB_User::getAllDB_Users($col, $direction);
		
		foreach( $dbUsers as $role ) {
			$myUsers[] = new WH_User(0, get_object_vars($role));
		}

		return $myUsers;
	}
	
	/* Get All Users for roles */
	public static function getAll_UsersForRoles($roles) {
		return WH_DB_User::getAllDB_UsersAndRoles($roles);
	}
	
	/* Get All Admins */
	public static function getAll_Admins() {
		return WH_DB_User::getAllDB_UsersAndRoles(array(WTRH_ROLE_ADMIN));
	}
	
	/* Get All Authors */
	public static function getAll_Authors() {
		return WH_DB_User::getAllDB_UsersAndRoles(array(WTRH_ROLE_AUTHOR));
	}
	
	/* Get All Editors */
	public static function getAll_Editors() {
		return WH_DB_User::getAllDB_UsersAndRoles(array(WTRH_ROLE_EDITOR));
	}
	
	/* Get All Readers */
	public static function getAll_Readers() {
		return WH_DB_User::getAllDB_UsersAndRoles(array(WTRH_ROLE_READER));
	}
	
	/* Get All Readers Premium */
	public static function getAll_ReadersPremium() {
		return WH_DB_User::getAllDB_UsersAndRoles(array(WTRH_ROLE_READERP));
	}
	
	
	
	/* return true if user exists in this role */
	public static function userExists($user_id, $role) {
		
		if( $user_id == 0 )
			return false;
		if( $role == null || $role == "" )
			return false;
		if( $role != WTRH_ROLE_READER  &&
            $role != WTRH_ROLE_READERP &&
            $role != WTRH_ROLE_AUTHOR  &&
            $role != WTRH_ROLE_EDITOR  &&
            $role != WTRH_ROLE_ADMIN )
			return false;
		
		$user = new WH_User(0, array('user_id'=>$user_id));
		$user->readRoles();
		
		$found = false;
		foreach( $user->roles as $r ) {
			if( $r['role'] == $role ) {
				$found = true;
				break;
			}
		}
		
		return $found;
	}


	/* Return true if action is authorized 
	   $action : 
				WTRH_CAP_LIST_BOOKS        : display books' list
				WTRH_CAP_MANAGE_BOOKS      : create/update/delete books, change status
				WTRH_CAP_LIST_BOOKWORLDS   : display bookworlds' list
				WTRH_CAP_MANAGE_BOOKWORLDS : create/update/delete bookworlds
				WTRH_CAP_EDITING_BOOKS     : editing books
				WTRH_CAP_READ_BOOKS        : read books
				WTRH_CAP_WRITE_TODO        : write in ToDoList
				WTRH_CAP_CHECK_TODO        : chech ToDoList
	*/
	public static function isAuthorized_action($action, $user_id = 0) {
							
		$ret      = false;
		
		if( $user_id == 0 )
			$user_id = get_current_user_id();		
		if( $user_id == 0 )
			return false;
		
		$usr = new WH_User(0, array('user_id'=>$user_id));
		$usr->readRoles();
		
		$isAdmin  = false;
		$isAuthor = false;
		$isEditor = false;
		$isReader = false;
		
		foreach( $usr->roles as $role ) {
			if( $role['role'] == WTRH_ROLE_ADMIN ) {
				$isAdmin  = true;
				$isAuthor = true;
				$isEditor = true;
				$isReader = true;
				break;
			}
			if( $role['role'] == WTRH_ROLE_AUTHOR ){
				$isAuthor = true;
				$isEditor = true;
				$isReader = true;
			}
			if( $role['role'] == WTRH_ROLE_EDITOR ){
				$isEditor = true;
			}
			if( $role['role'] == WTRH_ROLE_READER 
			 || $role['role'] == WTRH_ROLE_READERP ){
				$isReader = true;
			}
		}
		
		// if admin, user is authorized
		if( $isAdmin )
			$ret = true;
		
		else {
			// if author
			if( $isAuthor && 
			   ($action == WTRH_CAP_LIST_BOOKS        ||
			    $action == WTRH_CAP_MANAGE_BOOKS      ||
			    $action == WTRH_CAP_LIST_BOOKWORLDS   ||
			    $action == WTRH_CAP_MANAGE_BOOKWORLDS ||
			    $action == WTRH_CAP_EDITING_BOOKS     ||
			    $action == WTRH_CAP_READ_BOOKS        ||
			    $action == WTRH_CAP_WRITE_TODO        ||
			    $action == WTRH_CAP_CHECK_TODO        ) ) {
				$ret = true;
			
			// if editor
			} else if ( $isEditor && 
			   ($action == WTRH_CAP_LIST_BOOKS        ||
			    $action == WTRH_CAP_EDITING_BOOKS     ||
			    $action == WTRH_CAP_CHECK_TODO        )) {
				$ret = true;
				
			} else { // if reader
				if( $action == WTRH_CAP_READ_BOOKS )
					$ret = true;
			}
		}
		
		return $ret;

	}
		
	
	/* Return true if user can see a WriterHelper page on Admin Dashboard */
	public static function isAuthorizedOnPage($wtr_page, $user_id = 0) {
		
		global $wtr_pages_capabilities;
		$ret = false;
		
		// page not found
		if( ! isset($wtr_pages_capabilities[$wtr_page]) )
			return false;
		
		// get user id
		if( $user_id == 0 )
			$user_id = get_current_user_id();
		if( $user_id == 0 )
			return false;
		
		// get user roles
		$usr = new WH_User(0, array('user_id'=>$user_id));
		$usr->readRoles();
		
		// compare user roles and page roles
		foreach( $usr->roles as $role )
			if( in_array($role['role'], $wtr_pages_capabilities[$wtr_page]) ) {
				$ret = true;
				break;
			}
		
		return $ret;
	}
	
	
	/* Return true if user can see WriterHelper Page on Admin Dashboard */
	public static function isAuthorizedOnWHdashboard($user_id = 0) {
wtr_info_log(__METHOD__, "user_id = $user_id");
		
		$ret = false;
		if( $user_id == 0 )
			$user_id = get_current_user_id();
		if( $user_id == 0 )
			return false;
		
		$roles = array(WTRH_ROLE_AUTHOR, WTRH_ROLE_EDITOR, WTRH_ROLE_ADMIN);
		// get user roles
		$usr = new WH_User(0, array('user_id'=>$user_id));
		$usr->readRoles();
wtr_info_log(__METHOD__, "user_id = $user_id  /  user_roles = ".print_r($usr->roles, true));

		foreach( $usr->roles as $role )
			if( in_array($role['role'], $roles) ) {
				$ret = true;
				break;
			}
		
		return $ret;
	}

	/* Get WP User name */
	public static function getWpUserName($user_id) {
		$wp_name = "";
		
		if( ! function_exists('get_userdata') )
			return ""; 
		
		if( $user_id == 0 )
			return "";
		
		// get wp user name
		$user      = get_userdata($user_id);
		$user_data = ($user !== false)?$user->data:null;
		
		if( $user_data != null )
			if( strlen(trim($user_data->display_name)) > 0 )
				$wp_name = $user_data->display_name;
			else
				$wp_name = $user_data->user_nicename;
		
		return $wp_name;
	}

}
?>