
<div id="blanket" style="display:none"></div>
<div id="wtrh_wait_div" style='display:none'>
	<img src='<?php echo WTRH_IMG_URL."/loading.gif";?>'>
</div>
<div id="wh_popupDiv" style='display:none'>
	<img src='<?php echo WTRH_IMG_URL."/loading.gif";?>'>
</div>

<div class="wtrh_page">
	<input type='hidden' id='whMsgObjectNotSaved'
			value='<?php echo __('Changes not saved','wtr_helper')."\n".
							  __('Quit without save?','wtr_helper');?>'>	
	<input type='hidden' id='whLoadingGif'
			value='<?php echo WTRH_IMG_URL."/loading.gif";?>'>	
	<?php	
	// If current user is admin and not in the list of WH admin => add user as WH admin
	$wp_user_id = get_current_user_id();
	if( count(getWordPressAdminUsers($wp_user_id)) == 1 && ! WH_User::userExists($wp_user_id, WTRH_ROLE_ADMIN) )
		addWriterHelperAdministrator($wp_user_id);
	
	// verify user capabilities
	if( WH_User::isAuthorizedOnWHdashboard($wp_user_id) ) {
		
		$nb_param = count($_GET);
		$page = isset($_GET['page'])?wtr_sanitize($_GET['page'],'title') :"";
		// submenu if exists
		$tab  = isset($_GET['tab']) ?wtr_sanitize($_GET['tab'], 'title') :"";
		$found = false;

		switch( $page ) {
			
			// Parameters pages
			case WTRH_SETTINGS_MENU :
			
					// Settings page
					include('page_settings.php'); 
					break;
					
			// Book's pages
			case WTRH_BOOKS_MENU :
			
					// Print a book
					if( isset($_GET['print_book']) ) {
						$found = true;
						include('page_print.php'); 
					}
					
					// New book page
					if( isset($_GET['new_book']) ){
						$found = true;
						include('page_newbook.php'); 
					}
					
					// Book page
					if( isset($_GET['book_id']) 
						&& ! isset($_GET['print_book'])
						&& ! isset($_GET['scene']) ){
						$found = true;
						include('page_book.php'); 
					}
					
					// Chapter page
					if( isset($_GET['chapter_id']) ){
						$found = true;
						include('page_chapter.php');
					}
					
					// Scene page
					if( isset($_GET['scene_id']) || isset($_GET['scene']) ){
						$found = true;
						include('page_scene.php'); 
					}
					
					// Books' list
					if( ! $found )
						include('page_books.php'); 
					
					break;
					
			// Bookworld's pages
			case WTRH_BOOKWORLDS_MENU :
			
					// Bookworlds' list
					include('page_bookworlds.php'); 
					break;
					
			// To Do List pages		
			case WTRH_TODO_MENU :
			
					// To Do List page
					include('page_todolist.php'); 
					break;
					
			// Statistics pages
			case WTRH_STATS_MENU :
			
					// Stats page
					include('page_stats.php'); 
					break;
					
			// Users pages
			case WTRH_USERS_MENU :
			
					// Users page
					include('page_users.php'); 
					break;
					
			// Communities pages
			case WTRH_COMMUNITIES_MENU :
			
					// Communities page
					include('page_communities.php'); 
					break;
					
			default:
					include('page_index.php'); 
		}
	}else {
		$admins = WH_User::getAll_Admins();
		
		echo "<h1>Writer Helper</h1>";
		echo "<br/>";
		if( count($admins) > 0 ) {
			$html = "<ul>\n";

			foreach( $admins as $a ) {
				$html .= "<li>".WH_User::getWpUserName($a->user_id)."</li>\n";
			}
			$html .= "</ul>\n";
			
			
			_e('You are not authorized to access Writer Helper.','wtr_helper');
			echo "<br>";
			echo sprintf(__('Contact one of your website Writer Helper administrators : %s','wtr_helper'), $html);
		
		
		} else {
			_e('Your website does not have a Writer Helper administrator.','wtr_helper');
			echo "<br>";
			_e('Delete the plugin. Create a WordPress adminstrator user. Install the plugin again.','wtr_helper');
			echo "<br>";
			_e('This WordPress administrator user will be detected by Writer Helper and will be a Writer Helper adminstrator.','wtr_helper');
		}
		
	}
	?>
	
</div>
