<?php
/* Functions for chapters */

// Return the page HTML listing chapters
function getPageHTMLChaptersList($book_id, $entryElement = "book") {
	$page = "";
	$book = new WH_Book($book_id);
	$my_chapters = WH_Chapter::getAll_Chapters($book_id);
	$isDisabled  = true;
//	wtr_info_log(__METHOD__, "book_id = $book_id  /  nb chapters=".count($my_chapters));
	
	// Get user role on this book
	$user_id    = get_current_user_id();
	$editor     = $book->isAnAuthor($user_id);
	$author     = $book->isAnEditor($user_id);
	$admin      = WH_User::userExists($user_id, WTRH_ROLE_ADMIN);
	$authorized = ($admin || $author || $editor);
	$book       = new WH_Book($book_id);
	$isGameBook = $book->isGameBook;
	$existsStoryboard = false;
	if( class_exists("WH_Storyboard") )
		if( WH_Storyboard::get_BookStoryboard($book_id) !== false )
			$existsStoryboard = true;
	
	
	$page .= "<div class='whBookChapters nested-chapter'>";
	
	// only for admin, authors and editors
	if( $authorized && $book->status == WH_Status::DRAFT && ! $existsStoryboard ) {
		$page .= "<div class='whChapterLine'>".
				"<button type='submit' class='whActionButton whActionButtonSmall whNewChapterPanel'".
				" onclick=\"wtr_manageChapter('create', ".WH_Status::DRAFT.", 0, ".$book_id.")\">".
				__('Create a new chapter','wtr_helper').
				"</button></div>";
	}
	if( $existsStoryboard ) {
		$page .= "<div class='whChapterLine'>".
				__('Create chapters and scenes using the Storyboard','wtr_helper').
				"</div>";
	}
	
	if( count($my_chapters) == 0 ) {
		return "<label class='whMsg'>".
				__('No Chapter yet !','wtr_helper').
				"</label><br><br>".$page;
	}
	
	foreach( $my_chapters as $chapter ) {
//		wtr_info_log(__METHOD__, "chapter number =".$chapter->number);
		$isDisabled = ($chapter->status > WH_Status::EDITED)?true:false;
		
		$page .= "<div class='whBookChapter' ondragend='wtr_dragEndChapter(event)'>\n";
		
		// CHAPTER LINE
		$page .= "<div class='whBookChapterInfos'>\n";
		
		// --> chapter label : handler + "Chapter" 
		$page .= "<div class='whBookChapterLabel'>";		
		if( ! $isDisabled )
		$page .= "<span class='dashicons dashicons-menu whHandler'></span>";
		else
		$page .= "<span class='whHandlerNull'>&nbsp;</span>";
		
		$page .= "<span>".__('Chapter','wtr_helper')."</span>";
		$page .= "</div>\n";
		
		// --> chapter number
		$page .= "<div class='whBookChapterNumber whBookCSlist'".
					" id='whChapterNumber".$chapter->id."'>".
					$chapter->number."</div>\n";

		// --> chapter title
		$page .= "<div class='whBookChapterTitle'>".$chapter->title."</div>\n";

		// --> chapter post
		if( $book->status    != WH_Status::TRASHED &&
		    $chapter->status != WH_Status::TRASHED ) {
			$page .= "<div id='whChapterPostButton".$chapter->id."'>";
			if( $chapter->chapter_post == null ) {
				$page .= '<a class="wh_buttonDashicon" style="margin: 0;padding 0;" onclick="wtr_createChapterPost('.$chapter->id.')">';
				$page .= '<span class="dashicons dashicons-admin-links"';
				$page .= ' title="'.__('Create a post for your chapter','wtr_helper').'"></span>';
				$page .= '</a>';
			} else {
				$page .= '<a class="wh_buttonDashicon" style="margin: 0 10px 0 0;padding 0;" href="'.$chapter->get_ChapterPostUrl().'" target="_blank">';
				$page .= '<span class="dashicons dashicons-visibility"';
				$page .= '	title="'.__('Open post','wtr_helper').'"></span>';
				$page .= '</a>';
				$page .= '<a class="wh_buttonDashicon wh_buttonDashiconDel" style="margin: 0;padding 0;" onclick="wtr_deleteChapterPost('.$chapter->id.')">';
				$page .= '<span class="dashicons dashicons-editor-unlink"';
				$page .= '	title="'.__('Delete the post containing your chapter','wtr_helper').'"></span>';
				$page .= '</a>';			
			}
			$page .= "</div>\n";
		}
		
		// --> chapter status
		$page .= "<div id='whChapterStatus".$chapter->id."' style='display: inline; margin: 0;'>".
				 "<div class='whBookChapterStatus' ".
				 "style='".WH_Status::getStatusStyle($chapter->status)."'>";
		$page .= WH_Status::getStatusName($chapter->status);
		$page .= "</div>\n";
		

		// --> chapter buttons
		$page .= "<div class='whBookChapterButtons'>\n";
		$page .= getActionButtons($entryElement, $chapter->status, "chapter", 
									$chapter->id, $book_id);
		$page .= "</div>";
		
		$page .= "</div>\n</div>\n";
		
		// SCENES
		$page .= "<div id='whScenes".$chapter->id."'>\n";
		$page .= getPageHTMLScenesList($chapter->id, $entryElement, (($isDisabled || $isGameBook)?true:false));
		$page .= "</div></div>\n";
 
	}
		
	$page .= "</div>\n";
	return $page;
}


?>