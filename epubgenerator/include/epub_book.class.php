<?php
/************************************
 **      EPUB Book class      **
 ************************************/
include_once(EPUB_INCLUDE_DIR."/cmn_functions.php");
include_once(EPUB_INCLUDE_DIR."/epub_log.class.php");

class EPUB_Book extends EPUB_Log {
	
	public $title;
	public $authors;
	public $type;
	public $resume;
	public $cover_img;
	public $isbn;
	public $gameBook; // bool
	
	public $chapters;
	public $notes;
	
	/**
	* Class constructor
	*  $title    : string
	*  $authors  : array(string)
	*  $type     : string
	*  $resume   : string
	*  $cover_img: string
	*  $isbn     : string
	**/
    public function __construct($title, $authors = array(),
	                            $type = "", $resume = "",
								$cover_img = "", $isbn = "",
								$gameBook = false)    {
		$this->title     = $title    ;
		$this->authors   = (is_array($authors))?$authors:array($authors);
		$this->type      = $type     ; 
		$this->resume    = $resume   ;
		$this->cover_img = $cover_img;
		$this->isbn      = $isbn     ;
		$this->gameBook  = $gameBook ;
		$this->chapters  = array()   ;
		
		return true;
	}
	
	/* Add a chapter (at end by default)
	*   $chapter : array()
	*                - type (cf. cmn_functions)
	*                - id     
	*                - title     
	*                - text
	*                - links: array('chapter_id', 'text_link')
	*   $order   : number
	*/
	public function addChapter($chapter, $order = 9999) {
		global $eg_chapterTypes;
		$ret = true;
		$found = false;
		
		if( ! is_array($chapter) ){
			$this->error_log("chapter is not an array!");
			return false;
		}
		
		if( ! isset($chapter['text']) ){
			$this->error_log("no text in chapter array!");
			return false;
		}
		
		if( ! isset($chapter['notes']) ){
			$chapter['notes'] = array();
		}
		
		if( ! isset($chapter['type']) ) {
			$chapter['type']    = $eg_chapterTypes[EpubGen_CT_CHAPTER];
			$chapter['type_id'] = EpubGen_CT_CHAPTER;
		} else {
			$chapter['type_id'] = 99;
			foreach( $eg_chapterTypes as $id => $t ) {
				if( strtolower($chapter['type']) == strtolower($t) ) {
					$chapter['type_id'] = $id;
					break;
				}
			}
			if( $chapter['type_id'] = 99 ){
				$this->error_log("chapter type ('".$chapter['type']."') unknown!");
				return false;
			}		
		}
		
		if( $order == 9999 ) {
			$this->chapters[] = $chapter;
		} else {
			$chapters = array();
			foreach( $this->chapters as $key => $ch ) {
				if( $key == $order ) {
					$found = true;
					$chapters[] = $chapter;	
				}
				$chapters[] = $ch;
			}
			if( ! $found ) 
				$chapters[] = $chapter;
			$this->chapters = $chapters;
		}
		
		return $ret;
	}
	
	/* Delete a chapter 
	*   $ch_number   : number
	*/
	public function deleteChapter($ch_number) {
		$ret   = true;
		$found = false;
	
		if( $ch_number > count($this->chapters)-1 ){
			$this->error_log("chapter number > total number of chapters!");
			return false;
		}
		
		$chapters = array();
		foreach( $this->chapters as $key => $ch ) {
			if( $key != $ch_number ) {
				$found = true;
				$chapters[] = $ch;
			}
		}
		if( ! $found ) {
			$this->error_log("chapter number not found!");
			$ret = false;
		} else
			$this->chapters = $chapters;
	
		return $ret;
	}
	
	/* Add a note (at end by default)
	*   $note : array()
	*                - id     
	*                - text
	*   $order   : number
	*/
	public function addNote($note, $order = 9999) {
		$ret = true;
		$found = false;
		
		if( ! is_array($note) ){
			$this->error_log("not is not an array!");
			return false;
		}
		if( ! isset($note['id']) ){
			$this->error_log("id not found in note array!");
			return false;
		}
		if( ! isset($note['text']) ){
			$this->error_log("text not found in note array!");
			return false;
		}
		
		if( $order == 9999 ) {
			$this->notes[] = $note;
		} else {
			$notes = array();
			foreach( $this->notes as $key => $ch ) {
				if( $key == $order ) {
					$found = true;
					$notes[] = $note;	
				}
				$notes[] = $ch;
			}
			if( ! $found ) 
				$notes[] = $note;
			$this->notes = $notes;
		}
		
		return $ret;
	}
	
	/* Delete a note 
	*   $order   : number
	*/
	public function deleteNote($order) {
		$ret   = true;
		$found = false;
	
		if( $order > count($this->notes)-1  ){
			$this->error_log("note order > total number of notes!");
			return false;
		}
		
		$notes = array();
		foreach( $this->notes as $key => $ch ) {
			if( $key != $order ) {
				$found = true;
				$notes[] = $ch;
			}
		}
		if( ! $found ){
			$this->error_log("note order not found!");
			return false;
		} else
			$this->notes = $notes;
	
		return $ret;
	}
	
}