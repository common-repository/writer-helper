<?php
/************************************
 **      EPUB XHTML File class     **
 ************************************/
include_once(EPUB_INCLUDE_DIR."/epub_xhtmlfile.class.php");

class EPUB_Xhtml_Chapter extends EPUB_XhtmlFile {
	
	public $type;
	public $type_id;
	public $id;
	public $title;
	public $text;
	public $notes;
	public $links;
	
	/**
	* Class constructor 
	*  $metadata : array() (cf. EPUB_XhtmlFile class)
	*  $type     : string (cf. EpubGen_CT_* from cmn_functions.php)
	*  $id       : string
	*  $title    : string
	*  $text     : string
	*  $notes    : array()     in chapter text, a note is [note <note id>]
	*				'id'   => string
	*				'text' => string
	*  $links    : array()     links to other chapters, add at end of chapter
	*				'chapter_id' => string
	*				'text_link'  => string
	**/
    public function __construct($metadata, $type, $id, $title, $text, 
								$notes = array(), $links = array())    {
		global $eg_chapterTypes;
		
		$this->type    = trim($type  );
		$this->id      = trim($id    );
		$this->title   = trim($title );
		$this->text    = trim($text  );
		$this->notes   = is_array($notes)?$notes:array();
		$this->links   = is_array($links)?$links:array();
		
		if( strlen($this->text) == 0 ) {
			$this->error_log("chapter text not set! (type= $type, id=$id, title=$title)");
			//return false;
		}		
		
		if( strlen($this->type) == 0 ) {
			$this->type    = $eg_chapterTypes[EpubGen_CT_CHAPTER];
			$this->type_id = EpubGen_CT_CHAPTER;
		} else {
			$this->type_id = 99;
			foreach( $eg_chapterTypes as $id => $t ) {
				if( strtolower($type) == strtolower($t) ) {
					$this->type_id = $id;
					break;
				}
			}
			if( $this->type_id = 99 ){
				$this->error_log("chapter type ('".$type."') unknown!");
				return false;
			}		
		}
		
		return parent::__construct($metadata);
	}
	
	/*************************************
	* Return the content of XHTML file
	**************************************/
	public function getXhtmlFileContent($pageBreak = true) {
		$xhtml = "";
			
		// write <body>
		$xhtml = "<body>\n";
			
		// write page break
		if( $pageBreak )
			$xhtml .= $this->createPageBreakXhtml();
		
		// write content
		$xhtml .= $this->getFormatedContent();
		
		// write </body>
		$xhtml .= "\n</body>\n";
			
		
		return $xhtml;
	}
	
	/*************************************
	* Replace Media URLs in a chapter text
	*  $media : array() 
	*            x => array()
	*              'old_url' => string
	*              'new_url' => string
	**************************************/
	public function replaceMediaUrls($media) {
		$ret = true;
		
		if( ! is_array($media) ){
			$this->error_log("media is not an array!");
			return false;
		}		
		
		foreach( $media as $m ) {
		  if( strpos($this->text, $m['old_url']) !== false )
			$this->text = str_replace($m['old_url'], 
									  $m['new_url'], 
									  $this->text);
		}
		
		return $ret;
	}
	
	/*************************************
	* Format a content string to respect EPUB standards
	**************************************/
	private function getFormatedContent() {
		// get XHTML
		$result = stripslashes($this->generateSpecificXhtml());
		
		$result = str_replace("<br>", "<br/>", $result);
		// Apply rules
// raf		
/*
    Les retours à la ligne. Là encore, toutes les balises seront fermées : <br />

    Les espaces insécables et fines insécables sont remplacés par des &#160;   et &#8201;.
    Rappel: Une espace fine devant le point-virgule, le point d'exclamation, 
	le point d'interrogation. Une espace insécable devant les deux points, 
	entre les guillemets (voir 3.4)

    Tous les guillemets de type &ldquo; et &rdquo; ou &laquo; et &raquo; 
	ont été remplacés par « et », autrement dit &#171; et &#187; (voir 3.4)
	
    Toutes les esperluettes, surtout dans les URL, doivent être remplacées par &amp;
*/		
		
		return $result;
	}
	
	
	// Create xHTML of specific sections
	private function generateSpecificXhtml() {
		$result = "";
		$id     = $this->id;
		$title  = $this->title;
		$text   = $this->text;
		$notes  = $this->notes;
		$links  = $this->links;
		
		switch($this->type_id) {
			case EpubGen_CT_PREFACE    : $result = $this->createPrefaceXhtml($text); break;
			case EpubGen_CT_FOREWORD   : $result = $this->createForewordXhtml($text); break;
			case EpubGen_CT_INTRO      : $result = $this->createIntroductionXhtml($text); break;
			case EpubGen_CT_CONCLUSION : $result = $this->createConclusionXhtml($text); break;
			case EpubGen_CT_CHAPTER    : $result = $this->createChapterXhtml($id, $title, $text, $notes, $links); break;
			case EpubGen_CT_NOTE       : $result = $this->createNoteXhtml($id, $text); break;
			case EpubGen_CT_GLOSSARY   : $result = $this->createGlossaryXhtml($text); break;
			case EpubGen_CT_BIBLIO     : $result = $this->createBibliographyXhtml($text); break;
			case EpubGen_CT_INDEX      : $result = $this->createIndexXhtml($text); break;
			case EpubGen_CT_CONTRIBUTOR: $result = $this->createContributorsXhtml($text); break;
			case EpubGen_CT_DEDICATION : $result = $this->createDedicationXhtml($text); break;
			case EpubGen_CT_EPILOGUE   : $result = $this->createEpilogueXhtml($text); break;
			case EpubGen_CT_COPYRIGHT  : $result = $this->createCopyrightXhtml($text); break;
			default:
				    $result = $this->createChapterXhtml($id, $title, $text, $notes, $links); break;
		}
		return $result;
	}
	
	private function createPrefaceXhtml($text) {
		return "<section epub:type=\"preface\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createForewordXhtml($text) {
		return "<section epub:type=\"foreword\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createIntroductionXhtml($text) {
		return "<section epub:type=\"introduction\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createConclusionXhtml($text) {
		return "<section epub:type=\"conclusion\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createChapterXhtml($id, $title, $text, 
										$notes = array(), $links = array()) {
		$result = "";
		$aside  = "";
		
		// notes exist
		if( count($notes) > 0 ) {
			$aside .= "<aside id=\"fn".$n['id']."\" epub:type=\"footnote\">\n";
			foreach( $notes as $n ) {
				$aside .= $this->createNoteXhtml($n['id'], $n['text']);
			}
			$aside .= "</aside>\n";
			
			// search and replace [note <note id>] id chapter text
			$text = str_replace("[note ".$n['id']."]", 
					"<span class=\"noteid\" id=\"nr-".$n['id']."\">".
					"<sup><a href=\"#ft-".$n['id']."\">".
					$n['id'].
					"</a></sup></span>",
					$text);
		}
		
		// final xhtml content
		$result .= "<section epub:type=\"chapter\" id=\"chap".$this->id."\">\n".
			   "<h1 class='title' id=\"ct-".$id."\">".$title."</h1>\n".
			   "<br/><br/>".$text."\n";	
		// add links
		$result .= "<br/><br/>\n";

		foreach( $links as $l )
			if( $l['chapter_id'] == 0 ) {
				$result .= "<center>".
							$l['text_link']."</center><br/><br/>\n";
			} else
				$result .= "<a href=\"chap".sprintf("%09d", $l['chapter_id']).".xhtml\">".
							$l['text_link']."</a><br/><br/>\n";
		$result .= "<br/>\n";
		$result .= $aside;
		$result .= "</section>\n";
			   
		return $result;
	}
	private function createNoteXhtml($noteNumber, $noteText) {
		return "<aside epub:type=\"footnote\" id=\"ft".$noteNumber."\">\n".
			   "<a href=\"#nr-".$noteNumber."\">".$noteNumber.".</a> \n".
			     $noteText."\n".
			   "</aside>\n";
	}
	private function createSidebarXhtml($text) {
		return "<section epub:type=\"sidebar\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createGlossaryXhtml($text) {
		return "<section epub:type=\"glossary\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createBibliographyXhtml($text) {
		return "<span epub:type=\"annotation\" id=\"chap".$this->id."\">\n".
			     $text."\n".
			   "</span>\n";
	}
	private function createIndexXhtml($text) {
		return "<section epub:type=\"index\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createContributorsXhtml($text) {
		return "<section epub:type=\"contributors\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createDedicationXhtml($text) {
		return "<section epub:type=\"dedication\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createEpilogueXhtml($text) {
		return "<section epub:type=\"epilogue\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
	private function createCopyrightXhtml($text) {
		return "<section epub:type=\"copyright-page\" id=\"chap".$this->id."\">\n".$text."\n</section>\n";
	}
		
}