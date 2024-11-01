<?php 
/***** Writer Helper User constants *****/

//User admin capability on WordPress
if ( !defined('WTRH_USR_ADMIN_CAP') ){
	define('WTRH_USR_ADMIN_CAP', 'manage_options');
}
//User post creator capability on WordPress
if ( !defined('WTRH_USR_POST_CAP') ){
	define('WTRH_USR_POST_CAP', 'edit_posts');
}
//User media creator capability on WordPress
if ( !defined('WTRH_USR_MEDIA_CAP') ){
	define('WTRH_USR_MEDIA_CAP', 'upload_files');
}
//User WriterHelper user capability on WordPress
if ( !defined('WTRH_USR_WH_CAP') ){
	define('WTRH_USR_WH_CAP', 'read');
}

// Writer Helper Roles
const WTRH_ROLE_READER = "Reader";
const WTRH_ROLE_READERP= "Reader Premium";
const WTRH_ROLE_AUTHOR = "Author";
const WTRH_ROLE_EDITOR = "Editor";
const WTRH_ROLE_ADMIN  = "Administrator";
$wtr_roles = array(WTRH_ROLE_READER, WTRH_ROLE_READERP,
                   WTRH_ROLE_AUTHOR, WTRH_ROLE_EDITOR,
				   WTRH_ROLE_ADMIN);

// Writer Helper Capabilities
const WTRH_CAP_ADMIN_CONFIG         = "admin_config";
const WTRH_CAP_ADMIN_USERS          = "admin_users";
const WTRH_CAP_ADMIN_BOOKS          = "admin_books";
const WTRH_CAP_ADMIN_BOOKWORLDS     = "admin_bookworlds";
const WTRH_CAP_ADMIN_ARCS           = "admin_arcs";
const WTRH_CAP_LIST_BOOKS           = "list_books";
const WTRH_CAP_MANAGE_BOOKS         = "manage_books";
const WTRH_CAP_LIST_BOOKWORLDS      = "list_bookworlds";
const WTRH_CAP_MANAGE_BOOKWORLDS    = "manage_bookworlds";
const WTRH_CAP_EDITING_BOOKS        = "editing_books";
const WTRH_CAP_READ_BOOKS           = "read_books";
const WTRH_CAP_WRITE_TODO           = "write_todo";
const WTRH_CAP_CHECK_TODO           = "check_todo";

global $wtr_capabilities;
$wtr_capabilities = array(
			'updateBasicConfig'          ,
			'manageUsers'                ,
			'manageUsersRoles'           ,
			'manageUsersBooks'           ,
			'manageUsersBookworldAccess' ,
			'manageUsersBookworld'       ,
			'manageUsersStoryboardAccess',
			'manageStoryArcs'            ,
			'manageCharacterArcs'        ,
			'manageArchetyps'            ,
			'VisuStatsAdmin'             ,
			'manageBookworlds'           ,
			'manageBooks'                ,
			'manageChapters'             ,
			'manageScenes'               ,
			'VisuStatsAuthor'            ,
			'writeToDoList'              ,
			'manageEditing'              ,
			'manageEndEditing'           ,
			'seeToDoList'                ,
			'checkToDoList'              ,
			'visuStatsEditor'            ,
			'bookPrivatePreview'         ,
			'bookPreview'                ,
			'bookworldPrivatePreview'    ,
			'bookworldPreview'           ,
			'manageBookmark'             ,
			'visuStatsReader'
		);


// Writer Helper Roles/Capabilities
global $wtr_RolesCapabilities;
$wtr_RolesCapabilities = array(
		WTRH_ROLE_ADMIN   => $wtr_capabilities,
		WTRH_ROLE_AUTHOR  => array(
			'manageBookworlds'           ,
			'manageBooks'                ,
			'manageChapters'             ,
			'manageScenes'               ,
			'VisuStatsAuthor'            ,
			'writeToDoList'              ,
			'manageEditing'              ,
			'manageEndEditing'           ,
			'seeToDoList'                ,
			'checkToDoList'              ,
			'bookPrivatePreview'         ,
			'bookPreview'                ,
			'bookworldPrivatePreview'    ,
			'bookworldPreview'           ,
			'manageBookmark'             ,
		),
		WTRH_ROLE_EDITOR  => array(
			'manageEditing'              ,
			'manageEndEditing'           ,
			'seeToDoList'                ,
			'checkToDoList'              ,
			'visuStatsEditor'            ,
		),
		WTRH_ROLE_READERP => array(
			'bookPrivatePreview'         ,
			'bookPreview'                ,
			'bookworldPrivatePreview'    ,
			'bookworldPreview'           ,
			'manageBookmark'             ,
			'visuStatsReader'
		),
		WTRH_ROLE_READER  => array(
			'bookPrivatePreview'         ,
			'bookPreview'                ,
			'bookworldPrivatePreview'    ,
			'bookworldPreview'           ,
			'manageBookmark'             ,
			'visuStatsReader'
		)
);


// Writer Helper Dashboard Pages
global $wtr_pages_capabilities;
$wtr_pages_capabilities = array(
				"wtrh_parameters" => array(WTRH_ROLE_ADMIN),
				"wtrh_books"      => array(WTRH_ROLE_ADMIN, WTRH_ROLE_AUTHOR, WTRH_ROLE_EDITOR),
				"wtrh_bookworlds" => array(WTRH_ROLE_ADMIN, WTRH_ROLE_AUTHOR),
				"wtrh_todolist"   => array(WTRH_ROLE_ADMIN, WTRH_ROLE_AUTHOR, WTRH_ROLE_EDITOR),
				"wtrh_stats"      => array(WTRH_ROLE_ADMIN, WTRH_ROLE_AUTHOR, WTRH_ROLE_EDITOR)
				);


// Return a list of WordPress Users with admin capability
function getWordPressAdminUsers($user_id = 0) {
//wtr_info_log(__METHOD__, "user_id = $user_id");
	
	$list = array();
	$params = array();
	
	$wp_users = get_users($params);
	
	foreach($wp_users as $wpu){
		
		if( isset($wpu->caps['administrator']) && $wpu->caps['administrator'] ) {
//wtr_info_log(__METHOD__, print_r($wpu,true));
			if( $user_id == 0 || $user_id == $wpu->ID )
				$list[] = array('ID'            => $wpu->ID,
								'user_nicename' => $wpu->data->user_nicename,
								'display_name'  => $wpu->data->display_name,
								'roles'         => $wpu->roles,
								'capabilities'  => $wpu->caps
							);
			
		}
		
	}
//wtr_info_log(__METHOD__, "nb users = ".count($list));
	
	return $list;
}



// Add WordPress administrators as Writer Helper administrators
function addWriterHelperAdministrator($user_id) {
//wtr_info_log(__METHOD__, "user_id = $user_id");
	
	if( ! is_numeric($user_id) || count(getWordPressAdminUsers(get_current_user_id())) == 0 )
		return false;
	
	$obj_usr = new WH_User(0, array('user_id'   => $user_id)); 
	$obj_usr->save();
	$obj_usr->addRole(WTRH_ROLE_ADMIN);
	$obj_usr->addRole(WTRH_ROLE_AUTHOR);
	$obj_usr->addRole(WTRH_ROLE_EDITOR);
	$obj_usr->save();
	
	return true;
}


// Add WordPress administrators as Writer Helper administrators
function addWriterHelperAdministrators() {
	
	$wUsr = getWordPressAdminUsers();
	
	foreach( $wUsr as $usr ) {
		addWriterHelperAdministrator( $usr->ID ); 
	}
}


?>