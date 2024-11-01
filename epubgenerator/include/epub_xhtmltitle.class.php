<?php
/************************************
 **      EPUB XHTML File class     **
 ************************************/
include_once(EPUB_INCLUDE_DIR."/epub_xhtmlfile.class.php");

class EPUB_Xhtml_Title extends EPUB_XhtmlFile {
	
	public $book;
	public $img_alt;
	
	/**
	* Class constructor 
	*  $metadata : array() (cf. EPUB_XhtmlFile class)
	*  $book     : EPUB_Book
	**/
    public function __construct($metadata, $book)    {
		
		$this->book  = $book;
		$this->img_alt = "Cover of ".$metadata['title'];
		
		return parent::__construct($metadata);
	}
	
	
	public function getXhtmlFileContent($pageBreak = true) {
		$result = "";
					
		// write content
		if( strlen(trim($this->book->cover_img)) == 0 ) {
			$result .= "<body>";
			$result .= "<p class='titleFromTitlePage'>".$this->book->title."</p><br/>\n";
			$result .= "<p class='authorFromTitlePage'>".$this->book->authors."</p>\n";
		} else {
			$result .= "<body epub:type=\"cover\"> <img class=\"cover-img\" ".
					   "src=\"images/".basename($this->book->cover_img)."\" ".
					   "alt=\"".$this->img_alt."\" />\n";			
		}
		
	   // write page break
		if( $pageBreak )
			$result .= $this->createPageBreakXhtml();
			
		$result .= "</body>\n";
			
		return $result;
	}
}