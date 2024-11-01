<?php 
/**
 ** Shortcodes for managings a list of books 
 **/
include_once(WTRH_INCLUDE_DIR . '/classes/class_book.php');
	
// Display book info in a panel
function wtr_bookpanel($atts) {
	$a = shortcode_atts( array( 'id' => 0,
								'title' => ''),
						 $atts );
	
	// retrieve book_id
	$book_id    = isset($a['id'])? (int)$a['id']:0;
	$book_title = isset($a['title'])?(string)trim($a['title']):"";
	
	// get book's info
	if( $book_id == 0 && $book_title == "" ) 
		return __('This shortcode needs at least one parameter (id or title)','wtr_helper');

	if( $book_id == 0 ) { // search by title
		$books = WH_Book::getAll_Books($book_title);
		if( count($books) == 0 ) 
			return sprintf(__('No book found with title %s','wtr_helper'), $book_title);
		if( count($books) > 1 )
			return sprintf(__('More than one book found with title <%s>','wtr_helper'), $book_title);
		
		$book_id = $books[0]->id;
	}
	$book = new WH_Book($book_id, null, true);
	if( ! $book->isOk ) 
		return sprintf(__('Book not found with id %s','wtr_helper'), $book_id);
	
	$dateFormat = WH_Category::get_DateFormat();
	
	// Display book
	$html = wtrh_getBookPanel($book_id);
	
	return $html;
}
add_shortcode('writerhelper_bookinfo', 'wtr_bookpanel');

	
// Display book's chapters
function wtr_bookchapters($atts) {
	global $_SERVER;
	global $_GET;
	
	$a = shortcode_atts( array( 'id' => 0, 'chapter' => 0),
						 $atts );
	
	// retrieve book_id
	$book_id   = $a['id'];
	// retrieve chapter number
	$chap_nb   = intval($a['chapter']);
	$display_unpublished_chapters = ($chap_nb != 0)?true:false;
	if( $chap_nb == 0 )
		$chap_nb   = isset($_GET['chapter'])? wtr_sanitize($_GET['chapter'],'int'):0;
	
	// get book's info
	if( $book_id == 0 ) 
		return __('This shortcode needs at least the book id','wtr_helper');

	$book = new WH_Book($book_id, null, true);
	$nbFreeChapters = $book->book_info['freeChapter'];
	$isPreviewUser  = false;
	$isHiddenUser   = false;
	$user_id        = get_current_user_id();
	if( $user_id > 0 && class_exists("WH_Reader") ) {
		$isPreviewUser = WH_Reader::previewAuthorized($user_id);
		$isHiddenUser  = WH_Reader::hiddenAuthorized($user_id);
	}

	if( ! $book->isOk ) 
		return sprintf(__('Book not found with id %s','wtr_helper'), $book_id);
	
	$dateFormat = WH_Category::get_DateFormat();
	
	$html = "";
	$settings = new WH_BookSettings($book_id);
	
	if( $chap_nb == 0 ) { // Display book 1st page
		
		$html = wtrh_getBookPanel($book_id);
	
	} else { // display chapter text
		
		$my_chap  = $book->get_ChaptersText($chap_nb);
		
		if( $my_chap == null )
			return __('No chapter found','wtr_helper');
		
		if( $my_chap['status'] == WH_Status::TRASHED )
			$html .= __('This chapter has been trashed','wtr_helper');
		else if( ! in_array($my_chap['status'], WH_Status::getPublishStatuses()) )
			$html .= __('This chapter has not been published yet!','wtr_helper');
		else if( ($my_chap['status'] == WH_Status::HIDDEN  && ! $isHiddenUser) || 
		         ($my_chap['status'] == WH_Status::PREVIEW && ! $isPreviewUser)  )
			$html .= __('You are not authorized to read this chapter','wtr_helper');
		else {
			
			if( ! $display_unpublished_chapters ) {
				$html .= "<h1 class='whsBookChapterTitle'>".
						 $my_chap['title']."</h1>";
				$html .= "<br>\n";
			}
			
			// display edited scenes
			foreach($my_chap['scenes'] as $key => $scene) {
				if(  $scene['status'] == WH_Status::EDITED || 
				    ($scene['status'] == WH_Status::HIDDEN  && $isHiddenUser) || 
					($scene['status'] == WH_Status::PREVIEW && $isPreviewUser)  ) {
					if( $key > 0 )
						$html .= "<div class='whsSceneBetweenText'>***</div>\n";
					
					$html .= "<div class='whsSceneText'>".
							 $scene['text']."</div>\n";
				}
			}
		}
	}
//	$html .= "<br><br>\n";
		
	// buttons
	$next_chap = 0;
	$next_url  = "";
	$prev_chap = 0;
	$prev_url  = "";
	$exists_next = false;
	$exists_prevNP = false; // exists a previous chapter not yet published
	$exists_nextNP = false; // exists a next chapter not yet published
	
	// search previous and next chapters
	foreach( $book->chapters as $ch ) {	

		if( $ch->number < $chap_nb &&
			$ch->status != WH_Status::TRASHED && 
		   ($ch->status == WH_Status::PUBLISHED || 
		    $ch->status == WH_Status::ARCHIVED  || 
		   ($ch->status == WH_Status::HIDDEN  && $isHiddenUser) || 
		   ($ch->status == WH_Status::PREVIEW && $isPreviewUser) ) ) {
			   
			$prev_chap   = $ch->number;
			$prev_url    = $ch->get_ChapterPostUrl();

			$exists_prevNP = true;
			
			if( $prev_url == "" )
				if( in_array($ch->status, WH_Status::getPublishStatuses()) )
					$prev_url = $book->get_BookPostUrl()."?book=".$book_id."&chapter=".$prev_chap;
				else {
					$prev_chap     = 0;
				}
		}

		// Display next chapter if exists post
		if( $ch->number > $chap_nb &&
			$ch->status != WH_Status::TRASHED && 
		   ($ch->status == WH_Status::PUBLISHED || 
		    $ch->status == WH_Status::ARCHIVED  || 
		   ($ch->status == WH_Status::HIDDEN  && $isHiddenUser) || 
		   ($ch->status == WH_Status::PREVIEW && $isPreviewUser) ) &&
		     $next_chap  == 0 ) {
				 
			$next_chap   = $ch->number;
			$next_url    = $ch->get_ChapterPostUrl();
			
			$exists_next = true;

			if( $next_url == "" )
				if( in_array($ch->status, WH_Status::getPublishStatuses()) )
					$next_url = wtr_full_url($_SERVER)."?book=".$book_id."&chapter=".$next_chap;
				else {
					$next_chap   = 0;
					$exists_next = false;
				}
		}
		
		if( $ch->number > $chap_nb && 
			! in_array($ch->status, WH_Status::getPublishStatuses()) &&
			$ch->status != WH_Status::TRASHED ) {
			$exists_nextNP = true;
			break;
		}
	}

	if( $chap_nb >= count($book->chapters) )
		$html .= "<div class='whsTheEnd'>".
				 __('The End','wtr_helper')."</div>\n";
	
	$html .= "<div class='whsChaptersButtons'>\n";
	if( $prev_chap > 0 ) { // display "previous" button
		$html .= "<div class='whsPreviousChapterButton'><a href='".$prev_url."'>".
				 $settings->get_PreviousChapterLabel()."</a></div>\n";
	}else {
		if( $exists_prevNP )
		$html .= "<div class='whsPreviousChapterButton'>".
				 __('Previous chapter not yet published','wtr_helper')."</div>\n";			
		else
		$html .= "<div class='whsPreviousChapterButton whsNoPreviousChapter'></div>\n";
	}
	
	if( $exists_next ) { // display "next" button
		// free chapter
		if( $nbFreeChapters < 0 || $next_chap <= $nbFreeChapters )
		$html .= "<div class='whsNextChapterButton'><a href='".$next_url."'>".
				 $settings->get_NextChapterLabel()."</a></div>\n";
		else // not a free chapter
		$html .= "<div class='whsNextChapterButton whsNoNextChapter'>".
				 __('To read more, contact the author','wtr_helper')."</div>\n";
	} else {
		if( $exists_nextNP )
		$html .= "<div class='whsNextChapterButton whsNoNextChapter'>".
				 __('Next chapter not yet published','wtr_helper')."</div>\n";
	}
	$html .= "</div>\n<br><br>";

	// save book id
	$_GET['book'] = $book_id;
	
	return $html;
}
add_shortcode('writerhelper', 'wtr_bookchapters');


// Display book's summary
function wtr_booksummary($atts) {
	global $_SERVER;
	global $_GET;
	
	$a = shortcode_atts( array( 'id' => 0),
						 $atts );
	
	// retrieve book_id
	$book_id   = $a['id'];
	
	// get book's info
	if( $book_id == 0 ) 
		return __('This shortcode needs at least the book id','wtr_helper');

	$book = new WH_Book($book_id, null, true);
	
	$html = $book->get_BookSummaryHTML(get_current_user_id());;
	return $html;
}
add_shortcode('writerhelper_summary', 'wtr_booksummary');

?>