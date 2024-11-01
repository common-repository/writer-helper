<?php
/************************************
 **         Scene class         **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_scene.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_chapter.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_element.php");

class WH_Scene extends WH_Element {
	
//already in WH_Element	public $id;
	public $number;
	public $description;
	public $text;
	public $word_count;
//already in WH_Element	public $status;
	public $chapter_id;
	public $book_id;
	public $chapter;		/* object */
	public $editing_text;
	public $gameBook;		/* array */
	public $isGameBook;		/* bool */
	
	public $isOk;       	/* init ok ? */
	
	private $DB_Scene;		/* object */
	
	/**
	* Class constructor 
	*  $args : array()
	*			'number' => int
	*			'description' => string
	*			'text' => string
	*			'status' => int
	*			'chapter_id' => int
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id          = isset($args['id'])         ? (int)$args['id']: (int)$id;
		$this->number      = isset($args['number'])     ? (int)$args['number']:0;
		$this->description = isset($args['description'])? (string)$args['description']:'';
		$this->text        = isset($args['text'])       ? (string)$args['text']:'';
		$this->word_count  = isset($args['word_count']) ? (int)$args['word_count']:0;
		$this->status      = isset($args['status'])     ? (int)$args['status']:WH_Status::DRAFT;
		$this->chapter_id  = isset($args['chapter_id']) ? (int)$args['chapter_id']:0;
		$this->book_id     = isset($args['book_id'])    ? (int)$args['book_id']:0;
		
		$this->isGameBook  = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
			return false;
		} else {
			if( $id != 0 )
				$this->get_Scene($id);
			else {
				$this->DB_Scene = new WH_DB_Scene(0, $args);
				if( $this->number == 0 )
					$this->number = $this->DB_Scene->number;
				
				$this->isOk = $this->DB_Scene->isOk;
			}
			
			$this->word_count = wtr_word_count($this->text);
			$this->get_GameBook();
			if( $cascade ) {
				$this->chapter       = new WH_Chapter($this->chapter_id);
			}
		}
	}	

	
	/* Get publication date with formatting */
	public function get_PublicationDate($dateFormat = "") {
		$str = "";
		return $str;
	}
	
	/* Set publication date with formatting */
	public function set_PublicationDate($date = "") {
		$ret = false;
		return $ret;
	}
	/* Reset publication date */
	public function reset_PublicationDate() {}

	/* replace text */
	public function setText($text) {
		$this->text       = $text;
		$this->word_count = wtr_word_count($text);
	}

	/* get text */
	public function get_Text($format = 'html' ) {
		$str = $this->text;
		
		switch( $format ) {
			case 'text' : $str = html_entity_decode($str,ENT_QUOTES); break;
			case 'html' : $str = str_replace("\n","<br>",$str);
			default:
						  break;
		}
		
		return $str;
	}

	/* get editing text */
	public function get_EditingText($format = 'html' ) {
		$str = $this->editing_text;
		
		switch( $format ) {
			case 'text' : $str = html_entity_decode($str,ENT_QUOTES); break;
			case 'html' :
			default:
						  break;
		}
		
		return $str;
	}


	/* return linked scenes */
	public function get_GameBook() {

		$this->DB_Scene->readGameBook();
		
		$this->gameBook   = $this->DB_Scene->gameBook;
		$this->isGameBook = $this->DB_Scene->isGameBook;
		
		return $this->gameBook;
	}

	/* add a link to a book's scene */
	public function add_GameBookScene($index, $scene_id, $libelle ) {
		$ret    = true;
		$this->delete_GameBookScene($scene_id);
		
		$gb_array = array();
		$new_i = 0;
		$inserted = false;
		foreach( $this->gameBook as $sc ) {
			if( $new_i == $index ) {
				$gb_array[$new_i] = array('scene_id' => $scene_id,
										  'libelle'  => $libelle);
				$new_i++;
				$inserted = true;
			}
			$gb_array[$new_i] = $sc;
			$new_i++;
		}
		if( ! $inserted )
			$gb_array[] = array('scene_id' => $scene_id,
							    'libelle'  => $libelle);
		$this->gameBook = $gb_array;
		
		return $ret;
	}

	/* delete a link to a book's scene */
	public function delete_GameBookScene($scene_id) {
		$ret    = true;
		$trouve = false;
		$i = 0;
		foreach( $this->gameBook as $i => $sc ) {
			if( $sc['scene_id'] == $scene_id ) {
				$trouve = true;
				break;
			}
		}
		if( $trouve ) {
			unset($this->gameBook[$i]);
			ksort($this->gameBook);
			
			$gb_array = array();
			$new_i = 0;
			foreach( $this->gameBook as $i => $sc ) {
				$gb_array[$new_i] = $sc;
				$new_i++;
			}
			$this->gameBook = $gb_array;
		}
		
		return $ret;
	}

	/* save all linked scenes */
	public function save_GameBookScene() {
		$this->DB_Scene->gameBook = $this->gameBook;
		$ret = $this->DB_Scene->saveGameBook();
		return $ret;
	}
	
	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = $this->DB_Scene->save();
		if( $result )
			$this->id = $this->DB_Scene->id;
		return $result;
	}
	
	/* Delete object from DB */
	public function delete($cascade = false) {
		$this->updateDB_Object();
		return $this->DB_Scene->delete();
	}
	
	
	/* Return scene's subelements  */
	public function getSubElements() {
		return array();
	}

	/* Refresh upper element status  */
	public function refreshUpperElementStatus() {
		if( $this->chapter == null ) {
			$this->chapter = new WH_Chapter($this->chapter_id);
			$this->chapter->refreshStatus();
		}
	}
	
	
	/* Read DB */
	private function get_Scene($id) {
		$this->DB_Scene = new WH_DB_Scene($id);
		
		if( $this->DB_Scene->isOk ) {
			$this->id                = $this->DB_Scene->id                ;
			$this->number            = $this->DB_Scene->number            ;
			$this->description       = $this->DB_Scene->description       ;
			$this->text              = $this->DB_Scene->text              ;
			$this->status            = $this->DB_Scene->status            ;
			$this->chapter_id        = $this->DB_Scene->chapter_id        ;
			$this->book_id           = $this->DB_Scene->book_id           ;
			$this->editing_text      = $this->DB_Scene->editing_text      ;
			$this->gameBook          = $this->DB_Scene->gameBook          ;

			$this->isOk = true;
			
		} else
			$this->isOk = false;
	}
	
	public function refresh_data() {
		$this->get_Scene($this->id);
	}
	
	/* Update DB object */
	private function updateDB_Object() {
		$this->DB_Scene->id                = $this->id               ;
		$this->DB_Scene->number            = $this->number           ;
		$this->DB_Scene->description       = $this->description      ;
		$this->DB_Scene->text              = $this->text             ;
		$this->DB_Scene->word_count        = $this->word_count       ;
		$this->DB_Scene->status            = $this->status           ;
		$this->DB_Scene->chapter_id        = $this->chapter_id       ;
		$this->DB_Scene->editing_text      = $this->editing_text     ;
		$this->DB_Scene->gameBook          = $this->gameBook         ;
		// refresh object
		if( $this->chapter_id == 0 )
			$this->chapter        = null;
		else if ( $this->chapter != null && $this->chapter->id != $this->chapter_id )
			$this->chapter        = new WH_Chapter($this->chapter_id);
	}
	
	
	
	/* Get All Chapter's Scenes */
	public static function getAll_Scenes($chapter_id) {
		$myScenes = array();
		$dbScenes = WH_DB_Scene::getAllDB_Scenes($chapter_id);
		
		foreach( $dbScenes as $scene ) {
			$myScenes[] = new WH_Scene(0, get_object_vars($scene));
		}
		
		return $myScenes;
	}
	
	/* Get All Book's Scenes */
	public static function getAll_BookScenes($book_id) {
		$myScenes = array();
		$dbScenes = WH_DB_Scene::getAllDB_BookScenes($book_id);
		
		foreach( $dbScenes as $scene ) {
			$myScenes[] = new WH_Scene(0, get_object_vars($scene));
		}
		
		return $myScenes;
	}
	
	/* Get word count of a chapter */
	public static function get_WordCount($book_id, $chapter_id = 0) {
		return WH_DB_Scene::getDB_WordCount($book_id, $chapter_id);
	}
}
?>