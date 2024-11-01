<?php
/************************************
 **        DB Book class           **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_activity.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_chapter.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_scene.php");

class WH_DB_Book {
	
	// Metadata access keys
	const meta_obj_book    = "Book";
	
	const meta_key_post    = "BookPost";	
	const meta_key_info    = "BookInfo";
	const meta_key_bpage   = "BookPageUrl";
	const meta_key_status  = "BookStatuses";
	const meta_key_author  = WTRH_ROLE_AUTHOR;
	const meta_key_editor  = WTRH_ROLE_EDITOR;
	const meta_key_reader  = WTRH_ROLE_READER;
	const meta_key_readerp = WTRH_ROLE_READERP;
	const meta_key_game    = "GameBook";
	
	// DB info
	const tableName = "wtr_book";
	const createReq = "CREATE TABLE IF NOT EXISTS `%s".WH_DB_Book::tableName."` (".
	       "`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,".
	       "`title` text NOT NULL,".
	       "`resume` text NOT NULL,".
	       "`type` text NOT NULL,".
	       "`cover` text NOT NULL,".
	       "`banner` text NOT NULL,".
	       "`status` int(2) NOT NULL DEFAULT 0,".
	       "`creation_date` date NOT NULL,".
	       "`publication_date` date DEFAULT '0000-00-00',".
	       "`sale_url` text NOT NULL DEFAULT '',".
	       "`promo_url` text NOT NULL DEFAULT '',".
	       "`opinion_url` text NOT NULL DEFAULT '',".
	       "`isbn` text NOT NULL DEFAULT '',".
	       "`bookworld_id` bigint(20) NOT NULL DEFAULT 0,".
	       "`storyboard_id` bigint(20) NOT NULL DEFAULT 0,".
	       "`book_url` text NOT NULL,".
	       "`media_id` bigint(20) NOT NULL DEFAULT 0);";

	const tableCols= 'id, title, resume, type, cover, banner, '.
						'status, creation_date, publication_date, '.
						'sale_url, promo_url, opinion_url, isbn, '.
						'book_url, media_id, bookworld_id, storyboard_id';
	const selectBaseReq= "SELECT ".WH_DB_Book::tableCols.
	                             " FROM %s".WH_DB_Book::tableName;
	const selectBbyBReq= "SELECT ".WH_DB_Book::tableCols.
	                             " FROM %1\$s".WH_DB_Book::tableName.
								 " WHERE bookworld_id = %2\$s";
	const selectBbyCReq= "SELECT ".WH_DB_Book::tableCols.
	                             " FROM %1\$s".WH_DB_Book::tableName.
								 " WHERE id = (SELECT book_id FROM %1\$s".WH_DB_Chapter::tableName.
								 " WHERE id = %2\$s)";
	const selectBbySReq= "SELECT ".WH_DB_Book::tableCols.
	                             " FROM %1\$s".WH_DB_Book::tableName.
								 " WHERE id = (SELECT book_id FROM %1\$s".WH_DB_Chapter::tableName.
								 " WHERE id = (SELECT chapter_id FROM %1\$s".WH_DB_Scene::tableName.
								 " WHERE id = %2\$s))";
	const selectReq   = "SELECT ".WH_DB_Book::tableCols.
	                             " FROM %s".WH_DB_Book::tableName." WHERE id=%s";
	
	public $id;
	public $title;
	public $resume;
	public $type;
	public $cover;
	public $banner;
	public $status;
	public $creation_date;
	public $publication_date;
	public $sale_url;
	public $promo_url;
	public $opinion_url;
	public $isbn;
	public $bookworld_id;
	public $storyboard_id;
	public $book_url;
	public $media_id;
	
	public $book_post;		/* WH_Metadata */
	public $book_post_obj;  /* WP_Post */
	public $book_info;		/* WH_Metadata */
	public $book_info_array;/* array( freeChapter, seePublishedBook, seeHidden, seePreview, seeBookworld ) */
	public $authors;		/* WH_Metadata */
	public $authors_array;	/* array( user_id, user_name) */
	public $editors;		/* WH_Metadata */
	public $editors_array;	/* array( user_id, user_name) */
	public $readers;		/* WH_Metadata */
	public $readers_array;	/* array( user_id, user_name, capabilities( seePublishedBook, seeHidden, seePreview, seeBookworld)) */
	public $readersp;		/* WH_Metadata */
	public $readersp_array;	/* array( user_id, user_name, capabilities( seePublishedBook, seeHidden, seePreview, seeBookworld)) */
	public $gameBook;		/* WH_Metadata */
	public $gameBook_array; /* array() */
	public $isGameBook;
	
	public $isOk;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'title' => string
	*			'resume' => string
	*			'type' => string
	*			'cover' => string
	*			'banner' => string
	*			'status' => int
	*			'creation_date' => date
	*			'publication_date' => date
	*			'sale_url' => string
	*			'promo_url' => string
	*			'opinion_url' => string
	*			'isbn' => string
	*			'bookworld_id' => int
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

		$this->book_post      = null;
		$this->book_post_obj  = null;
		$this->book_info      = null;
		$this->book_info_array= array();
		$this->gameBook       = null;
		$this->gameBook_array = array();
		$this->authors        = array();
		$this->authors_array  = array();
		$this->editors        = array();
		$this->editors_array  = array();
		$this->readers        = array();
		$this->readers_array  = array();
		$this->readersp       = array();
		$this->readersp_array = array();
		
		$this->isOk = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
		} else {
			if( $id != 0 )
				$this->isOk = $this->getDB_Book($id);
			else
				$this->isOk = true;
						
			if( $cascade ) {
				$this->readBookInfo();
				$this->readGameBookInfo();
				$this->readPageUrl();
				$this->readAuthors();
				$this->readEditors();
				$this->readReaders();
				$this->readReadersPremium();
			}
		}
		return $this->isOk;
	}

	/* Update DB */
	public function save() {
		$result = false;
		
		// Save book
		if( $this->id == 0 ) { $result = $this->insertDB_Book(); }
		else {                 $result = $this->updateDB_Book();	}
		
		
		
		// update metadata
		if( $this->book_post != null ) {
			$this->book_post->meta_value = $this->book_post_obj->ID;
			$this->book_post->save();
		} else {
			// create metadata
			if( $this->book_post_obj != null ) {
				$this->book_post = new WH_DB_Metadata(0, array(
											'meta_obj' => WH_DB_Book::meta_obj_book,
											'obj_id'   => $this->id,
											'meta_key' => WH_DB_Book::meta_key_post
										));
				$this->book_post->meta_value = $this->book_post_obj->ID;
				$this->book_post->save();
			}
		}
		
		if( $this->book_info != null ) {
			$this->book_info->meta_value = json_encode($this->book_info_array);
			$this->book_info->save();			
		}
		
		if( $this->isGameBook ) {
			if( $this->gameBook == null ) 
				$this->readGameBookInfo(true);
			if( $this->gameBook != null ) {
				$this->gameBook->meta_value = json_encode($this->gameBook_array);
				$this->gameBook->save();			
			}
		}
		
		foreach( $this->authors as $i => $a ) {
			$this->authors[$i]->meta_value = $this->authors_array[$i]['name'];
			$this->authors[$i]->save();			
		}
		foreach( $this->editors as $i => $a ) {
			$this->editors[$i]->meta_value = $this->editors_array[$i]['name'];
			$this->editors[$i]->save();			
		}
		foreach( $this->readers as $i => $a ) {
			$this->readers[$i]->meta_value = json_encode($this->readers_array[$i]);
			$this->readers[$i]->save();			
		}
		foreach( $this->readersp as $i => $a ) {
			$this->readersp[$i]->meta_value = json_encode($this->readersp_array[$i]);
			$this->readersp[$i]->save();			
		}
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		
		if( $this->id == 0 )
			return true;

		if( $this->book_post == null ) 
			$this->readBookPost();
		if( $this->book_info == null ) 
			$this->readBookInfo();
		if( $this->gameBook == null && $this->isGameBook ) 
			$this->readGameBookInfo();
		
		$this->deleteBookPost();
		
		if( $this->book_info != null ) {
			$this->book_info->delete();			
			$this->book_info = null;			
		}
		
		if( $this->gameBook != null ) {
			$this->gameBook->delete();			
			$this->gameBook = null;			
		}
		
		if( count($this->authors) > 0 ) 
			$this->readAuthors();
		foreach( $this->authors as $i => $a ) {
			$this->authors[$i]->delete();			
		}
		$this->authors = array();
		
		if( count($this->editors) > 0 ) 
			$this->readEditors();
		foreach( $this->editors as $i => $a ) {
			$this->editors[$i]->delete();			
		}
		$this->editors = array();
		
		if( count($this->readers) > 0 ) 
			$this->readReaders();
		foreach( $this->readers as $i => $a ) {
			$this->readers[$i]->delete();			
		}
		$this->readers = array();
		
		if( count($this->readersp) > 0 ) 
			$this->readReadersPremium();
		foreach( $this->readersp as $i => $a ) {
			$this->readersp[$i]->delete();			
		}
		$this->readersp = array();
		
		
		// delete metadatas
		WH_DB_Metadata::deleteDB_ObjectMetadatas(WH_DB_Book::meta_obj_book, $this->id);
		
		// delete object
		return $this->deleteDB_Book();
	}
	
	/* Read DB */
	private function getDB_Book($id) {
		global $wpdb;
		
		$result = $wpdb->get_row(sprintf(WH_DB_Book::selectReq, $wpdb->prefix, $id), ARRAY_A);
		
		if( $result ) {
			$this->id               = $id;
			$this->title            = $result['title'];
			$this->resume           = $result['resume'];
			$this->type             = $result['type'];
			$this->cover            = $result['cover'];
			$this->banner           = $result['banner'];
			$this->status           = $result['status'];
			$this->creation_date    = $result['creation_date'];
			$this->publication_date = $result['publication_date'];
			$this->sale_url         = $result['sale_url'];
			$this->promo_url        = $result['promo_url'];
			$this->opinion_url      = $result['opinion_url'];
			$this->isbn             = $result['isbn'];
			$this->bookworld_id     = $result['bookworld_id'] ;
			$this->storyboard_id    = $result['storyboard_id'];
			$this->book_url         = $result['book_url'];
			$this->media_id         = $result['media_id'];
			$this->isOk = true;
		} else {
			wtr_error_log(__METHOD__, "<".$wpdb->last_query."> : ".$wpdb->last_error);
			$this->isOk = false;
		}
		return $this->isOk;
	}
	
	/* Insert into DB */
	private function insertDB_Book() {
		global $wpdb;
		$ret = false;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Book::tableName, 
								"insert",
		                        array('title'            => $this->title, 
		                              'resume'           => $this->resume,
		                              'type'             => $this->type, 
		                              'cover'            => $this->cover, 
		                              'banner'           => $this->banner, 
		                              'status'           => $this->status, 
		                              'creation_date'    => $this->creation_date, 
		                              'publication_date' => $this->publication_date, 
		                              'sale_url'         => $this->sale_url, 
		                              'promo_url'        => $this->promo_url, 
		                              'opinion_url'      => $this->opinion_url, 
		                              'isbn'             => $this->isbn, 
		                              'bookworld_id'     => $this->bookworld_id, 
		                              'storyboard_id'    => $this->storyboard_id, 
		                              'book_url'         => $this->book_url,
		                              'media_id'         => $this->media_id) );
		if( $result !== false ) {
			$this->id = $wpdb->insert_id;
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->id, 
												'action'      => "insert", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
			$ret = true;
		} else {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->id, 
												'action'      => "insert", 
												'comment'     => $comment));
		}
		return $ret;
	}
	
	/* Update DB */
	private function updateDB_Book() {
		global $wpdb;
		$ret = true;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Book::tableName, 
								"update",
								array('title'            => $this->title, 
		                              'resume'           => $this->resume, 
		                              'type'             => $this->type, 
		                              'cover'            => $this->cover, 
		                              'banner'           => $this->banner, 
		                              'status'           => $this->status, 
		                              'creation_date'    => $this->creation_date, 
		                              'publication_date' => $this->publication_date, 
		                              'sale_url'         => $this->sale_url, 
		                              'promo_url'        => $this->promo_url, 
		                              'opinion_url'      => $this->opinion_url, 
		                              'isbn'             => $this->isbn, 
		                              'bookworld_id'     => $this->bookworld_id, 
		                              'storyboard_id'    => $this->storyboard_id, 
		                              'book_url'         => $this->book_url,
		                              'media_id'         => $this->media_id),
								array('id' => $this->id) );
		if( $result === false ) {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->id, 
												'action'      => "update", 
												'comment'     => $comment));
			$ret = false;
		} else {
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->id, 
												'action'      => "update", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		}
		return $ret;
	}
	
	/* Delete DB */
	private function deleteDB_Book() {
		global $wpdb;
		$ret = true;
		$result = wtr_deleteRow($wpdb->prefix . WH_DB_Book::tableName, array('id' => $this->id) );
		if( $result === false ) {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->id, 
												'action'      => "delete", 
												'comment'     => $comment));
			$ret = false;
		} else {
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->id, 
												'action'      => "delete", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		}
		return $ret;
	}


	
	/* Read book's info */
	public function readBookInfo() {
		
		if( ! is_numeric($this->id) )
			return array();
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Book::meta_obj_book,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Book::meta_key_info
						));

		$this->book_info       = null;
		$this->book_info_array = array();
		if( isset($result[0]) ) {
			
			$this->book_info       = new WH_DB_Metadata($result[0]->meta_id);
			$this->book_info_array = json_decode($result[0]->meta_value, true);
			
		} else {
			$this->book_info       = new WH_DB_Metadata(0, array(
											'meta_obj' => WH_DB_Book::meta_obj_book,
											'obj_id'   => $this->id,
											'meta_key' => WH_DB_Book::meta_key_info
										));
			$this->book_info_array = array('freeChapter'      => -1,
										   'seePublishedBook' => WTRH_BACCESS_ALL,
										   'seeHidden'        => WTRH_BACCESS_NONE,
										   'seePreview'       => WTRH_BACCESS_NONE,
										   'seeBookworld'     => WTRH_BACCESS_NONE
										);
			$this->book_info->meta_value = json_encode($this->book_info_array);
		}
	}
	
	/* Read book's game info */
	public function readGameBookInfo($create = false) {
		
		if( ! is_numeric($this->id) )
			return array();
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Book::meta_obj_book,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Book::meta_key_game
						));

		$this->isGameBook     = false;
		$this->gameBook       = null;
		$this->gameBook_array = array();
		if( isset($result[0]) ) {
			
			$this->isGameBook     = true;
			$this->gameBook       = new WH_DB_Metadata($result[0]->meta_id);
			$this->gameBook_array = json_decode($result[0]->meta_value, true);
			
		} else {
			if( $create ) {
				$this->isGameBook     = true;
				$this->gameBook       = new WH_DB_Metadata(0, array(
												'meta_obj' => WH_DB_Book::meta_obj_book,
												'obj_id'   => $this->id,
												'meta_key' => WH_DB_Book::meta_key_game
											));
				$this->gameBook_array = array();
				$this->gameBook->meta_value = json_encode($this->gameBook_array);
			}
		}
	}
	
	
	/* Read book's authors */
	public function readAuthors() {
		
		if( ! is_numeric($this->id) )
			return array();
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Book::meta_obj_book,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Book::meta_key_author
						));

		$this->authors       = array();
		$this->authors_array = array();
		foreach($result as $meta ) {
			
			$this->authors[]       = new WH_DB_Metadata($meta->meta_id);
			$this->authors_array[] = array('id'   => $meta->user_id,
										   'name' => $meta->meta_value);
		}
	}
	
	/* Read book's editors */
	public function readEditors() {
		
		if( ! is_numeric($this->id) )
			return array();
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Book::meta_obj_book,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Book::meta_key_editor
						));

		$this->editors       = array();
		$this->editors_array = array();
		foreach($result as $meta ) {
			
			$this->editors[]       = new WH_DB_Metadata($meta->meta_id);
			$this->editors_array[] = array('id'   => $meta->user_id,
										   'name' => $meta->meta_value);
		}
	}
	
	/* Read book's readers */
	public function readReaders() {
		
		if( ! is_numeric($this->id) )
			return array();
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Book::meta_obj_book,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Book::meta_key_reader
						));

		$this->readers       = array();
		$this->readers_array = array();
		foreach($result as $meta ) {
			
			$this->readers[]       = new WH_DB_Metadata($meta->meta_id);
			$this->readers_array[] = array('id'   => $meta->user_id,
										   'meta' => json_decode($meta->meta_value, true));
		}
	}
	
	/* Read book's readersp */
	public function readReadersPremium() {
		
		if( ! is_numeric($this->id) )
			return array();
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Book::meta_obj_book,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Book::meta_key_readerp
						));

		$this->readersp       = array();
		$this->readersp_array = array();
		foreach($result as $meta ) {
			
			$this->readersp[]       = new WH_DB_Metadata($meta->meta_id);
			$this->readersp_array[] = array('id'   => $meta->user_id,
											'meta' => json_decode($meta->meta_value, true));
		}
	}
	
	
	/* Add book's authors */
	public function addAuthor($user_id, $user_name) {
		
		if( ! is_numeric($user_id) )
			return false;
		if( $user_id == 0 )
			return false;
		if( strlen(trim($user_name)) == 0 )
			return false;
		
		if( count($this->authors) == 0 )
			$this->readAuthors();
		
		$found = false;
		foreach( $this->authors_array as $i => $a )
			if( $a['id'] == $user_id ) {
				$found = true;
				break;
			}
		
		if( ! $found ) {
			$meta = new WH_Metadata(0, 
						array(
							'meta_obj'   => WH_DB_Book::meta_obj_book,
							'obj_id'     => $this->id,
							'meta_key'   => WH_DB_Book::meta_key_author,
							'meta_value' => $user_name,
							'user_id'    => $user_id
						));
			$meta->save();
			
			$this->authors[]       = $meta;
			$this->authors_array[] = array('id'   => $meta->user_id,
										   'name' => $meta->meta_value);
		}else {
			$this->authors_array[$i]['name'] = $user_name;
		}
		
		return true;
	}
	
	/* Add book's editors */
	public function addEditor($user_id, $user_name) {
		
		if( ! is_numeric($user_id) )
			return false;
		if( $user_id == 0 )
			return false;
		if( strlen(trim($user_name)) == 0 )
			return false;
		
		if( count($this->editors) == 0 )
			$this->readEditors();
		
		$found = false;
		foreach( $this->editors_array as $i => $a )
			if( $a['id'] == $user_id ) {
				$found = true;
				break;
			}
		
		if( ! $found ) {
			$meta = new WH_Metadata(0, 
						array(
							'meta_obj'   => WH_DB_Book::meta_obj_book,
							'obj_id'     => $this->id,
							'meta_key'   => WH_DB_Book::meta_key_editor,
							'meta_value' => $user_name,
							'user_id'    => $user_id
						));
			$meta->save();
			
			$this->editors[]       = $meta;
			$this->editors_array[] = array('id'   => $meta->user_id,
										   'name' => $meta->meta_value);
		}else {
			$this->editors_array[$i]['name'] = $user_name;
		}
		
		return true;
	}
	
	/* Add book's readers */
	public function addReaders($user_id, $meta) {
		
		if( ! is_numeric($user_id) )
			return false;
		if( $user_id == 0 )
			return false;
		if( strlen(trim($user_name)) == 0 )
			return false;
		
		$found = false;
		foreach( $this->readers_array as $i => $a )
			if( $a['id'] == $user_id ) {
				$found = true;
				break;
			}
		
		if( ! $found ) {
			$meta = new WH_Metadata(0, 
						array(
							'meta_obj'   => WH_DB_Book::meta_obj_book,
							'obj_id'     => $this->id,
							'meta_key'   => WH_DB_Book::meta_key_reader,
							'meta_value' => $meta,
							'user_id'    => $user_id
						));
			$meta->save();
			
			$this->readers[]       = $meta;
			$this->readers_array[] = array('id'   => $meta->user_id,
										   'meta' => $meta->meta_value);
		}else {
			$this->readers_array[$i]['meta'] = $meta;
		}
		
		return true;
	}
	
	/* Add book's readersp */
	public function addReadersPremium($user_id, $meta) {
		
		if( ! is_numeric($user_id) )
			return false;
		if( $user_id == 0 )
			return false;
		if( strlen(trim($user_name)) == 0 )
			return false;
		
		$found = false;
		foreach( $this->readersp_array as $i => $a )
			if( $a['id'] == $user_id ) {
				$found = true;
				break;
			}
		
		if( ! $found ) {
			$meta = new WH_Metadata(0, 
						array(
							'meta_obj'   => WH_DB_Book::meta_obj_book,
							'obj_id'     => $this->id,
							'meta_key'   => WH_DB_Book::meta_key_readerp,
							'meta_value' => $meta,
							'user_id'    => $user_id
						));
			$meta->save();
			
			$this->readersp[]       = $meta;
			$this->readersp_array[] = array('id'   => $meta->user_id,
										    'meta' => $meta->meta_value);
		} else {
			$this->readersp_array[$i]['meta'] = $meta;
		}
		
		return true;
	}
	


	
	/* Read book's post */
	public function readBookPost() {
		
		if( ! is_numeric($this->id) )
			return false;
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Book::meta_obj_book,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Book::meta_key_post
						));

		$this->book_post       = null;
		$this->book_post_obj   = null;
		if( isset($result[0]) ) {
			
			$this->book_post = new WH_DB_Metadata($result[0]->meta_id);
			
			$post_id = intval($result[0]->meta_value); 
			if( $post_id != 0 )
				$this->book_post_obj = get_post($post_id);
			
		} 
	}
	
	/* Delete book's post */
	public function deleteBookPost() {
		$ret = true;
		
		// Delete Post object
		if( $ret && 
		    $this->book_post_obj != null && 
		    get_class($this->book_post_obj) == "WP_Post" &&
			$this->book_post_obj->ID != 0 ) {
			$pret = wp_delete_post($this->book_post_obj->ID, true);	

			// If deletion ko
			if( $pret === false || $pret == null ) {
				$ret = false;
				wtr_error_log(__METHOD__, 'Error while deleting WP_Post (id: '.$this->book_post_obj->ID.')');
			} else
				$this->book_post_obj = null;
		} 
		
		// Delete WH_Metadata
		if( $ret && $this->book_post != null ) {
			$ret = $this->book_post->delete();
			$this->book_post = null;			

			if( ! $ret )
				wtr_error_log(__METHOD__, 'Error while deleting WH_Metadata (id: '.$this->book_post->meta_id.')');
		} 
		
		return $ret;
	}
	
	



	
	/* Get All DB */
	public static function getAllDB_Books($status_array = array(),
										  $type = "",
										  $col = "id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Book::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		if( trim($type) != "" ) {
			$where .= "type='%s'";
			$args[] = $type;
		} 
		if( is_array($status_array) && count($status_array) > 0 ) {
			if( count($args) > 0 )
				$where .= " AND ";
			$where .= "status IN (".implode(',', $status_array).")";
		} else
			if( count($args) == 0 )
				$where = "";
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}

	
	/* Get a specified set of Books' info from an array of ids */
	public static function getAllDB_BooksInfoByIds($ids_array, $cols_array = array(),
										  $col = "id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$cols     = (count($cols_array)>0)?implode(",",$cols_array):WH_DB_Book::tableCols;
		$query    = "SELECT ".$cols." FROM ".$wpdb->prefix.WH_DB_Book::tableName;
		$where    = " WHERE id in (".implode(",", $ids_array).")";
		$order_by = " ORDER BY ".$col." ".$direction;
		$result = wtr_getResults($query.$where.$order_by);

		return $result;
	}
	
	
	/* Get all Game Books */
	public static function getAllDB_GameBooks($cols_array = array(),
										  $col = "id", $direction = "asc") {
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Book::meta_obj_book,
							'meta_key' => WH_DB_Book::meta_key_game
						));
		
		$books_id = array();
		foreach($result as $meta ) {
			$books_id[] = $meta->obj_id;
		}
		return WH_DB_Book::getAllDB_BooksInfoByIds($books_id, $cols_array, $col, $direction);
	}
	
	/* Get All DB Book's users */
	public static function getAllDB_BookUsersId($book_id, $roles = array()) {
		global $wpdb;
		
		if( ! is_numeric($book_id) )
			return array();
		if( $book_id == 0 )
			return array();

		$result   = false;
		$query = "SELECT user_id ".
	             " FROM ".$wpdb->prefix.WH_DB_Metadata::tableName;
		$where = " WHERE meta_obj='".WH_DB_Book::meta_obj_book."'".
				 "   AND obj_id=".$book_id.
				 "   AND user_id<>0";
		$order_by = " ORDER BY 1 asc";
		
		if( is_array($roles) && count($roles) > 0 ) {
			$roles_array = array();
			foreach( $roles as $r ) {
				if( $r == WTRH_ROLE_AUTHOR ||
				    $r == WTRH_ROLE_EDITOR ||
				    $r == WTRH_ROLE_READER ||
				    $r == WTRH_ROLE_READERP )
					$roles_array[] = $r;
			}
			
			if( count($roles_array) > 1 ) {
				if( count($roles_array) == 1 )
					$where .= " AND meta_key='".$roles_array[0]."'";
				else {
					$where .= " AND meta_key in (";
					foreach( $roles_array as $i => $r ) {
						if( $i > 0 )
							$where .= ",";
						$where .= "'".$r."'";
					}
					$where .= ")";
				}
			}
		}
		
		$result = wtr_getResults($query.$where.$order_by);

		return $result;
	}
	
	
	/* Get All DB from user */
	public static function getAllDB_BooksForUser($user_id, $roles = array(), 
										  $status_array = array(), 
										  $col = "id", $direction = "asc") {
		global $wpdb;
		global $wtr_roles;
		$result = false;
		
		if( ! is_numeric($user_id) )
			return array();
		if( $user_id == 0 )
			return array();
		
		$query = "SELECT ".WH_DB_Book::tableCols.", meta_key".
	             " FROM ".$wpdb->prefix.WH_DB_Book::tableName." a,".
	                  " ".$wpdb->prefix.WH_DB_Metadata::tableName." b";
		$where = " WHERE a.id=obj_id ".
				 "   AND meta_obj='".WH_DB_Book::meta_obj_book."'".
				 "   AND meta_key='".WH_DB_Book::meta_key_author."'".
				 "   AND user_id=".$user_id;
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		// add statuses
		if( is_array($status_array) && count($status_array) > 0 ) {
			$where .= " AND ";
			$where .= "status IN (";
			foreach($status_array as $i => $st )  {
				if( WH_Status::existsStatus($st) ) {
					if( $i > 0 )
						$where .= ",";
					$where .= $st;
				}
			}
			$where .= ")";
		} 
		// add user roles
		if( is_array($roles) && count($roles) > 0 ) {
			$where .= " AND ";
			$where .= "meta_key IN (";
			foreach($roles as $i => $role )  {
				if( in_array($role, $wtr_roles) ) {
					if( $i > 0 )
						$where .= ",";
					$where .= "'".$role."'";
				}
			}
			$where .=")";
		} 
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/* Get Book by chapter id */
	public static function getBookByChapter($chapter_id) {
		global $wpdb;
		$result = false;
		$query  = sprintf(WH_DB_Book::selectBbyCReq, $wpdb->prefix, $chapter_id);
		
		$result = wtr_getRow($query, null, ARRAY_A);

		return $result;
	}
	
	/* Get Book by scene id */
	public static function getBookByScene($scene_id) {
		global $wpdb;
		$result = false;
		$query  = sprintf(WH_DB_Book::selectBbySReq, $wpdb->prefix, $scene_id);
		
		$result = wtr_getRow($query, null, ARRAY_A);

		return $result;
	}
	
	/* Get All published books */
	public static function getAllDB_LibraryBooks($col = "id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Book::selectBaseReq, $wpdb->prefix)." a";
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		$where .= " status in (".implode(',', WH_Status::getPublishStatuses()).")";
		$where .= " OR exists (SELECT id FROM ".$wpdb->prefix.WH_DB_Chapter::tableName.
				" WHERE book_id = a.id AND status in (".implode(',', WH_Status::getPublishStatuses())."))";
	
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
		
	/* Get latest published books */
	/*   nb : number of books to return  */
	public static function getDB_LatestPublishedBooks($nb = "5") {
		global $wpdb;
		
		if( ! is_numeric($nb) )
			$nb = 5;
		
		$result   = false;
		$query    = sprintf(WH_DB_Book::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE status = ".WH_Status::PUBLISHED;
		$order_by = " ORDER BY publication_date desc, creation_date desc";
		$limit    = "";
		if( $nb > 0 ) 
			$limit = " LIMIT ".$nb;
		
		$result = wtr_getResults($query.$where.$order_by.$limit);

		return $result;
	}

	/* Get Bookworld Books */
	public static function getAllDB_BookworldBooks($bookworld_id, $col = "id", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Book::selectBbyBReq, $wpdb->prefix,$bookworld_id);
		$order_by = " ORDER BY ".$col." ".$direction;
		
		$result = wtr_getResults($query.$order_by);

		return $result;
	}


}
?>