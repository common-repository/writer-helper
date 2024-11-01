<?php
/* Functions for books */
include_once(WTRH_INCLUDE_DIR . '/classes/class_book.php');
include_once(WTRH_INCLUDE_DIR . '/functions/buttons_functions.php');
include_once(WTRH_EPUBGENERATOR_DIR . '/epubgenerator.php');

// Book access
const WTRH_BACCESS_ALL     = 00;
const WTRH_BACCESS_READER  = 10;
const WTRH_BACCESS_READERP = 20;
const WTRH_BACCESS_SEL     = 30;
const WTRH_BACCESS_NONE    = 90;
$wtr_book_access = array(WTRH_BACCESS_ALL     => __('All','wtr_helper'),
						 WTRH_BACCESS_READER  => __('Registered readers','wtr_helper'),
						 WTRH_BACCESS_READERP => __('Premium readers','wtr_helper'),
						 WTRH_BACCESS_SEL     => __('Selected readers','wtr_helper'),
				         WTRH_BACCESS_NONE    => __('None','wtr_helper')
						);



// Return the page HTML listing books
function wtrh_getPageHTMLBooksList($title, $statuses = array()) {
	$page = "";
	if( ! is_array($statuses) || count($statuses) == 0 )
		$statuses = WH_Status::BOOK_STATUSES;

	$my_books = WH_Book::getAll_BooksForUser($title, 0, $statuses);
//	wtr_info_log(__METHOD__, "title = $title  /  nb books=".count($my_books));
	
	$page .= "<ul class='whBooksList'>";
	
	// only for admin, authors and editors
	if( WH_User::isAuthorized_action(WTRH_CAP_MANAGE_BOOKS) ) {
		$page .= "<li><button type='submit' class='whNewBookPanel' ".
				 "onclick='wtr_goTo(\"".
					admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&new_book')."\")'>\n".
				 __('Create a new book','wtr_helper')."</button></li>\n";
	}
	
	if( count($my_books) == 0 ) {
		return "<label class='whMsg'>".__('No Book found!','wtr_helper').
				"</label><br><br>".$page."</ul>";
	}
	
	$entryElement = "books";
	
	foreach( $my_books as $book ) {
//		wtr_info_log(__METHOD__, "book title =".$book->title);
		
		$page .= "<li><span><table class='whOneBookPanel'>\n";
		$page .= "<tr><td class='whBookPanelCover'>\n";
		if( strlen(trim($book->cover)) > 0 ) {
			$image = json_decode($book->cover);
			$page .= "<img src='".$image->guid."' width='200px'>\n";
		} else
			$page .= "<img src='".WTRH_IMG_URL."/NoCover2.png' width='200px'>\n";

		$page .= "</td>\n";
		$page .= "<td class='whBookPanelInfo'>\n";
		$page .= "<table><tr><td><div class='whBookPanelBookTitle'>\n";
		$page .= $book->getTitle()."</div>";
		if( $book->isGameBook )
		$page .= "<span class='dashicons dashicons-games whGameBook' title='".__('Game Book','wtr_helper')."'> </span>";
		$page .= "</td></tr>\n";
		$page .= "<tr><td><div class='whBookPanelBookWorld'>";
		$page .= "</div></td></tr>\n";
		$page .= "<tr><td><div class='whBookPanelBookType'>\n";
		$page .= __($book->get_Type('text'),'wtr_helper')."</div></td></tr>\n";
		$page .= "<tr><td><div class='whBookPanelBookAuthors'>\n";
		$page .= __('Authors','wtr_helper');
		$page .= "<ul class='whBookPanelAuthorsList'>\n";

		$book->get_BookAuthors();
		foreach( $book->authors as $author ) 
			$page .= "<li>".$author['name']."&nbsp;</li>";

		$page .= "</ul></div></td></tr>\n";
		$page .= "<tr><td><div class='whBookPanelBookEditors'>\n";
		$page .= __('Editors','wtr_helper');
		$page .= "<ul class='whBookPanelEditorsList'>\n";

		$book->get_BookEditors();
		foreach( $book->editors as $edt ) 
			$page .= "<li>".$edt['name']."&nbsp;</li>\n";

		$page .= "</ul></div></td></tr>\n";
		
		if( $book->status == WH_Status::PUBLISHED ) {
		$page .= "<tr><td><div class='whBookPanelBookEditors'>\n";
		$page .= __('Publication date','wtr_helper');
		$page .= "<ul class='whBookPanelEditorsList'>\n";
		$page .= "<li>".$book->get_PublicationDate()."&nbsp;</li>\n";
		$page .= "</ul></div></td></tr>\n";
		}
		
		$page .= "</table>\n";
		$page .= "</td>\n";
		$page .= "<td rowspan='2'>\n";
		$page .= getGotoButtons('books', $book->status, $book->id);
		$page .= "</td></tr>\n";
		$page .= "<tr><td colspan='2' class='whBookPanelStatus'>\n";
		$page .= "<div class='whBookPanelStatus' style='".WH_Status::getStatusStyle($book->status)."'>";
		$page .= WH_Status::getStatusName($book->status)."</div></td></tr>\n";

		$page .= "<tr><td colspan='3'>\n";
		$page .= "<span><b>".__('Resume','wtr_helper')."</b></span><br/>\n";
		$page .= "<span class='whBookPanelResume'>".$book->get_Resume()."</span>\n";
		$page .= "</td></tr>\n";

		// Book buttons
		$page .= "<tr><td>\n";
		$st = new WH_Status($book->id, array('element'=>'WH_Book','status'=>$book->status));
		$next_statuses = $st->next_status;
		ksort($next_statuses);
		$buttons = $st->get_NextStatuses_buttons();
	
		// Button : line 1 column 1
		if( isset($next_statuses['11']) ) {
			$page .= $buttons[$next_statuses['11']];
		} 
		$page .= "</td><td>";
		
		// Button : line 1 column 2
		if( isset($next_statuses['12']) ) {
			$page .= $buttons[$next_statuses['12']];
		} 
		$page .= "</td><td>";
		
		// Button : line 1 column 3
		if( isset($next_statuses['13']) ) {
			$page .= $buttons[$next_statuses['13']];
		} 
		$page .= "</td></tr><tr><td>";
		
		// Button : line 2 column 1
		if( isset($next_statuses['21']) ) {
			$page .= $buttons[$next_statuses['21']];
		} 
		$page .= "</td><td>";
		
		// Button : line 2 column 2
		if( isset($next_statuses['22']) ) {
			$page .= $buttons[$next_statuses['22']];
		} 
		$page .= "</td><td>";
		
		// Button : line 2 column 3
		if( isset($next_statuses['23']) ) {
			$page .= $buttons[$next_statuses['23']];
		} 
		

		$page .= "</td></tr></table></span></li>";
	}
		
	$page .= "</ul>\n";
	return $page;
}

// Return the page HTML listing books
function wtrh_getHTMLBooksListSelection($title, $div) {
	$page = "";

	$my_books = WH_Book::getAll_BooksForUser($title);
//	wtr_info_log(__METHOD__, "title = $title  /  nb books=".count($my_books));
	
	$page .= "<ul class='whBooksListSelection'>\n";
	
	foreach( $my_books as $book ) {
//		wtr_info_log(__METHOD__, "book title =".$book->title);
		
		$page .= "<li><a onclick='addToDiv(".$book->id.
					',"'.htmlentities($book->title,ENT_QUOTES).'","'.$div.'"'.")'>".
					$book->title."</a></li>\n";
	}
		
	$page .= "</ul>\n";
	return $page;
}


// Return HTML code for a display of book panel
function wtrh_getBookPanel($book_id, $from = "shortcode") {
	$settings = null;
	return wtrh_getBookPanelAndSettings($book_id, $settings, $from);
}
function wtrh_getBookPanelAndSettings($book_id, &$settings, $from = "shortcode") {

//wtr_info_log(__METHOD__,"book_id=$book_id");
	if( ! is_numeric($book_id) || $book_id == 0 ) {
		wtr_error_log( __METHOD__, "book_id incorrect : ".$book_id);
		return "";
	}
	
	// Get book info in DB
	// ---------------------------
	$book = new WH_Book($book_id);
	
	if( ! $book->isOk ) {
		wtr_error_log( __METHOD__, "book_id not found : ".$book_id);
		return "";
	}
	
	// Get book display settings
	// ---------------------------
	$settings = new WH_BookSettings($book_id);
	
	// Get Date format
	// ---------------------------
	$dateFormat = WH_Category::get_DateFormat();
	
	// Generate Book Panel
	// ---------------------------
	$html = "";
	
	if( $from == "shortcode" ) {

		$html = "<div class='whsBookPanelDiv'>";
		
		// Display cover
		// ---------------------------
		if( in_array(WH_BookSettings::BI_Cover, $settings->get_BookInfo()) ) {
			
			if( strlen(trim($book->cover)) > 0 ) {
				$html .= "<div class='whsBookCover'><img ".
						 "src='".$book->get_CoverUrl()."' alt='".
						  $book->title."' /></div>";
			} else {
				$html .= "<div class='whsBookCover'><img ".
						 "src='".WTRH_IMG_URL."/NoCover2.png' alt='".
						  str_replace("'"," ",stripslashes(__('No Cover for this book','wtr_helper')))."' /></div>";
			}
		}
		
		$html .= "\n<div class='whsBookDiv'>";
		$html .= "<ul class='whsBook'>\n";
		
		foreach( $settings->get_BookInfo() as $set ) {
			
			switch($set) {
			case WH_BookSettings::BI_Title:
				// Book Title
				$html .= "<li class='whsBookTitle'>".$book->title."</li>\n";
				break;
				
			case WH_BookSettings::BI_Type:
				// Book Type
				$html .= "<li class='whsBookType'>".__($book->get_Type('text'),'wtr_helper')."</li>\n";
				break;
				
			case WH_BookSettings::BI_Status:
				// Book status
				$html .= "<li><span class='whStatus' style='".
							WH_Status::getStatusStyle($book->status)."'>&nbsp;".
							WH_Status::getStatusName($book->status)."&nbsp;</span></li>\n";
				break;
				
			case WH_BookSettings::BI_Isbn:
				// Book Isbn
				if( strlen(trim($book->isbn)) > 0 )
					$html .= "<li class='whsBookIsbn'>ISBN: ".$book->isbn."</li>\n";
				break;
				
			case WH_BookSettings::BI_PubDate:
				// Publication date
				$html .= "<li>".__('Publication date','wtr_helper')." : ".
						$book->get_PublicationDate("d/m/Y")."</li>\n";
				break;
				
			case WH_BookSettings::BI_CustomStatus:
				// Custom status
				if( strlen(trim($settings->get_CustomStatusLabel())) > 0 ) {
					$html .= "<li";
					if( strlen(trim($settings->get_CustomStatusStyle())) > 0 ) 
						$html .= " style='".$settings->get_CustomStatusStyle()."'";
					$html .= ">".$settings->get_CustomStatusLabel()."</li>";
				}
				break;
				
			case WH_BookSettings::BI_SaleUrl:
				// Sale URL
				if( strlen(trim($book->sale_url)) > 0 )
					$html .= "<li><a href='".$book->sale_url."' target='_blank'>".
							__('Buy here','wtr_helper')."</a></li>";
				break;
				
			case WH_BookSettings::BI_PromoUrl:
				// Promo URL
				if( strlen(trim($book->promo_url)) > 0 )
					$html .= "<li><a href='".$book->promo_url."' target='_blank'>".
							__('Learn more','wtr_helper')."</a></li>";
				break;
				
			case WH_BookSettings::BI_OpinionUrl:
				// Opinion URL
				if( strlen(trim($book->opinion_url)) > 0 )
					$html .= "<li><a href='".$book->opinion_url."' target='_blank'>".
							__('Readers reviews','wtr_helper')."</a></li>";
				break;
				
			case WH_BookSettings::BI_Authors:
				// Book authors
				$html .= "<li>".__('Author(s)','wtr_helper').": ";
				$authors = array();
				foreach( $book->get_BookAuthors() as $a )
					$authors[] = $a['name'];
				$html .= implode(",", $authors);
				$html .= "</li>";
				break;
				
			case WH_BookSettings::BI_Editors:
				// Book editors
				$html .= "<li>".__('Editor(s)','wtr_helper').": ";
				$editors = array();
				foreach( $book->get_BookEditors() as $a )
					$editors[] = $a['name'];
				$html .= implode(",", $editors);
				$html .= "</li>";
				break;
				
			case WH_BookSettings::BI_Resume:
				// Book resume
				$html .= "<li><br/><label class='whsResume'>".
						 __('Resume','wtr_helper')."</label><br/>";
				$html .= $book->get_Resume();
				$html .= "</li>";
				break;
			}
		}
		
		$html .= "</ul>\n</div></div>";		
	}
	
	if( $from == "widget" ) {
		
		// Display info
		$html  = "<div class='whwBookInfoDiv'>\n";
		$html .= "<ul class='whwBookInfoList'>";
		
		foreach( $settings->get_BookInfo() as $set ) {
			
			switch($set) {
			case WH_BookSettings::BI_Title:
				// Book Title
				$html .= "<li><label class='whwBookInfoTitle'>".$book->title."</label></li>";
				break;
				
			case WH_BookSettings::BI_Cover:
				// Book cover
				if( strlen(trim($book->cover)) > 0 )
					$html .= "<li><img class='whwBookInfoCover' ".
						 "src='".$book->get_CoverUrl()."' alt='".
						  $book->get_Title()."' /></li>";
				break;
				
			case WH_BookSettings::BI_Type:
				// Book type
				$html .= "<li class='whwBookType'>".__($book->get_Type('text'),'wtr_helper')."</li>";
				break;
				
			case WH_BookSettings::BI_Status:
				// Book status
				$html .= "<li><span class='whStatus' style='".
							WH_Status::getStatusStyle($book->status)."'>&nbsp;".
							WH_Status::getStatusName($book->status)."&nbsp;</span></li>\n";
				break;
				
			case WH_BookSettings::BI_Authors:
				// Book authors
				$html .= "<li>".__('Author(s)','wtr_helper').": ";
				$authors = array();
				foreach( $book->get_BookAuthors() as $a )
					$authors[] = $a['name'];
				$html .= implode(",", $authors);
				$html .= "</li>\n";
				break;
				
			case WH_BookSettings::BI_Isbn:
				// Book Isbn
				if( strlen(trim($book->isbn)) > 0 )
					$html .= "<li class='whsBookIsbn'>ISBN: ".$book->isbn."</li>\n";
				break;
				
			case WH_BookSettings::BI_CustomStatus:
				// Custom status
				if( strlen(trim($settings->get_CustomStatusLabel())) > 0 ) {
					$html .= "<li";
					if( strlen(trim($settings->get_CustomStatusStyle())) > 0 ) 
						$html .= " style='".$settings->get_CustomStatusStyle()."'";
					$html .= ">".$settings->get_CustomStatusLabel()."</li>";
				}
				break;
				
			case WH_BookSettings::BI_Editors:
				// Book editors
				$html .= "<li>".__('Editor(s)','wtr_helper').": ";
				$editors = array();
				foreach( $book->get_BookEditors() as $a )
					$editors[] = $a['name'];
				$html .= implode(",", $editors);
				$html .= "</li>\n";
				break;
				
			case WH_BookSettings::BI_PubDate:
				// Publication date
				$html .= "<li>".__('Publication date','wtr_helper')." : ".$book->get_PublicationDate()."</li>";
				break;
				
			case WH_BookSettings::BI_SaleUrl:
				// Sale URL
				if( strlen(trim($book->sale_url)) > 0 )
					$html .= "<li><a href='".$book->sale_url."' target='_blank'>".
							__('Buy here','wtr_helper')."</a></li>";
				break;
				
			case WH_BookSettings::BI_PromoUrl:
				// Promo URL
				if( strlen(trim($book->promo_url)) > 0 )
					$html .= "<li><a href='".$book->promo_url."' target='_blank'>".
							__('Learn more','wtr_helper')."</a></li>";
				break;
				
			case WH_BookSettings::BI_OpinionUrl:
				// Opinion URL
				if( strlen(trim($book->opinion_url)) > 0 )
					$html .= "<li><a href='".$book->opinion_url."' target='_blank'>".
							__('Readers reviews','wtr_helper')."</a></li>";
				break;
			}
		}
		$html .= "</ul></div>";

	}
	
	return $html;
}


// Export a book to an epub file and add it to media library
// book_id  : integer : book id
// statuses : array   : chapters, scenes statuses to export
// => return media URL
function wtrh_exportToEpub($book_id, $statuses = array()) {

//wtr_info_log(__METHOD__,"1-statuses = ".print_r($statuses, true));
	
	$url_epub  = "";
	if( ! is_array($statuses) || count($statuses) == 0 )
		$statuses = WH_Status::BOOK_STATUSES;
//wtr_info_log(__METHOD__,"2-statuses = ".print_r($statuses, true));
	
	// verify export directory
	if( ! file_exists(WTRH_EXPORT_DIR) ) 
		if( ! mkdir(WTRH_EXPORT_DIR) ) {
			wtr_error_log(__METHOD__, "create dir has failed! (dir=".WTRH_EXPORT_DIR.")");
			return "";
		}			

	$book      = new WH_Book($book_id);
	$book->get_BookAuthors();
	$authors = array();
	foreach( $book->authors as $a )
		$authors[] = $a['name'];
		
	$epub_book = new EPUB_Book($book->title, $authors, 
	                           $book->type, $book->get_Resume(),
							   $book->get_CoverUrl(), 
							   $book->isbn);
	$text      = "";
	
	foreach( $book->get_ChaptersText(0, $statuses) as $ch ) {
		$ch_text = "";
		foreach( $ch['scenes'] as $i => $sc ) {
			if( $i > 0 )
				$ch_text .= "\n<br/><br/>".
						"<p class='whsSceneBetweenText'>***</p>".
						"<br/><br/>\n";
			
			$ch_text .= wtrh_replaceWidthImage($sc['text']);
		}
		$text .= $ch_text;
		$epub_book->addChapter(array('id'    => $ch['id'],
		                             'title' => $ch['title'],
		                             'text'  => $ch_text,
		                             'links' => $ch['links']));
	}
	
	$book_media = wtrh_getMedia($text);
	
	$authors = array();
	foreach( $book->get_BookAuthors() as $au )
		$authors[] = $au['name'];
	
	$epub_file = wtr_sanitize($book->get_Title('text'),'file');
	$ident = uniqid().'-'.uniqid().'-'.
	         uniqid().'-'.uniqid().'-'.uniqid();
	
	$metadata = array('title'       => $book->title,
					  'creator'     => implode(", ", $authors),
					  'subject'     => "",
					  'description' => $book->get_Resume('text'),
					  'date'        => $book->get_PublicationDate(),
					  'type'        => "book",
					  'format'      => "epub",
					  'identifier'  => $ident,
					  'language'    => substr(get_locale(),0,2),
					  'copyright'   => "All rights reserved to ".implode(", ", $authors)
	                  );
	$ffn = wp_upload_dir()['path'].'/'.$epub_file.".epub";
	$epub_gen  = new EPUB_Generator(WTRH_EXPORT_DIR.'/epub_trav', $ffn, 
									$metadata, $epub_book, $book_media);
	
	if( ! $epub_gen->generateEpub() )
		return false;
	
	
	$posts = array();
	// create a WordPress media IF USER CAN
	if( current_user_can(WTRH_USR_MEDIA_CAP) ) {
		$posts = get_posts( array('name'           => "wh_epub_".$epub_file,
								  'post_mime_type' => 'application/epub+zip',
								  'post_type'      => 'attachment',
								  'numberposts'    => 1));
		$local_media_id = 0;
		if( isset($posts[0]) &&  $posts[0]->post_name == "wh_epub_".$epub_file )
			$local_media_id = $posts[0]->ID;

		// Create a wordpress media
		$attachment = array(
			'post_mime_type' => 'application/epub+zip',
			'post_type'      => 'attachment',
			'post_name'      => "wh_epub_".$epub_file,
			'post_title'     => $book->title. " (EPUB)",
			'post_content'   => "",
			'post_status'    => 'publish'
		);

		if( $local_media_id == 0 ) {
			// Insert the attachment.
			$local_media_id = wp_insert_attachment( $attachment, $ffn, 0 );
		}
		
		// Generate the metadata for the attachment
		$attach_data = wp_generate_attachment_metadata( $local_media_id, $ffn );
		wp_update_attachment_metadata( $local_media_id, $attach_data );				

		$book->book_url = wp_get_attachment_url($local_media_id);
		$book->media_id = $local_media_id;
		$book->save();
		
		// get wordpress url
		$url_epub = wp_get_attachment_url($local_media_id);
	}
	
	return $url_epub;
}



// Export a list of books to one epub file 
// and add it to media library
// books_ids : array  : WH_Book ids
// title     : string : EPUB Book title (epub file name too)
// cover     : string : image cover URL
// authors   : array  : list of author's names
// type      : string : books genre
// statuses : array   : chapters, scenes statuses to export
// => return array ('media_id', 'media_url')
function wtrh_exportBooksToEpub($books_ids, $title = "", $cover = "",
								$resume = "", $authors = "", $type = "",
								$statuses = array()) {
	
	$url_epub  = array();
	if( ! is_array($statuses) || count($statuses) == 0 )
		$statuses = WH_Status::BOOK_STATUSES;

	// verify export directory
	if( ! file_exists(WTRH_EXPORT_DIR) ) 
		if( ! mkdir(WTRH_EXPORT_DIR) ) {
			wtr_error_log(__METHOD__, "create dir has failed! (dir=".WTRH_EXPORT_DIR.")");
			return $url_epub;
		}			

	// verify books ids
	if( ! is_array($books_ids) || count($books_ids) == 0 )  {
		wtr_error_log(__METHOD__, "No books given");
		return $url_epub;
	}			
	$books = WH_Book::getAll_BooksInfoByIds($books_ids, $statuses);
	
	if( strlen(trim($title)) == 0 )
		$title = __('Miscellaneous','wtr_helper');
	if( strlen(trim($resume)) == 0 ) {
		$resume = "<h1>".__('Anthology of books','wtr_helper')."</h1><br/>";
		foreach( $books as $b ) {
			$resume .= "<b>".$b->get_Title()."</b><br/>";
			$resume .= $b->get_Resume()."<br/><br/>";
		}
	}
	if( ! is_array($authors) || count($authors) == 0 ) {
		$authors = array();
		foreach( $books as $b ) {
			foreach( $b->get_BookAuthors() as $a )
				$authors[] = $a['name'];
		}
	}
	if( strlen(trim($type)) == 0 )
		$type = __('Miscellaneous','wtr_helper');
		
	$epub_book = new EPUB_Book($title, $authors, 
	                           $type, $resume,
							   $cover, "");
	
	// For each book, create title and resume chapter-like info
	
	$text      = ""; // Text to search for media in books
	
	foreach( $books as $i => $book ) {
		$chap_book_id = sprintf('%03d', $i);
		// create cover chapter
		$ch_text = "<img class=\"cover-img\" ".
				   "src=\"".$book->get_CoverUrl()."\" ".
				   "alt=\"".sprintf(__('Cover of %s','wtr_helper'), $book->get_Title('text'))."\" />";
		$text .= $ch_text;
		$epub_book->addChapter(array('id'    => $chap_book_id."1"."00000",
									 'title' => $book->get_Title(),
									 'text'  => $ch_text,
									 'links' => array()));		
		// create resume chapter
		$ch_text = $book->get_Resume();
		$text .= $ch_text;
		$epub_book->addChapter(array('id'    => $chap_book_id."2"."00000",
									 'title' => sprintf(__('Resume of %s','wtr_helper'),$book->get_Title()),
									 'text'  => $ch_text,
									 'links' => array()));		
		
		// regular chapters
		foreach( $book->get_ChaptersText(0, $statuses) as $ch ) {
			$ch_text = "";
			foreach( $ch['scenes'] as $i => $sc ) {
				if( $i > 0 )
					$ch_text .= "\n<br/><br/>".
							"<p class='whsSceneBetweenText'>***</p>".
							"<br/><br/>\n";
				
				$ch_text .= wtrh_replaceWidthImage($sc['text']);
			}
			$text .= $ch_text;
			$epub_book->addChapter(array('id'    => $chap_book_id."3".sprintf('%05d',$ch['id']),
										 'title' => $ch['title'],
										 'text'  => $ch_text,
										 'links' => $ch['links']));
		}
		// End of book
		$epub_book->addChapter(array('id'    => $chap_book_id."4"."00000",
									 'title' => "",
									 'text'  => "<center>*** ".sprintf(__('End of %s','wtr_helper'),$book->get_Title())." ***</center>",
									 'links' => array()));		
	}
	
	// Create EPUB
	$book_media = wtrh_getMedia($text);
	
	$ident = uniqid().'-'.uniqid().'-'.
	         uniqid().'-'.uniqid().'-'.uniqid();
	
	$metadata = array('title'       => wtr_sanitize($title,'title'),
					  'creator'     => implode(", ", $authors),
					  'subject'     => "",
					  'description' => wtr_sanitize($resume,'title'),
					  'date'        => wtr_getFormatedDate(),
					  'type'        => "book",
					  'format'      => "epub",
					  'identifier'  => $ident,
					  'language'    => substr(get_locale(),0,2),
					  'copyright'   => "All rights reserved to ".implode(", ", $authors)
	                  );
	$epub_file = wtr_sanitize($title,'file');
	$ffn = wp_upload_dir()['path'].'/'.$epub_file.".epub";
	$epub_gen  = new EPUB_Generator(WTRH_EXPORT_DIR.'/epub_trav', $ffn, 
									$metadata, $epub_book, $book_media);
	
	if( ! $epub_gen->generateEpub() )
		return false;
	
	
	$posts = array();
	// create a WordPress media IF USER CAN
	if( current_user_can(WTRH_USR_MEDIA_CAP) ) {
		$posts = get_posts( array('name'           => "wh_epub_".$epub_file,
								  'post_mime_type' => 'application/epub+zip',
								  'post_type'      => 'attachment',
								  'numberposts'    => 1));
		$local_media_id = 0;
		if( isset($posts[0]) &&  $posts[0]->post_name == "wh_epub_".$epub_file )
			$local_media_id = $posts[0]->ID;

		// Create a wordpress media
		$attachment = array(
			'post_mime_type' => 'application/epub+zip',
			'post_type'      => 'attachment',
			'post_name'      => "wh_epub_".$epub_file,
			'post_title'     => $title. " (EPUB)",
			'post_content'   => $resume,
			'post_status'    => 'publish'
		);

		if( $local_media_id == 0 ) {
			// Insert the attachment.
			$local_media_id = wp_insert_attachment( $attachment, $ffn, 0 );
		}
		
		// Generate the metadata for the attachment
		$attach_data = wp_generate_attachment_metadata( $local_media_id, $ffn );
		wp_update_attachment_metadata( $local_media_id, $attach_data );				
		
		// Return wordpress media id & url
		$url_epub = array("media_id"  => $local_media_id, 
						  "media_url" => wp_get_attachment_url($local_media_id));
	}
	
	return $url_epub;
}



// Return book text in HTML format
function wtrh_getBookText($book_id, $statuses = array()) {
	
	$html = "";
	
	$book = new WH_Book($book_id);
	
	$html .= "<!DOCTYPE html>".
			 "<html xmlns=\"http://www.w3.org/1999/xhtml\" ".
			 "lang=\"".get_locale()."\">\n".
			 "<head>\n".
			 "<meta http-equiv=\"Content-Type\" ".
			 "content=\"text/html; charset=UTF-8\" />\n".
			 "<title>".$book->title."</title>\n".
			 "<link rel='stylesheet' href='".WTRH_CSS_URL."/book.css' type='text/css' media='all' />\n".
			 "</head>\n".
			 "<body>\n";

	if( $book_id == 0 || ! $book->isOk ) {
		return sprintf(__('Book reference not found : %s','wtr_helper'), $book_id)."</body></html>";
	}
	
	
	// Display book cover
	$cover_url = $book->get_CoverUrl();
	if( strlen(trim($cover_url)) > 0 ) {
		$html .= "<p class='whPrintBook_Cover'>".
			 "<img src='".$cover_url.
			 "' alt='".__('Cover','wtr_helper')."' />".
			 "</p>\n";
	} else {
		$html .= "<p class='whPrintBook_Cover'>".
			 "<span class='whPrintBook_CoverTitle'>".$book->title."</span>";

		foreach( $book->get_BookAuthors() as $a )
			$html .= "<span class='whPrintBook_CoverAuthor'>".$a['name']."</span><br/>";
		
		$html .= "</p>";
	}
	$html .= "<p class='whPageBreak'>&nbsp;</p>\n";
	
	
	// Book resume
	$html .= "<h1 class='whPrintBook_ResumeTitle'>".
			__('Resume','wtr_helper')."</h1>";
	$html .= $book->get_Resume();
	$html .= "<p class='whPageBreak'>&nbsp;</p>\n";
	
	
	// Book summary
	$html .= "<h1 class='whPrintBook_SummaryTitle'>".
			__('Summary','wtr_helper')."</h1><br/>";
	$html .= "<ul class='whPrintBook_SummaryList'>\n";
	foreach( $book->get_ChaptersText(0, $statuses) as $key => $ch ) 
		$html .= "<li><a href='#ct-".$key."'>".
				str_replace('<br/>',' : ',$ch['title'])."</a></li>\n";
	$html .= "</ul>";
	$html .= "<p class='whPageBreak'>&nbsp;</p>\n";
	
	
	// Book chapters
	foreach( $book->get_ChaptersText(0, $statuses) as $key => $ch ) {
		$ch_text = "";
		// Group text scenes
		foreach( $ch['scenes'] as $i => $sc ) {
			if( $i > 0 )
				$ch_text .= "\n<br/><br/>".
						"<p class='whsSceneBetweenText'>***</p>".
						"<br/><br/>\n";
						
			$ch_text .= wtrh_replaceWidthImage($sc['text']);
		}
		// Display Chapter
		$html .= "<h1 class='whPrintBook_ChapterTitle' id='ct-".$key."'>".$ch['title']."</h1>\n";
		$html .= $ch_text;
		$html .= "<p class='whPageBreak'>&nbsp;</p>\n";
	}
	
	return $html."</body></html>";
}


// Return an array of media contained in text
/* return array : x => array
*						'type'     => string [image|audio|video]
*						'title'    => string
*						'filename' => string
*						'url'      => string
*/
function wtrh_getMedia($text) {
	
	$media_list = array();
	$fname_list = array();
	
	// get a list of media from a HTML text
	$pattern = '/(src=\")(?P<fname>[^"]*)/';

	$offset = 0;

	while( 1 == ($ret = preg_match($pattern, $text, $tab, PREG_OFFSET_CAPTURE, $offset)) ) {

		$offset   = $tab['fname'][1] + 1;
		$ffname   = $tab['fname'][0];
		$mimetype = wp_check_filetype($ffname)['type'];

		// If media not in array
		if( ! in_array($ffname, $fname_list) )
			$media_list[] = array( 
							   'title'    => basename($ffname),
							   'filename' => $ffname,
							   'url'      => $ffname,
							   'type'     => explode("/", $mimetype)[0]
							   );
		$fname_list[] = $ffname;
	}
		
	return $media_list;
}


// Replace width="" height="" 
function wtrh_replaceWidthImage($text) {
	$patern = '/width=\"[0-9]*\" height=\"[0-9]*\"/';
	$repl   = 'style="width:auto;"';
	
	return preg_replace($patern,$repl,$text);
	
}
?>