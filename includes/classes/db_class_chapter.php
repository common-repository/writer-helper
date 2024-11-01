<?php
/************************************
 **         Chapter class         **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_activity.php");

class WH_DB_Chapter {
	const meta_obj = "Chapter";
	const meta_key_post = "ChapterPost";

	const tableName = "wtr_chapter";
	const createReq = "CREATE TABLE IF NOT EXISTS `%s".WH_DB_Chapter::tableName."` (".
	       "`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,".
	       "`title` text NOT NULL,".
	       "`number` int(3) NOT NULL,".
	       "`status` int(2) NOT NULL DEFAULT 0,".
	       "`show_number` boolean NOT NULL DEFAULT true,".
	       "`show_title` boolean NOT NULL DEFAULT false,".
	       "`publication_date` date DEFAULT '0000-00-00',".
	       "`book_id` bigint(20) unsigned NOT NULL);";

    const tableCols= 'id, title, number, status, show_number, show_title, publication_date, book_id';
    const selectBaseReq= "SELECT ".WH_DB_Chapter::tableCols.
	                          " FROM %s".WH_DB_Chapter::tableName;
    const selectReq= "SELECT ".WH_DB_Chapter::tableCols.
	                          " FROM %s".WH_DB_Chapter::tableName.
							  " WHERE id=%s";
    const selectMNBReq= "SELECT max(number)".
	                          " FROM %1\$s".WH_DB_Chapter::tableName.
							  " WHERE book_id=%2\$s";
	
	public $id;
	public $title;
	public $number;
	public $status;
	public $show_number;
	public $show_title;
	public $publication_date;
	public $book_id;
	
	public $chapter_post;     /* WH_Metadata */
	public $chapter_post_obj; /* WP_Post */
	
	public $isOk;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'title' => string
	*			'number' => int
	*			'status' => int
	*			'show_number' => boolean
	*			'show_title' => boolean
	*			'publication_date' => date
	*			'book_id' => int
	**/
    public function __construct($id, $args = array())    {
		$this->id               = isset($args['id'])         ? (int)$args['id']: (int)$id;
		$this->title            = isset($args['title'])      ? (string)$args['title']:'';
		$this->number           = isset($args['number'])     ? (int)$args['number']:0;
		$this->status           = isset($args['status'])     ? (int)$args['status']:WH_Status::DRAFT;
		$this->show_number      = isset($args['show_number'])? (boolean)$args['show_number']:true;
		$this->show_title       = isset($args['show_title']) ? (boolean)$args['show_title']:false;
		$this->publication_date = isset($args['publication_date'])? (string)$args['publication_date']:"0000-00-00";
		$this->book_id          = isset($args['book_id'])    ? (int)$args['book_id']:0;
		
		$this->chapter_post = null;
		$this->isOk = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
		} else {
			if( $id != 0 )
				$this->isOk = $this->getDB_Chapter($id);
			else {
				$this->isOk = true;
				
				// if chapter number = 0, get next chapter number
				if( $this->number == 0 )
					$this->number = WH_DB_Chapter::getDB_NumberMax($this->book_id)+1;
			}
				
			$this->readChapterPost();
		}
	}

	/* Update DB */
	public function save() {
		$result = false;
		
		if( $this->id == 0 ) { $result = $this->insertDB_Chapter(); }
		else {                 $result = $this->updateDB_Chapter();	}
		
		
		// update metadata
		if( $this->chapter_post != null ) {
			$this->chapter_post->meta_value = $this->chapter_post_obj->ID;
			$this->chapter_post->save();
		} else {
			// create metadata
			if( $this->chapter_post_obj != null ) {
				$this->chapter_post = new WH_DB_Metadata(0, array(
											'meta_obj' => WH_DB_Chapter::meta_obj,
											'obj_id'   => $this->id,
											'meta_key' => WH_DB_Chapter::meta_key_post
										));
				$this->chapter_post->meta_value = $this->chapter_post_obj->ID;
				$this->chapter_post->save();
			}
		}
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		
		if( $this->id == 0 )
			return true;
		
		// Delete chapter post
		if( ! $this->deleteChapterPost() )
			return false;
		
		// delete metadatas
		WH_DB_Metadata::deleteDB_ObjectMetadatas(WH_DB_Chapter::meta_obj, $this->id);
		
		return $this->deleteDB_Chapter();
	}
	
	/* Read DB */
	private function getDB_Chapter($id) {
		global $wpdb;
		
		$result = $wpdb->get_row(sprintf(WH_DB_Chapter::selectReq, $wpdb->prefix, $id), ARRAY_A);
		
		if( $result ) {
			$this->id               = $id;
			$this->title            = $result['title'];
			$this->number           = $result['number'];
			$this->status           = $result['status'];
			$this->show_number      = $result['show_number'];
			$this->show_title       = $result['show_title'];
			$this->publication_date = $result['publication_date'];
			$this->book_id          = $result['book_id'];
			return true;
		} else {
			wtr_error_log(__METHOD__, "<".$wpdb->last_query."> : ".$wpdb->last_error);
		}
		return false;
	}
	
	/* Insert into DB */
	private function insertDB_Chapter() {
		global $wpdb;
		$ret = false;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Chapter::tableName, 
								"insert",
		                        array('title'       => $this->title, 
		                              'number'      => $this->number, 
		                              'status'      => $this->status, 
		                              'show_number' => $this->show_number, 
		                              'show_title'  => $this->show_title, 
		                              'book_id'     => $this->book_id) );
		if( $result !== false ) {
			$this->id = $wpdb->insert_id;
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->book_id, 
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
												'book_id'     => $this->book_id, 
												'action'      => "insert", 
												'comment'     => $comment));
		}
		return $ret;
	}
	
	/* Update DB */
	private function updateDB_Chapter() {
		global $wpdb;
		$ret = true;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Chapter::tableName, 
								"update",
								array('title'       => $this->title, 
		                              'number'      => $this->number, 
		                              'status'      => $this->status, 
		                              'show_number' => $this->show_number, 
		                              'show_title'  => $this->show_title, 
		                              'publication_date' => $this->publication_date, 
		                              'book_id'     => $this->book_id),
								array('id' => $this->id) );
		if( $result === false ) {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->book_id, 
												'action'      => "update", 
												'comment'     => $comment));
			$ret = false;
		} else {
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->book_id, 
												'action'      => "update", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		}
		return $ret;
	}
	
	/* Delete DB */
	private function deleteDB_Chapter() {
		global $wpdb;
		$ret = true;
		$result = wtr_deleteRow($wpdb->prefix . WH_DB_Chapter::tableName, array('id' => $this->id) );
		if( $result === false ) {
			$comment = array("type"     => "error",
							  "data"     => get_object_vars($this),
							  "request"  => $wpdb->last_query,
							  "msg"      => $wpdb->last_error);
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->book_id, 
												'action'      => "delete", 
												'comment'     => $comment));
			$ret = false;
		} else {
			WH_DB_Activity::addActivity(array(  'element'     => wtr_get_class($this),
												'element_id'  => $this->id, 
												'book_id'     => $this->book_id, 
												'action'      => "delete", 
												'comment'     => print_r($this,true), 
												'action_seen' => true));
		}
		return $ret;
	}
	

	
	/* Read chapter's post */
	public function readChapterPost() {
		
		if( ! is_numeric($this->id) )
			return false;
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Chapter::meta_obj,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Chapter::meta_key_post
						));

		$this->chapter_post     = null;
		$this->chapter_post_obj = null;
		
		if( isset($result[0]) ) {
			
			$this->chapter_post = new WH_DB_Metadata($result[0]->meta_id);
			
			$post_id = intval($result[0]->meta_value); 
			if( $post_id != 0 )
				$this->chapter_post_obj = get_post($post_id);
			
		} 
	}
	
	/* Delete chapter's post */
	public function deleteChapterPost() {
		$ret = true;
		
		// Delete Chapter object
		if( $ret && 
		    $this->chapter_post_obj != null && 
		    get_class($this->chapter_post_obj) == "WP_Post"&& 
		    $this->chapter_post_obj->ID != 0 ) {
			$pret = wp_delete_post($this->chapter_post_obj->ID, true);	
			// If deletion ko
			if( $pret === false || $pret == null ) {
				$ret = false;
				wtr_error_log(__METHOD__, 'Error while deleting WP_Post (id: '.$this->chapter_post_obj->ID.')');
			} else
				$this->chapter_post_obj = null;
		} 
		
		// Delete WH_Metadata
		if( $ret && $this->chapter_post != null ) {
			$ret = $this->chapter_post->delete();
			$this->chapter_post     = null;	

			if( ! $ret )
				wtr_error_log(__METHOD__, 'Error while deleting WH_Metadata (id: '.$this->chapter_post->meta_id.')');
		} 
		
		return $ret;
	}

	
	
	/* Get All DB */
	public static function getAllDB_Chapters($book_id, $status_array = array(), 
											 $col = "number", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Chapter::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		if( $book_id != 0 ) {
			$where .= "book_id=%d";
			$args[] = $book_id;
		}
		
		if( is_array($status_array) && count($status_array) > 0 ) {
			if( count($args) > 0 )
				$where .= " AND ";
			$where .= "status in (".implode(',', $status_array).")";
		} else
			if( count($args) == 0 )
				$where = "";

		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/* Get Latest published chapters */
	/*   nb : number of chapters to return  */
	public static function getDB_LatestPublishedChapters($nb = "5") {
		global $wpdb;
		
		if( ! is_numeric($nb) )
			$nb = 5;
		
		$result   = false;
		$query    = sprintf(WH_DB_Chapter::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE status = ".WH_Status::PUBLISHED;
		$order_by = " ORDER BY publication_date desc";
		$limit    = "";
		if( $nb > 0 ) 
			$limit = " LIMIT ".$nb;
		
		$result = wtr_getResults($query.$where.$order_by.$limit);

		return $result;
	}
	
	
	
	/* Get book id from a chapter */
	public static function getDB_BookId($chapter_id) {
		global $wpdb;
		$result = $wpdb->get_row(sprintf(WH_DB_Chapter::selectReq, $wpdb->prefix, $chapter_id), ARRAY_A);
		return $result['book_id'];
	}
	
	/* Get max number chapter of a book */
	public static function getDB_NumberMax($book_id) {
		global $wpdb;
		$nb = 0;
		$nb = intval($wpdb->get_var(sprintf(WH_DB_Chapter::selectMNBReq, $wpdb->prefix, $book_id)));
		return $nb;
	}
}
?>