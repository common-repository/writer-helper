<?php
/*
 * Writer Helper common AJAX functions
 */
include_once('book_functions.php');
include_once('chapter_functions.php');
include_once('scene_functions.php');

// Create a list of books to display
add_action( 'wp_ajax_wtrh_ajax_getBooksList', 'wtrh_ajax_getBooksList' );
function wtrh_ajax_getBooksList() {

	$title  = isset( $_POST['title'] ) ? wtr_sanitize($_POST['title'],'title') : "";	
	$status = isset( $_POST['status'] ) ? wtr_sanitize($_POST['status'],'title') : "";	
//	wtr_info_log(__METHOD__, "title=".$title);

	$statuses = array();
	if( $status != "" )
		foreach( explode(',' ,$status) as $st )
			if( in_array($st, WH_Status::ALL_STATUSES) )
				$statuses[] = $st;
	
	echo "OK;".wtrh_getPageHTMLBooksList($title, $statuses);
	wp_die();
}

// Create a list of books to select
add_action( 'wp_ajax_wtrh_ajax_getBooksListSelection', 'wtrh_ajax_getBooksListSelection' );
function wtrh_ajax_getBooksListSelection() {

	$title = isset( $_POST['title'] ) ? wtr_sanitize($_POST['title'],'title') : "";	
	$div   = isset( $_POST['div'] )   ? wtr_sanitize($_POST['div']  ,'title') : "";	
//	wtr_info_log(__METHOD__, "title=".$title);
	
	if( $div == "" )
		echo "KO;".__('No HTML div set','wtr_helper');
	else
		echo "OK;".wtrh_getHTMLBooksListSelection($title, $div);
	
	wp_die();
}


// Format date or time
add_action( 'wp_ajax_wtrh_ajax_formatDate', 'wtrh_ajax_formatDate' );
function wtrh_ajax_formatDate() {

	$type   = isset( $_POST['type'] )   ? wtr_sanitize($_POST['type']  ,'title') : "";	
	$format = isset( $_POST['format'] ) ? wtr_sanitize($_POST['format'],'title') : "";	
wtr_info_log(__METHOD__, "type=".$type."  format=".$format);
	
	echo "OK;".date($format);
	wp_die();
}


// Install module new version
add_action( 'wp_ajax_wtrh_ajax_installVersion', 'wtrh_ajax_installVersion' );
function wtrh_ajax_installVersion() {

	$file   = isset( $_POST['file'] )   ? wtr_sanitize($_POST['file']  ,'title') : "";	
wtr_info_log(__METHOD__, "file=".basename($file));
	$msg = "";
	$continue = true;
	
	$farray = explode("_", basename($file));
	$module_name = $farray[1];
	$fname = basename($file);
	
	$dirZip   = WTRH_MODULES_DIR;
	switch( $module_name ) {
		case "storyboard": 
			$dirCible = WTRH_MODULES_DIR."/storyboard/";
			break;
		case "bookworld":
			$dirCible = WTRH_MODULES_DIR."/bookworld/";
			break;
		case "writerseditors":
			$dirCible = WTRH_MODULES_DIR."/writers_editors/";
			break;
		case "readers":
			$dirCible = WTRH_MODULES_DIR."/readers/";
			break;
		case "communities":
			$dirCible = WTRH_MODULES_DIR."/communities/";
			break;
		default:
			$continue = false;
			$msg = sprintf(__('Unknown add-on name %s','wtr_helper'),$module_name)."<br/>".
				   __('Nothing has been installed','wtr_helper');
	}
	
	// File exists
	$head = get_headers($file);
	if( ! stripos($head[0],"200 OK") ){
		$continue = false;
		$msg = sprintf(__('File %s not found','wtr_helper'), $fname)."<br/>".
			   __('Nothing has been installed','wtr_helper');
	}
	
	// create "modules" directory
	if( $continue && ! file_exists($dirZip) )
		if( ! mkdir( $dirZip ) ) {
			$continue = false;
			$msg = sprintf(__('Cannot create directory %s','wtr_helper'), $dirZip)."<br/>".
				   __('Nothing has been installed','wtr_helper');
		}
	
	// create module directory
	if( $continue && ! file_exists($dirCible) )
		if( ! mkdir( $dirCible ) ) {
			$continue = false;
			$msg = sprintf(__('Cannot create directory %s','wtr_helper'), $dirCible)."<br/>".
				   __('Nothing has been installed','wtr_helper');
		}
	
	// install zip file
	if( $continue ){

		// copy file		
		$uploadfile = $dirZip."/".basename($file);
		$fh = fopen($uploadfile, "w");
		if( $fh === false ){
			$continue = false;
			$msg = sprintf(__('Cannot create file %s','wtr_helper'), $uploadfile)."<br/>".
				   __('Nothing has been installed','wtr_helper');
		}
		if( $continue && fwrite($fh, file_get_contents($file)) === false ){
			$continue = false;
			$msg = sprintf(__('Cannot updload file %s','wtr_helper'), $fname)."<br/>".
				   __('Nothing has been installed','wtr_helper');
		}
		fclose($fh);
		
		// unzip file
		$zip = new ZipArchive;
		
		if( $continue && $zip->open($uploadfile) !== true ) {
			$continue = false;
			$msg = sprintf(__('Cannot open file %s','wtr_helper'), $fname)."<br/>".
				   __('Nothing has been installed','wtr_helper');
		}
			
		if( $continue && $zip->extractTo($dirCible) === false ){
			$continue = false;
			$msg = sprintf(__('Cannot unzip file %s to directory %s','wtr_helper'), $fname, $dirCible)."<br/>".
				   __('Nothing has been installed','wtr_helper');
		}
		if( $continue )
			$msg = __('Module installed','wtr_helper')."<br/>";
				
		$zip->close();
		
		if( $continue && ! unlink($uploadfile) ){
			$continue = false;
			$msg = sprintf(__('Cannot delete file %s','wtr_helper'), $uploadfile)."<br/>".
				   __('Nothing has been installed','wtr_helper');
		}
	}
	
	echo (($continue)?"OK;":"KO;").$msg;
	wp_die();
}


// Change the format of date or time
add_action( 'wp_ajax_wtrh_ajax_changeFormatDate', 'wtrh_ajax_changeFormatDate' );
function wtrh_ajax_changeFormatDate() {
	$result = false;
	$type   = isset( $_POST['type'] )  ? wtr_sanitize($_POST['type']  ,'title') : "";	
	$format = isset( $_POST['format'] )? wtr_sanitize($_POST['format'],'title') : "";	
wtr_info_log(__METHOD__, "type=".$type."  format=".$format);
	
	if( $type == "date" ) 
		$result = WH_Category::update_DateFormat($format);
	if( $type == "time" ) 
		$result = WH_Category::update_TimeFormat($format);
	
	if( $result )
		echo "OK;";
	else
		echo "KO;";
	wp_die();
}


// Add/delete a Category
add_action( 'wp_ajax_wtrh_ajax_manageCategory', 'wtrh_ajax_manageCategory' );
function wtrh_ajax_manageCategory() {

	$type     = isset( $_POST['type'] )    ? wtr_sanitize($_POST['type']     ,'title') : "";	
	$id       = isset( $_POST['id'] )      ? wtr_sanitize($_POST['id']       ,'int')   : 0;	
	$element  = isset( $_POST['element'] ) ? wtr_sanitize($_POST['element']  ,'title') : "";	
	$title    = isset( $_POST['title'] )   ? wtr_sanitize($_POST['title']    ,'title') : "";	
	$number   = isset( $_POST['number'] )  ? wtr_sanitize($_POST['number']   ,'int')   : 0;	
	$desc     = isset( $_POST['desc'] )    ? wtr_sanitize($_POST['desc']     ,'text')  : "";	
	$parent_id= isset( $_POST['parent_id'])? wtr_sanitize($_POST['parent_id'],'int')   : 0;	
	$msg = "";
	$cat = null;
wtr_info_log(__METHOD__, "type=".$type." id=".$id." element=".$element." title=".$title);


	// Update and Display Default Book Settings
	// -------------------------------------------------------
	if( $element == WTRH_CAT_BOOKSETTINGS ) {
		$settings = new WH_BookSettings(0);
		
		if( $type == "update" ) { // update Default Books Settings

			$settings->updateSettings(html_entity_decode($desc));
			if( ! $settings->save() )
				echo "KO;".__('Default Books Settings not saved','wtr_helper');
			else
				echo "OK;";
		}
		
		if( $type == "delete" ) { // update Delete Specific Books' Settings

			if( ! WH_BookSettings::deleteAllBooksSettings() )
				echo "KO;".__('Error while deleting specific books settings','wtr_helper');
			else
				echo "OK;";
		}
		
		goto wtrh_ajax_manageCategory_end;
	}
	

	// Save Authors or Editors settings
	// -------------------------------------------------------
	if( $type == "modifyAuthorsSettings" ||
	    $type == "modifyEditorsSettings" ) {
		
		if( $id == 0 ) {
			echo "KO;".__('Category Id not found','wtr_helper');
			goto wtrh_ajax_manageCategory_end;
		}
		
		$my_cat = new WH_Category($id);
		
		if( $my_cat->element != WTRH_CAT_BOOKSETTINGS ){
			echo "KO;".__('Category Id incorrect','wtr_helper');
			goto wtrh_ajax_manageCategory_end;
		}
		
		$my_cat->description =  wtr_sanitize($_POST['desc'],'html');
		if( ! $my_cat->save() )
			echo "KO;".sprintf(__('Error while saving category %s','wtr_helper'), WTRH_CAT_BOOKSETTINGS);
		else
			echo "OK;".__('Data saved','wtr_helper');
		
		goto wtrh_ajax_manageCategory_end;
	}
	

	// Get a list of categories
	// -------------------------------------------------------
	$types = WH_Category::getAll_Categories($element);
	// Add a new category
	if( $type == "add" && $title != "" ) {
		// if category already exists, do not create
		$found = false;
		foreach( $types as $t ) {
			if( wtr_ucase($t->title) == wtr_ucase($title) ) {
				$found = true;
				break;
			}
		}
		if( ! $found ) {
			$cat = new WH_Category(0, array('element' => $element, 
											'number'  => $number, 
											'title'   => $title,
											'description' => $desc,
											'parent_id'   => $parent_id));
			$cat->save();
		}
	}
	// Delete a category
	if( $type == "delete" && $id != 0 ) {
		$cat = new WH_Category($id);
		
		if( $element == WTRH_CAT_BOOKTYPE ) { // count books by type
			$books = WH_Book::getAll_BooksByType($cat->title);
			if( count($books) == 0 )
				$cat->delete();
			else
				$msg = __('Not deleted','wtr_helper').". ".
						__('Books are related to this type','wtr_helper').". ".
						__('Modify or delete books first','wtr_helper').".";
		} else
			$cat->delete();
	}
	// Update a category
	if( $type == "update" && $id != 0 ) {
		$cat              = new WH_Category($id);
		$cat->title       = $title;
		$cat->number      = $number;
		$cat->description = $desc;
		$cat->parent_id   = $parent_id;
		$cat->save();
	}
	
	$html = "<table class='whBookTypesList'>";
	$types = WH_Category::get_BookTypes();
	foreach( $types as $t ) {
		$nb_books = 0;
		$html .= "<tr><td>".__($t->title,'wtr_helper')."</td>";
		if( $element == WTRH_CAT_BOOKTYPE ) { // count books by type
			$books = WH_Book::getAll_BooksByType($t->title);
			$nb_books = count($books);
			if( $nb_books > 1 )
				$html .= "<td>(".$nb_books." ".__('books','wtr_helper').")</td>";
			else
				$html .= "<td>(".$nb_books." ".__('book','wtr_helper').")</td>";
		} else
			$html .= "<td></td>";
		$html .= "<td>";
		$html .= "<button type='button' class='whCategoryButton'".
				 " onclick='wtr_manageCategory(\"delete\",".$t->id.",\"".$element."\")'";
		if( $nb_books > 0 )
			$html .= " disabled";
		$html .= ">".__('Delete','wtr_helper')."</button>";
		$html .= "</td>";
		if( $cat != null && $cat->id == $t->id )
			$html .= "<td>".$msg."</td>";
		else
			$html .= "<td></td>";
		$html .= "</tr>";
	}
	$html .= "</table>";
	
	echo "OK;".$html;
	
wtrh_ajax_manageCategory_end:
	wp_die();
}


// Add/delete a user
add_action( 'wp_ajax_wtrh_ajax_manageUser', 'wtrh_ajax_manageUser' );
function wtrh_ajax_manageUser() {

	$type     = isset( $_POST['type'] )    ? wtr_sanitize($_POST['type']     ,'title') : "";	
	$id       = isset( $_POST['id'] )      ? wtr_sanitize($_POST['id']       ,'int')   : 0;	
	$book_id  = isset( $_POST['book_id'] ) ? wtr_sanitize($_POST['book_id']  ,'int')   : 0;	
wtr_info_log(__METHOD__, "type=".$type." id=".$id." book_id=".$book_id);
	$role = "";
	
	// Add Admin
	// -------------------------------------------------------
	if( $type == "addAdmin" && $id == 0 ) {
		echo "KO;".__('Error user id not found','wtr_helper');
		goto wtrh_ajax_manageUser_end;
	}
	if( $type == "addAdmin" && $id != 0 && $book_id == 0 ) {
				
		if( ! addWriterHelperAdministrator($id) ) {
			echo "KO;".__('Error while adding administrator user','wtr_helper');
			goto wtrh_ajax_manageUser_end;
		}
	}
	
	// Add Writer
	// -------------------------------------------------------
	if( $type == "addWriter" && $id == 0 ) {
		echo "KO;".__('Error user id not found','wtr_helper');
		goto wtrh_ajax_manageUser_end;
	}
	if( $type == "addWriter" && $id != 0 && $book_id == 0 ) {
		$role = WTRH_ROLE_AUTHOR;
		$usr  = new WH_User(0, array('user_id'=>$id));
		$usr->save();
		$usr->addRole(WTRH_ROLE_AUTHOR);
		$usr->addRole(WTRH_ROLE_EDITOR);
		
		if( ! $usr->save() ) {
			echo "KO;".__('Error while saving user','wtr_helper');
			goto wtrh_ajax_manageUser_end;
		}
	}
	
	// Add Editor
	// -------------------------------------------------------
	if( $type == "addEditor" && $id == 0 ) {
		echo "KO;".__('Error user id not found','wtr_helper');
		goto wtrh_ajax_manageUser_end;
	}
	if( $type == "addEditor" && $id != 0 && $book_id == 0 ) {
		$role = WTRH_ROLE_EDITOR;
		$usr  = new WH_User(0, array('user_id'=>$id));
		$usr->save();
		$usr->addRole(WTRH_ROLE_EDITOR);
		
		if( ! $usr->save() ) {
			echo "KO;".__('Error while saving user','wtr_helper');
			goto wtrh_ajax_manageUser_end;
		}
	}
	
	// Add Reader
	// -------------------------------------------------------
	if( $type == "addReader" && $id == 0 ) {
		echo "KO;".__('Error user id not found','wtr_helper');
		goto wtrh_ajax_manageUser_end;
	}
	if( $type == "addReader" && $id != 0 && $book_id == 0 ) {
		$role = WTRH_ROLE_READER;
		$usr  = new WH_User(0, array('user_id'=>$id));
		$usr->save();
		$usr->addRole(WTRH_ROLE_READER);
		
		if( ! $usr->save() ) {
			echo "KO;".__('Error while saving user','wtr_helper');
			goto wtrh_ajax_manageUser_end;
		}
	}
	
	// Add Reader Premium
	// -------------------------------------------------------
	if( $type == "addReaderP" && $id == 0 ) {
		echo "KO;".__('Error user id not found','wtr_helper');
		goto wtrh_ajax_manageUser_end;
	}
	if( $type == "addReaderP" && $id != 0 && $book_id == 0 ) {
		$role = WTRH_ROLE_READERP;
		$usr  = new WH_User(0, array('user_id'=>$id));
		$usr->save();
		$usr->addRole(WTRH_ROLE_READERP);
		
		if( ! $usr->save() ) {
			echo "KO;".__('Error while saving user','wtr_helper');
			goto wtrh_ajax_manageUser_end;
		}
	}
	

	// Delete a user
	// -------------------------------------------------------
	if( $type == "delete" && $id == 0 ) {
		echo "KO;".__('Error user id not found','wtr_helper');
		goto wtrh_ajax_manageUser_end;
	}
	if( $type == "delete" && $id != 0 && $book_id == 0 ) {
		$usr = new WH_User($id);
		if( ! $usr->delete() ){
			echo "KO;".__('Error while deleting user','wtr_helper');
			goto wtrh_ajax_manageUser_end;
		}
	}
		
	// Return a table of type of users
	$users = array();
	switch( $role ) {
		case WTRH_ROLE_AUTHOR :
			$users = WH_User::getAll_Authors();
			break;
		case WTRH_ROLE_EDITOR :
			$users = WH_User::getAll_Editors();
			break;
		case WTRH_ROLE_READER :
			$users = WH_User::getAll_Readers();
			break;
		case WTRH_ROLE_READERP:
			$users = WH_User::getAll_ReadersPremium();
			break;
		default:
			$users = WH_User::getAll_Admins();
			break;
	}
	
	
	// List of Users from role
	$html = "<table class='wh_usersList'>\n";

	$html .= "<tr class='wh_userInfoHeader'>";
	$html .= "<th>".__('Role','wtr_helper')."</th>";
	$html .= "<th>".__('WordPress display name','wtr_helper')."</th>";
	$html .= "<th></th>";
	$html .= "</tr>";

	foreach( $users as $u ) {
		$html .= "<tr class='wh_userInfo'>";
		$html .= "<td>".$u->meta_key."</td>";
		$html .= "<td>".WH_User::getWpUserName($u->user_id)."</td>";
		$html .= "<td><button class='whDeleteUser' ".
				"onclick='wtr_manageUser(\"delete\",".$u->id.")'>".
				__('Delete','wtr_helper')."</button></td>";
		$html .= "</tr>";
	}
	$html .= "</table>";
	
	echo "OK;".$html;
	
wtrh_ajax_manageUser_end:
//wtr_info_log(__METHOD__, "$html");
	wp_die();
}


// Get a list of WordPress user
add_action( 'wp_ajax_wtrh_ajax_listWordPressUsers', 'wtrh_ajax_listWordPressUsers' );
function wtrh_ajax_listWordPressUsers() {

	$type     = isset( $_POST['type'] )    ? wtr_sanitize($_POST['type']     ,'title') : "";	
	$name     = isset( $_POST['name'] )    ? wtr_sanitize($_POST['name']     ,'title') : "";	
	$role     = isset( $_POST['role'] )    ? wtr_sanitize($_POST['role']     ,'title') : "";	
	$book_id  = isset( $_POST['book_id'] ) ? wtr_sanitize($_POST['book_id']  ,'int')   : 0;	
wtr_info_log(__METHOD__, "type=".$type." name=".$name." role=$role"." book_id=$book_id");
	$wp_role  = "";
	$action_role = $role;
	
	if( $type == "listEditors" )
		$role = WTRH_ROLE_EDITOR;
	if( $type == "listAuthors" )
		$role = WTRH_ROLE_AUTHOR;
	
	switch($role){
		case WTRH_ROLE_ADMIN :
			$wp_role     = "Administrator";
			$action_role = "Admin";
			break;
		case WTRH_ROLE_AUTHOR :
			$wp_role     = "Author";
			break;
		case WTRH_ROLE_EDITOR :
		case WTRH_ROLE_READER :
		case WTRH_ROLE_READERP:
		default:
			//$wp_role     = array("Administrator","Author","Editor","Contributor","Subscriber");
			break;
	}
	if( $role == WTRH_ROLE_READERP )
		$action_role = "ReaderP";
	
	// users list to display
	$users       = array();
	// users id to exclude
	$excluded_ids = array();
	
	// If Book selected, get Writer Helper Users not already in
	if( $book_id != 0 ) {
		$book        = new WH_Book($book_id);
		$excluded_ids = $book->getUsersId($role);

		// Get the Writer Helper Users
		$wh_users = WH_User::getAll_UsersForRoles(array($role));
		foreach( $wh_users as $u )
			$users[] = array('id'           => $u->user_id,
							 'nice_name'    => "",
							 'display_name' => WH_User::getWpUserName($u->user_id)
							 );
		
	} else { 
	// Get WordPress Users not in Writer Helper Users
		$params = array();
		//if( $wp_role != "" )
		//	$params['role'] = $wp_role;
		
		// Get the list of WP Users
		$wp_users = get_users($params);
		foreach( $wp_users as $u )
			$users[] = array('id'           => $u->ID,
							 'nice_name'    => $u->user_nicename,
							 'display_name' => $u->display_name
							 );
		
		// Get the Writer Helper Users
		$wh_users = WH_User::getAll_UsersForRoles(array($role));
		foreach( $wh_users as $u )
			$excluded_ids[] = $u->user_id;
	}
	
	$found = false;
	$html  = "";
	foreach( $users as $u ) {
		// look for user with same name but not already in the list
		if( ! in_array($u['id'], $excluded_ids) &&
			(strpos($u['nice_name'], $name) !== false ||
			 strpos($u['display_name'], $name) !== false)  ) {
			$found = true;
			if( $book_id == 0 )
				$html .= "<a class='whAddUserOption' ".
					 "onclick='wtr_manageUser(\"add".$action_role."\",".$u['id'].")'>".
					 $u['nice_name']." / ".$u['display_name']."</a>";
			else {
				if( $role == WTRH_ROLE_AUTHOR )
					$html .= "<a class='whAddUserOption' onclick='wtr_addAuthor(".$u['id'].", \"".$u['display_name']."\")'>".
					 $u['nice_name']." / ".$u['display_name']."</a>";
				if( $role == WTRH_ROLE_EDITOR )
					$html .= "<a class='whAddUserOption' onclick='wtr_addEditor(".$u['id'].", \"".$u['display_name']."\")'>".
					 $u['nice_name']." / ".$u['display_name']."</a>";
			}
		}
	}
	
	if( ! $found ) 
		$html = __('No user found','wtr_helper');
	
	echo "OK;".$html;
	
wtrh_ajax_listWordPressUsers_end:
	wp_die();
}


// Create/delete/change status of books
add_action( 'wp_ajax_wtrh_ajax_manageBook', 'wtrh_ajax_manageBook' );
function wtrh_ajax_manageBook() {

	$type       = isset( $_POST['type'] )       ? wtr_sanitize($_POST['type']  ,'title')   : "";	
	$id         = isset( $_POST['id'] )         ? wtr_sanitize($_POST['id']    ,'int')     : 0;	
	$img_id     = isset( $_POST['img_id'] )     ? wtr_sanitize($_POST['img_id'],'int')     : 0;	
	$status     = isset( $_POST['status'] )     ? wtr_sanitize($_POST['status'],'int')     : 0;	
	$title      = isset( $_POST['title'] )      ? wtr_sanitize($_POST['title'] ,'title')   : "";	
	$btype      = isset( $_POST['btype'] )      ? wtr_sanitize($_POST['btype'] ,'text')    : "";	
	$resume     = isset( $_POST['resume'] )     ? wtr_sanitize($_POST['resume'],'text')    : "";	
	$saleUrl    = isset( $_POST['saleUrl'] )    ? wtr_sanitize($_POST['saleUrl'],'url')    : "";	
	$promoUrl   = isset( $_POST['promoUrl'] )   ? wtr_sanitize($_POST['promoUrl'],'url')   : "";	
	$opinionUrl = isset( $_POST['opinionUrl'] ) ? wtr_sanitize($_POST['opinionUrl'],'url') : "";	
	$isbn       = isset( $_POST['isbn'] )       ? wtr_sanitize($_POST['isbn'],'title')     : "";	
	$cs_list    = isset( $_POST['cs_list'] )    ? wtr_sanitize($_POST['cs_list'],'title')  : "";	
	$user_id    = isset( $_POST['user_id'] )    ? wtr_sanitize($_POST['user_id'],'int')    : 0;	
	$user_name  = isset( $_POST['user_name'] )  ? wtr_sanitize($_POST['user_name'],'title'): "";	
	$user_role  = isset( $_POST['user_role'] )  ? wtr_sanitize($_POST['user_role'],'title'): "";	

	$msg  = "";
	$html = "";
	$ret = true;
	wtr_info_log(__METHOD__, "type=".$type.
					" id=".$id." status=".$status." user_id=".$user_id.
					" user_name=".$user_name." cs_list=".$cs_list);

	// Create a new book
	if( $type == "create" ) {
		
		$book = new WH_Book(0, array('title'     => html_entity_decode($title), 
		                             'resume'    => html_entity_decode($resume), 
									 'type'      => $btype, 
								     'status'    => WH_Status::DRAFT, 
								     'game_book' => (bool)$id));		
		if( ! $book->save() )
			$msg .= __('Error book create','wtr_helper')."\n";
		else {
			$id = $book->id;
		
			// Add author
			if( ! $book->add_Author(get_current_user_id()) )
				$msg .= __('Error book add author','wtr_helper')."\n";
			// Add editor
			if( ! $book->add_Editor(get_current_user_id()) )
				$msg .= __('Error book add editor','wtr_helper')."\n";
		}
		$html = $id;
	}
	
	// Update a book
	if( $type == "update" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "update" && $id != 0 ) {
		$book = new WH_Book($id);
		if( strlen($title) > 0 )
			$book->title  = html_entity_decode($title);
		if( $img_id != 0 )
			$book->cover  = json_encode(get_post($img_id));
		else
			$book->cover  = '';
		if( strlen($resume) > 0 )
			$book->resume = $resume;
		if( strlen($btype) > 0 )
			$book->type   = $btype;
		if( strlen($saleUrl) > 0 ) {
			if( substr($saleUrl, 0, 4) != "http" && substr($saleUrl, 0, 5) != "https" )
				$saleUrl = "http://".$saleUrl;
			$book->sale_url    = $saleUrl;
		}
		if( strlen($promoUrl) > 0 ) {
			if( substr($promoUrl, 0, 4) != "http" && substr($promoUrl, 0, 5) != "https" )
				$promoUrl = "http://".$promoUrl;
			$book->promo_url   = $promoUrl;
		}
		if( strlen($opinionUrl) > 0 ) {
			if( substr($opinionUrl, 0, 4) != "http" && substr($opinionUrl, 0, 5) != "https" )
				$opinionUrl = "http://".$opinionUrl;
			$book->opinion_url = $opinionUrl;
		}
		if( strlen($isbn) > 0 ) {
			$book->isbn = $isbn;
		}
		
		if( ! $book->save() ) 
			$msg = __('Error book udpate','wtr_helper');
	}
	
	// Update book's chapters
	if( $type == "updateChaptersList" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "updateChaptersList" && $id != 0 ) {
		$book = new WH_Book($id);
		
		// Change chapters & scenes numbers
		foreach( explode("c", $cs_list) as $ch_w ) {
			$ch = null;
			foreach( explode("s", $ch_w) as $key => $list ) {
				if( strlen(trim($list)) > 0 ) {
					if( $key == 0 ) { // chapter
						$l2 = explode("-", $list);
						$ch = new WH_Chapter(intval($l2[0]));
						if( $ch->number != intval($l2[1]) ) {
							$ch->number = intval($l2[1]);
							$ch->save();
						}
					} else { // scene
						$l3 = explode("-", $list);
						$sc = new WH_Scene(intval($l3[0]));
						if( $sc->number != intval($l3[1]) || $sc->chapter_id != $ch->id ) {
							$sc->number = intval($l3[1]);
							$sc->chapter_id = $ch->id;
							$sc->save();
						}
					}
				}
			}
		}
		
	}
	
	// Update book's display info
	if( $type == "updateBookDisplaySettings" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "updateBookDisplaySettings" && $id != 0 ) {
		$settings = new WH_BookSettings($id);
		
//wtr_info_log(__METHOD__, "desc=".html_entity_decode($cs_list));
		$settings->updateSettings(html_entity_decode($cs_list));
		$settings->save();
	}
	
	// Update book's info
	if( $type == "updateBookSettings" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "updateBookSettings" && $id != 0 ) {
		$book = new WH_Book($id);
		$book->get_BookInfo();
		$book->book_info=json_decode($cs_list, true);
		if( ! is_numeric($book->book_info['freeChapter']) || strlen(trim($book->book_info['freeChapter'])) == 0 )
			$book->book_info['freeChapter'] = -1;
		$book->save();
	}
	
	// Delete a book
	if( $type == "delete" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "delete" && $id != 0 ) {
		$book = new WH_Book($id);
		if( ! $book->delete() ) 
			$msg = __('Error book delete','wtr_helper');
	}
	
	// Change book status
	if( $type == "status" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "status" && $id != 0 ) {
		$book = new WH_Book($id);
		
//wtr_info_log(__METHOD__, "BEFORE book status=".$book->status);
		switch( $status ) {
			case WH_Status::DRAFT    :	$ret = $book->unpublish(); break;
			case WH_Status::TOEDIT   :	$ret = $book->endDrafting(); break;
			case WH_Status::EDITING  :	$ret = $book->startEditing(); break;
			case WH_Status::EDITED   :	$ret = $book->endEditing(); break;
			case WH_Status::TOPUBLISH:	$ret = $book->toPublish(); break;
			case WH_Status::PREVIEW  :	$ret = $book->preview(); break;
			case WH_Status::HIDDEN   :	$ret = $book->hide(); break;
			case WH_Status::PUBLISHED:	$ret = $book->publish(); break;
			case WH_Status::ARCHIVED :	$ret = $book->archive(); break;
			case WH_Status::ARC_UNP  :	$ret = $book->archiveUnpublish(); break;
			case WH_Status::TRASHED  :	$ret = $book->trash(); break;
			default: $msg = sprintf(__('Book status unknown : %s','wtr_helper'), $status);
		}
//wtr_info_log(__METHOD__, "AFTER book status=".$book->status);
		
		if( ! $ret )
			$msg = __('Not all chapters/scenes are at the same status.','wtr_helper')."\n".
				   __('Change status of chapters or/and scenes individually.','wtr_helper');
	}
	
	// Refresh book status
	if( $type == "refreshStatus" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "refreshStatus" && $id != 0 ) {
		$book = new WH_Book($id);
		
		$old_status = $book->status;
		$book->refreshStatus();
//wtr_info_log(__METHOD__, "book old status=".$old_status." new status=".$book->status);
		
		$html = "<span class='whStatus' style='".
				WH_Status::getStatusStyle($book->status)."'>&nbsp;".
				WH_Status::getStatusName($book->status)."&nbsp;</span>\n";
	}
	
	// Add author
	if( $type == "addAuthor" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "addAuthor" && $user_id == 0 ) {
		$msg = __('Error author id not found','wtr_helper');
	}
	if( $type == "addAuthor" && $id != 0 && $user_id != 0 ) {
		$book = new WH_Book($id);		
		
		if( ! $book->add_Author($user_id) )
			$msg = __('Error add book author','wtr_helper');
		else {
			$html = "<table class='whBookAuthorsList'>\n";
			foreach( $book->authors as $au ) {
				$html .= "<tr><td> - </td><td>";
				$html .= "<input type='text' id='whAuthorName".$au['id']."' ".
						"title='".sprintf(__('WP User is %s','wtr_helper'), WH_User::getWpUserName($au['id']))."' ".
						"value='".stripslashes($au['name'])."' >";
				$html .= "</td><td><button type='button' onclick='wtr_changeAuthorName(".$au['id'].")'>".
						__('Change name','wtr_helper')."</button></td>";
				$html .= "<td><button type='button' ";
				$html .= "onclick='wtr_deleteAuthor(".$au['id'].")'>";
				$html .= "X</button></td></tr>";
			}
			$html .= "<tr><td> - </td><td>";
			$html .= "<input type='text' id='whAddAuthorName' value=''";
			$html .= "	oninput='wtr_listAuthors()'";
			$html .= "	placeholder='".__('Enter an author name','wtr_helper')."'>";
			$html .= "<div id='whAddAuthorNameDiv'></div>";
			$html .= "</td><td>".__('Add author','wtr_helper')."</td><td></td></tr>";
			$html .= "</table>\n";
		}
	}
	
	// Delete author
	if( $type == "deleteAuthor" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "deleteAuthor" && $user_id == 0 ) {
		$msg = __('Error author id not found','wtr_helper');
	}
	if( $type == "deleteAuthor" && $id != 0 && $user_id != 0 ) {
		$book = new WH_Book($id, null, true);		
		
		if( ! $book->delete_Author($user_id) )
			$msg = __('Error delete book author','wtr_helper');
		else {
			$html = "<table class='whBookAuthorsList'>\n";
			foreach( $book->authors as $au ) {
				$html .= "<tr><td> - </td><td>";
				$html .= "<input type='text' id='whAuthorName".$au['id']."' ".
						"title='".sprintf(__('WP User is %s','wtr_helper'), WH_User::getWpUserName($au['id']))."' ".
						"value='".stripslashes($au['name'])."' >";
				$html .= "</td><td><button type='button' onclick='wtr_changeAuthorName(".$au['id'].")'>".
						__('Change name','wtr_helper')."</button></td>";
				$html .= "<td><button type='button' ";
				$html .= "onclick='wtr_deleteAuthor(".$au['id'].")'>";
				$html .= "X</button></td></tr>";
			}
			$html .= "<tr><td> - </td><td>";
			$html .= "<input type='text' id='whAddAuthorName' value=''";
			$html .= "	oninput='wtr_listAuthors()'";
			$html .= "	placeholder='".__('Enter an author name','wtr_helper')."'>";
			$html .= "<div id='whAddAuthorNameDiv'></div>";
			$html .= "</td><td>".__('Add author','wtr_helper')."</td><td></td></tr>";
			$html .= "</table>\n";
		}
	}
	
	// Change author name
	if( $type == "changeAuthorName" && $user_id == 0 ) {
		$msg = __('Error author id not found','wtr_helper');
	}
	if( $type == "changeAuthorName" && $user_id != 0 ) {
		$book = new WH_Book($id);		
		$book->add_Author($user_id, $user_name);
		
		if( ! $book->save() )
			$msg = __('Error update author name','wtr_helper');
		else {
			$html = $user_name;
		}
	}
	
	// Add editor
	if( $type == "addEditor" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "addEditor" && $user_id == 0 ) {
		$msg = __('Error editor id not found','wtr_helper');
	}
	if( $type == "addEditor" && $id != 0 && $user_id != 0 ) {
		$book = new WH_Book($id, null, true);		
		
		if( ! $book->add_Editor($user_id) )
			$msg = __('Error add book editor','wtr_helper');
		else {
			$html = "<table class='whBookEditorsList'>\n";
			foreach( $book->editors as $ed ) {
				$html .= "<tr><td> - </td><td>";
				$html .= "<input type='text' id='whEditorName".$ed['id']."' ".
						"title='".sprintf(__('WP User is %s','wtr_helper'), WH_User::getWpUserName($ed['id']))."' ".
						"value='".stripslashes($ed['name'])."' >";
				$html .= "</td><td><button type='button' onclick='wtr_changeEditorName(".$ed['id'].")'>".
						__('Change name','wtr_helper')."</button></td>";
				$html .= "<td><button type='button' ";
				$html .= "onclick='wtr_deleteEditor(".$ed['id'].")'>";
				$html .= "X</button></td></tr>";
			}
			$html .= "<tr><td> - </td><td>";
			$html .= "<input type='text' id='whAddEditorName' value=''";
			$html .= "	oninput='wtr_listEditors()'";
			$html .= "	placeholder='".__('Enter an author name','wtr_helper')."'>";
			$html .= "<div id='whAddEditorNameDiv'></div>";
			$html .= "</td><td>".__('Add editor','wtr_helper')."</td><td></td></tr>";
			$html .= "</table>\n";
		}
	}
	
	// Delete editor
	if( $type == "deleteEditor" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "deleteEditor" && $user_id == 0 ) {
		$msg = __('Error editor id not found','wtr_helper');
	}
	if( $type == "deleteEditor" && $id != 0 && $user_id != 0 ) {
		$book = new WH_Book($id, null, true);		
		
		if( ! $book->delete_Editor($user_id) )
			$msg = __('Error delete book editor','wtr_helper');
		else {
			$html = "<table class='whBookEditorsList'>\n";
			foreach( $book->editors as $ed ) {
				$html .= "<tr><td> - </td><td>";
				$html .= "<input type='text' id='whEditorName".$ed['id']."' ".
						"title='".sprintf(__('WP User is %s','wtr_helper'), WH_User::getWpUserName($ed['id']))."' ".
						"value='".stripslashes($ed['name'])."' >";
				$html .= "</td><td><button type='button' onclick='wtr_changeEditorName(".$ed['id'].")'>".
						__('Change name','wtr_helper')."</button></td>";
				$html .= "<td><button type='button' ";
				$html .= "onclick='wtr_deleteEditor(".$ed['id'].")'>";
				$html .= "X</button></td></tr>";
			}
			$html .= "<tr><td> - </td><td>";
			$html .= "<input type='text' id='whAddEditorName' value=''";
			$html .= "	oninput='wtr_listEditors()'";
			$html .= "	placeholder='".__('Enter an author name','wtr_helper')."'>";
			$html .= "<div id='whAddEditorNameDiv'></div>";
			$html .= "</td><td>".__('Add editor','wtr_helper')."</td><td></td></tr>";
			$html .= "</table>\n";
		}
	}
	
	// Change editor name
	if( $type == "changeEditorName" && $user_id == 0 ) {
		$msg = __('Error editor id not found','wtr_helper');
	}
	if( $type == "changeEditorName" && $user_id != 0 ) {
		$book = new WH_Book($id);		
		$book->add_Editor($user_id, $user_name);
		
		if( ! $book->save() )
			$msg = __('Error update editor name','wtr_helper');
		else {
			$html = $user_name;
		}
	}

	
	// Search books
	if( $type == "search" ) {
		$books = WH_Book::getAll_BooksForUser($title);
		
		$html = "<ul class='whSearchUl'>";
		foreach( $books as $b ) {
			$html .= "<li>".$b->title."</li>";
		}
		$html .= "</ul>\n";
	}

	
	// Export book Form
	if( $type == "exportBookForm" && $id == 0 ) {
		$msg = __('Error book reference not given','wtr_helper');
	}
	if( $type == "exportBookForm" && $id != 0 ) {
		$html = "";
		$book = new WH_Book($id);
		$statuses = array();
		foreach( $book->get_BookChapters() as $ch ) {
			if( ! in_array($ch->status, $statuses) )
				$statuses[] = $ch->status;
			foreach( $ch->get_ChapterScenes() as $sc )
				if( ! in_array($sc->status, $statuses) )
					$statuses[] = $sc->status;
		}
		
		$html .= "<table id='wtr_statusForm'>";

		$html .= "<tr><td>";
		$html .= __('Select statuses','wtr_helper')."</td><td>".
				"<input type='hidden' id='wtr_status_book_id' value='".$id."'>";
		
		foreach ( $statuses as $st ) {
			$html .= "<span class='wh_buttonCBSpan'>";
			$html .= "<input type='checkbox' class='wh_buttonCB wh_buttonCBStatus'".
						" id='".$st."' name='".$st."'";
			$html .= ">";
			$html .= "<label for='".$st."'>".WH_Status::getStatusName($st)."</label>";
			$html .= "</span>&nbsp;";
			
		}
		$html .= "</td></tr>\n";
		$html .= "</table><br/>\n";

		$html .= "<a class='wh_button wh_buttonSave'".
				 " onclick='wtr_exportBook(".$id.")'>".
				 __('Save','wtr_helper')."</a>\n";
		$html .= "<a class='wh_button wh_buttonCancel'".
				 " onclick='wtr_cancelStatusDiv();'>".
				 __('Cancel','wtr_helper')."</a>\n";
	}
	
	
	// Export book
	if( $type == "exportBook" && $id == 0 ) {
		$msg = __('Error book reference not given','wtr_helper');
	}
	if( $type == "exportBook" && $id != 0 ) {
		$statuses = array();
		if( $cs_list != "" )
			foreach( explode(',' ,$cs_list) as $st )
				if( in_array($st, WH_Status::ALL_STATUSES) )
					$statuses[] = $st;

		$html = wtrh_exportToEpub($id, $statuses);		
	}
	
	// Print book
	if( $type == "printBook" && $id == 0 ) {
		$msg = __('Error book reference not given','wtr_helper');
	}
	if( $type == "printBook" && $id != 0 ) {
		$html = wtrh_getBookText($id);
	}
	
	
	
	// Create book post
	if( $type == "createPost" && $id == 0 ) {
		$msg = __('Error book reference not given','wtr_helper');
	}
	if( $type == "createPost" && $id != 0 ) {
		$book = new WH_Book($id);
				
		if( ! $book->create_BookPost() )
			$msg = __('Error while creating the new post.','wtr_helper')."\n".
				   __('Verify your WordPress user capabilities.','wtr_helper');
		else {
			$html  = '<a class="wh_buttonDashicon" href="'.$book->get_BookPostUrl().'" target="_blank">';
			$html .= '<span class="dashicons dashicons-visibility"';
			$html .= '	title="'.__('Open post','wtr_helper').'"></span>';
			$html .= '</a>';
			$html .= '<a class="wh_buttonDashicon wh_buttonDashiconDel" onclick="wtr_deleteBookPost('.$book->id.')">';
			$html .= '<span class="dashicons dashicons-editor-unlink"';
			$html .= '	title="'.__('Delete the post containing your book','wtr_helper').'"></span>';
			$html .= '</a>';			
		}
		
		if( ! $book->save() )
			$msg = __('Error while saving book','wtr_helper');
	}
	
	// Delete book post
	if( $type == "deletePost" && $id == 0 ) {
		$msg = __('Error book reference not given','wtr_helper');
	}
	if( $type == "deletePost" && $id != 0 ) {
		$book = new WH_Book($id);

		if( ! $book->delete_BookPost() )
			$msg = __('Error while deleting the post.','wtr_helper')."\n".
				   __('Verify your WordPress user capabilities.','wtr_helper');
		else {
			$html  = '<a class="wh_buttonDashicon" onclick="wtr_createBookPost('.$book->id.')">';
			$html .= '<span class="dashicons dashicons-admin-links"';
			$html .= ' title="'.__('Create a post for your book','wtr_helper').'"></span>';
			$html .= '</a>';
		}
		
		if( ! $book->save() )
			$msg = __('Error while saving book','wtr_helper');
	}
	
	
	
	if( $msg != "" )
		echo "KO;".$msg;
	else
		echo "OK;".$html;
	wp_die();
}



// Publish a book
add_action( 'wp_ajax_wtrh_ajax_publishBook', 'wtrh_ajax_publishBook' );
function wtrh_ajax_publishBook() {

	$type       = isset( $_POST['type'] )       ? wtr_sanitize($_POST['type']  ,'title')   : "";	
	$id         = isset( $_POST['id'] )         ? wtr_sanitize($_POST['id']    ,'int')     : 0;	
	$date       = isset( $_POST['date'] )       ? wtr_sanitize($_POST['date']  ,'title')   : "";	

	$msg  = "";
	$html = "";
	$ret = true;
	wtr_info_log(__METHOD__, "type=".$type." id=".$id." date=".$date);	
	
	// Publish book
	if( $type == "publish" && $id == 0 ) {
		$msg = __('Error book reference not given','wtr_helper');
	}
	if( $type == "publish" && $id != 0 ) {
		$book = new WH_Book($id);
		$ret  = $book->publish($date);
//wtr_info_log(__METHOD__, "publish status=".$book->status);
		
		if( ! $ret )
			$msg = __('An error occured while publishing your book.','wtr_helper')."\n".
				   __('Verify the date entered.','wtr_helper')."\n".
				   __('Verify the statuses of all your chapters.','wtr_helper');
	}
	
	// Change publication date
	if( $type == "changeDate" && $id == 0 ) {
		$msg = __('Error book reference not given','wtr_helper');
	}
	if( $type == "changeDate" && $id != 0 ) {
		$book = new WH_Book($id);
		$ret = $book->set_PublicationDate($date);
		if( ! $ret ) {
			$msg = sprintf(__('Invalid date : %s','wtr_helper'),$date);
			$msg .= "\n".__('Publication date not updated','wtr_helper');
		} else
			if( ! $book->save() )
				$msg = __('Error update publication date','wtr_helper');			
//wtr_info_log(__METHOD__, "changeDate msg=".$msg);
//wtr_info_log(__METHOD__, "changeDate pDate=".$book->publication_date);		
	}
	
	if( $msg != "" )
		echo "KO;".$msg;
	else
		echo "OK;".$html;
	wp_die();
}


// Create/delete/change status of chapters
add_action( 'wp_ajax_wtrh_ajax_manageChapter', 'wtrh_ajax_manageChapter' );
function wtrh_ajax_manageChapter() {

	$type       = isset( $_POST['type'] )   ? wtr_sanitize($_POST['type']   ,'title') : "";	
	$id         = isset( $_POST['id'] )     ? wtr_sanitize($_POST['id']     ,'int')   : 0;	
	$status     = isset( $_POST['status'] ) ? wtr_sanitize($_POST['status'] ,'int')   : WH_Status::DRAFT;	
	$title      = isset( $_POST['title'] )  ? wtr_sanitize($_POST['title']  ,'title') : "";	
	$number     = isset( $_POST['number'] ) ? wtr_sanitize($_POST['number'] ,'int')   : 0;	
	$showNumber = isset( $_POST['showN'] )  ? wtr_sanitize($_POST['showN']  ,'bool')  : false;	
	$showTitle  = isset( $_POST['showT'] )  ? wtr_sanitize($_POST['showT']  ,'bool')  : false;	
	$s_list     = isset( $_POST['s_list'] ) ? wtr_sanitize($_POST['s_list'] ,'text')  : "";	
	$book_id    = isset( $_POST['book_id'] )? wtr_sanitize($_POST['book_id'],'int')   : 0;	

	$msg  = "";
	$html = "";
	$ret  = true;
	wtr_info_log(__METHOD__, 
				 "type=".$type." id=".$id." status=".$status.
				 " book_id=".$book_id." s_list=".$s_list);

	// Create a new chapter
	if( $type == "create" ) {
		// If book id not found
		if( $book_id == 0 ) {
			$id = 0;
			$msg = __('Error book reference not found','wtr_helper');
			
		} else {
			if( ! $showNumber && ! $showTitle )
				$showNumber = true;
			
			$chapter = new WH_Chapter(0, array('title'  => $title, 
										 'number'      => $number, 
										 'status'      => $status, 
										 'show_number' => $showNumber, 
										 'show_title'  => $showTitle, 
										 'book_id'     => $book_id));		
			if( ! $chapter->save() )
				$msg = __('Error chapter create','wtr_helper');
			else 
				$id = $chapter->id;
			
			// If GameBook, create scene too
			$book = new WH_Book($book_id);
			if( $book->isGameBook ) {
				$scene = new WH_Scene(0, array('chapter_id' => $chapter->id,
											  'book_id'    => $book_id
												));
				if( ! $scene->save() )
					$msg = __('Error scene create','wtr_helper');
			}
			
			$html = getPageHTMLChaptersList($book_id);
		}
	}
	
	// Update a chapter
	if( $type == "update" && $id == 0 ) {
		$msg = __('Error chapter reference not found','wtr_helper');
	}
	if( $type == "update" && $id != 0 ) {
		$chapter = new WH_Chapter($id);
		if( $number > 0 )
			$chapter->number  = $number;
		if( strlen($title) > 0 )
			$chapter->title   = $title;
		if( $book_id > 0 )
			$chapter->book_id = $book_id;
		if( $showNumber != $chapter->show_number )
			$chapter->show_number = $showNumber;
		if( $showTitle != $chapter->show_title )
			$chapter->show_title  = $showTitle;
		
		if( ! $chapter->save() ) 
			$msg = __('Error chapter udpate','wtr_helper');
		else {
			// Change chapters & scenes numbers
			$book = new WH_Book(0, array('id' => $book_id));
			$book->get_BookChapters(true);
			
			$tmp = explode("s",$s_list);
			$sc_list = array();
			foreach( $tmp as $val ) {
				if( strlen($val) > 0 ) {
					$atmp = explode("-",$val);
					$sc_list[$atmp[0]] = $atmp[1];
				}
			}
			$ch_found = false;
			$sc_num = 0;
			foreach( $book->chapters as $ch ) {
				foreach( $ch->scenes as $sc ) {
					$sc_num++;
					if( $ch->id == $id ) {
						$ch_found = true;
						
						if( $sc->number != $sc_list[$sc->id] ) {
							$sc->number = $sc_list[$sc->id];
							$sc->save();
						}
					} else {
						if( $ch_found ) { // re-number scenes after
							$sc->number = $sc_num;
							$sc->save();
						}
					}
				}
			}
		}
	}
	
	// Delete a chapter
	if( $type == "delete" && $id == 0 ) {
		$msg = __('Error chapter reference not found','wtr_helper');
	}
	if( $type == "delete" && $id != 0 ) {
		$chapter = new WH_Chapter($id);
		if( ! $chapter->delete() ) 
			$msg = __('Error chapter delete','wtr_helper');
		else {
			if( ! WH_Book::refreshNumbers(array('book_id'=>$chapter->book_id)) )
				$msg = __('Error refresh numbers','wtr_helper');
			else
				$html = getPageHTMLChaptersList($chapter->book_id);
		}
	}
	
	// Change chapter status
	if( $type == "status" && $id == 0 ) {
		$msg = __('Error chapter reference not found','wtr_helper');
	}
	if( $type == "status" && $id != 0 ) {
		$chapter = new WH_Chapter($id);
		
		switch( $status ) {
			case WH_Status::DRAFT    :	$ret = $chapter->unpublish(); break;
			case WH_Status::TOEDIT   :	$ret = $chapter->endDrafting(); break;
			case WH_Status::EDITING  :	$ret = $chapter->startEditing(); break;
			case WH_Status::EDITED   :	$ret = $chapter->endEditing(); break;
			case WH_Status::TOPUBLISH:	$ret = $chapter->toPublish(); break;
			case WH_Status::PREVIEW  :	$ret = $chapter->preview(); break;
			case WH_Status::HIDDEN   :	$ret = $chapter->hide(); break;
			case WH_Status::PUBLISHED:	$ret = $chapter->publish(); break;
			case WH_Status::ARCHIVED :	$ret = $chapter->archive(); break;
			case WH_Status::ARC_UNP  :	$ret = $chapter->archiveUnpublish(); break;
			case WH_Status::TRASHED  :	$ret = $chapter->trash(); break;
			default: $msg = sprintf(__('Chapter status unknown : %s','wtr_helper'), $status);
		}
//wtr_info_log(__METHOD__, "chapter status=".$chapter->status);
		
		if( ! $ret )
			$msg = __('Not all scenes are at the same status.','wtr_helper')."\n".
				   __('Change status of scenes individually.','wtr_helper');
		else
			$html = getPageHTMLChaptersList($chapter->book_id);
	}
	
	// Refresh chapter status
	if( $type == "refreshStatus" && $id == 0 ) {
		$msg = __('Error chapter reference not found','wtr_helper');
	}
	if( $type == "refreshStatus" && $id != 0 ) {
		$chapter = new WH_Chapter($id);
		
		$old_status = $chapter->status;
		$chapter->refreshStatus();
//		wtr_info_log(__METHOD__, "chapter old status=".$old_status." new status=".$chapter->status);
		
		if( $status == 0 ) // book page -> show buttons
			$html = "<div class='whBookChapterStatus' ".
					"style='".WH_Status::getStatusStyle($chapter->status)."'>".
					WH_Status::getStatusName($chapter->status)."</div>\n".
			        "<div class='whBookChapterButtons'>".
					getActionButtons("book", $chapter->status, "chapter", 
					$chapter->id, $chapter->book_id)."</div>\n";
		else // chapter page -> no buttons
			$html = "<span class='whStatus".WH_Status::getStatusStyle($chapter->status)."'>".
					WH_Status::getStatusName($chapter->status)."</span>\n";
	}
	
	
	
	// Create chapter post
	if( $type == "createPost" && $id == 0 ) {
		$msg = __('Error chapter reference not given','wtr_helper');
	}
	if( $type == "createPost" && $id != 0 ) {
		$chapter = new WH_Chapter($id);
				
		if( ! $chapter->create_ChapterPost() )
			$msg = __('Error while creating the new post.','wtr_helper')."\n".
				   __('Verify your WordPress user capabilities.','wtr_helper');
		else {
			$html  = '<a class="wh_buttonDashicon" style="margin: 0 10px 0 0;padding 0;" href="'.$chapter->get_ChapterPostUrl().'" target="_blank">';
			$html .= '<span class="dashicons dashicons-visibility"';
			$html .= '	title="'.__('Open post','wtr_helper').'"></span>';
			$html .= '</a>';
			$html .= '<a class="wh_buttonDashicon wh_buttonDashiconDel" style="margin: 0;padding 0;" onclick="wtr_deleteChapterPost('.$chapter->id.')">';
			$html .= '<span class="dashicons dashicons-editor-unlink"';
			$html .= '	title="'.__('Delete the post containing your chapter','wtr_helper').'"></span>';
			$html .= '</a>';			
		}
		
		if( ! $chapter->save() )
			$msg = __('Error while saving chapter','wtr_helper');
	}
	
	// Delete chapter post
	if( $type == "deletePost" && $id == 0 ) {
		$msg = __('Error chapter reference not given','wtr_helper');
	}
	if( $type == "deletePost" && $id != 0 ) {
		$chapter = new WH_Chapter($id);

		if( ! $chapter->delete_ChapterPost() )
			$msg = __('Error while deleting the post.','wtr_helper')."\n".
				   __('Verify your WordPress user capabilities.','wtr_helper');
		else {
			$html  = '<a class="wh_buttonDashicon" onclick="wtr_createChapterPost('.$chapter->id.')">';
			$html .= '<span class="dashicons dashicons-admin-links"';
			$html .= ' title="'.__('Create a post for your chapter','wtr_helper').'"></span>';
			$html .= '</a>';
		}
		
		if( ! $chapter->save() )
			$msg = __('Error while saving chapter','wtr_helper');
	}


	
	if( $msg != "" )
		echo "KO;".$msg;
	else
		echo "OK;".$html;
	wp_die();
}


// Create/delete/change status of scenes
add_action( 'wp_ajax_wtrh_ajax_manageScene', 'wtrh_ajax_manageScene' );
function wtrh_ajax_manageScene() {

	$type       = isset( $_POST['type'] )        ? wtr_sanitize($_POST['type']  ,'title')     : "";	
	$id         = isset( $_POST['id'] )          ? wtr_sanitize($_POST['id']    ,'int')       : 0;	
	$status     = isset( $_POST['status'] )      ? wtr_sanitize($_POST['status'],'int')       : WH_Status::DRAFT;	
	$desc       = isset( $_POST['description'] ) ? wtr_sanitize($_POST['description'],'text') : "";	
	$text       = isset( $_POST['text'] )        ? wtr_sanitize($_POST['text']       ,'html') : "";	
	$ttext      = isset( $_POST['ttext'] )       ? wtr_sanitize($_POST['ttext']      ,'text') : "";	
	$etext      = isset( $_POST['editingText'] ) ? wtr_sanitize($_POST['editingText'],'text') : "";	
	$number     = isset( $_POST['number'] )      ? wtr_sanitize($_POST['number']     ,'int')  : 0;	
	$chapter_id = isset( $_POST['chapter_id'] )  ? wtr_sanitize($_POST['chapter_id'] ,'int')  : 0;	

	$msg = "";
	$html = "";
	$ret = true;
	wtr_info_log(__METHOD__, "type=".$type." id=".$id." status=".$status);
//	wtr_info_log(__METHOD__, "_POST['text']=".$_POST['text']);
//	wtr_info_log(__METHOD__, "text=".$text);

	// Create a new scene
	if( $type == "create" ) {
		// If chapter id not found
		if( $chapter_id == 0 ) {
			$id = 0;
			$msg = __('Error book reference not found','wtr_helper');
			
		} else {
			$scene = new WH_Scene(0, array('number'    => $number, 
										 'description' => $desc, 
										 'text'        => $text, 
										 'status'      => $status, 
										 'chapter_id'  => $chapter_id));		
			if( ! $scene->save() )
				$msg = __('Error scene create','wtr_helper');
			else {
				$id = $scene->id;
				// refresh numbers
				if( ! WH_Book::refreshNumbers(array('chapter_id'=>$chapter_id)) )
					$msg = __('Error refresh numbers','wtr_helper');
			}
		}
		$html = admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&scene_id='.$id);
	}
	
	// Update a scene
	if( $type == "update" && $id == 0 ) {
		$msg = __('Error scene reference not found','wtr_helper');
	}
	if( $type == "update" && $id != 0 ) {
		$scene = new WH_Scene($id);
		if( $number > 0 )
			$scene->number      = $number;
		if( strlen($desc) > 0 )
			$scene->description = $desc;
		if( strlen($text) > 0 ) {
			$scene->text       = $text;
			$scene->word_count = wtr_word_count(stripslashes($ttext));
		}
		if( strlen($etext) > 0 && $scene->status == WH_Status::EDITING ) {
			$scene->editing_text = $etext;
		}
		if( strlen($etext) > 0 && $scene->status == WH_Status::DRAFT ) {
			$etext = wtr_sanitize($_POST['editingText'],'title');
			$scene->gameBook = json_decode($etext, true);
		}
		if( $chapter_id > 0 )
			$scene->chapter_id  = chapter_id;
		
		if( ! $scene->save() ) 
			$msg = __('Error scene udpate','wtr_helper');
	}
	
	// Delete a scene
	if( $type == "delete" && $id == 0 ) {
		$msg = __('Error scene reference not found','wtr_helper');
	}
	if( $type == "delete" && $id != 0 ) {
		$scene = new WH_Scene($id);
		if( ! $scene->delete() ) 
			$msg = __('Error scene delete','wtr_helper');
		else {
			if( ! WH_Book::refreshNumbers(array('chapter_id'=>$scene->chapter_id)) )
				$msg = __('Error refresh numbers','wtr_helper');
		}
	}
	
	// Change scene status
	if( $type == "status" && $id == 0 ) {
		$msg = __('Error scene reference not found','wtr_helper');
	}
	if( $type == "status" && $id != 0 ) {
		$scene = new WH_Scene($id);
		
		switch( $status ) {
			case WH_Status::DRAFT    :	$ret = $scene->unpublish(); break;
			case WH_Status::TOEDIT   :	$ret = $scene->endDrafting(); break;
			case WH_Status::EDITING  :	$ret = $scene->startEditing(); break;
			case WH_Status::EDITED   :	$ret = $scene->endEditing(); break;
			case WH_Status::TOPUBLISH:	$ret = $scene->toPublish(); break;
			case WH_Status::PREVIEW  :	$ret = $scene->preview(); break;
			case WH_Status::HIDDEN   :	$ret = $scene->hide(); break;
			case WH_Status::PUBLISHED:	$ret = $scene->publish(); break;
			case WH_Status::ARCHIVED :	$ret = $scene->archive(); break;
			case WH_Status::ARC_UNP  :	$ret = $scene->archiveUnpublish(); break;
			case WH_Status::TRASHED  :	$ret = $scene->trash(); break;
			default: $msg = sprintf(__('Scene status unknown : %s','wtr_helper'), $status);
		}
wtr_info_log(__METHOD__, "scene status=".$scene->status);
		
		if( ! $ret )
			$msg = __('Error status update','wtr_helper');
		else
			$html = getPageHTMLScenesList($scene->chapter_id);
	}
	
	// Add Linked Scene
	if( $type == "addLinkedScene" && $id == 0 ) {
		$msg = __('Error scene reference not found','wtr_helper');
	}
	if( $type == "addLinkedScene" && $id != 0 ) {
		$scene = new WH_Scene($id);
		$scene->add_GameBookScene(9999, $chapter_id, $desc);
		$ret = $scene->save_GameBookScene();
		if( ! $ret )
			$msg = __('Error addLinkedScene','wtr_helper');
		else
			$html = getHTML_GameBookScenes($id, false);
	}
	
	// Update Linked Scene
	if( $type == "updateLinkedScene" && $id == 0 ) {
		$msg = __('Error scene reference not found','wtr_helper');
	}
	if( $type == "updateLinkedScene" && $id != 0 ) {
		$scene = new WH_Scene($id);
		$scene->add_GameBookScene($number, $chapter_id, $desc);
		$ret = $scene->save_GameBookScene();
		if( ! $ret )
			$msg = __('Error updateLinkedScene','wtr_helper');
		else
			$html = getHTML_GameBookScenes($id, false);
	}
	
	// Delete Linked Scene
	if( $type == "deleteLinkedScene" && $id == 0 ) {
		$msg = __('Error scene reference not found','wtr_helper');
	}
	if( $type == "deleteLinkedScene" && $id != 0 ) {
		$scene = new WH_Scene($id);
		$scene->delete_GameBookScene($chapter_id);
		$ret = $scene->save_GameBookScene();
		if( ! $ret )
			$msg = __('Error deleteLinkedScene','wtr_helper');
		else
			$html = getHTML_GameBookScenes($id, false);
	}
	
	
	// Return linkeds scene array
	if( $type == "refreshGraphData" && $id == 0 ) {
		$msg = __('Error book reference not found','wtr_helper');
	}
	if( $type == "refreshGraphData" && $id != 0 ) {
		
		$linkedScenes = getArray_GameBook($id, false);
		$html = json_encode($linkedScenes);
	}
	
	if( $msg != "" ) {
		wtr_error_log(__METHOD__, $msg);
		echo "KO;".$msg;
	} else
		echo "OK;".$html;
	wp_die();
}

?>