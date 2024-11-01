<?php
/************************************
 **      EPUB XHTML File class     **
 ************************************/
include_once(EPUB_INCLUDE_DIR."/epub_xhtmlfile.class.php");

class EPUB_Xhtml_Cover extends EPUB_XhtmlFile {
	
	public $img_url;
	public $img_alt;
	
	/**
	* Class constructor 
	*  $metadata : array() (cf. EPUB_XhtmlFile class)
	*  $img_url  : string
	*  $img_alt  : string
	**/
    public function __construct($metadata, $img_url, $img_alt = "")    {
		
		$this->img_url  = trim($img_url);
		$this->img_alt  = trim($img_alt);
		
		if( strlen($this->img_url) == 0 ) {
			$this->error_log("img_url not set!");
			return false;
		}
		if( strlen($this->img_alt) == 0 && isset($metadata['title']) )
			$this->img_alt = "Cover of ".$metadata['title'];
		
		return parent::__construct($metadata);
	}
	
	
	public function getXhtmlFileContent($pageBreak = true) {
		$result = "";
		
		// write content
		$result .= "<body epub:type=\"cover\"> <img class=\"cover-img\" ".
				   "src=\"images/".basename($this->img_url)."\" ".
				   "alt=\"".$this->img_alt."\" />\n";

	   // write page break
		if( $pageBreak )
			$result .= $this->createPageBreakXhtml();
			
		$result .= "</body>\n";
			
		return $result;
	}
}