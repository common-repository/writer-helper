<?php
/************************************
 **      EPUB Book class      **
 ************************************/
include_once(EPUB_INCLUDE_DIR."/cmn_functions.php");
include_once(EPUB_INCLUDE_DIR."/epub_book.class.php");

class EPUB_BookSerie extends EPUB_Log {
	
	public $title;
	public $description;
	public $cover_img;
	
	public $books; /* EPUB_Book */
	
	/**
	* Class constructor
	*  $title    : string
	*  $desc     : string
	*  $cover_img: string
	**/
    public function __construct($title, $desc = "", $cover_img = "", $books = array())    {
		$this->title       = $title    ;
		$this->description = $desc   ;
		$this->cover_img   = $cover_img;
		$this->books       = $books   ;
		
		return true;
	}
	
	/**
	* Return an array of books' authors
	**/
	public function getAuthors() {
		$authors = array();
		
		foreach( $this->books as $b )
			foreach( $b->authors as $a )
				$authors[$a] = $a;
		if( count($authors) > 0 )
			ksort($authors);
		
		return array_keys($authors);
	}
	
}