<?php
/************************************
 **      EPUB XHTML File class     **
 ************************************/
include_once(EPUB_INCLUDE_DIR."/cmn_functions.php");
include_once(EPUB_INCLUDE_DIR."/epub_xhtmlfile.class.php");

class EPUB_Xhtml_Nav extends EPUB_XhtmlFile {
	
	public $chapters;
	public $summary_lib;
	public $resume_lib;
	
	/**
	* Class constructor 
	*  $metadata : array() (cf. EPUB_XhtmlFile class)
	*  $chapters : array()
	*			'id'          => string
	*			'title'       => string
	*  $summary_lib : string
	**/
    public function __construct($metadata, $chapters, 
	                            $summary_lib = "",
								$resume_lib = "")    {
		
		$this->chapters     = $chapters;
		$this->summary_lib  = trim($summary_lib);
		$this->resume_lib   = trim($resume_lib);
		
		if( !is_array($this->chapters) || count($this->chapters) == 0 ) {
			$this->error_log("chapters not an array!");
			return false;
		}
		
		if( strlen($this->summary_lib) == 0 )
			$this->summary_lib = EpubGen_Summary;
		
		if( strlen($this->resume_lib) == 0 )
			$this->resume_lib = EpubGen_Resume;
		
		return parent::__construct($metadata);
	}
	
	
	public function getXhtmlFileContent($pageBreak = true) {
		$result = "";
		
		$result .= "<body>\n";
			
		// write page break
		if( $pageBreak )
			$result .= $this->createPageBreakXhtml();
	
        $result .= "<section epub:type=\"frontmatter toc\">\n";
        $result .= "<h1 class='title'>".$this->summary_lib."</h1>\n";
        $result .= "<br/><br/><nav epub:type=\"toc\" id=\"toc\">\n";
        $result .= "<ol class='summary'>\n";
        $result .= "<li><a href=\"abstract.xhtml\">".$this->resume_lib."</a></li>\n";
		        
		foreach( $this->chapters as $ch ) {
			if( isset($ch['id']) && isset($ch['title']) && strlen(trim($ch['title'])) > 0 ) 
			$result .= "<li><a href=\"".basename($ch['epub_fn'])."#ct-".$ch['id']."\">".
					str_replace("<br/>"," ",$ch['title'])."</a></li>\n";
		}
		
        $result .= "</ol>\n";
        $result .= "</nav>\n";
        $result .= "</section>\n";

		$result .= "</body>\n";
		
		return $result;
	}

	
}