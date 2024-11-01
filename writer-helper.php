<?php
/*
 * Plugin Name: Writer Helper
 * Plugin URI: https://writerhelper.clarissek.fr
 * Description: Write and edit books on your website
 * Version: 3.1.6
 * Author: Clarisse K.
 * Author URI: https://clarissek.fr
 * Text Domain: wtr_helper
 * Domain Path: /languages
 * Licence: Copyright © 2019 & ongoings Clarisse K.
**/

// Check if get_plugins() function exists. This is required on the front end of the
// site, since it is in a file that is normally only loaded in the admin.
if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

require_once(ABSPATH . '/wp-admin/includes/template.php');

//Plugin website
if ( !defined('WTRH_WEBSITE') ){
	define('WTRH_WEBSITE', 'https://writerhelper.clarissek.fr');
}


//Path to this file
if ( !defined('WTRH_PLUGIN_FILE') ){
	define('WTRH_PLUGIN_FILE', __FILE__);
}

//Path to the plugin's directory
if ( !defined('WTRH_DIRECTORY') ){
	define('WTRH_DIRECTORY', dirname(__FILE__));
}

//URL to the plugin's directory
if ( !defined('WTRH_URL') ){
	define('WTRH_URL', plugins_url() . "/" . dirname( plugin_basename( __FILE__ ) ));
}
//Path to the images directory
if ( !defined('WTRH_IMG_URL') ){
	define('WTRH_IMG_URL', WTRH_URL . "/img");
}
//Path to the javascript url
if ( !defined('WTRH_JS_URL') ){
	define('WTRH_JS_URL', WTRH_URL . "/js");
}
//Path to the languages url
if ( !defined('WTRH_CSS_URL') ){
	define('WTRH_CSS_URL', WTRH_URL . "/css");
}
//Path to the export url
if ( !defined('WTRH_EXPORT_URL') ){
	define('WTRH_EXPORT_URL', WTRH_URL . "/export");
}


//Path to the epubgenerator directory
if ( !defined('WTRH_EPUBGENERATOR_DIR') ){
	define('WTRH_EPUBGENERATOR_DIR', WTRH_DIRECTORY . "/epubgenerator");
}

//Path to the include directory
if ( !defined('WTRH_INCLUDE_DIR') ){
	define('WTRH_INCLUDE_DIR', WTRH_DIRECTORY . "/includes");
}
//Path to the javascript directory
if ( !defined('WTRH_JS_DIR') ){
	define('WTRH_JS_DIR', WTRH_DIRECTORY . "/js");
}
//Path to the css directory
if ( !defined('WTRH_CSS_DIR') ){
	define('WTRH_CSS_DIR', WTRH_DIRECTORY . "/css");
}
//Path to the languages directory
if ( !defined('WTRH_LANG_DIR') ){
	define('WTRH_LANG_DIR', WTRH_DIRECTORY . "/languages");
}
//Path to the export directory
if ( !defined('WTRH_EXPORT_DIR') ){
	define('WTRH_EXPORT_DIR', WTRH_DIRECTORY . "/export");
}

//Path to the directory containing extra modules
if ( !defined('WTRH_MODULES_DIR') ){
	define('WTRH_MODULES_DIR', WTRH_DIRECTORY . "/modules");
}
//Path to Storyboard extra module
if ( !defined('WTRH_STORYBOARD_DIR') ){
	define('WTRH_STORYBOARD_DIR', WTRH_MODULES_DIR . "/storyboard");
}
//Path to Bookworld extra module
if ( !defined('WTRH_BOOKWORLDS_DIR') ){
	define('WTRH_BOOKWORLDS_DIR', WTRH_MODULES_DIR . "/bookworld");
}
//Path to Writers/Editors extra module
if ( !defined('WTRH_WRITEDIT_DIR') ){
	define('WTRH_WRITEDIT_DIR', WTRH_MODULES_DIR . "/writers_editors");
}
//Path to Readers extra module
if ( !defined('WTRH_READERS_DIR') ){
	define('WTRH_READERS_DIR', WTRH_MODULES_DIR . "/readers");
}
//Path to Communities extra modules
if ( !defined('WTRH_COMMUNITIES_DIR') ){
	define('WTRH_COMMUNITIES_DIR', WTRH_MODULES_DIR . "/communities");
}



//transient name for event "plugin activated"
if ( !defined('WTRH_ACTIVATE_TRANSIENT') ){
	define('WTRH_ACTIVATE_TRANSIENT', 'wtr_plugin_activate');
}
//transient name for event "plugin updated"
if ( !defined('WTRH_UPDATE_TRANSIENT') ){
	define('WTRH_UPDATE_TRANSIENT', 'wtr_plugin_update');
}


// Main menu slug
if ( !defined('WTRH_MAIN_MENU') ){
	define('WTRH_MAIN_MENU', 'wtr_helper');
}
// Config menu slug
if ( !defined('WTRH_SETTINGS_MENU') ){
	define('WTRH_SETTINGS_MENU', 'wtrh_settings');
}
// Books menu slug
if ( !defined('WTRH_BOOKS_MENU') ){
	define('WTRH_BOOKS_MENU', 'wtrh_books');
}
// Bookworlds menu slug
if ( !defined('WTRH_BOOKWORLDS_MENU') ){
	define('WTRH_BOOKWORLDS_MENU', 'wtrh_bookworlds');
}
// ToDo menu slug
if ( !defined('WTRH_TODO_MENU') ){
	define('WTRH_TODO_MENU', 'wtrh_todolist');
}
// Stats menu slug
if ( !defined('WTRH_STATS_MENU') ){
	define('WTRH_STATS_MENU', 'wtrh_stats');
}
// Users menu slug
if ( !defined('WTRH_USERS_MENU') ){
	define('WTRH_USERS_MENU', 'wtrh_users');
}
// Communities menu slug
if ( !defined('WTRH_COMMUNITIES_MENU') ){
	define('WTRH_COMMUNITIES_MENU', 'wtrh_communities');
}


require_once(WTRH_INCLUDE_DIR . '/writer-helper-class.php');
require_once(WTRH_INCLUDE_DIR . "/functions/plugin_change_functions.php");

// upgrade data

if( ! get_transient(WTRH_UPDATE_TRANSIENT) || 
get_transient(WTRH_UPDATE_TRANSIENT) != get_plugin_data(WTRH_PLUGIN_FILE)['Version'] ) {
	wtrh_update_changes($errmsg);
	delete_transient(WTRH_UPDATE_TRANSIENT);
	set_transient(WTRH_UPDATE_TRANSIENT, get_plugin_data(WTRH_PLUGIN_FILE)['Version']);
}

	
/*
* Plugin Class
*/
class Writer_Helper_Plugin
{
	public function __construct()	{
		
		new Writer_Helper();
		
		// Add a new menu to the main menu of Admin WordPress interface
		add_action('admin_menu', array($this, 'add_admin_menu'));
		// Add activation hooks to install plugin
		register_activation_hook(WTRH_PLUGIN_FILE, 'Writer_Helper::install');
		// Add deactivation hooks to uninstall plugin
		register_deactivation_hook(WTRH_PLUGIN_FILE, 'Writer_Helper::uninstall');
		
	}
	
	// Add the menu to the main menu of Admin WordPress interface
	public function add_admin_menu()	{
		
		add_menu_page('Writer Helper2', 'Writer Helper', 
						WTRH_USR_WH_CAP, WTRH_MAIN_MENU, 
						array($this, 'menu_html_index'), 'dashicons-book');
						
		add_submenu_page(WTRH_MAIN_MENU, __('Starting with WH', 'wtr_helper'), __('Starting with WH', 'wtr_helper'), 
						 WTRH_USR_WH_CAP, WTRH_MAIN_MENU, array($this, 'menu_html_index'));
						
		add_submenu_page(WTRH_MAIN_MENU, __('My Books', 'wtr_helper'), __('My Books', 'wtr_helper'), 
						 WTRH_USR_WH_CAP, WTRH_BOOKS_MENU, array($this, 'menu_html_books'));
						
		add_submenu_page(WTRH_MAIN_MENU, __('Bookworlds', 'wtr_helper'), __('Bookworlds', 'wtr_helper'), 
						 WTRH_USR_WH_CAP, WTRH_BOOKWORLDS_MENU, array($this, 'menu_html_bookworlds'));
						
		add_submenu_page(WTRH_MAIN_MENU, __('My To Do List', 'wtr_helper'), __('My To Do List', 'wtr_helper'), 
						 WTRH_USR_WH_CAP, WTRH_TODO_MENU, array($this, 'menu_html_todo'));
						
		add_submenu_page(WTRH_MAIN_MENU, __('Statistics', 'wtr_helper'), __('Statistics', 'wtr_helper'), 
						 WTRH_USR_WH_CAP, WTRH_STATS_MENU, array($this, 'menu_html_stats'));
						
		add_submenu_page(WTRH_MAIN_MENU, __('Users', 'wtr_helper'), __('Users', 'wtr_helper'), 
						 WTRH_USR_ADMIN_CAP, WTRH_USERS_MENU, array($this, 'menu_html_users'));
						
		add_submenu_page(WTRH_MAIN_MENU, __('Communities', 'wtr_helper'), __('Communities', 'wtr_helper'), 
						 WTRH_USR_ADMIN_CAP, WTRH_COMMUNITIES_MENU, array($this, 'menu_html_communities'));
						
		add_submenu_page(WTRH_MAIN_MENU, __('Settings', 'wtr_helper'), __('Settings', 'wtr_helper'), 
						 WTRH_USR_ADMIN_CAP, WTRH_SETTINGS_MENU, array($this, 'menu_html_parameters'));
	}
	
	// Description of the plugin page
	public function menu_html_index()	{		
		include_once('includes/pages/wtrh_struct_page.php');		
	}
	public function menu_html_books()	{		
		include_once('includes/pages/wtrh_struct_page.php');		
	}
	public function menu_html_bookworlds()	{		
		include_once('includes/pages/wtrh_struct_page.php');		
	}
	public function menu_html_todo()	{		
		include_once('includes/pages/wtrh_struct_page.php');		
	}
	public function menu_html_stats()	{		
		include_once('includes/pages/wtrh_struct_page.php');		
	}
	public function menu_html_users()	{		
		include_once('includes/pages/wtrh_struct_page.php');		
	}
	public function menu_html_communities()	{		
		include_once('includes/pages/wtrh_struct_page.php');		
	}
	public function menu_html_parameters()	{		
		include_once('includes/pages/wtrh_struct_page.php');		
	}
	
}
	

function wtr_helper_plugin_init(){
	//Set up localisation. First loaded overrides strings present in later loaded file
	if( ! load_plugin_textdomain('wtr_helper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/') ) 
		echo ("ERROR load plugin textdomain : ". dirname( plugin_basename( __FILE__ ) ) . '/languages/');
}
add_action( 'plugins_loaded' , 'wtr_helper_plugin_init');


// Création du plugin
new Writer_Helper_Plugin();


?>