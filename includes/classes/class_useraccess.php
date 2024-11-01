<?php
/************************************
 **      Users Access class       **
 ************************************/

class WH_UsersAccess {
	
	const OBJECT_BOOK      = 'WH_Book';
	const OBJECT_BOOK_POST = 'WP_Post';
	const OBJECT_BOOK_COVER= 'WP_UploadMedia';
	const OBJECT_STORYBOARD= 'WH_Storyboard';
	const OBJECT_BOOKWORLD = 'WH_Bookworld';
	const OBJECT_TODOLIST  = 'WH_ToDoList';
	const OBJECTS = array(WH_UsersAccess::OBJECT_BOOK,
						  WH_UsersAccess::OBJECT_STORYBOARD,
						  WH_UsersAccess::OBJECT_BOOKWORLD,
						  WH_UsersAccess::OBJECT_TODOLIST);
	
	const MENU_BOOK        = 'Book';
	const MENU_BOOKWORLD   = 'Bookworld';
	const MENU_TODOLIST    = 'ToDoList';
	const MENU_STATISTICS  = 'Statistics';
	const MENU_USERS       = 'Users';
	const MENU_COMMUNITIES = 'Communities';
	const MENU_PARAMETERS  = 'Parameters';
	const MENUS = array(WH_UsersAccess::MENU_BOOK,
						WH_UsersAccess::MENU_BOOKWORLD,
						WH_UsersAccess::MENU_TODOLIST,
						WH_UsersAccess::MENU_STATISTICS,
						WH_UsersAccess::MENU_USERS,
						WH_UsersAccess::MENU_COMMUNITIES,
						WH_UsersAccess::MENU_PARAMETERS);
	
	const PAGE_BOOK_GEN          = 'book_general'   ;
	const PAGE_BOOK_CHAPT        = 'book_chapters'  ;
	const PAGE_BOOK_SB           = 'book_storyboard';
	const PAGE_BOOK_SETTINGS     = 'book_settings'  ;
	const PAGE_BOOK_USR          = 'book_users'     ;	
	const PAGE_BOOKW_GEN         = 'bw_general'   ;
	const PAGE_BOOKW_BOOKS       = 'bw_books'     ;
	const PAGE_BOOKW_CHARAC      = 'bw_characters';
	const PAGE_BOOKW_WEB         = 'bw_web'       ;
	const PAGE_BOOKW_STORIES     = 'bw_stories'   ;	
	const PAGE_TODOLIST          = 'ToDoList';
	const PAGE_STATISTICS        = 'Statistics';	
	const PAGE_USERS_ADM         = 'users_admin'  ;
	const PAGE_USERS_WRITEDIT    = 'users_writers_editors'    ;
	const PAGE_USERS_GROUPS      = 'users_groups' ;
	const PAGE_USERS_READERS     = 'users_readers';	
	const PAGE_COMMUNITIES_ALL   = 'communities_all'        ;
	const PAGE_COMMUNITIES_GUEST = 'communities_guests'     ;
	const PAGE_COMMUNITIES_SUBSC = 'communities_subscribers';	
	const PAGE_PARAM_GEN         = 'settings_general'  ;
	const PAGE_PARAM_BOOKS       = 'settings_books'    ;
	const PAGE_PARAM_BOOKS_USR   = 'settings_books_usr';
	const PAGE_PARAM_ARCS        = 'settings_arcs'     ;
	const PAGE_PARAM_ARCHETYPS   = 'settings_archetyps';
	const PAGES = array(WH_UsersAccess::PAGE_BOOK_GEN         ,
						WH_UsersAccess::PAGE_BOOK_CHAPT       ,
						WH_UsersAccess::PAGE_BOOK_SB          ,
						WH_UsersAccess::PAGE_BOOK_SETTINGS    ,
						WH_UsersAccess::PAGE_BOOK_USR         ,
						WH_UsersAccess::PAGE_BOOKW_GEN        ,
						WH_UsersAccess::PAGE_BOOKW_BOOKS      ,
						WH_UsersAccess::PAGE_BOOKW_CHARAC     ,
						WH_UsersAccess::PAGE_BOOKW_WEB        ,
						WH_UsersAccess::PAGE_BOOKW_STORIES    ,
						WH_UsersAccess::PAGE_TODOLIST         ,
						WH_UsersAccess::PAGE_STATISTICS       ,
						WH_UsersAccess::PAGE_USERS_ADM        ,
						WH_UsersAccess::PAGE_USERS_WRITEDIT   ,
						WH_UsersAccess::PAGE_USERS_GROUPS     ,
						WH_UsersAccess::PAGE_USERS_READERS    ,
						WH_UsersAccess::PAGE_COMMUNITIES_ALL  ,
						WH_UsersAccess::PAGE_COMMUNITIES_GUEST,
						WH_UsersAccess::PAGE_COMMUNITIES_SUBSC,
						WH_UsersAccess::PAGE_PARAM_GEN        ,
						WH_UsersAccess::PAGE_PARAM_BOOKS      ,
						WH_UsersAccess::PAGE_PARAM_BOOKS_USR  ,
						WH_UsersAccess::PAGE_PARAM_ARCS       ,
						WH_UsersAccess::PAGE_PARAM_ARCHETYPS  
					);
	
	const ACTION_CREATE 		= "create";
	const ACTION_UPDATE 		= "update";
	const ACTION_DELETE 		= "delete";
	const ACTION_SEE			= "see";
	const ACTION_READ			= "read";
	const ACTION_READ_PREVIEW	= "readPreview";
	const ACTION_READ_HIDDEN	= "readHidden";
	const ACTIONS = array(WH_UsersAccess::ACTION_CREATE,
						  WH_UsersAccess::ACTION_UPDATE,
						  WH_UsersAccess::ACTION_DELETE,
						  WH_UsersAccess::ACTION_SEE,
						  WH_UsersAccess::ACTION_READ,
						  WH_UsersAccess::ACTION_READ_HIDDEN,
						  WH_UsersAccess::ACTION_READ_PREVIEW);
	
	
	const SCOPE_NONE      = "none";
	const SCOPE_OWN       = "ownObjects";
	const SCOPE_SELECTED  = "selectedObjects";
	const SCOPE_GROUP     = "groupObjects";
	const SCOPE_ALL       = "allObjects";
	const SCOPES = array(  WH_UsersAccess::SCOPE_NONE,
						   WH_UsersAccess::SCOPE_ALL,
						   WH_UsersAccess::SCOPE_OWN,
						   WH_UsersAccess::SCOPE_GROUP,
						   WH_UsersAccess::SCOPE_SELECTED);
	
	
	/* Category fields */
	const CategoryElement = "Settings::UserAccess";
	
	/* Metadata fields */
	const MetaKey  = "UsersAccess";
	
	/* Class variables */
	public $id;				/* id of WH_Metadata */
	public $obj_id;			/* id of object WH_User or WH_UsersGroup */
	public $meta_obj;		/* WH_User::meta_obj or WH_UsersGroup::meta_obj */
	public $role;			/* WH_User::ROLE_* */

	public $obj_access; 	// array of WH Object access
	public $menu_access; 	// array of WH Menus access
	public $action_access; 	// array of WH Actions access
	public $quantities; 	// array of Objects creation quantities

	public $isOk;       	/* init ok ? */
	
	private $DB_Metadata;  	/* object */
	private $DB_Category;  	/* object */
		
	/**
	* Class constructor 
	*  $args : array()
	*			'id'       	 => int
	*			'obj_id'   	 => int
	*			'meta_obj'	 => string
	*			'role'	 	 => string
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id      	= isset($args['id'])      ? (int)$args['id']:(int)$id;
		$this->obj_id  	= isset($args['obj_id'])  ? (int)$args['obj_id']:0;
		$this->meta_obj	= isset($args['meta_obj'])? $args['meta_obj']:"";
		$this->role		= isset($args['role'])    ? $args['role']:"";
		
		$this->isOk         = false;
		
		$this->obj_access 	= array();
		$this->menu_access	= array();
		$this->action_access= array();
		$this->quantities 	= array();

		$this->DB_Metadata = null;
		$this->DB_Category = null;
		
		if( ! is_numeric($this->id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
			return false;
			
		} else {
			
			$this->isOk = $this->get_UserAccess($this->id);
			if( $this->isOk && $this->id == 0 )
				$this->isOk = $this->initAccessByRole();
		}
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = false;
				
		// Save WH_Metadata
		if( $this->obj_id != 0 ) {
			
			if( $this->DB_Metadata == null ) { // insert Metadata 
				$this->DB_Metadata = new WH_Metadata(0, array(
											'meta_obj' => $this->meta_obj, 
											'obj_id'   => $this->obj_id,
											'user_id'  => 0,
											'meta_key' => WH_UsersAccess::MetaKey
										));
			} 
			
			$this->DB_Metadata->meta_value = json_encode(array('role'   		=> $this->role,
																'obj_access'   	=> $this->obj_access,
																'menu_access' 	=> $this->menu_access,
																'action_access' => $this->action_access,
																'quantities' 	=> $this->quantities));
			$result   = $this->DB_Metadata->save();
			$this->id = $this->DB_Metadata->id;
		
		}	
		
		// Save WH_Category
		if( $this->obj_id == 0 ) {
			
			if( $this->DB_Category == null ) { // insert Metadata 
				$this->DB_Category = new WH_Category(0, array(
											'element'     => WH_UsersAccess::CategoryElement, 
											'number'      => 0,
				                            'title'       => $this->role,
											'description' => '',
											'parent_id'   => 0
										));
			} 
			
			$this->DB_Category->description = json_encode(array('obj_access'   	=> $this->obj_access,
																'menu_access' 	=> $this->menu_access,
																'action_access' => $this->action_access,
																'quantities' 	=> $this->quantities));
			$result   = $this->DB_Category->save();
			$this->id = $this->DB_Category->id;
		
		} 
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$ret = true;
		
		if( $this->obj_id != 0 )
			$ret = $this->DB_Metadata->delete();
		
		if( $ret ) 
			$this->id = 0;
			
		return $ret;
	}
	
	/* Read DB */
	private function get_UserAccess($id) {
		
		$ret = false;
		
		// If user access
		if( $this->obj_id != 0 && $id != 0 ) { 
				
			// Get Display Settings
			$this->DB_Metadata  = new WH_Metadata($id);
			
			if( $this->DB_Metadata->isOk ) {

				$tab = json_decode($this->DB_Metadata->meta_value, true);
				
				if( is_array($tab) ) {
					$this->role  		= $tab['role'];
					$this->obj_access  	= $tab['obj_access'];
					$this->menu_access 	= $tab['menu_access'];
					$this->action_access= $tab['action_access'];
					$this->quantities 	= $tab['quantities'];
					$ret = true;
				} 
			} 
		}
		
		// If default user access
		if( $this->obj_id == 0 && $id != 0 ) { 
				
			// Get Display Settings
			$this->DB_Category  = new WH_Category($id);
			
			if( $this->DB_Category->isOk ) {
				
				$this->role = $this->DB_Category->title;
				$tab = json_decode($this->DB_Category->description, true);
				
				if( is_array($tab) ) {
					$this->obj_access  	= $tab['obj_access'];
					$this->menu_access 	= $tab['menu_access'];
					$this->action_access= $tab['action_access'];
					$this->quantities 	= $tab['quantities'];
					$ret = true;
				} 
			} 
		}
		
		return $ret;
	}

	
	/* Update DB object */
	private function updateDB_Object() {
		
	}

	// Init access by role 
	public function initAccessByRole($role = "") {
		
		$this->obj_access 	= array();
		$this->menu_access	= array();
		$this->action_access= array();
		$this->quantities 	= array();
		
		if( $role == "" )
			$role = $this->role;
		
		if( ! in_array($role, WH_UsersAccess::ROLES) )
			return false;
		
		$this->role = $role;
		$scopes = WH_UsersAccess::get_DefaultSettings($role);
		if( ! is_array($scopes) )
			return false;
		
		$this->obj_access    = $scopes['obj_access'];
		$this->menu_access	 = $scopes['menu_access'];
		$this->action_access = $scopes['action_access'];
		$this->quantities 	 = $scopes['quantities'];
		
		// Search for "Post creation" WordPress capabality
		if( $this->role == WH_User::ROLE_AUTHOR || $this->role == WH_User::ROLE_ADMIN ) {
			$scope = WH_UsersAccess::SCOPE_OWN;
			if( $this->role == WH_User::ROLE_ADMIN )
				$scope = WH_UsersAccess::SCOPE_ALL;
			
			if( ! user_can($this->obj_id, 'publish_posts') )
				$scope = WH_UsersAccess::SCOPE_NONE;
			
			$this->addAction(WH_UsersAccess::OBJECT_BOOK_POST, WH_UsersAccess::ACTION_CREATE, $scope);
			$this->addAction(WH_UsersAccess::OBJECT_BOOK_POST, WH_UsersAccess::ACTION_UPDATE, $scope);
			$this->addAction(WH_UsersAccess::OBJECT_BOOK_POST, WH_UsersAccess::ACTION_DELETE, $scope);
		}
		
		// Search for "Upload files" WordPress capabality
		if( $this->role == WH_User::ROLE_AUTHOR || $this->role == WH_User::ROLE_ADMIN ) {
			$scope = WH_UsersAccess::SCOPE_OWN;
			if( $this->role == WH_User::ROLE_ADMIN )
				$scope = WH_UsersAccess::SCOPE_ALL;
			
			if( ! user_can($this->obj_id, 'upload_files') )
				$scope = WH_UsersAccess::SCOPE_NONE;
			
			$this->addAction(WH_UsersAccess::OBJECT_BOOK_COVER, WH_UsersAccess::ACTION_CREATE, $scope);
			$this->addAction(WH_UsersAccess::OBJECT_BOOK_COVER, WH_UsersAccess::ACTION_UPDATE, $scope);
			$this->addAction(WH_UsersAccess::OBJECT_BOOK_COVER, WH_UsersAccess::ACTION_DELETE, $scope);
		}
		
		return true;
	}
		
	
	// Update Object Book or Bookworld or ToDoList access
	public function updateObjectAccess($obj, $bool_access, $obj_ids = array()) {
		if( ! is_array($obj_ids) )
			return false;
		
		if( ! in_array($obj, WH_UsersAccess::OBJECTS) )
			return false;
		
		$this->obj_access[$obj] = array('access' => boolval($bool_access),
										'ids'    => $obj_ids);
		return true;
	}
	// Add Object Id access
	public function addObjectIdAccess($obj, $obj_id) {
		if( ! is_numeric($obj_id) )
			return false;
		
		if( ! in_array($obj, WH_UsersAccess::OBJECTS) )
			return false;
		
		$ids = $this->obj_access[$obj]['ids'];
		
		if( ! in_array($obj_id, $ids) )
			$this->obj_access[$obj]['ids'][] = $obj_id;
		
		return true;
	}
	// delete Object Id access
	public function deleteObjectIdAccess($obj, $obj_id) {
		if( ! is_numeric($obj_id) )
			return false;
		
		if( ! in_array($obj, WH_UsersAccess::OBJECTS) )
			return false;
		
		if( in_array($obj_id, $this->obj_access[$obj]['ids']) )
			foreach( $this->obj_access[$obj]['ids'] as $key => $id )
				if( $obj_id == $id )
					unset($this->obj_access[$obj]['ids'][$key]);
		
		return true;
	}
	
	// Add/Update Object Book or Bookworld or ToDoList actions
	public function addAction($obj, $action, $scope) {
		if( ! in_array($obj, WH_UsersAccess::OBJECTS) )
			return false;
		if( ! in_array($action, WH_UsersAccess::ACTIONS) )
			return false;
		if( ! in_array($scope, WH_UsersAccess::SCOPES) )
			return false;
		
		if( ! is_array($this->action_access[$obj]) )
			$this->action_access[$obj] = array();
		$this->action_access[$obj][$action] = $scope;
		return true;
	}
	// Delete Object Book or Bookworld or ToDoList actions
	public function deleteAction($obj, $action) {
		if( ! in_array($obj, WH_UsersAccess::OBJECTS) )
			return false;
		if( ! in_array($action, WH_UsersAccess::ACTIONS) )
			return false;
		
		if( isset($this->action_access[$obj]) && 
		    is_array($this->action_access[$obj]) &&
			isset($this->action_access[$obj][$action]) )
			unset($this->action_access[$obj][$action]);
		return true;
	}
	
	// See or not the Menu 
	public function manageMenu($menu, $visible = false) {
		if( ! in_array($menu, WH_UsersAccess::MENUS) )
			return false;
		
		$this->menu_access[$menu] = boolval($visible);
		return true;
	}
	
	// Update Quantity of Books or Bookworlds or ToDoList
	public function updateQuantity($obj, $qty) {
		if( ! in_array($obj, WH_UsersAccess::OBJECTS) )
			return false;
		
		$this->quantities[$obj] = $qty;
		return true;
	}





	public static function existsRole($role) {
		return in_array($role, WH_UsersAccess::ROLES);
	}
	public static function isDefaultRole($role) {
		return in_array($role, WH_UsersAccess::DEFAULT_ROLES);
	}

	public static function get_DefaultSettings($role) {
		if( ! in_array($role, WH_UsersAccess::ROLES) )
			return false;
		
		$scopes = false;
		switch($role) {
			case WH_User::ROLE_ADMIN:      $scopes = WH_UsersAccess::get_AdminDefaultSettings(); break;
			case WH_User::ROLE_AUTHOR:     $scopes = WH_UsersAccess::get_AuthorDefaultSettings(); break;
			case WH_User::ROLE_EDITOR:     $scopes = WH_UsersAccess::get_EditorDefaultSettings(); break;
			case WH_User::ROLE_READER:     
			case WH_User::ROLE_READERP:    $scopes = WH_UsersAccess::get_ReaderDefaultSettings(); break;
			case WH_User::ROLE_GROUPADMIN: $scopes = WH_UsersAccess::get_GroupUserDefaultSettings(); break;
			case WH_User::ROLE_GROUPUSER:  $scopes = WH_UsersAccess::get_GroupAdminDefaultSettings(); break;
		}
		
		return $scopes;
	}
	
	// Get default settings or create them
	public static function get_AdminDefaultSettings() {
		
		// Return default settings
		$obj_access 	= array();
		$menu_access	= array();
		$action_access	= array();
		$quantities 	= array();

		foreach( WH_UsersAccess::OBJECTS as $obj )
			$obj_access[$obj] = true;
		
		foreach( WH_UsersAccess::OBJECTS as $obj )
			$quantities[$obj] = -1;
		
		foreach( WH_UsersAccess::MENUS as $menu )
			$menu_access[$menu] = true;
			
		foreach( WH_UsersAccess::OBJECTS as $obj )
			foreach( WH_UsersAccess::ACTIONS as $action ) {
				if( ! isset($action_access[$obj]) )
					$action_access[$obj] = array();
				$action_access[$obj][$action] = WH_UsersAccess::SCOPE_ALL;
			}
		
		$scopes = array(  'obj_access'    => $obj_access,
							'menu_access'   => $menu_access,
							'action_access' => $action_access,
							'quantities'    => $quantities);
			
		return $scopes;
	}
	public static function get_AuthorDefaultSettings() {
		
		$all_cat = WH_Category::getAll_Categories(WH_UsersAccess::CategoryElement);
		$scopes = null;
		
		foreach( $all_cat as $cat ) {
			if( $cat->title == WH_User::ROLE_ADMIN ) {
				$tab = json_decode($cat->description, true);
				if( is_array($tab) )
					$scopes = $tab;
				break;
			}
		}
		if( $scopes != null )
			return $scopes;
		
		// Create default settings
		$obj_access 	= array();
		$menu_access	= array();
		$action_access	= array();
		$quantities 	= array();

		$obj_access[WH_UsersAccess::OBJECT_BOOK]  = true;
		$menu_access[WH_UsersAccess::OBJECT_BOOK] = true;
		$quantities[WH_UsersAccess::OBJECT_BOOK]  = -1;
		
		$book_actions = array(	WH_UsersAccess::ACTION_CREATE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_UPDATE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_DELETE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_SEE	  => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_READ   => WH_UsersAccess::SCOPE_OWN);
		$action_access[WH_UsersAccess::OBJECT_BOOK] = $book_actions;

		$scopes = array(  'obj_access'    => $obj_access,
							'menu_access'   => $menu_access,
							'action_access' => $action_access,
							'quantities'    => $quantities);
		
		// Create WH_Category
		$cat = new WH_Category(0, array(
									'element'     => WH_UsersAccess::CategoryElement, 
									'number'      => 0,
									'title'       => WH_User::ROLE_AUTHOR,
									'description' => '',
									'parent_id'   => 0
								)); 
			
		$cat->description = json_encode($scopes);
		$cat->save();
			
		return $scopes;
	}
	public static function get_EditorDefaultSettings() {
		
		$all_cat = WH_Category::getAll_Categories(WH_UsersAccess::CategoryElement);
		$scopes = null;
		
		foreach( $all_cat as $cat ) {
			if( $cat->title == WH_User::ROLE_EDITOR ) {
				$tab = json_decode($cat->description, true);
				if( is_array($tab) )
					$scopes = $tab;
				break;
			}
		}
		if( $scopes != null )
			return $scopes;
		
		// Create default settings
		$obj_access 	= array();
		$menu_access	= array();
		$action_access	= array();
		$quantities 	= array();

		$obj_access[WH_UsersAccess::OBJECT_TODOLIST]  = true;
		$menu_access[WH_UsersAccess::OBJECT_TODOLIST] = true;
		$quantities[WH_UsersAccess::OBJECT_TODOLIST]  = 1;
		
		$obj_access[WH_UsersAccess::OBJECT_BOOK]  = true;
		$menu_access[WH_UsersAccess::OBJECT_BOOK] = true;
		$quantities[WH_UsersAccess::OBJECT_BOOK]  = -1;
		
		$book_actions = array(	WH_UsersAccess::ACTION_UPDATE => WH_UsersAccess::SCOPE_SELECTED,
								WH_UsersAccess::ACTION_SEE	  => WH_UsersAccess::SCOPE_SELECTED,
								WH_UsersAccess::ACTION_READ   => WH_UsersAccess::SCOPE_SELECTED);
		$action_access[WH_UsersAccess::OBJECT_BOOK] = $book_actions;
		
		$todo_actions = array(	WH_UsersAccess::ACTION_CREATE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_UPDATE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_DELETE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_SEE	  => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_READ   => WH_UsersAccess::SCOPE_OWN);
		$action_access[WH_UsersAccess::OBJECT_TODOLIST] = $todo_actions;

		$scopes = array(  'obj_access'    => $obj_access,
							'menu_access'   => $menu_access,
							'action_access' => $action_access,
							'quantities'    => $quantities);
		
		// Create WH_Category
		$cat = new WH_Category(0, array(
									'element'     => WH_UsersAccess::CategoryElement, 
									'number'      => 0,
									'title'       => WH_User::ROLE_EDITOR,
									'description' => '',
									'parent_id'   => 0
								)); 
			
		$cat->description = json_encode($scopes);
		$cat->save();
			
		return $scopes;
	}
	public static function get_ReaderDefaultSettings() {
		
		$all_cat = WH_Category::getAll_Categories(WH_UsersAccess::CategoryElement);
		$scopes = null;
		
		foreach( $all_cat as $cat ) {
			if( $cat->title == WH_User::ROLE_READER ) {
				$tab = json_decode($cat->description, true);
				if( is_array($tab) )
					$scopes = $tab;
				break;
			}
		}
		if( $scopes != null )
			return $scopes;
		
		// Create default settings
		$obj_access 	= array();
		$menu_access	= array();
		$action_access	= array();
		$quantities 	= array();

		$obj_access[WH_UsersAccess::OBJECT_BOOK]  = true;
		$menu_access[WH_UsersAccess::OBJECT_BOOK] = true;
		$quantities[WH_UsersAccess::OBJECT_BOOK]  = 0;
		
		$book_actions = array(	WH_UsersAccess::ACTION_SEE	  => WH_UsersAccess::SCOPE_SELECTED,
								WH_UsersAccess::ACTION_READ   => WH_UsersAccess::SCOPE_SELECTED);
		$action_access[WH_UsersAccess::OBJECT_BOOK] = $book_actions;
		
		$scopes = array(  'obj_access'    => $obj_access,
							'menu_access'   => $menu_access,
							'action_access' => $action_access,
							'quantities'    => $quantities);
		
		// Create WH_Category
		$cat = new WH_Category(0, array(
									'element'     => WH_UsersAccess::CategoryElement, 
									'number'      => 0,
									'title'       => WH_User::ROLE_READER,
									'description' => '',
									'parent_id'   => 0
								)); 
			
		$cat->description = json_encode($scopes);
		$cat->save();
			
		return $scopes;
	}

	public static function get_GroupAdminDefaultSettings() {
		
		$all_cat = WH_Category::getAll_Categories(WH_UsersAccess::CategoryElement);
		$scopes = null;
		
		foreach( $all_cat as $cat ) {
			if( $cat->title == WH_User::ROLE_GROUPADMIN ) {
				$tab = json_decode($cat->description, true);
				if( is_array($tab) )
					$scopes = $tab;
				break;
			}
		}
		if( $scopes != null )
			return $scopes;
		
		// Create default settings
		$obj_access 	= array();
		$menu_access	= array();
		$action_access	= array();
		$quantities 	= array();

		$obj_access[WH_UsersAccess::OBJECT_BOOK]  = true;
		$menu_access[WH_UsersAccess::OBJECT_BOOK] = true;
		$quantities[WH_UsersAccess::OBJECT_BOOK]  = -1;
		
		$book_actions = array(	WH_UsersAccess::ACTION_CREATE => WH_UsersAccess::SCOPE_GROUP,
								WH_UsersAccess::ACTION_UPDATE => WH_UsersAccess::SCOPE_GROUP,
								WH_UsersAccess::ACTION_DELETE => WH_UsersAccess::SCOPE_GROUP,
								WH_UsersAccess::ACTION_SEE	  => WH_UsersAccess::SCOPE_GROUP,
								WH_UsersAccess::ACTION_READ   => WH_UsersAccess::SCOPE_GROUP);
		$action_access[WH_UsersAccess::OBJECT_BOOK] = $book_actions;

		$scopes = array(  'obj_access'    => $obj_access,
							'menu_access'   => $menu_access,
							'action_access' => $action_access,
							'quantities'    => $quantities);
		
		// Create WH_Category
		$cat = new WH_Category(0, array(
									'element'     => WH_UsersAccess::CategoryElement, 
									'number'      => 0,
									'title'       => WH_User::ROLE_GROUPADMIN,
									'description' => '',
									'parent_id'   => 0
								)); 
			
		$cat->description = json_encode($scopes);
		$cat->save();
			
		return $scopes;
	}
	public static function get_GroupAuthorUserDefaultSettings() {
		
		$all_cat = WH_Category::getAll_Categories(WH_UsersAccess::CategoryElement);
		$scopes = null;
		
		foreach( $all_cat as $cat ) {
			if( $cat->title == WH_User::ROLE_GROUPAUTHOR ) {
				$tab = json_decode($cat->description, true);
				if( is_array($tab) )
					$scopes = $tab;
				break;
			}
		}
		if( $scopes != null )
			return $scopes;
		
		// Create default settings
		$obj_access 	= array();
		$menu_access	= array();
		$action_access	= array();
		$quantities 	= array();

		$obj_access[WH_UsersAccess::OBJECT_BOOK]  = true;
		$menu_access[WH_UsersAccess::OBJECT_BOOK] = true;
		$quantities[WH_UsersAccess::OBJECT_BOOK]  = -1;
		
		$book_actions = array(	WH_UsersAccess::ACTION_CREATE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_UPDATE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_DELETE => WH_UsersAccess::SCOPE_OWN,
								WH_UsersAccess::ACTION_SEE	  => WH_UsersAccess::SCOPE_GROUP,
								WH_UsersAccess::ACTION_READ   => WH_UsersAccess::SCOPE_OWN);
		$action_access[WH_UsersAccess::OBJECT_BOOK] = $book_actions;

		$scopes = array(  'obj_access'    => $obj_access,
							'menu_access'   => $menu_access,
							'action_access' => $action_access,
							'quantities'    => $quantities);
		
		// Create WH_Category
		$cat = new WH_Category(0, array(
									'element'     => WH_UsersAccess::CategoryElement, 
									'number'      => 0,
									'title'       => WH_User::ROLE_GROUPAUTHOR,
									'description' => '',
									'parent_id'   => 0
								)); 
			
		$cat->description = json_encode($scopes);
		$cat->save();
			
		return $scopes;
	}
	public static function get_GroupEditorUserDefaultSettings() {
		
		$all_cat = WH_Category::getAll_Categories(WH_UsersAccess::CategoryElement);
		$scopes = null;
		
		foreach( $all_cat as $cat ) {
			if( $cat->title == WH_User::ROLE_GROUPEDITOR ) {
				$tab = json_decode($cat->description, true);
				if( is_array($tab) )
					$scopes = $tab;
				break;
			}
		}
		if( $scopes != null )
			return $scopes;
		
		// Create default settings
		$obj_access 	= array();
		$menu_access	= array();
		$action_access	= array();
		$quantities 	= array();

		$obj_access[WH_UsersAccess::OBJECT_BOOK]  = true;
		$menu_access[WH_UsersAccess::OBJECT_BOOK] = true;
		$quantities[WH_UsersAccess::OBJECT_BOOK]  = 0;
		
		$book_actions = array(	WH_UsersAccess::ACTION_UPDATE => WH_UsersAccess::SCOPE_GROUP,
								WH_UsersAccess::ACTION_SEE	  => WH_UsersAccess::SCOPE_GROUP,
								WH_UsersAccess::ACTION_READ   => WH_UsersAccess::SCOPE_GROUP);
		$action_access[WH_UsersAccess::OBJECT_BOOK] = $book_actions;

		$scopes = array(  'obj_access'    => $obj_access,
							'menu_access'   => $menu_access,
							'action_access' => $action_access,
							'quantities'    => $quantities);
		
		// Create WH_Category
		$cat = new WH_Category(0, array(
									'element'     => WH_UsersAccess::CategoryElement, 
									'number'      => 0,
									'title'       => WH_User::ROLE_GROUPEDITOR,
									'description' => '',
									'parent_id'   => 0
								)); 
			
		$cat->description = json_encode($scopes);
		$cat->save();
			
		return $scopes;
	}
	public static function get_GroupReaderUserDefaultSettings() {
		
		$all_cat = WH_Category::getAll_Categories(WH_UsersAccess::CategoryElement);
		$scopes = null;
		
		foreach( $all_cat as $cat ) {
			if( $cat->title == WH_User::ROLE_GROUPREADER ) {
				$tab = json_decode($cat->description, true);
				if( is_array($tab) )
					$scopes = $tab;
				break;
			}
		}
		if( $scopes != null )
			return $scopes;
		
		// Create default settings
		$obj_access 	= array();
		$menu_access	= array();
		$action_access	= array();
		$quantities 	= array();

		$obj_access[WH_UsersAccess::OBJECT_BOOK]  = true;
		$menu_access[WH_UsersAccess::OBJECT_BOOK] = true;
		$quantities[WH_UsersAccess::OBJECT_BOOK]  = 0;
		
		$book_actions = array(	WH_UsersAccess::ACTION_SEE	  => WH_UsersAccess::SCOPE_GROUP,
								WH_UsersAccess::ACTION_READ   => WH_UsersAccess::SCOPE_GROUP);
		$action_access[WH_UsersAccess::OBJECT_BOOK] = $book_actions;

		$scopes = array(  'obj_access'    => $obj_access,
							'menu_access'   => $menu_access,
							'action_access' => $action_access,
							'quantities'    => $quantities);
		
		// Create WH_Category
		$cat = new WH_Category(0, array(
									'element'     => WH_UsersAccess::CategoryElement, 
									'number'      => 0,
									'title'       => WH_User::ROLE_GROUPREADER,
									'description' => '',
									'parent_id'   => 0
								)); 
			
		$cat->description = json_encode($scopes);
		$cat->save();
			
		return $scopes;
	}
	
}
?>