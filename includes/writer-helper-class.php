<?php
/*
* Class: Writer Helper
*/
// classes
include_once(WTRH_INCLUDE_DIR . "/classes/class_main.php");

// functions
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/functions/db_functions.php");
include_once(WTRH_INCLUDE_DIR . "/functions/user_functions.php");
include_once(WTRH_INCLUDE_DIR . "/functions/ajax_functions.php");
include_once(WTRH_INCLUDE_DIR . "/functions/buttons_functions.php");
include_once(WTRH_INCLUDE_DIR . "/functions/book_functions.php");
include_once(WTRH_INCLUDE_DIR . "/functions/chapter_functions.php");
include_once(WTRH_INCLUDE_DIR . "/functions/scene_functions.php");

// widgets
include_once(WTRH_INCLUDE_DIR . "/widgets/books_widgets.php");
include_once(WTRH_INCLUDE_DIR . "/widgets/books_shortcodes.php");
include_once(WTRH_INCLUDE_DIR . "/widgets/book_widgets.php");
include_once(WTRH_INCLUDE_DIR . "/widgets/book_shortcodes.php");
include_once(WTRH_INCLUDE_DIR . "/widgets/author_stats_widgets.php");

// ------------------------
// MODULES
// ------------------------
// Bookworld module
if( file_exists(WTRH_BOOKWORLDS_DIR."/writerhelper_bookworld.php") )
	include_once(WTRH_BOOKWORLDS_DIR."/writerhelper_bookworld.php");

// Communities module
if( file_exists(WTRH_COMMUNITIES_DIR."/writerhelper_communities.php") ) 
	include_once(WTRH_COMMUNITIES_DIR."/writerhelper_communities.php");

// Readers module
if( file_exists(WTRH_READERS_DIR."/writerhelper_readers.php") )
	include_once(WTRH_READERS_DIR."/writerhelper_readers.php");

// Storyboard module
if( file_exists(WTRH_STORYBOARD_DIR."/writerhelper_storyboard.php") )
	include_once(WTRH_STORYBOARD_DIR."/writerhelper_storyboard.php");

// Writers/Editors module
if( file_exists(WTRH_WRITEDIT_DIR."/writerhelper_writerseditors.php") )
	include_once(WTRH_WRITEDIT_DIR."/writerhelper_writerseditors.php");

// ------------------------


//CRON names
if ( !defined('WTRH_PURGE_ACTIVITIES') ){
	define('WTRH_PURGE_ACTIVITIES', 'writerhelper_purge_activities');
}


class Writer_Helper
{

    public function __construct()    {
		global $wpdb;
					
		// Add the plugin menu to the main menu of Admin WordPress interface
		add_action('admin_menu', array($this, 'add_admin_menu'), 20);
	
		// add js script only on nomad-wp pages
		$page = isset($_GET['page'])? wtr_sanitize($_GET['page'],'title'):"";
		if ( $page == "wtr_helper" || substr($page,0,3) == "wtr" ){
            add_action('admin_enqueue_scripts', array(&$this, 'admin_menu_page_scripts'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_menu_page_styles'));
		}

		if (! is_admin() ) {
			// frontend script and style
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
		
		// Add CRON actions
		$this->cronActions();
	}	
	
	function admin_menu_page_scripts() 
    {
        wp_enqueue_script( 'wh-csspopupjs', WTRH_URL . '/js/css-popup.js', array('jquery') );
        wp_enqueue_script( 'wh-sortablejs', WTRH_URL . '/js/Sortable/Sortable.js', array('jquery') );
        wp_enqueue_script( 'wh-d3js', WTRH_URL . '/js/d3/d3.min.js', array('jquery') );
        wp_enqueue_script( 'ajax-script-wtr_helper-functions', WTRH_URL . '/js/wtr_helper-functions.js', array('jquery') );
        wp_enqueue_script( 'ajax-script-wtrh-books', WTRH_URL . '/js/wtrh-books.js', array('jquery') );
        wp_enqueue_script( 'ajax-script-wtrh-chapter', WTRH_URL . '/js/wtrh-chapter.js', array('jquery') );
        wp_enqueue_script( 'ajax-script-wtrh-scene', WTRH_URL . '/js/wtrh-scene.js', array('jquery') );
		
		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
		wp_localize_script( 'ajax-script-wtr_helper-functions', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
	}
	
    function admin_menu_page_styles() 
    {
        wp_enqueue_style('joint-css',      WTRH_JS_URL.   '/jointjs/joint.min.css');
        wp_enqueue_style('wtr_helper-css', WTRH_CSS_URL.  '/wtr_helper.css');
        wp_enqueue_style('wtrh-css-users', WTRH_CSS_URL.  '/users.css');
        wp_enqueue_style('wtrh-css-books', WTRH_CSS_URL.  '/books.css');
        wp_enqueue_style('wtrh-css-book', WTRH_CSS_URL.   '/book.css');
        wp_enqueue_style('wtrh-css-chapter', WTRH_CSS_URL.'/chapter.css');
        wp_enqueue_style('wtrh-css-scene', WTRH_CSS_URL.  '/scene.css');
        wp_enqueue_style('wtrh-css-gotob', WTRH_CSS_URL.  '/goto_buttons.css');
        wp_enqueue_style('wtrh-css-actionb', WTRH_CSS_URL.'/action_buttons.css');
		
		// add if

		// If Communities Pages
		if( isset($_GET['page']) && ( 
		    $_GET['page'] == WTRH_COMMUNITIES_MENU ) &&
			file_exists(WTRH_COMMUNITIES_DIR. '/css/whc_communities.css') ) {
			wp_enqueue_style('wtrh-css-communities', WTRH_URL. '/modules/communities/css/whc_communities.css', array(), 
							filemtime(WTRH_COMMUNITIES_DIR. '/css/whc_communities.css'), 'all');
		}
		
		// If Storyboard Pages
		if( isset($_GET['page']) && isset($_GET['tab']) && (
		  ( $_GET['page'] == WTRH_SETTINGS_MENU && $_GET['tab'] == 'settings_arcs' ) || 
		  ( $_GET['page'] == WTRH_BOOKS_MENU    && $_GET['tab'] == 'book_storyboard' ) ) &&
		    file_exists(WTRH_STORYBOARD_DIR. '/css/whs_storyboard.css') ) {
			wp_enqueue_style('wtrh-css-storyboard', WTRH_URL. '/modules/storyboard/css/whs_storyboard.css', array(), 
							filemtime(WTRH_STORYBOARD_DIR. '/css/whs_storyboard.css'), 'all');
		}
		
		// If Bookworld Pages
		if( isset($_GET['page']) && (
		  ( $_GET['page'] == WTRH_SETTINGS_MENU && isset($_GET['tab']) && $_GET['tab'] == 'settings_arcs' ) || 
		  ( $_GET['page'] == WTRH_BOOKWORLDS_MENU ) ) &&
		    file_exists(WTRH_BOOKWORLDS_DIR. '/css/whb_bookworld.css') ) {
			wp_enqueue_style('wtrh-css-bookworld', WTRH_URL. '/modules/bookworld/css/whb_bookworld.css', array(), 
							filemtime(WTRH_BOOKWORLDS_DIR. '/css/whb_bookworld.css'), 'all');
		}
    }
	
	
	/**********************
	* frontend enqueues
	***********************/
	public function enqueue_scripts() {
//		wp_enqueue_script( '', plugins_url( '.js', __FILE__ ), array('jquery'), $this->version );
	} // enqueue_scripts()

	public function enqueue_styles() {
        wp_enqueue_style('wtrh-css-book-post', WTRH_URL. '/css/book.css');
	} // enqueue_styles()
	
	/***************************************************************************************
	* Actions to do for plugin installation
	****************************************************************************************/
	public static function install()	{
		global $wtr_book_types;
		
wtr_info_log(__METHOD__,"create DB");
		$ret = wtr_createDB();

		// Create categories
		// --------------------------------------
		$lCats = WH_Category::getAll_Categories();
		if( count($lCats) == 0 ) {
			// 1- Book types
			$number=0;
			foreach( $wtr_book_types as $type) {
				$number++;
				$cat = new WH_Category(0, array('element' => WTRH_CAT_BOOKTYPE, 
												'number'  => $number, 
												'title'   => esc_html($type)));
				if( $cat->id == 0 && ! $cat->save() ) {
					wtr_error_log(__METHOD__, 
								  "add new category '".WTRH_CAT_BOOKTYPE."' : ".esc_html($type));
					$comment = 	array("type"     => "error",
									  "data"     => get_object_vars($cat),
									  "request"  => "",
									  "msg"      => "");
					WH_DB_Activity::addActivity(array(
													'element'     => wtr_get_class($cat),
													'element_id'  => 0, 
													'action'      => "save", 
													'comment'     => $comment));												
					$ret = false;
					break;
				}
			}



			// 2- Date format
			$cat = new WH_Category(0, array('element' => WTRH_CAT_DATEFORMAT, 
											'number'  => 0, 
											'title'   => "d-m-Y"));
			if( $cat->id == 0 && ! $cat->save() ) {
				wtr_error_log(__METHOD__, 
							  "add new category '".WTRH_CAT_DATEFORMAT."' : d-m-Y");
				$comment = 	array("type"     => "error",
								  "data"     => get_object_vars($cat),
								  "request"  => "",
								  "msg"      => "");
				WH_DB_Activity::addActivity(array(
												'element'     => wtr_get_class($cat),
												'element_id'  => 0, 
												'action'      => "save", 
												'comment'     => $comment));
				$ret = false;
			}
			
			
			
			// 3- Time format
			$cat = new WH_Category(0, array('element' => WTRH_CAT_TIMEFORMAT, 
											'number'  => 0, 
											'title'   => "H:i"));
			if( $cat->id == 0 && ! $cat->save() ) {
				wtr_error_log(__METHOD__, 
							  "add new category '".WTRH_CAT_TIMEFORMAT."' : H:i");
				$comment = 	array("type"     => "error",
								  "data"     => get_object_vars($cat),
								  "request"  => "",
								  "msg"      => "");
				WH_DB_Activity::addActivity(array(
												'element'     => wtr_get_class($cat),
												'element_id'  => 0, 
												'action'      => "save", 
												'comment'     => $comment));
				$ret = false;
			}
		}		
		
		
		// 4- Settings
		$bs = WH_Category::get_BooksSettings();
		// if no books' settings
		if( count($bs) == 0 ) {

			// 4.1 - Books' settings
			$tit  = "Authors";
			$desc = array('nbBooks'       => -1,
						  'useBookworld'  => false,
						  'nbBookworlds'  => 0,
						  'useStoryboard' => false, 
						  'useStatistics' => false, 
						  'useToDoList'   => false);
			$cat  = new WH_Category(0, array('element' => WTRH_CAT_BOOKSETTINGS,
											 'number'  => 1,
											 'title'   => $tit,
											 'description' => json_encode($desc)));
			if( $cat->id == 0 && ! $cat->save() ) {
				wtr_error_log(__METHOD__, 
							  "add new category '".WTRH_CAT_BOOKSETTINGS."' : ".$tit);
				$comment = 	array("type"     => "error",
								  "data"     => get_object_vars($cat),
								  "request"  => "",
								  "msg"      => "");
				WH_DB_Activity::addActivity(array(
												'element'     => wtr_get_class($cat),
												'element_id'  => 0, 
												'action'      => "save", 
												'comment'     => $comment));
				$ret = false;
			}
			$tit = "Editors";
			$desc = array('editAllBooks'  => false, 
						  'useStatistics' => false, 
						  'useToDoList'   => false);
			$cat = new WH_Category(0, array('element' => WTRH_CAT_BOOKSETTINGS,
											'number'  => 2,
											'title'   => $tit,
											'description' => json_encode($desc)));
			if( $cat->id == 0 && ! $cat->save() ) {
				wtr_error_log(__METHOD__, 
							  "add new category '".WTRH_CAT_BOOKSETTINGS."' : ".$tit);
				$comment = 	array("type"     => "error",
								  "data"     => get_object_vars($cat),
								  "request"  => "",
								  "msg"      => "");
				WH_DB_Activity::addActivity(array(
												'element'     => wtr_get_class($cat),
												'element_id'  => 0, 
												'action'      => "save", 
												'comment'     => $comment));
				$ret = false;
			}
			$tit = "Statuses";
			$desc = array(	WH_Status::DRAFT    ,
							WH_Status::TOEDIT   ,
							WH_Status::EDITING  ,
							WH_Status::EDITED   ,
							WH_Status::TOPUBLISH,
							WH_Status::PUBLISHED,
							WH_Status::TRASHED  );
			$cat = new WH_Category(0, array('element' => WTRH_CAT_BOOKSETTINGS,
											'number'  => 2,
											'title'   => $tit,
											'description' => json_encode($desc)));
			if( $cat->id == 0 && ! $cat->save() ) {
				wtr_error_log(__METHOD__, 
							  "add new category '".WTRH_CAT_BOOKSETTINGS."' : ".$tit);
				$comment = 	array("type"     => "error",
								  "data"     => get_object_vars($cat),
								  "request"  => "",
								  "msg"      => "");
				WH_DB_Activity::addActivity(array(
												'element'     => wtr_get_class($cat),
												'element_id'  => 0, 
												'action'      => "save", 
												'comment'     => $comment));
				$ret = false;
			}
		}		
		
		
		// Add admin users to WH_User
		// --------------------------------------
		addWriterHelperAdministrators();
		
		
		return $ret;
	}

	/***************************************************************************************
	** Actions to do for plugin uninstallation
	****************************************************************************************/
	public static function uninstall()	{

		// delete CRON actions
		$timestamp = wp_next_scheduled( WTRH_PURGE_ACTIVITIES );
		wp_unschedule_event( $timestamp,WTRH_PURGE_ACTIVITIES );
		
		return true;
	}
	
	/***************************************************************************************
	// Description of the plugin menu
	****************************************************************************************/
    public function add_admin_menu()    {
		
	}
	
	/***************************************************************************************
	// Action to do when form submit 
	****************************************************************************************/
	public function process_action()	{

	}
	/***************************************************************************************
	// Actions to do trhough CRON
	****************************************************************************************/
	public function cronActions() {

		add_action(WTRH_PURGE_ACTIVITIES, 'Writer_Helper::purgeActivities');
		if ( ! wp_next_scheduled( WTRH_PURGE_ACTIVITIES ) ) 
			wp_schedule_event( time(), 'daily', WTRH_PURGE_ACTIVITIES );

	}
	///////////////////////////////	
	//      PURGE ACTIVITIES     //
	///////////////////////////////	
	public static function purgeActivities()	{

		if( ! WH_Activity::purgeActivities() )
			wtr_error_log(__METHOD__,"Error on activities purge");
	}

		
}


?>