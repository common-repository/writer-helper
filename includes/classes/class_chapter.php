<?php
/************************************
 **          Chapter class         **
 ************************************/

class WH_Chapter extends WH_Element {
	
//already in WH_Element	public $id;
	public $title;
	public $number;
//already in WH_Element	public $status;
	public $show_number;
	public $show_title;
	public $publication_date;
	public $book_id;
	public $book;       /* object */
	
	public $isOk;       /* init ok ? */
	public $scenes;     /* array(object) */
	public $word_count;
	
	public $chapter_post;	/* WP_Post */
	
	private $DB_Chapter;/* object */
	
	/**
	* Class constructor 
	*  $args : array()
	*			'title' => string
	*			'number' => int
	*			'status' => int
	*			'show_number' => boolean
	*			'show_title' => boolean
	*			'book_id' => int
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id               = isset($args['id'])              ? (int)$args['id']: (int)$id;
		$this->title            = isset($args['title'])           ? (string)$args['title']:'';
		$this->number           = isset($args['number'])          ? (int)$args['number']:0;
		$this->status           = isset($args['status'])          ? (int)$args['status']:WH_Status::DRAFT;
		$this->show_number      = isset($args['show_number'])     ? (string)$args['show_number']:true;
		$this->show_title       = isset($args['show_title'])      ? (string)$args['show_title']:false;
		$this->publication_date = isset($args['publication_date'])? (string)$args['publication_date']:"0000-00-00";
		$this->book_id          = isset($args['book_id'])         ? (int)$args['book_id']:0;
		
		$this->chapter_post     = null;
		$this->book             = null;
		$this->scenes           = array();

		$this->isOk             = false;
		$this->word_count       = ($this->id!=0)?$this->getWordCount():0;
		
		$this->DB_Chapter       = null;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
			return false;
		} else {
			if( $id != 0 ) {
				$this->get_Chapter($id);
			} else {
				$this->DB_Chapter = new WH_DB_Chapter(0, $args);
				$this->isOk = $this->DB_Chapter->isOk;
				
				if( $this->number == 0 )
				$this->number = $this->DB_Chapter->number;
			}
			
			$this->get_ChapterPost();
			
			if( $cascade ) {
				$this->get_ChapterScenes();
				$this->book = new WH_Book($this->book_id);
			}
		}
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = $this->DB_Chapter->save();
		if( $result )
			$this->id = $this->DB_Chapter->id;
		
		$this->update_ChapterPost();
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$this->updateDB_Object();
				
		// delete all scenes
		$this->get_ChapterScenes();
		foreach($this->scenes as $key => $scene) {
			$ret = $scene->delete();
			unset($this->scenes[$key]);
		}

		return $this->DB_Chapter->delete();
	}
	
	
	/* Return chapter's scenes  */
	public function getSubElements() {
		return $this->get_ChapterScenes();
	}

	/* Refresh upper element status  */
	public function refreshUpperElementStatus() {
		if( $this->book == null ){
			$this->book = new WH_Book($this->book_id);
			$this->book->refreshStatus();
		}
	}
	
	
	
	/* calculate word count  */
	public function getWordCount() {
		return WH_Scene::get_WordCount(0, $this->id);
	}
	
	/* Get publication date with formatting */
	public function get_PublicationDate($dateFormat = "") {
		if( $dateFormat == "" )
			$dateFormat = WH_Category::get_DateFormat();
		$str = "";
		
		if( $this->publication_date != "0000-00-00" )
			$str = date_format(date_create($this->publication_date), $dateFormat);
		
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
	
	/* Generate chapter title  */
	public function getChapterTitle($one_line = true, $force = false) {
		$title     = "";
		$title_num = __('Chapter','wtr_helper')." ".$this->number;
		if( $this->show_number )
			$title .= $title_num;
		if( $this->show_title ) {
			if(! $one_line && $title != "")
				$title .= "<br/>";
			else
				$title .= " ";
			$title .= $this->title;
		}
		
		if( $title == "" && $force )
			$title = $title_num;
		
		return $title;
	}
	
	/*  return scenes' text if not trashed */
	public function get_ScenesText($statuses = array()) {
		$this->get_ChapterScenes();
		$texts = array();
		if( ! is_array($statuses) || count($statuses) == 0 )
			$statuses = WH_Status::CHAPTER_STATUSES;
		if( in_array(WH_Status::PUBLISHED, $statuses) && ! in_array(WH_Status::EDITED, $statuses) )
			$statuses[] = WH_Status::EDITED;
		
		foreach( $this->scenes as $sc )
			if( count($statuses) == 0 || in_array($sc->status, $statuses) )
				$texts[] = array('status' => $sc->status,
			                     'text'   => $sc->get_Text('text'));
			
		return $texts;
	}
	
	/*  return scenes' links if not trashed */
	public function get_ScenesLinks($statuses = array()) {
		if( count($this->scenes) == 0 )
			$this->get_ChapterScenes();
		$links = array();
		if( ! is_array($statuses) || count($statuses) == 0 )
			$statuses = WH_Status::CHAPTER_STATUSES;
		if( in_array(WH_Status::PUBLISHED, $statuses) && ! in_array(WH_Status::EDITED, $statuses) )
			$statuses[] = WH_Status::EDITED;
		
		foreach( $this->scenes as $sc ) {
			if( in_array($sc->status, $statuses) ) {
				$linkedScenes = $sc->get_GameBook();
				if( $sc->isGameBook && count($linkedScenes) == 0 ) { // End of the book 
					$links[] = array('chapter_id' => 0,
									 'text_link'  => __('The End','wtr_helper'));
					$links[] = array('chapter_id' => 1,
									 'text_link'  => __('Go back to the beginning','wtr_helper'));
				}
				foreach( $linkedScenes as $l ) {
					$ls = new WH_Scene($l['scene_id'], array(), true);
					$ltext = sprintf(__('Go to chapter %s','wtr_helper'), $ls->chapter->number);
					if( strlen(trim($l['libelle'])) > 0 )
						$ltext = trim($l['libelle']);
					$links[] = array('chapter_id' => $ls->chapter->number,
									 'text_link'  => $ltext);
				}
			}
		}
		return $links;
	}
	
	/* Read DB */
	private function get_Chapter($id) {
		$this->DB_Chapter = new WH_DB_Chapter($id);
		
		if( $this->DB_Chapter->isOk ) {
			$this->id               = $this->DB_Chapter->id         ;
			$this->title            = $this->DB_Chapter->title      ;
			$this->number           = $this->DB_Chapter->number     ;
			$this->status           = $this->DB_Chapter->status     ;
			$this->show_number      = $this->DB_Chapter->show_number;
			$this->show_title       = $this->DB_Chapter->show_title ;
			$this->publication_date = $this->DB_Chapter->publication_date ;
			$this->book_id          = $this->DB_Chapter->book_id    ;

			$this->isOk = true;
			
		} else
			$this->isOk = false;
	}

	/* Update DB object */
	private function updateDB_Object() {
		$this->DB_Chapter->id               = $this->id              ;
		$this->DB_Chapter->title            = $this->title           ;
		$this->DB_Chapter->number           = $this->number          ;
		$this->DB_Chapter->status           = $this->status          ;
		$this->DB_Chapter->show_number      = $this->show_number     ;
		$this->DB_Chapter->show_title       = $this->show_title      ;
		$this->DB_Chapter->publication_date = $this->publication_date;
		$this->DB_Chapter->book_id          = $this->book_id         ;
		
		$this->DB_Chapter->chapter_post_obj = $this->chapter_post    ;
		
		// refresh object
		if( $this->book_id == 0 )
			$this->book = null;
		else if ( $this->book != null && $this->book->id != $this->book_id )
			$this->book = new WH_Book($this->book_id);
	}
	
	/* Get Chapter's Scenes */
	public function get_ChapterScenes() {
		if( $this->id != 0 )
			$this->scenes = WH_Scene::getAll_Scenes($this->id);
		return $this->scenes;
	}
	
	
	/* Get Chapter's post */
	public function get_ChapterPost() {
		$this->chapter_post = null;
		
		if( $this->id != 0 ) {
			$this->DB_Chapter->readChapterPost();
			$this->chapter_post = $this->DB_Chapter->chapter_post_obj;	
		}
		return $this->chapter_post;
	}
	
	/* Get Chapter's post URL */
	public function get_ChapterPostUrl() {
		$url = "";
		if( $this->chapter_post == null )
			$this->get_ChapterPost();
		if( $this->chapter_post != null )
			$url = $this->chapter_post->guid;
			
		return $url;
	}
		
	/* Delete Chapter WP_Post */
	public function delete_ChapterPost() {
		$ret = $this->DB_Chapter->deleteChapterPost();
		if( $ret )
			$this->chapter_post = null;
		
		return $ret;
	}
		
	/* Create Chapter WP_Post */
	public function create_ChapterPost($post_args = array()) {
		$ret = true;
		
		// if chapter not saved
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
															"[writerhelper id=".$this->book_id." chapter=".$this->number."]\n".
															"<!-- /wp:shortcode -->",
							//	'post_content_filtered' => ,
								'post_title'            => $this->getChapterTitle(false),
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
			$this->chapter_post = get_post($post_id);
		
		return $ret;
	}

	/* Update Chapter WP_Post */
	public function update_ChapterPost($title = "", $content = "",
										$status = "", $cats = array(), 
										$tags = array()) {
		$ret = false;
		
		// If there is a WP_Post linked to the chapter
		if( $this->chapter_post != null && 
		    get_class($this->chapter_post) == "WP_Post"&& 
		    $this->chapter_post->ID != 0 ) {
				
			$args = array('ID' => $this->chapter_post->ID);
			
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
				
			$ret = $this->create_ChapterPost($args);
		}
		
		return $ret;
	}
	
	
	
	/* Get Book's Chapters */
	public static function getAll_Chapters($book_id, $status_array = array(), 
											$col = "number", $direction = "asc",
											$cascade = false) {
		$myChapters = array();
		$dbChapters = WH_DB_Chapter::getAllDB_Chapters($book_id,
														$status_array, 
														$col, $direction);
		foreach( $dbChapters as $chapter ) {
			$myChapters[] = new WH_Chapter(0, get_object_vars($chapter), $cascade);
		}
		
		return $myChapters;
	}
	
	
	/* Get Latest published chapters */
	/*   nb : number of chapters to return  */
	public static function get_LatestPublishedChapters($nb = "5") {
		$myChapters = array();
		$dbChapters = WH_DB_Chapter::getDB_LatestPublishedChapters($nb);
		
		foreach( $dbChapters as $chapter ) {
			$myChapters[] = new WH_Chapter(0, get_object_vars($chapter));
		}
		
		return $myChapters;
	}
	
}
?>