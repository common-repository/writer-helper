<?php
/************************************
 **           Book class           **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/classes/class_element.php");
include_once(WTRH_EPUBGENERATOR_DIR . "/epubgenerator.php");

class WH_Book extends WH_Element {
	
//already in WH_Element	public $id;
	public $title;
	public $resume;
	public $type;
	public $cover;
	public $banner;
//already in WH_Element	public $status;
	public $creation_date;
	public $publication_date;
	public $sale_url;
	public $promo_url;
	public $opinion_url;
	public $isbn;
	public $bookworld_id;
	public $storyboard_id;
	public $book_url;	/* epub url */
	public $media_id;   /* epub wordpress's media */
	
	public $isOk;       /* init ok ? */
	public $isGameBook; /* is a game book ? */
	public $chapters;   /* array(object) */
	
	public $book_post;	/* WP_Post */
	public $book_info;	/* array */
	public $authors;	/* array */
	public $editors;	/* array */
	public $readers;	/* array */
	public $readersp;	/* array */
	
	public $word_count;
	
	private $DB_Book;    /* object */
		
	/**
	* Class constructor 
	*  $args : array()
	*			'title'         => string
	*			'resume'        => string
	*			'type'          => string
	*			'cover'         => string
	*			'banner'        => string
	*			'status'        => int
	*			'creation_date' => date
	*			'publication_date' => date
	*			'sale_url'      => string
	*			'promo_url'     => string
	*			'opinion_url'   => string
	*			'isbn'          => string
	*			'bookworld_id'  => int
	*			'storyboard_id' => int
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id               = isset($args['id'])              ? (int)$args['id']:(int)$id;
		$this->title            = isset($args['title'])           ? (string)$args['title']:'';
		$this->resume           = isset($args['resume'])          ? (string)$args['resume']:'';
		$this->type             = isset($args['type'])            ? (string)$args['type']:'';
		$this->cover            = isset($args['cover'])           ? (string)$args['cover']:'';
		$this->banner           = isset($args['banner'])          ? (string)$args['banner']:'';
		$this->status           = isset($args['status'])          ? (int)$args['status']:WH_Status::DRAFT;
		$this->creation_date    = isset($args['creation_date'])   ? (string)$args['creation_date']:date('Y-m-d');
		$this->publication_date = isset($args['publication_date'])? (string)$args['publication_date']:"0000-00-00";
		$this->sale_url         = isset($args['sale_url'])        ? (string)$args['sale_url']:'';
		$this->promo_url        = isset($args['promo_url'])       ? (string)$args['promo_url']:'';
		$this->opinion_url      = isset($args['opinion_url'])     ? (string)$args['opinion_url']:'';
		$this->isbn             = isset($args['isbn'])            ? (string)$args['isbn']:'';
		$this->bookworld_id     = isset($args['bookworld_id'])    ? (int)$args['bookworld_id']:0;
		$this->storyboard_id    = isset($args['storyboard_id'])   ? (int)$args['storyboard_id']:0;
		$this->book_url         = "";
		$this->media_id         = 0;
		$this->isGameBook       = isset($args['game_book'])       ? (bool)$args['game_book']:false;
		
		$this->chapters         = array();
		$this->authors          = array();
		$this->editors          = array();
		$this->readers          = array();
		$this->readersp         = array();
		
		$this->isOk             = false;
		$this->word_count       = $this->getWordCount();
		$this->statuses         = WH_Status::getBookStatuses($this->id);
		
		$this->DB_Book          = null;
		
		if( ! empty($id) && ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
			return false;
		} else {
			if( $id != 0 ) {
				$this->get_Book($id);

			} else {
				$this->DB_Book = new WH_DB_Book(0, $args);
				$this->isOk = $this->DB_Book->isOk;
			}
			$this->get_BookInfo();
			$this->get_BookPost();
			
			if( $cascade ) {
				$this->get_BookChapters($cascade);
				$this->get_BookAuthors();
				$this->get_BookEditors();
				$this->get_BookReaders();
				$this->get_BookReadersPremium();
			}						
		}
		
		// Verify if media exists
		if( $this->media_id != 0 ) {
			$post = get_post($this->media_id);
			if( $post == null ) {
				$this->media_id = 0;
				$this->book_url = "";
				$this->save();
			} else {
				$this->book_url = $post->guid;
			}
		}
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = $this->DB_Book->save();
		if( $result ) // copy id
			$this->id = $this->DB_Book->id;
		
		$this->update_BookPost();
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$ret = true;
		$this->updateDB_Object();

		// delete all chapters
		$this->delete_BookChapters();
		
		if( $ret )
			$ret = $this->DB_Book->delete();
		
		return $ret;
	}
	
	/* get word count for a book  */
	public function getWordCount() {
		return WH_Scene::get_WordCount($this->id);
	}
	
	/* Get book title */
	public function get_Title($format = 'html') {
		$title = $this->title;
		
		switch( $format ) {
			case 'text':
						$title = html_entity_decode($title,ENT_QUOTES);
						break;
			case 'html':
			default:
						break;
		}
		
		return $title;
	}
	
	/* Get book resume */
	public function get_Resume($format = 'html') {
		$str = $this->resume;
		
		switch( $format ) {
			case 'text':
						$str = html_entity_decode($str,ENT_QUOTES);
						break;
			case 'html':$str = str_replace("\n","<br>",$str);
			default:
						break;
		}
		
		return $str;
	}
	
	/* Get book type */
	public function get_Type($format = 'html') {
		$type = $this->type;
		
		switch( $format ) {
			case 'text':
						$type = html_entity_decode($type,ENT_QUOTES);
						break;
			case 'html':
			default:
						break;
		}
		
		return $type;
	}
	
	/* Get cover image url */
	public function get_CoverUrl() {
		$url = "";
		
		if( strlen(trim($this->cover)) > 0 ) {
			$media = json_decode($this->cover);
			$url   = $media->guid;
		} else
			$url   = WTRH_IMG_URL."/NoCover2.png";
		
		
		return $url;
	}
	
	/* Get bookworld name */
	public function get_BookworldName() {
		$name = "";
		
		if( $this->bookworld_id != 0 ) {
			if( class_exists('WH_Bookworld') ) {
				$bw = new WH_Bookworld($this->bookworld_id);
				if( $bw->isOk )
					$name = $bw->title;
			}
		}
		return $name;
	}
	
	/* Get publication date with formatting */
	public function get_PublicationDate($dateFormat = "") {
		$str = "";
		
		if( $this->publication_date != "0000-00-00" )
			$str = wtr_getFormatedDate($this->publication_date, $dateFormat);
		else 
			$str = __('Coming soon','wtr_helper');
		
		return $str;
	}
	
	/* Set publication date with formatting */
	public function set_PublicationDate($date = "") {
		$ret = true;
		
		if( $date == "" )
			$date = date('Y-m-d');
		
		$str = wtr_getFormatedDate($date, 'Y-m-d');
		if( $str === false ) {
			wtr_error_log(__METHOD__, 
						  "Error while formating publication date".
						  " (date=".$date.")");			
			$ret = false;
		} else
			$this->publication_date = $str;
		
		return $ret;
	}
	/* Reset publication date */
	public function reset_PublicationDate() {
		$this->publication_date = "0000-00-00";
	}
	
	/* Get book's summary  */
	public function get_BookSummary() {
		$sum_lines = array();
		$continue = true;
		
		// read all chapters
		$this->get_BookChapters();
		
		foreach($this->chapters as $chap) {
			
			if( $chap->status != WH_Status::TRASHED ) {
				$title    = $chap->getChapterTitle();
				$title_f  = $chap->getChapterTitle(true, true);
				$chap_url = "";
				$publish  = WH_Status::isPublishStatus($chap->status);
				if( $publish > 0 && $continue )
					$chap_url = $this->get_BookPostUrl()."?book=".$this->id."&chapter=".$chap->number;
				
				if( $publish == 0 )
					$continue = false;
				
				$sum_lines[] =array('url'    => $chap_url,
									'title'  => $title,
									'title_f'=> $title_f,
									'id' 	 => $chap->id,
									'status' => $chap->status,
									'publish'=> $publish,
									'number' => $chap->number);
			}
		}
		
//wtr_info_log(__METHOD__, "Summary array: ".print_r($sum_lines, true));		
		return $sum_lines;
	}

	/* Return Book's summary in HTML format */
	/* user_id : reader id if set */
	public function get_BookSummaryHTML($user_id = 0, $chapter_number = 0) {
		$html = "";
		$nbFreeChapters = -1;
		$isPreviewUser  = false;
		$isHiddenUser   = false;
		
		if( $user_id > 0 && class_exists("WH_Reader") ) {
			$isPreviewUser = WH_Reader::previewAuthorized($user_id);
			$isHiddenUser  = WH_Reader::hiddenAuthorized($user_id);
		}
		
		if( is_array($this->book_info) && isset($this->book_info['freeChapter']) )
			$nbFreeChapters = $this->book_info['freeChapter'];
		if( is_array($this->book_info) && isset($this->book_info['seeHidden']) )
			if(  $this->book_info['seeHidden'] == WTRH_BACCESS_ALL )
				$isHiddenUser = true;
		if( is_array($this->book_info) && isset($this->book_info['seePreview']) )
			if(  $this->book_info['seePreview'] == WTRH_BACCESS_ALL )
				$isPreviewUser = true;

		
		$html .= "<div class='whwBookSummaryDiv'>\n";
		$html .= "<label class='whwBookSummaryTitle'>".
					$this->getTitle()." - ".
					__('Summary','wtr_helper')."</label>";
		$html .= "<ul class='whwBookSummary'>\n";
		foreach( $this->get_BookSummary() as $line ){
			$li_html = "";
			$dispUrl = false;
			$lib     = (($nbFreeChapters>0)?__('free','wtr_helper'):'');
			if( $line['publish'] == 0 )
				$lib = __('Coming soon','wtr_helper');
			if( $line['status'] == WH_Status::PREVIEW )
				$lib = __('Preview','wtr_helper');
			if( $line['status'] == WH_Status::HIDDEN )
				$lib = __('Hidden','wtr_helper');
				
			$title = ($line['title']!=""?$line['title']:$line['title_f']);
			// if line is displayed chapter
			if( $line['number'] == $chapter_number ) {
				$li_html .= "<span class='whDisplayedChapter'>".
							$title."</span>";
			} else {
				
				if( $line['url'] != "" && $line['publish'] == 2 ) {
					if( $nbFreeChapters < 0 || $line['number'] <= $nbFreeChapters ){ // if chapter published and free
						$dispUrl = true;					
					}
				}
				if( $line['status'] == WH_Status::HIDDEN )
					if( $isHiddenUser )
						$dispUrl = true;
					else
						$dispUrl = false;
				
				if( $line['status'] == WH_Status::PREVIEW )
					if( $isPreviewUser )
						$dispUrl = true;
					else
						$dispUrl = false;
					
				if( $dispUrl )
					$li_html .= "<a href='".$line['url']."'>";
				$li_html .= $title;
				if( $dispUrl )
					$li_html .= "</a> ";
								
			}
			if( $lib != "" )
				$li_html .= " <span class='whwComingSoon'>(".$lib.")</span>";
			$li_html .= "</li>\n";
			
			
			$html .= "<li class='whChapterSummary".($dispUrl?'':'Inactive')."'>".$li_html;
		}
		$html .= "</ul></div>";
		
		return $html;
	}
	
	/* Get book text  
	* nb       : integer : chapter number
	* statuses : array   : chapters & scenes's statuses
	*/
	public function get_ChaptersText($nb = 0, $statuses = array()) {
		$texts = array();
		if( ! is_array($statuses) || count($statuses) == 0 )
			$statuses = WH_Status::BOOK_STATUSES;
		
		// read all chapters
		$this->get_BookChapters();
		
		foreach($this->chapters as $chap) {
			
			// if chapter to get
			if( ($nb == 0 || $chap->number == $nb) &&
			    (count($statuses) == 0 || in_array($chap->status, $statuses)) ) {
				
				if( $chap->status != WH_Status::TRASHED ) {
					$chap_title = "";
					if( ! $this->isGameBook ) 
						$chap_title = $chap->getChapterTitle(false);
					
					if( $nb == 0 )
						$texts[] = array('status' => $chap->status,
										 'id'     => $chap->number,
										 'title'  => $chap_title,
										 'scenes' => $chap->get_ScenesText($statuses),
										 'links'  => $chap->get_ScenesLinks());
					else {
						$texts   = array('status' => $chap->status,
										 'id'     => $chap->number,
										 'title'  => $chap_title,
										 'scenes' => $chap->get_ScenesText($statuses),
										 'links'  => $chap->get_ScenesLinks());
						break;
					}
				}
				
			}
		}
		
		return $texts;
	}
	
	/* Export book text in text file */
	public function exportBookInTextFile($filename) {
		$success = true;
		$fn = WTRH_EXPORT_DIR . '/' . $filename . '.txt';
		
		// If file exists, delete
		if( file_exists($fn) )
			unlink($fn);
		
		$fh = fopen( $fn, "w+");
		if( $fh == false ) {
			$success = false;
			$comment = 	array("type"     => "error",
							  "data"     => array('filename'  =>$filename,
												  'full_filename' =>$fn),
							  "request"  => "fopen",
							  "msg"      => __('Opening file failed','wtr_helper'));
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->id, 
												'action'      => "exportBookInTextFile", 
												'comment'     => $comment));
		} else {
			if( ! fwrite($fh, "\n\n\n\n") )
				$success = false;
			
			// Write page 1 : book info
			$this->get_BookAuthors();
			$this->get_BookEditors();
			
			$binfo_order = WH_DB_Category::getAllDB_Categories(WTRH_CAT_EXPORTBOOK, "number", "asc");
			foreach( $binfo_order as $info ) {
				if( $info == "title" )
					if( ! fwrite($fh, $this->title."\n\n") )
						$success = false;
				if( $info == "bookworld" )
					if( ! fwrite($fh, $this->bookworld->title."\n\n") )
						$success = false;
				if( $info == WTRH_ROLE_AUTHOR )
					if( ! fwrite($fh, implode(', ', $this->authors)."\n\n") )
						$success = false;
				if( $info == WTRH_ROLE_EDITOR )
					if( ! fwrite($fh, implode(', ', $this->editors)."\n\n") )
						$success = false;
				if( $info == "type" )
					if( ! fwrite($fh, $this->type."\n\n") )
						$success = false;
				if( $info == "resume" )
					if( ! fwrite($fh, "\n\n".$this->resume."\n\n") )
						$success = false;
			}
			if( ! fwrite($fh, "\n\n\n\n") )
				$success = false;
			
			// Write texts
			$texts = $this->get_ChaptersText();
			foreach( $texts as $tt ) {
				// Write chapter's title
				if( strlen(trim($tt['title'])) > 0 )
					if( ! fwrite($fh, $tt['title']."\n\n\n") )
						$success = false;
					
				// Write all scenes
				foreach( $tt['scenes'] as $sc ) {
					if( ! fwrite($fh, $sc['text']."\n\n\n") )
						$success = false;
				}
				
				if( ! fwrite($fh, "\n\n\n\n") )
					$success = false;
			}
			
			if( ! $success )
				$comment =	array("type"     => "error",
								  "data"     => array('filename'  =>$filename,
													  'full_filename' =>$fn),
								  "request"  => "fwrite",
								  "msg"      => __('Writing file failed','wtr_helper'));
				WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->id, 
												'action'      => "exportBookInTextFile", 
												'comment'     => $comment, 
												'action_seen' => false,
												'action_done' => false));
		}
		
		fclose($fh);
		
		return $success;
	}
	
	/* Return book's chapters  */
	public function getSubElements() {
		return $this->get_BookChapters();
	}

	/* Refresh upper element status  */
	public function refreshUpperElementStatus() {}	
	


	/* Read DB */
	private function get_Book($id) {
		$this->DB_Book = new WH_DB_Book($id);

		if( $this->DB_Book->isOk ) {
			$this->id               = $this->DB_Book->id              ;
			$this->title            = $this->DB_Book->title           ;
			$this->resume           = $this->DB_Book->resume          ;
			$this->type             = $this->DB_Book->type            ;
			$this->cover            = $this->DB_Book->cover           ;
			$this->banner           = $this->DB_Book->banner          ;
			$this->status           = $this->DB_Book->status          ;
			$this->creation_date    = $this->DB_Book->creation_date   ;
			$this->publication_date = $this->DB_Book->publication_date;
			$this->sale_url         = $this->DB_Book->sale_url        ;
			$this->promo_url        = $this->DB_Book->promo_url       ;
			$this->opinion_url      = $this->DB_Book->opinion_url     ;
			$this->isbn             = $this->DB_Book->isbn            ;
			$this->bookworld_id     = $this->DB_Book->bookworld_id    ;
			$this->storyboard_id    = $this->DB_Book->storyboard_id   ;
			$this->book_url         = $this->DB_Book->book_url        ;
			$this->media_id         = $this->DB_Book->media_id        ;
			$this->isGameBook       = $this->DB_Book->isGameBook      ;
			
			$this->isOk = true;
			
		} else {
			$this->isOk = false;
		}
	}
	
	/* Update DB object */
	private function updateDB_Object() {
		$this->DB_Book->id              = $this->id              ;
		$this->DB_Book->title           = $this->title           ;
		$this->DB_Book->resume          = $this->resume          ;
		$this->DB_Book->type            = $this->type            ;
		$this->DB_Book->cover           = $this->cover           ;
		$this->DB_Book->banner          = $this->banner          ;
		$this->DB_Book->status          = $this->status          ;
		$this->DB_Book->creation_date   = $this->creation_date   ;
		$this->DB_Book->publication_date= $this->publication_date;
		$this->DB_Book->sale_url        = $this->sale_url        ;
		$this->DB_Book->promo_url       = $this->promo_url       ;
		$this->DB_Book->opinion_url     = $this->opinion_url     ;
		$this->DB_Book->isbn            = $this->isbn            ;
		$this->DB_Book->bookworld_id    = $this->bookworld_id    ;
		$this->DB_Book->storyboard_id   = $this->storyboard_id   ;
		$this->DB_Book->book_url        = $this->book_url        ;
		$this->DB_Book->media_id        = $this->media_id        ;
		$this->DB_Book->isGameBook      = $this->isGameBook      ;
		
		$this->DB_Book->book_post_obj   = $this->book_post       ;	
		$this->DB_Book->book_info_array = $this->book_info       ;	
		$this->DB_Book->authors_array   = $this->authors         ;	
		$this->DB_Book->editors_array   = $this->editors         ;	
		$this->DB_Book->readers_array   = $this->readers         ;	
		$this->DB_Book->readersp_array  = $this->readersp        ;	

	}
	
	/* Display object info */
	public function __toString() {
		$obj  = "Id: ".$this->id."\n";
		$obj .= "Title: ".$this->title."\n";
		$obj .= "Resume: ".$this->resume."\n";
		$obj .= "Status: ".$this->status."\n";
		$obj .= "Storyboard Id: ".$this->storyboard_id."\n";
		$obj .= "Bookworld Id: ".$this->bookworld_id;
		
		return $obj;
	}
	
	
	
	
	/* Delete Book's Chapters */
	public function delete_BookChapters($cascade = false) {
		// delete all chapters
		if( count($this->chapters) == 0 )
			$this->get_BookChapters();
		
		foreach($this->chapters as $key => $chapter) {
			$ret = $chapter->delete();
			unset($this->chapters[$key]);
		}
		
	}
	
	
	/* Get Book's Chapters */
	public function get_BookChapters($cascade = false) {
		if( $this->id != 0 )
		$this->chapters = WH_Chapter::getAll_Chapters($this->id, array(), "number", "asc", $cascade);
	
		return $this->chapters;
	}

	/* Pull a chapter up */
	public function changeChapterNumber($chapter_id, $new_number) {
		
		if( $chapter_id == 0 )
			return false;
		if( $new_number == 0 )
			return false;
		
		if( count($this->chapters) == 0 )
			$this->get_BookChapters();
		
		if( $new_number > count($this->chapters) )
			$new_number = count($this->chapters);

		$number = 1;
		$found = false;
		foreach( $this->chapters as $ch ) {
			
			if( $new_number == $ch->number ) {
				if( $chapter_id != $ch->id ) { // change numbers
					$found = true;
					$ch->number++;
					$ch->save();
					continue;
				}
			}
			
			if( $found && $chapter_id != $ch->id ) {
					$ch->number++;
					$ch->save();
			}

			if( $chapter_id == $ch->id ) { // modify concerned chapter
				$ch->number = $new_number;
				$ch->save();
				$found = false; // stop the number modification
			}
		}

		return true;
	}


	/* Get Book's title */
	public function getTitle($format = "HTML") {
		return $this->get_Title($format);
	}
	
	/* Get Book's resume */
	public function getResume($format = "HTML") {
		return $this->get_Resume($format);
	}
	
	

	/* Get Book's info */
	public function get_BookInfo() {
		$this->book_info = array();
		
		if( $this->id != 0 ) {
			$this->DB_Book->readBookInfo();
			$this->book_info  = $this->DB_Book->book_info_array;
			$this->DB_Book->readGameBookInfo();
			$this->isGameBook = $this->DB_Book->isGameBook;
		}
		return $this->book_info;
	}
		
	/* Get Book's authors */
	public function get_BookAuthors() {
		$this->authors = array();
		
		if( $this->id != 0 ) {
			$this->DB_Book->readAuthors();
			$this->authors = $this->DB_Book->authors_array;	
		}
		return $this->authors;
	}
	
	/* Add author */
	public function add_Author($user_id, $user_name = "") {
		$ret = false;
		
		if( strlen(trim($user_name)) == 0 )
			$user_name = WH_User::getWpUserName($user_id);
		
		$ret = $this->DB_Book->addAuthor($user_id, $user_name);
		$this->authors = $this->DB_Book->authors_array;
		
		return $ret;
	}
	
	/* Delete author */
	public function delete_Author($user_id) {
		$ret = false;
		$found = false;
		
		foreach( $this->authors as $key => $author ) {
			if( $author['id'] == $user_id ) {
				$ret = $this->DB_Book->authors[$key]->delete();
				unset($this->DB_Book->authors_array[$key]);
				unset($this->authors[$key]);
				$found = true;
				break;
			}
		}
		
		return $ret;
	}
	
	/* Is user id an author id */
	public function isAnAuthor($user_id) {
		$ret = false;
		$found = false;
		
		if( count($this->authors) == 0 )
			$this->get_BookAuthors();
		
		foreach( $this->authors as $key => $author ) {
			if( $author['id'] == $user_id ) {
				$found = true;
				break;
			}
		}
		
		return $found;
	}
		

	/* Get Book's editors */
	public function get_BookEditors() {
		$this->editors = array();
		
		if( $this->id != 0 ) {
			$this->DB_Book->readEditors();
			$this->editors = $this->DB_Book->editors_array;	
		}
		return $this->editors;
	}
	
	/* Add editor */
	public function add_Editor($user_id, $user_name = "") {
		$ret = false;
		
		if( strlen(trim($user_name)) == 0 )
			$user_name = WH_User::getWpUserName($user_id);

		$ret = $this->DB_Book->addEditor($user_id, $user_name);
		$this->editors = $this->DB_Book->editors_array;
		
		return $ret;
	}
	
	/* Delete editor */
	public function delete_Editor($user_id) {
		$ret = false;
		$found = false;
		$id = 0;
		
		foreach( $this->editors as $key => $editor ) {
			if( $editor['id'] == $user_id ) {
				$ret = $this->DB_Book->editors[$key]->delete();
				unset($this->DB_Book->editors_array[$key]);
				unset($this->editors[$key]);
				$found = true;
				break;
			}
		}
		
		return $ret;
	}
	
	/* Is user id an editor id */
	public function isAnEditor($user_id) {
		$ret = false;
		$found = false;
		
		if( count($this->editors) == 0 )
			$this->get_BookEditors();
		
		foreach( $this->editors as $key => $editor ) {
			if( $editor['id'] == $user_id ) {
				$found = true;
				break;
			}
		}
		
		return $found;
	}
	
	/* Get Book's readers */
	public function get_BookReaders() {
		$this->readers = array();
		
		if( $this->id != 0 ) {
			$this->DB_Book->readReaders();
			$this->readers = $this->DB_Book->readers_array;	
		}
		return $this->readers;
	}
	
	/* Get Book's readers premium */
	public function get_BookReadersPremium() {
		$this->readersp = array();
		
		if( $this->id != 0 ) {
			$this->DB_Book->readReadersPremium();
			$this->readersp = $this->DB_Book->readersp_array;	
		}
		return $this->readersp;
	}
	
	
	/* Return an array of ids from selected users (by role) */
	public function getUsersId($role) {
		$ids   = array();
		$users = array();
		
		switch($role){
			case WTRH_ROLE_AUTHOR :
				$users = $this->get_BookAuthors();
				break;
			case WTRH_ROLE_EDITOR :
				$users = $this->get_BookEditors();
				break;
			case WTRH_ROLE_READER :
				$users = $this->get_BookReaders();
				break;
			case WTRH_ROLE_READERP:
				$users = $this->get_BookReadersPremium();
				break;
		}
		
		foreach( $users as $u )
			$ids[] = $u['id'];
			
		return $ids;
	}
	
	
	

	/* Get Book's post */
	public function get_BookPost() {
		$this->book_post = null;
		
		if( $this->id != 0 ) {
			$this->DB_Book->readBookPost();
			$this->book_post = $this->DB_Book->book_post_obj;	
		}
		return $this->book_post;
	}
	
	/* Get Book's post URL */
	public function get_BookPostUrl($chapter_number = 0) {
		$url = "";
		if( $this->book_post == null )
			$this->get_BookPost();
		if( $this->book_post != null )
			$url = $this->book_post->guid;
		
		if( $chapter_number != 0 )
			$url .= "?book=".$this->id."&chapter=".$chapter_number;
		
		return $url;
	}
	
	/* Delete Book WP_Post */
	public function delete_BookPost() {
		$ret = $this->DB_Book->deleteBookPost();
		if( $ret )
			$this->book_post = null;
		
		return $ret;
	}
		
	/* Create Book WP_Post */
	public function create_BookPost($post_args = array()) {
		$ret = true;
		
		// if book not saved
		if( $this->id == 0 )
			return false;
		
		// IF User unauthorized to manage books
		if( ! WH_User::isAuthorized_action(WTRH_CAP_MANAGE_BOOKS) )
			return false;
		
		$post_default = array(  'ID'                    => 0,
								'post_author'           => get_current_user_id(),
							//	'post_date'             => ,  // default: current date
							//	'post_date_gmt'         => ,  // default: current date
								'post_content'          => "<!-- wp:shortcode -->\n".
															"[writerhelper id=".$this->id."]\n".
															"<!-- /wp:shortcode -->",
							//	'post_content_filtered' => ,
								'post_title'            => $this->getTitle(),
								'post_excerpt'          => "",
								'post_status'           => "publish"
							//	'post_type'             => , // default: 'post'
							//	'post_password'         => ,
							//	'post_modified'         => ,  // default: current date
							//	'post_modified_gmt'     => ,  // default: current date
							//	'post_parent'           => ,  // default: 0
							//	'menu_order'            => ,  // default: 0
							//	'post_mime_type'        => ,
							//	'post_category'         => ,
							//	'tags_input'            => 
							);
		
		if( empty($post_args) || ! is_array($post_args) || count($post_args) == 0 )
			$post_args = $post_default;
		else
			$post_args = array_merge($post_default, $post_args);
		
		$post_id = wp_insert_post($post_args, true);
		
		// If error
		if( is_wp_error($post_id) ) {
			$ret = false;
			wtr_error_log(__METHOD__, $post_id->get_error_message());
		} else
			$this->book_post = get_post($post_id);
		
		return $ret;
	}

	/* Update Book WP_Post */
	public function update_BookPost($title = "", $content = "",
									$status = "", $cats = array(), 
									$tags = array()) {
		$ret = false;
		
		// If there is a WP_Post linked to the book
		if( $this->book_post != null && 
		    get_class($this->book_post) == "WP_Post"&& 
		    $this->book_post->ID != 0 ) {
				
			$args = array('ID' => $this->book_post->ID);
			
			if( $title != "" )
				$args['post_title'] = $title;
			if( $content != "" )
				$args['post_content'] = $content;
			if( $status != "" )
				$args['post_status'] = $status;
			if( is_array($cats) && count($cats) > 0 )
				$args['post_category'] = $cats;
			if( is_array($tags) && count($tags) > 0 )
				$args['tags_input'] = $tags;
				
			$ret = $this->create_BookPost($args);
		}
		
		return $ret;
	}


	
	
	/* Get All Books */
	public static function getAll_Books($title = "", 
										$status_array = array(), 
										$col = "id", $direction = "asc") {
		$myBooks = array();
		$dbBooks  = WH_DB_Book::getAllDB_Books($status_array, "",
												$col, $direction);
		
		foreach( $dbBooks as $book ) {
			if( $title == "" || wtr_instr($book->title, $title) !== false )
			$myBooks[] = new WH_Book(0, get_object_vars($book));
		}
		                  
		return $myBooks;
	}
	
	
	/* Get a specified set of Books' info from an array of ids */
	public static function getAll_BooksInfoByIds($ids_array, $statuses = array(),
													$cols_array = array(),
													$col = "id", $direction = "asc") {
		if( ! is_array($ids_array) || count($ids_array) == 0 )
			return false;
		
		$myBooks = array();
		$dbBooks  = WH_DB_Book::getAllDB_BooksInfoByIds($ids_array, $cols_array, $col, $direction);
		
		if( is_array($dbBooks) )
			foreach( $dbBooks as $book ) {
				if( count($statuses) == 0 || in_array($book->status, $statuses) )
					$myBooks[] = new WH_Book(0, get_object_vars($book));
			}
		                  
		return $myBooks;
	}
	
	
	/* Get All Books For User */
	public static function getAll_BooksForUser($title = "", $user_id = 0, 
										$status_array = array(),
										$col = "title", $direction = "asc") {
		
		if( $user_id == 0 )
			$user_id = get_current_user_id();

		if( $user_id == 0 )
			return array();

		$user = new WH_User(0, array('user_id' => $user_id, 'role' => WTRH_ROLE_ADMIN));
		
		$myBooks = array();
		if( $user->id != 0 ) // admin => get all books
			$dbBooks  = WH_DB_Book::getAllDB_Books($status_array, "",
												$col, $direction);
		else // author, editor or reader => get related books
			$dbBooks  = WH_DB_Book::getAllDB_BooksForUser($user_id, array(),
									$status_array, $col, $direction);
		
		foreach( $dbBooks as $book ) {
			if( $title == "" || wtr_instr($book->title, $title) !== false )
				$myBooks[] = new WH_Book(0, get_object_vars($book));
		}
		                  
		return $myBooks;
	}
	
	/* Get All Books */
	public static function getAll_LibraryBooks($col = "id", $direction = "asc") {
		$myBooks = array();
		$dbBooks  = WH_DB_Book::getAllDB_LibraryBooks($col, $direction);
		
		foreach( $dbBooks as $book ) {
			$myBooks[] = new WH_Book(0, get_object_vars($book));
		}
		                  
		return $myBooks;
	}
	
	/* Get latest published books */
	/*   nb : number of books to return  */
	public static function get_LatestPublishedBooks($nb = "5") {
		$myBooks = array();
		$dbBooks  = WH_DB_Book::getDB_LatestPublishedBooks($nb);
		
		foreach( $dbBooks as $book ) {
			$myBooks[] = new WH_Book(0, get_object_vars($book));
		}
		                  
		return $myBooks;
	}
	
	/* Get All Books for a bookworld */
	public static function getAll_BooksByBookworld($bookworld_id, $col = "id", $direction = "asc") {
		$myBooks = array();
		$dbBooks  = WH_DB_Book::getAllDB_BookworldBooks($bookworld_id, $col, $direction);
		
		foreach( $dbBooks as $book ) {
			$myBooks[] = new WH_Book(0, get_object_vars($book));
		}
		                  
		return $myBooks;
	}
	
	/* Get All Books for a book type */
	public static function getAll_BooksByType($type, $col = "id", $direction = "asc") {
		$myBooks = array();
		$dbBooks  = WH_DB_Book::getAllDB_Books(array(), $type, $col, $direction);
		
		foreach( $dbBooks as $book ) {
			$myBooks[] = new WH_Book(0, get_object_vars($book));
		}
		                  
		return $myBooks;
	}
	
	/* Get All Game Books */
	public static function getAll_GameBooks($title = "", $col = "id", $direction = "asc") {
		$myBooks = array();
		$dbBooks  = WH_DB_Book::getAllDB_GameBooks(array(), $col, $direction);
		
		foreach( $dbBooks as $book ) {
			if( $title == "" || wtr_instr($book->title, $title) !== false )
				$myBooks[] = new WH_Book(0, get_object_vars($book));
		}
		                  
		return $myBooks;
	}
	
	/* Get All Books for a status */
	public static function getAll_BooksByStatus($status, $col = "id", $direction = "asc") {
		$myBooks = array();
		$dbBooks  = WH_DB_Book::getAllDB_Books(array($status), "", $col, $direction);
		
		foreach( $dbBooks as $book ) {
			$myBooks[] = new WH_Book(0, get_object_vars($book));
		}
		                  
		return $myBooks;
	}

	/* Re calculate chapters and scenes numbers */
	public static function refreshNumbers($args) {
		$ret     = true;
		$book    = null;
		$my_args = $args;
		
		// Get book
		if( isset($my_args['book_id']) && intval($my_args['book_id']) != 0) {
			$book    = new WH_Book(intval($my_args['book_id']), null, true);
			$my_args = array();
		} 
		if( isset($my_args['chapter_id']) && intval($my_args['chapter_id']) != 0) {
			$book    = new WH_Book(0, WH_DB_Book::getBookByChapter($my_args['chapter_id']), true);
			$my_args = array();
		}
		if( isset($my_args['scene_id']) && intval($my_args['scene_id']) != 0) {
			$book    = new WH_Book(0, WH_DB_Book::getBookByScene($my_args['scene_id']), true);
			$my_args = array();
		}
		// No book
		if( $book === null || $book->id == 0 )
			return $ret;
		
		// refresh numbers
		$ch_nb = 0;
		$sc_nb = 0;
		foreach( $book->chapters as $ch ) {
			$ch_nb++;
			// if different numbers
			if( $ch->number != $ch_nb ) {
				$ch->number = $ch_nb;
				$ret = $ch->save();
			}
			if( ! $ret ) {
				wtr_error_log(__METHOD__, 
							  "Save chapter error (chapter=".print_r($ch, true));
				break;
			}
			
			foreach( $ch->scenes as $sc ) {
				$sc_nb++;
				// if different numbers
				if( $sc->number != $sc_nb ) {
					$sc->number  = $sc_nb;
					$ret = $sc->save();
				}
				if( ! $ret ) {
					wtr_error_log(__METHOD__, 
								  "Save scene error (scene=".print_r($sc, true));
					break;
				}
			}
		}
		
		return $ret;
	}


	/* Return true if user id has the role of a book */
	// args : array ('book_id'    => int
	//               'chapter_id' => int
	//               'scene_id'   => int)
	public static function hasTheRoleForBook($args, $user_id, $role) {
		$found   = false;
		$book    = null;
		$my_args = $args;
		
		// If admin
		if( WH_User::userExists($user_id, WTRH_ROLE_ADMIN) )
			return true;
		
		// Get book
		if( isset($my_args['book_id']) && intval($my_args['book_id']) != 0) {
			$book    = new WH_Book(intval($my_args['book_id']), null, true);
			$my_args = array();
		} else
			if( isset($my_args['chapter_id']) && intval($my_args['chapter_id']) != 0) {
				$book    = new WH_Book(0, WH_DB_Book::getBookByChapter($my_args['chapter_id']), true);
				$my_args = array();
			} else
				if( isset($my_args['scene_id']) && intval($my_args['scene_id']) != 0) {
					$book    = new WH_Book(0, WH_DB_Book::getBookByScene($my_args['scene_id']), true);
					$my_args = array();
				}
				
		// No book
		if( $book == null || $book->id == 0 )
			return false;
		
		// get authors
		if( $role == WTRH_ROLE_AUTHOR ) {
			$book->get_BookAuthors();
			foreach( $book->authors as $au )
				if( $au['id'] == $user_id ) {
					$found = true;
					break;
				}
		}
		
		// get editors
		if( $role == WTRH_ROLE_EDITOR ) {
			$book->get_BookEditors();
			foreach( $book->editors as $ed )
				if( $ed['id'] == $user_id ) {
					$found = true;
					break;
				}
		}
		
		
		return $found;
	}
}
?>