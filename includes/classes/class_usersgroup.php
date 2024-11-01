<?php
/************************************
 **      Users Group class       **
 ************************************/

class WH_UsersGroup {
	
	const DEFAULT_GROUP_ADMINS 	 = "WH_".WH_User::ROLE_ADMIN  ;
	const DEFAULT_GROUP_AUTHORS	 = "WH_".WH_User::ROLE_AUTHOR ;
	const DEFAULT_GROUP_EDITORS  = "WH_".WH_User::ROLE_EDITOR ;
	const DEFAULT_GROUP_READERS  = "WH_".WH_User::ROLE_READER ;
	const DEFAULT_GROUP_READERSP = "WH_".WH_User::ROLE_READERP;
	const DEFAULT_GROUPS= array(WH_UsersGroup::DEFAULT_GROUP_ADMINS,
								WH_UsersGroup::DEFAULT_GROUP_AUTHORS,
								WH_UsersGroup::DEFAULT_GROUP_EDITORS,
								WH_UsersGroup::DEFAULT_GROUP_READERS,
								WH_UsersGroup::DEFAULT_GROUP_READERSP);
	
	const meta_obj = "UsersGroup";
	
	public $id; 			  
	public $libelle;

	public $isOk;       	/* init ok ? */
	
	public $users_id;		/* array of ('id'=>int, 'role'='') */
	public $users;			/* array of WH_User */
	
	public $tab_access; 	/* array of object WH_UsersAccess */
	
	private $DB_Metadata;  	/* object */
		
	/**
	* Class constructor 
	*  $args : array()
	*			'id'       		       => int
	*			'libelle'   	       => string
	*			'users_id'  	       => array
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id      				= isset($args['id'])                  ? (int)$args['id']:(int)$id;
		$this->libelle  			= isset($args['libelle'])             ? $args['libelle']:"";
		$this->users_id 			= isset($args['users_id'])            ? (array)$args['users_id']:array();
		
		$this->isOk         		= false;
		
		$this->users       			= array();
		$this->tab_access  			= array();
		$this->DB_Metadata 			= null;
		
		if( ! is_numeric($this->id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$this->id );
			return false;
		} else {
			$this->get_UsersGroup($this->id);
			
			if( $cascade ) {
				$this->readUsers();
				$this->readGroupAccess();
			}
		}
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = false;
		
		// Save access
		foreach( $this->tab_access as $a ){
			$ret = $a->save();
			if( ! $ret )
				break;
		}
			
		// Save WH_Metadata
		if( $this->DB_Metadata == null ) { // insert Metadata 
			$this->DB_Metadata = new WH_Metadata(0, array(
										'meta_obj' => WH_UsersGroup::meta_obj, 
										'obj_id'   => 0,
										'user_id'  => 0,
										'meta_key' => $this->libelle
									));
		} 
		
		$this->DB_Metadata->meta_value = json_encode(array('libelle'   	=> $this->libelle,
															'users_id' 	=> $this->users_id));
		$result   = $this->DB_Metadata->save();
		$this->id = $this->DB_Metadata->id;
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$ret = true;
		
		// delete access
		foreach( $this->tab_access as $a ) {
			$ret = $a->delete();
			if( ! $ret )
				break;
		}
		if( $ret ) 
			$ret = $this->DB_Metadata->delete();
		
		
		return $ret;
	}
	
	/* Read DB */
	private function get_UsersGroup($id) {
		
		$this->isOk = false;
		
		// If users group id
		if( $id != 0 ) { 
				
			// Get Display Settings
			$this->DB_Metadata  = new WH_Metadata($id);
			$this->isOk 		= $this->DB_Metadata->isOk;
			
			if( $this->DB_Metadata->isOk ) {

				$this->libelle = $this->DB_Metadata->meta_key;
				$tab = json_decode($this->DB_Metadata->meta_value, true);
				
				if( is_array($tab) ) {
					$this->users_id 			= $tab['users_id'];
				} else
					$this->isOk = false;
			} 
		}
		
		return $this->isOk;
	}

	/* Update DB object */
	private function updateDB_Object() {
		
	}

	
	
	/* User in group ? */
	public function existsUser($uid) {
		if( count($this->users) == 0 )
			$this->readUsers();
		
		return isset($this->users[$uid]);
	}
	
	/* Get Users info */
	public function readUsers() {
		$this->users = array();
		foreach( $this->users_id as $tab_usr ) {
			$uid = isset($tab_usr['id'])?(int)$tab_usr['id']:0;
			if( $uid != 0 && is_numeric($uid) )
				$this->users[$uid] = new WH_User(0, array('user_id'=>$uid));
		}
//wtr_info_log(__METHOD__, "Group users: ".print_r($this->users,true));				
	}
	
	public function addUser($uid, $role) {
		
		if( ! in_array($role, WH_UsersAccess::ROLES) )
			return false;
		
		if( count($this->users) == 0 )
			$this->readUsers();
		
		if( ! isset($this->users[$uid]) ) {
			$this->users[$uid]    = new WH_User(0, array('user_id'=>$uid));
			$this->users_id[$uid] = array('id'=>$uid, 'role'=>$role);
		}
	}
	public function deleteUser($uid) {
		if( count($this->users) == 0 )
			$this->readUsers();
		
		if( isset($this->users[$uid]) ) {
			unset($this->users_id[$uid]);
			unset($this->users[$uid]);
		}
	}
	
	
	
	/* Get Access info */
	public function readGroupAccess() {
		$this->tab_access = WH_UsersAccess::get_GroupAccesses($this->id);
	}
	
	
	
	
	/* GET All users groups */
	public static function getAll_UsersGroups($col = "meta_key", $direction = "asc") {
		
		$result = array();
		$usersGroups_meta = WH_DB_Metadata::getAllDB_Metadatas(array(
				                                        'meta_obj' => WH_UsersGroup::meta_obj,
				                                        'obj_id'   => 0
														), $col, $direction);

		foreach( $usersGroups_meta as $meta ) {
			$meta_value = json_decode($meta->meta_value, true);
			
			if( is_array($meta_value) ) {
				$uid_array = isset($meta_value['users_id'])?$meta_value['users_id']:array();
				$aid_array = isset($meta_value['admins_id'])?$meta_value['admins_id']:array();
				$user_aid  = isset($meta_value['groupUser_access_id'])?$meta_value['groupUser_access_id']:array();
				$group_aid = isset($meta_value['groupAdmin_access_id'])?$meta_value['groupAdmin_access_id']:array();
//wtr_info_log(__METHOD__, "Users group=".print_r($meta, true));

				$result[] = new WH_UsersGroup(0, array( 'id' 		           => $meta->meta_id,
														'libelle' 	           => $meta_value['libelle'],
														'users_id' 	           => $uid_array,
														'admins_id'            => $aid_array,
														'groupUser_access_id'  => $user_aid,
														'groupAdmin_access_id' => $group_aid
														));
			}
		}
		return $result;
	}
	
	/* GET all User's groups */
	public static function getAll_UserGroups($user_id) {
		
		$allGroups = WH_UsersGroup::getAll_UsersGroups();
		
		$result = array();
		foreach( $allGroups as $g ) {
			if( $g->existsUser($user_id) )
				$result[$g->id] = $g;
		}
		return $result;
	}

	public static function existsUsersGroup($group_id) {
		$g = new WH_UsersGroup($group_id);
		return $g->isOk;
	}

	private static function get_WriterHelperRoleGroup($role) {
		$default_group_id = "";
		switch($role) {
			case WH_User::ROLE_ADMIN:   $default_group_id = WH_UsersGroup::DEFAULT_GROUP_ADMINS; break;
			case WH_User::ROLE_AUTHOR:  $default_group_id = WH_UsersGroup::DEFAULT_GROUP_AUTHORS; break;
			case WH_User::ROLE_EDITOR:  $default_group_id = WH_UsersGroup::DEFAULT_GROUP_EDITORS; break;
			case WH_User::ROLE_READER:  $default_group_id = WH_UsersGroup::DEFAULT_GROUP_READERS; break;
			case WH_User::ROLE_READERP: $default_group_id = WH_UsersGroup::DEFAULT_GROUP_READERSP; break;
			default:
				return null;
		}
		
		$tab_meta = WH_Metadata::getAll_OneMetadata_ofObject(WH_UsersGroup::meta_obj, 0, $default_group_id);

		if( count($tab_meta) > 1 ) {
			wtr_error_log(__METHOD__, "More than 1 group for ".$default_group_id);
		}
		if( count($tab_meta) == 1 )
			return new WH_UsersGroup($tab_meta[0]->id);
		
		// Create the default group
		$default_group = new WH_UsersGroup(0, array('libelle'=>$default_group_id));
		$default_group->save(); // get an id
		
		$group_access  = new WH_UsersAccess(0, array('obj_id'   => $default_group->id,
													 'meta_obj' => "WH_UsersGroup",
													 'role'     =>$role));
		$group_access->initAccessByRole();
		if( ! $group_access->save() ) {
			wtr_error_log(__METHOD__, "Error while saving '$role access'");
			return false;
		}
		if( ! $default_group->save() ) {
			wtr_error_log(__METHOD__, "Error while saving '$role access'");
			return false;
		}
		
		return $default_group;
	}
	
	public static function get_WriterHelperAdminsGroup(){
		return WH_UsersGroup::get_WriterHelperRoleGroup(WH_User::ROLE_ADMIN);
	}
	public static function get_WriterHelperAdminsGroup_id(){
		$group_id =0;
		$group = WH_UsersGroup::get_WriterHelperAdminsGroup();
		if( $group !== false )
			$group_id = $group->id;
		
		return $group_id;
	}
	public static function get_WriterHelperAuthorsGroup(){
		return WH_UsersGroup::get_WriterHelperRoleGroup(WH_User::ROLE_AUTHOR);
	}
	public static function get_WriterHelperAuthorsGroup_id(){
		$group_id =0;
		$group = WH_UsersGroup::get_WriterHelperAuthorsGroup();
		if( $group !== false )
			$group_id = $group->id;
		
		return $group_id;
	}
	public static function get_WriterHelperEditorsGroup(){
		return WH_UsersGroup::get_WriterHelperRoleGroup(WH_User::ROLE_EDITOR);
	}
	public static function get_WriterHelperEditorsGroup_id(){
		$group_id =0;
		$group = WH_UsersGroup::get_WriterHelperEditorsGroup();
		if( $group !== false )
			$group_id = $group->id;
		
		return $group_id;
	}
	public static function get_WriterHelperReadersGroup(){
		return WH_UsersGroup::get_WriterHelperRoleGroup(WH_User::ROLE_READER);
	}
	public static function get_WriterHelperReadersGroup_id(){
		$group_id =0;
		$group = WH_UsersGroup::get_WriterHelperReadersGroup();
		if( $group !== false )
			$group_id = $group->id;
		
		return $group_id;
	}
	public static function get_WriterHelperReadersPremiumGroup(){
		return WH_UsersGroup::get_WriterHelperRoleGroup(WH_User::ROLE_READERP);
	}
	public static function get_WriterHelperReadersPremiumGroup_id(){
		$group_id =0;
		$group = WH_UsersGroup::get_WriterHelperReadersPremiumGroup();
		if( $group !== false )
			$group_id = $group->id;
		
		return $group_id;
	}
}
?>