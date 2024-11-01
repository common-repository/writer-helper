<?php
/************************************
 **      Category class      **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_category.php");

class WH_Category {
	
	public $id;
	public $element;
	public $number;
	public $title;
	public $description;
	public $parent_id;
	
	public $isOk;
	
	private $DB_Category;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'element' => string
	*			'number' => int
	*			'title' => string
	*			'description' => string
	*			'parent_id' => int
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->id          = isset($args['id'])         ? (int)$args['id']: (int)$id;
		$this->element     = isset($args['element'])    ? (string)$args['element']:'';
		$this->number      = isset($args['number'])     ? (int)$args['number']:0;
		$this->title       = isset($args['title'])      ? (string)$args['title']:'';
		$this->description = isset($args['description'])? (string)$args['description']:'';
		$this->parent_id   = isset($args['parent_id'])  ? (int)$args['parent_id']:0;
		
		$this->isOk = false;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
			return false;
		} else {
			if( $id != 0 )
				$this->get_Category($id);
			else {
				$this->DB_Category = new WH_DB_Category(0, $args);
				$this->isOk = $this->DB_Category->isOk;
				
				// Look for a same category in DB
				$res = WH_DB_Category::getDB_CategoryByTitle($this->element, $this->title);
				if( $res !== false )
					if( count($res) == 1 )
						$this->id = $res[0]->id;
			}
		}
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = $this->DB_Category->save();
		if( $result )
			$this->id = $this->DB_Category->id;
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$this->updateDB_Object();
		return $this->DB_Category->delete();
	}
	
	/* Get title */
	public function get_Title($format = 'html' ){
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
	
	/* Get description */
	public function get_Desc($format = 'html' ){
		$desc = $this->description;
		
		switch( $format ) {
			case 'text':
						$desc = html_entity_decode($desc,ENT_QUOTES);
						break;
			case 'html':
			default:
						break;
		}
		
		return $desc;
	}
	
	/* Read DB */
	private function get_Category($id) {
		$this->DB_Category = new WH_DB_Category($id);
		
		if( $this->DB_Category->isOk ) {
			$this->id           = $this->DB_Category->id           ;
			$this->element      = $this->DB_Category->element      ;
			$this->number       = $this->DB_Category->number       ;
			$this->title        = $this->DB_Category->title        ;
			$this->description  = $this->DB_Category->description  ;
			$this->parent_id    = $this->DB_Category->parent_id    ;

			$this->isOk = true;
			
		} else
			$this->isOk = false;
	}

	/* Update DB object */
	private function updateDB_Object() {
		$this->DB_Category->id          = $this->id          ;
		$this->DB_Category->element     = $this->element     ;
		$this->DB_Category->number      = $this->number      ;
		$this->DB_Category->title       = $this->title       ;
		$this->DB_Category->description = $this->description ;
		$this->DB_Category->parent_id   = $this->parent_id   ;
	}
	
	/* Get All DB */
	public static function getAll_Categories($element = "", $col = "element", $direction = "asc") {		
		$myCats = array();
		$dbCats = WH_DB_Category::getAllDB_Categories($element, $col, $direction);
		
		foreach( $dbCats as $cat ) {
//wtr_info_log(__METHOD__,"Cat ".$cat->id." ".$cat->title);			
			$myCats[] = new WH_Category(0, get_object_vars($cat));
		}
		
		return $myCats;
	}
	
	/* Get All DB */
	public static function getAll_CategoriesByParent($parent_id) {
		$myCats = array();
		$dbCats = WH_DB_Category::getAllDB_CategoriesByParent($parent_id);
		
		foreach( $dbCats as $cat ) {
			$myCats[] = new WH_Category(0, get_object_vars($cat));
		}
		
		return $myCats;
	}
	
	/* Return the date format */
	public static function get_DateFormat() {
		$date_format = "d-m-Y";
		
		$cats = WH_Category::getAll_Categories(WTRH_CAT_DATEFORMAT, "number", "asc");
		if( isset($cats[0]) )
			$date_format = $cats[0]->title;
		
		return $date_format;
	}
	
	/* Return the time format */
	public static function get_TimeFormat() {
		$time_format = "H:i";
		
		$cats = WH_Category::getAll_Categories(WTRH_CAT_TIMEFORMAT, "number", "asc");
		if( isset($cats[0]) )
			$time_format = $cats[0]->title;
		
		return $time_format;
	}
	
	/* Return book types */
	public static function get_BookTypes() {
		return WH_Category::getAll_Categories(WTRH_CAT_BOOKTYPE, "title", "asc");
	}
	
	/* Return books' settings */
	public static function get_BooksSettings() {
		return WH_Category::getAll_Categories("Settings::Books", "number", "asc");
	}
	
	/* Return the date format */
	public static function update_DateFormat($format) {
		$my_cat = null;
		$cats = WH_Category::getAll_Categories(WTRH_CAT_DATEFORMAT, "number", "asc");
//wtr_info_log(__METHOD__,"format=".$format);
//wtr_info_log(__METHOD__,"cats =".print_r($cats,true));

		if( isset($cats[0]) ) {
			$my_cat = $cats[0];
			$my_cat->title = $format;
		} else // non existent category
			$my_cat = new WH_Category(0, array( 'element' => WTRH_CAT_DATEFORMAT, 
												'number'  => 1, 
												'title'   => $format));
		
		return $my_cat->save();
	}
	
	/* Return the time format */
	public static function update_TimeFormat($format) {
		$my_cat = null;
		$cats = WH_Category::getAll_Categories(WTRH_CAT_TIMEFORMAT, "number", "asc");
		
		if( isset($cats[0]) ){
			$my_cat = $cats[0];
			$my_cat->title = $format;
		} else // non existent category
			$my_cat = new WH_Category(0, array( 'element' => WTRH_CAT_TIMEFORMAT, 
												'number'  => 1, 
												'title'   => $format));
		
		return $my_cat->save();
	}
	
}
?>