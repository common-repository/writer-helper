<?php /* Buttons functions */

// Return HTML code for goto buttons
function getGotoButtons($entryElement, $status, $book_id = 0, 
						$chapter_id = 0) {
	$html = "";
	$html .= "<div class='whGotoButtons whGotoButtons".$entryElement."'>\n";
    $html .= "<div class='whGotoButtonRow1'></div>\n";
	$html .= "<div class='whGotoButtonRow2'>\n";
	
	$disabled = ($status == WH_Status::TRASHED)?'disabled ':'';
	$existsStoryboard = false;
	if( class_exists("WH_Storyboard") )
		if( WH_Storyboard::get_BookStoryboard($book_id) !== false )
			$existsStoryboard = true;
	
	// Go to CHAPTER
    if( $entryElement == 'scene' && $chapter_id != 0 && ! $existsStoryboard ) { 
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton' ";
		$html .= " onclick=\"wtr_goTo('".
				admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&chapter_id='.$chapter_id)."')\">";
		$html .= __('Go to Chapter','wtr_helper')."</button>";
		$html .= "</div>\n";
	} 
	
	// Book shortcode
    if( $entryElement == 'books' ) { 
		$html .= "<div class='whShortCode'>";
		$html .= "<label>".__('Shortcode','wtr_helper').
				 "<br>[writerhelper id=".$book_id."]</label>";
		$html .= "</div>\n";
	} 
	// Book word count
    if( $entryElement == 'books' ) { 
		
		$html .= "<div class='whWordCount'>";
		$html .= "<label>".
				  WH_Scene::get_WordCount($book_id)." ".
				  __('words','wtr_helper')."</label>";
		$html .= "</div>\n";
	} 
	
	// Export BOOK
    if( $entryElement == 'books' ) { 
		$book = new WH_Book($book_id);
		// Post url
		if( $book->book_post != null ) {
		$html .= "<div class='whLink whLink".$entryElement."'>";
		$html .= "<a href='".$book->get_BookPostUrl()."' target='_blank'>";
		$html .= __('Go To WordPress Post','wtr_helper')."</a>";
		$html .= "</div>\n";
		}
		// EPUB url
		if( $book->book_url != "" ) {
		$html .= "<div class='whLink whLink".$entryElement."'>";
		$html .= "<a href='".$book->book_url."' target='_blank'>";
		$html .= __('Go To Epub','wtr_helper')."</a>";
		$html .= "</div>\n";
		}
		// export button
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton'";
		$html .= " onclick='wtr_exportBookForm(".$book_id.")'>";
		$html .= __('Export To EPUB','wtr_helper')."</button>";
		$html .= "</div>\n";
		// print button
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton'";
		$html .= " onclick='wtr_printBook(".$book_id.")'>";
		$html .= __('Print','wtr_helper')."</button>";
		$html .= "</div>\n";
	}  
	
	// Export BOOKWORLD
    if( $entryElement == 'bookworld' ) { 
		$bw = new WH_Bookworld($book_id);
		// EPUB url
		if( $bw->epub_url != "" ) {
		$html .= "<div class='whLink whLink".$entryElement."'>";
		$html .= "<a href='".$bw->epub_url."' target='_blank'>";
		$html .= __('Go To Epub','wtr_helper')."</a>";
		$html .= "</div>\n";
		}
		// export button
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton'";
		$html .= " title='".__('Export all books in one EPUB file','wtr_helper')."'";
		$html .= " onclick='wtr_exportBookworld(".$book_id.")'>";
		$html .= __('Export To EPUB','wtr_helper')."</button>";
		$html .= "</div>\n";
	} 
	
	// Go to BOOK
    if( $entryElement != 'book' && $entryElement != 'bookworld' ) { 
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton'\n";
		$html .= " onclick='wtr_goTo(\"";
		switch( $entryElement ) {
			case "chapter":
			case "scene":
					$html .= admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&tab=book_chapters&book_id='.$book_id);
					break;
			case "storyboard":
			case "bookworld":
			default:
					$html .= admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&book_id='.$book_id);
		}
		$html .= "\")'>";
		$html .= __('Go to Book','wtr_helper')."</button>";
		$html .= "</div>\n";
	} 
	
	// Go to Book's STORYBOARD
    if( ($entryElement == 'books' || $entryElement == 'scene') && $existsStoryboard ) { 
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton'\n";
		$html .= " onclick='wtr_goTo(\"";
		$html .= admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&tab=book_storyboard&book_id='.$book_id);
		$html .= "\")'>";
		$html .= __('Go to Storyboard','wtr_helper')."</button>";
		$html .= "</div>\n";
	} 
	
	
	
	// BOOKWORLD buttons
    if( $entryElement == 'bookworld' && class_exists("WH_Bookworld") ) { 
		// 
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton'";
		$html .= " onclick='wtr_goTo(\"".admin_url('admin.php?page='.WTRH_BOOKWORLDS_MENU.'&bookworld_id='.$book_id.'&tab=bw_general')."\")'>";
		$html .= __('Go To Bookworld','wtr_helper')."</button>";
		$html .= "</div>\n";
		// 
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton'";
		$html .= " onclick='wtr_goTo(\"".admin_url('admin.php?page='.WTRH_BOOKWORLDS_MENU.'&bookworld_id='.$book_id.'&tab=bw_characters')."\")'>";
		$html .= __('Go To Characters','wtr_helper')."</button>";
		$html .= "</div>\n";
		// 
		$html .= "<div class='whButtonGoToBook whGotoButton".$entryElement."'>";
		$html .= "<button type='button' ".$disabled;
		$html .= " class='whGotoButton'";
		$html .= " onclick='wtr_goTo(\"".admin_url('admin.php?page='.WTRH_BOOKWORLDS_MENU.'&bookworld_id='.$book_id.'&tab=bw_stories')."\")'>";
		$html .= __('Go To Stories','wtr_helper')."</button>";
		$html .= "</div>\n";
	} 
	
	
	$html .= "</div></div>\n";
		
	// Go BACK
	if( $entryElement != 'books' && $entryElement != 'bookworld' ) { 
		$html .= "<br/><br/><span class='whGoBackButton'>\n";
		$html .= "<button onclick='wtr_goBack()'>".__('Go Back','wtr_helper')."</button>\n";
		$html .= "</span>\n";
	} 
	
	return $html;
}


// Return an URL from a GO TO Button
function wtr_getAdminUrl($cible, $id = array(), $tab = array(), $otherArgs = array() ) {
	
	$url = 'admin.php?page='.$cible;
	
	// display a selected tab
	if( is_array($tab) && count($tab) == 1 ) {
		$url .= '&tab='.$tab[0];
	}
	
	if( is_array($id) && count($id) > 0 ) {
		foreach( $id as $idType => $idValue )
			$url .= '&'.$idType.'='.$idValue;
	}
	
	if( is_array($otherArgs) && count($otherArgs) > 0 ) {
		foreach( $otherArgs as $argName => $argValue )
			$url .= '&'.$argName.'='.$argValue;
	}
	
	return admin_url($url);
}


// Return HTML code for action buttons
function getActionButtons($entryElement, $status, $type, $id, $book_id = 0, $chapter_id = 0) {
	$html = "";

	// only for admin, authors and editors
	$user_id    = get_current_user_id();
	$args       = array('book_id' => $book_id);
	
	$editor     = WH_Book::hasTheRoleForBook($args, $user_id, WTRH_ROLE_EDITOR);
	$author     = WH_Book::hasTheRoleForBook($args, $user_id, WTRH_ROLE_AUTHOR);
	$admin      = WH_User::userExists($user_id, WTRH_ROLE_ADMIN);
	$authorized = ($admin || $author || $editor);
	$book       = new WH_Book($book_id);
	$isGameBook = $book->isGameBook;
	$existsStoryboard = false;
	if( class_exists("WH_Storyboard") )
		if( WH_Storyboard::get_BookStoryboard($book_id) !== false )
			$existsStoryboard = true;

	$html .= "<div class='whActionButtons whActionButtons".$entryElement."'>";
	
	// Chapter's buttons
	if( $type == "chapter" && $authorized) {
		$html .= "<div class='whChapterButtons'>\n";
		
		// Open chapter page
		if( $entryElement == "book" && ! $existsStoryboard ) {
			$html .= "<div class='whZero'><button type='button' ";
			$html .= "class='whActionButton whActionButtonChapter' ";
			$html .= "onclick='wtr_goTo(\"".
					admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&chapter_id='.$id).
					"\")'>".__('Open','wtr_helper')."</button>";
			$html .= "</div>\n";
		}
		
		// create a new scene
		if( $status == WH_Status::DRAFT && ($author || $admin) && ! $existsStoryboard && ! $isGameBook ) {
			$html .= "<div class='whZero'><button type='button' ";
			$html .= "class='whActionButton whActionButtonNewChapter' ";
			$html .= "onclick=\"wtr_manageScene('create', ".WH_Status::DRAFT.", 0, ".$id.")\">".
					__('Create a new scene','wtr_helper')."</button> ";
			$html .= "</div>\n";
		}
		
		// change status buttons
		if( $entryElement == "book" && ($author || $admin) ) {
			
			// Scene's buttons
			$st = new WH_Status($book_id, array('element'   => 'WH_Chapter',
												'element_id'=> $id,
												'chapter_id'=> $id,
												'status'    => $status));
			$next_statuses = $st->next_status;
			sort($next_statuses);
//wtr_info_log(__METHOD__, "Next statuses from Chapter (current status=$status): ".print_r($next_statuses,true));
			$buttons = $st->get_NextStatuses_buttons();

			foreach( $next_statuses as $next_st ) {
				if( ($author || $admin) &&
				   ( ($next_st == WH_Status::DRAFT && $status == WH_Status::PUBLISHED) ||
					 ($next_st == WH_Status::DRAFT && $status == WH_Status::ARC_UNP) ||
					  $next_st != WH_Status::DRAFT
				   ))
					$html .= $buttons[$next_st];
			}
/*
			// to publish
			if( $status == WH_Status::EDITED  ) {
				$html .= "<div class='whZero'><button type='button' ".
						 "class='whActionButton whActionButtonChapter' ".
						 "onclick=\"wtr_manageChapter('status', ".WH_Status::TOPUBLISH.", ".$id.", 0)\">".
						__('To Publish','wtr_helper')."</button></div>\n ";
			}
			
			// publish
			if( $status == WH_Status::TOPUBLISH && ($author || $admin) ) {
				$html .= "<div class='whZero'><button type='button' ";
				$html .= "class='whActionButton whActionButtonChapter' ";
				$html .= "onclick=\"wtr_manageChapter('status', ".WH_Status::PUBLISHED.", ".$id.", 0)\">".
						__('Publish','wtr_helper')."</button> ";
				$html .= "</div> ";
			}
			// unpublish
			if( $status == WH_Status::PUBLISHED && ($author || $admin) ) {
				$html .= "<div class='whZero'><button type='button'  ";
				$html .= "class='whActionButton whActionButtonChapter' ";
				$html .= "onclick=\"wtr_manageChapter('status', ".WH_Status::DRAFT.", ".$id.", 0)\">".
						__('Unpublish','wtr_helper')."</button> ";
				$html .= "</div> ";
			}
			// Delete
			if( $status < WH_Status::PUBLISHED && ($author || $admin) 
			 && $status < WH_Status::PUBLISHED )  {
			$html .= "<div class='whZero'><button type='button' ".
					"class='whActionButton whDeleteButton whActionButton".$status."' ".
					"onclick=\"wtr_manageChapter('status', ".WH_Status::TRASHED.", ".$id.", ".$book_id.")\">".
					__('Delete','wtr_helper')."</button></div>\n";
			}
			// Untrash or Definitive delete
			if( $status == WH_Status::TRASHED && ($author || $admin) ) {
			$html .= "<div class='whZero'><button type='button' ".
					"class='whActionButton whActionButton".$status."' ".
					"onclick=\"wtr_manageChapter('status', ".WH_Status::DRAFT.", ".$id.", ".$book_id.")\">".
					__('Untrash','wtr_helper')."</button></div>\n";
			$html .= "<div><button type='button' ".
					"class='whActionButton whDeleteButton whActionButton".$status."' ".
					"onclick=\"wtr_manageChapter('delete', ".WH_Status::DRAFT.", ".$id.", ".$book_id.")\">".
					__('Definitive delete','wtr_helper')."</button></div>\n";
			}
			*/
		}
		
		$html .= "</div>";
	}

	// buttons for saving objects
	if( $type == "save" && $authorized && $status < WH_Status::TOPUBLISH ) {
		$html .= "<div class='whActionButtonSceneRow1'>";
		$html .= "<div class='whActionButtonSave'>";
		$html .= "<button type='button' ";
		$html .= "class='whActionButton whActionButtonSave".
					$entryElement."' ";
		$html .= "onclick='wtr_save".ucfirst(strtolower($entryElement))."(".$id.")'>";
		$html .= __('Save','wtr_helper')."</button></div>";
		
		if( $entryElement == 'scene' && $status == WH_Status::DRAFT && ! $existsStoryboard && ! $isGameBook ) {
			$html .= "<div class='whActionButtonAdd'>";
			$html .= "<button type='button' ";
			$html .= "class='whActionButton whActionButtonAddScene' ";
			$html .= "onclick='wtr_goTo(\"".
					  admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&scene=new&scene_id='.$id)."\")'>";
			$html .= "(+) ".__('Create New Scene','wtr_helper')."</button></div>";
		}
		$html .= "</div>";
	}

	// buttons for saving objects' settings
	if( $type == "saveSettings" && $authorized ) {
		$html .= "<div class='whActionButtonSceneRow1'>";
		$html .= "<div class='whActionButtonSave'>";
		$html .= "<button type='button' ";
		$html .= "class='whActionButton whActionButtonSave".
					$entryElement."' ";
		$html .= "onclick='wtr_save".ucfirst(strtolower($entryElement))."(".$id.")'>";
		$html .= __('Save settings','wtr_helper')."</button></div>";
		
		$html .= "</div>";
	}


	// Scene's buttons
	if( $type == "scene" && $authorized) {
		$chapter_status = 0;
		if( $chapter_id != 0 ) {
			$chapter = new WH_Chapter($chapter_id);
			$chapter_status = $chapter->status;
		}
		
		$html .= "<div class='whActionButtonSceneRow2'>\n";

		// Write scene
		if( ($status == WH_Status::DRAFT || $status == WH_Status::EDITING)
		 && $authorized
		 && $chapter_status < WH_Status::PUBLISHED ) {
		$html .= "<div><button type='button' ";
		$html .= "class='whActionButton' ";
		$html .= "onclick='wtr_goTo(\"".
					admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&scene_id='.$id)."\")'>".
					__('Write','wtr_helper')."</button></div>\n";
		}
		
		// Scene's statuses action buttons
		$st = new WH_Status($book_id, array('element'   => 'WH_Scene',
											'element_id'=> $id,
											'chapter_id'=> $chapter_id,
											'status'    => $status));
		$next_statuses = $st->next_status;
		sort($next_statuses);
//wtr_info_log(__METHOD__, "Next statuses from Scene (current status=$status): ".print_r($next_statuses,true));
		$buttons = $st->get_NextStatuses_buttons();

		foreach( $next_statuses as $next_st ) {
			if( ($next_st == WH_Status::DRAFT   && $authorized
					 && $chapter_status <= WH_Status::EDITED) || 
			    ($next_st == WH_Status::TOEDIT  && $authorized
					 && $chapter_status <= WH_Status::EDITED) || 
			    ($next_st == WH_Status::EDITING && $authorized
					 && $chapter_status <= WH_Status::EDITED) || 
			    ($next_st == WH_Status::HIDDEN && $authorized) || 
			    ($next_st == WH_Status::EDITED && $authorized) || 
			    ($next_st >= WH_Status::EDITED  && ($author || $admin) 
					&& $chapter_status <= WH_Status::EDITED) ) 
				$html .= $buttons[$next_st];
		}
		
 		$html .= "</div>\n";
	}
	$html .= "</div>\n";

	
	return $html;
}

?>
