<?php
/************************************
 **         Scene class         **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_activity.php");

class WH_DB_Scene {
	const meta_obj = "Scene";
	const meta_key_editText = "EditingText";
	const meta_key_game     = "GameBook";
	
	const tableName = "wtr_scene";
	const createReq = "CREATE TABLE IF NOT EXISTS `%s".WH_DB_Scene::tableName."` (".
	       "`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,".
	       "`number` int(5) NOT NULL,".
	       "`description` text NOT NULL,".
	       "`text` longtext NOT NULL,".
	       "`word_count` bigint(20) NOT NULL,".
	       "`status` int(2) NOT NULL DEFAULT 0,".
	       "`chapter_id` bigint(20) unsigned NOT NULL);";

	const tableCols= 'id, number, description, text, word_count, status, chapter_id';
    const selectBaseReq= "SELECT ".WH_DB_Scene::tableCols.
	                          " FROM %s".WH_DB_Scene::tableName;
    const selectMNCReq= "SELECT max(number)".
	                          " FROM %1\$s".WH_DB_Scene::tableName.
							  " WHERE chapter_id IN (SELECT id FROM".
	                          "    %1\$s".WH_DB_Chapter::tableName.
							  "    WHERE book_id= (SELECT book_id FROM".
							  "    %1\$s".WH_DB_Chapter::tableName.
							  "    WHERE id=%2\$s))";
    const selectMNBReq= "SELECT max(number)".
	                          " FROM %1\$s".WH_DB_Scene::tableName.
							  " WHERE chapter_id IN (SELECT id FROM".
	                          "    %1\$s".WH_DB_Chapter::tableName.
							  "    WHERE book_id= %2\$s)";
    const selectWCCReq= "SELECT sum(word_count)".
	                          " FROM %s".WH_DB_Scene::tableName.
							  " WHERE chapter_id=%s";
    const selectWCBReq= "SELECT sum(word_count)".
	                          " FROM %1\$s".WH_DB_Scene::tableName.
							  " WHERE chapter_id IN (SELECT id FROM".
	                          "    %1\$s".WH_DB_Chapter::tableName.
							  "    WHERE book_id=%2\$s)";
    const selectReq   = "SELECT ".WH_DB_Scene::tableCols.
	                          " FROM %s".WH_DB_Scene::tableName.
							  " WHERE id=%s";
    const selectBSReq = "SELECT ".WH_DB_Scene::tableCols.
	                          " FROM %1\$s".WH_DB_Scene::tableName.
							  " WHERE chapter_id IN (SELECT id FROM".
	                          "    %1\$s".WH_DB_Chapter::tableName.
							  "    WHERE book_id=%2\$s)";
	
	public $id;
	public $number;
	public $description;
	public $text;
	public $word_count;
	public $status;
	public $chapter_id;
	public $book_id;
	
	public $isOk;
	public $editingText_meta;      /* WH_Metadata */
	public $editing_text;     /* text */
	public $gameBook_meta;      /* WH_Metadata */
	public $gameBook;     /* array( array('scene_id','libelle') ) */
	public $isGameBook;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'number' => int
	*			'description' => string
	*			'text' => string
	*			'status' => int
	*			'chapter_id' => int
	**/
    public function __construct($id, $args = array())    {
		$this->id             = isset($args['id'])         ? (int)$args['id']:(int)$id;
		$this->number         = isset($args['number'])     ? (int)$args['number']:0;
		$this->description    = isset($args['description'])? (string)$args['description']:'';
		$this->text           = isset($args['text'])       ? (string)$args['text']:'';
		$this->word_count     = isset($args['word_count']) ? (int)$args['word_count']:wtr_word_count($this->text);
		$this->status         = isset($args['status'])     ? (int)$args['status']:WH_Status::DRAFT;
		$this->chapter_id     = isset($args['chapter_id']) ? (int)$args['chapter_id']:0;
		$this->book_id        = isset($args['book_id'])    ? (int)$args['book_id']:0;
		$this->editing_text   = isset($args['editing_text'])?(string)$args['editing_text']:"";
		$this->gameBook       = array();
		
		$this->isOk = false;
		$this->isGameBook = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
		} else {
			if( $id != 0 )
				$this->isOk = $this->getDB_Scene($id);
			else {
				$this->isOk = true;
				
				// if scene number = 0, get next scene number
				if( $this->number == 0 )
					$this->number = WH_DB_Scene::getDB_NumberMax(0, $this->chapter_id)+1;

			}
			
			if( $this->book_id > 0 ) {
				$book = new WH_Book($this->book_id);
				$this->isGameBook = $book->isGameBook;
			}
			$this->readEditingText();
			$this->readGameBook();
		}
	}

	/* Update DB */
	public function save() {
		$result = false;
		
		if( $this->id == 0 ) { $result = $this->insertDB_Scene(); }
		else {                 $result = $this->updateDB_Scene();	}
		
		// update metadata
		if( $this->editingText_meta != null ) {
			$this->editingText_meta->meta_value = $this->editing_text;
			$this->editingText_meta->save();
		} else {
			// create metadata
			if( $this->editing_text != "" ) {
				$this->editingText_meta = new WH_DB_Metadata(0, array(
											'meta_obj' => WH_DB_Scene::meta_obj,
											'obj_id'   => $this->id,
											'meta_key' => WH_DB_Scene::meta_key_editText
										));
				$this->editingText_meta->meta_value = $this->editing_text;
				$this->editingText_meta->save();
			}
		}
		
		// update metadata
		$this->saveGameBook();
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		
		if( $this->id == 0 )
			return true;

		// delete metadatas
		WH_DB_Metadata::deleteDB_ObjectMetadatas(WH_DB_Scene::meta_obj, $this->id);

		return $this->deleteDB_Scene();
	}
	
	/* Read DB */
	private function getDB_Scene($id) {
		global $wpdb;
		
		$result = $wpdb->get_row(sprintf(WH_DB_Scene::selectReq, $wpdb->prefix, $id), ARRAY_A);
		
		if( $result ) {
			$this->id             = $id;
			$this->number         = $result['number'];
			$this->description    = $result['description'];
			$this->text           = $result['text'];
			$this->word_count     = $result['word_count'];
			$this->status         = $result['status'];
			$this->chapter_id     = $result['chapter_id'];
			$this->book_id        = WH_DB_Chapter::getDB_BookId($result['chapter_id']);
			return true;
		} else {
			wtr_error_log(__METHOD__, "<".$wpdb->last_query."> : ".$wpdb->last_error);
		}
		return false;
	}
	
	/* Insert into DB */
	private function insertDB_Scene() {
		global $wpdb;
		$ret = false;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Scene::tableName, 
								"insert",
		                        array('number'      => $this->number, 
		                              'description' => $this->description,  
		                              'text'        => $this->text,  
		                              'word_count'  => $this->word_count,         
		                              'status'      => $this->status,
		                              'chapter_id'  => $this->chapter_id));
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
	private function updateDB_Scene() {
		global $wpdb;
		$ret = true;
		$result = wtr_setRow($wpdb->prefix . WH_DB_Scene::tableName, 
								"update",
								array('number'      => $this->number, 
		                              'description' => $this->description,  
		                              'text'        => $this->text,  
		                              'word_count'  => $this->word_count, 
		                              'status'      => $this->status,
		                              'chapter_id'  => $this->chapter_id),
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
	private function deleteDB_Scene() {
		global $wpdb;
		$ret = true;
		$result = wtr_deleteRow($wpdb->prefix . WH_DB_Scene::tableName, array('id' => $this->id) );
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
	
	
	/* Read editing text */
	public function readEditingText() {
		
		$this->editingText_meta = null;
		$this->editing_text     = "";
		
		if( ! is_numeric($this->id) )
			return false;
		if( $this->id == 0 )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Scene::meta_obj,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Scene::meta_key_editText
						));

		if( isset($result[0]) ) {			
			$this->editingText_meta = new WH_DB_Metadata($result[0]->meta_id);
			$this->editing_text     = $result[0]->meta_value;
		} 
	}

	
	/* Read game book's scenes */
	public function readGameBook() {
		
		$this->gameBook_meta = null;
		$this->gameBook      = array();
		
		// read book info
		if( $this->book_id == 0 ) {
			$this->book_id = WH_DB_Chapter::getDB_BookId($this->chapter_id);
			$book = new WH_Book($this->book_id);
			$this->isGameBook = $book->isGameBook;
		}
		
		if( ! is_numeric($this->id) )
			return false;
		if( $this->id == 0 )
			return false;
		if( ! $this->isGameBook )
			return false;
		
		$result = WH_DB_Metadata::getAllDB_Metadatas(
						array(
							'meta_obj' => WH_DB_Scene::meta_obj,
							'obj_id'   => $this->id,
							'meta_key' => WH_DB_Scene::meta_key_game
						));

		if( isset($result[0]) ) {			
			$this->gameBook_meta = new WH_DB_Metadata($result[0]->meta_id);
			$this->gameBook      = json_decode($result[0]->meta_value, true);
			if( ! is_array($this->gameBook) )
				$this->gameBook = array();
		} 
	}
	
	/* Delete editing text */
	public function deleteEditingText() {
		$ret = true;

		// Delete WH_Metadata
		if( $ret && $this->editingText_meta != null ) {
			$ret = $this->editingText_meta->delete();
			$this->editingText_meta = null;	
			$this->editing_text     = "";	

			if( ! $ret )
				wtr_error_log(__METHOD__, 'Error while deleting WH_Metadata (id: '.$this->editingText_meta->meta_id.')');
		} 
		
		return $ret;
	}
	
	/* Delete game book's scenes */
	public function deleteGameBook() {
		$ret = true;

		// Delete WH_Metadata
		if( $ret && $this->gameBook_meta != null ) {
			$ret = $this->gameBook_meta->delete();
			if( ! $ret )
				wtr_error_log(__METHOD__, 'Error while deleting WH_Metadata (id: '.$this->gameBook_meta->meta_id.')');
			else
				$this->gameBook_meta = null;	
			$this->gameBook      = array();	
		} 
		
		return $ret;
	}
	
	/* Save game book's scenes */
	public function saveGameBook() {
		$ret  = true;
		
		if( ! $this->isGameBook )
			return false;
		
		if( $this->gameBook_meta == null ) 
			$this->readGameBook();
			
		// create Metadata
		if( $this->gameBook_meta == null ) {
			$this->gameBook_meta = new WH_DB_Metadata(0, array(
										'meta_obj' => WH_DB_Scene::meta_obj,
										'obj_id'   => $this->id,
										'meta_key' => WH_DB_Scene::meta_key_game
									));
		}
		// update 
		if( $this->gameBook_meta != null ) {
			$this->gameBook_meta->meta_value = json_encode($this->gameBook);
			$ret = $this->gameBook_meta->save();
		} 
		
		return $ret;
	}

	
	/* Get All DB */
	public static function getAllDB_Scenes($chapter_id, $col = "number", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Scene::selectBaseReq, $wpdb->prefix);
		$where    = " WHERE ";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		if( $chapter_id != 0 ) {
			$where .= "chapter_id=%s";
			$args[] = $chapter_id;
		}
		
		if( count($args) == 0 )
			$where = "";
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/* Get All Scenes from a book */
	public static function getAllDB_BookScenes($book_id, $col = "number", $direction = "asc") {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Scene::selectBSReq, $wpdb->prefix, $book_id);
		$where    = "";
		$order_by = " ORDER BY ".$col." ".$direction;
		$args     = array();
		
		$result = wtr_getResults($query.$where.$order_by, $args);

		return $result;
	}
	
	/* Get max number scene of a book */
	public static function getDB_NumberMax($book_id, $chapter_id = 0) {
		global $wpdb;
		$nb = 0;
		
		if( $book_id != 0 )
			$nb = intval($wpdb->get_var(sprintf(WH_DB_Scene::selectMNBReq, $wpdb->prefix, $book_id)));
		else
			$nb = intval($wpdb->get_var(sprintf(WH_DB_Scene::selectMNCReq, $wpdb->prefix, $chapter_id)));
		
		return $nb;
	}
	
	/* Get word count of a book or a chapter */
	public static function getDB_WordCount($book_id, $chapter_id = 0) {
		global $wpdb;
		$nb = 0;
		
		if( $book_id != 0 )
			$nb = intval($wpdb->get_var(sprintf(WH_DB_Scene::selectWCBReq, 
												$wpdb->prefix, $book_id)));
		else
			$nb = intval($wpdb->get_var(sprintf(WH_DB_Scene::selectWCCReq, 
												$wpdb->prefix, $chapter_id)));
		
		return $nb;
	}
}
?>