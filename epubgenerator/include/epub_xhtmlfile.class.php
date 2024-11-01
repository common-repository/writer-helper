<?php
/************************************
 **      EPUB XHTML File class     **
 ************************************/
include_once(EPUB_INCLUDE_DIR."/epub_log.class.php");

abstract class EPUB_XhtmlFile extends EPUB_Log {
	
	public $metadata;

	/**
	* Class constructor 
	*  $metadata : array()
	*			'title'       => string
	*			'creator'     => string
	*			'subject'     => string
	*			'description' => string
	*			'keywords'    => string
	*			'publisher'   => string
	*			'contributor' => string
	*			'date'        => string
	*			'type'        => string
	*			'format'      => string
	*			'identifier'  => string
	*			'source'      => string
	*			'language'    => string
	*			'relation'    => string
	*			'coverage'    => string
	*			'copyright'   => string
	*  			'css_files'   => array()
	*							x => array()
	*								'filename' => string
	*  			'scripts'	  => array()
	*							x => array()
	*								'filename' => string
	**/
    public function __construct($metadata)    {
		$this->metadata   = $metadata;
		
		return $this->epubDataControl();
	}
	
	private function epubDataControl() {
		
		// Minimum data set for metadata
		if( ! isset($this->metadata['title']) ) {
			$this->error_log("no title in metadata array!");
			return false;
		}
		if( ! isset($this->metadata['description']) ) {
			$this->error_log("no description in metadata array!");
			return false;
		}
		if( ! isset($this->metadata['identifier']) ) {
			$this->error_log("no identifier in metadata array!");
			return false;
		}
		if( ! isset($this->metadata['language']) ) {
			$this->error_log("no language in metadata array!");
			return false;
		}
		
		if( ! isset($this->metadata['css_files']) )
			$this->metadata['css_files'] = array();
		if( ! isset($this->metadata['scripts']) )
			$this->metadata['scripts'] = array();
		
	}	
	
	
	
	/*************************************
	* Create XHTML file content
	*  $ffn  : string (full file name)
	*  $pb   : boolean(page break)
	**************************************/
	public function createXhtmlFile($ffn, $pb = false) {
		$ret = true;
		
		// Control ffn (full file name)
		if( strlen(trim($ffn)) == 0 ) {
			$this->error_log("filename is not set!");
			return false;
		}
		if( substr($ffn, -6) != ".xhtml" )
			$ffn = trim($ffn).".xhtml";
		
		$dir = dirname(trim($ffn));

		// File exists => delete file
		if( file_exists($ffn) )
			if( ! unlink($ffn) ){
				$this->error_log("Delete file error (file=".
								 $ffn.")");
				return false;
			}		
			
		// Dir does not exists => create dir
		if( ! file_exists($dir) )
			if( ! mkdir($dir) ){
				$this->error_log("Create directory error (dir=".
								 $dir.")");
				return false;
			}		

		// Create file
		$fh = fopen($ffn, "w");
		if( $fh === false ){
			$this->error_log("Create file error (file=".
							 $ffn.")");
			$ret = false;
		}		
		
		// write head
		if( $ret && ! fwrite($fh, $this->getXhtmlHead()) ){
			$this->error_log("Write <head> error (file=".
							 $ffn.")");
			$ret = false;
		}		
		
		// write content
		if($ret && ! fwrite($fh, $this->getXhtmlFileContent($pb)) ) {
			$this->error_log("Write <body> error (file=".
							 $ffn.")");
			$ret = false;
		}		
			
		// write </html>
		if($ret && ! fwrite($fh, "</html>") ) {
			$this->error_log("Write </html> error (file=".
							 $ffn.")");
			$ret = false;
		}		
			
		if( $fh !== false )
			fclose($fh);
			

		return $ret;
	}
	
	
	/*************************************
	* Generate XHTML file content
	**************************************/
	abstract function getXhtmlFileContent($pageBreak = true);
	
	/*************************************
	* Generate HEAD from XHTML file
	**************************************/
	public function getXhtmlHead() {
		$result = "";
		
		$result = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n".
				  "<!DOCTYPE html>\n".
				  "<html xmlns=\"http://www.w3.org/1999/xhtml\" ".
				  " xmlns:epub=\"http://www.idpf.org/2007/ops\" ".
				  " lang=\"".$this->metadata['language']."\" ".
				  " xml:lang=\"".$this->metadata['language']."\">".
		          "<head>\n".
				  "<title>".$this->metadata['title']."</title>\n".
				  EPUB_XhtmlFile::getXhtmlMetadata($this->metadata);
		
		if( isset($this->metadata['css_files']) && is_array($this->metadata['css_files']) )
		foreach( $this->metadata['css_files'] as $css ) {
			$result .= "<link rel=\"stylesheet\" ".
					   " href=\"scripts/".basename($css['epub_fn'])."\" type=\"text/css\" />\n";
		}
		
		if( isset($this->metadata['scripts']) && is_array($this->metadata['scripts']) )
		foreach( $this->metadata['scripts'] as $sc ) {
			$result .= "<script type=\"text/javascript\" ".
					   " src=\"scripts/".basename($sc['epub_fn'])."\"></script>\n";
		}
		
		$result .= "</head>\n";
		
		return $result;
	}
	
	private function getXhtmlMetadata() {
		$result = "";
		
	// Dublin Core meta (http://purl.org/dc/elements/1.1/creator)
/*		$result .= "<metadata xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
		$result .= "<dc:identifier id=\"uuid_id\">urn:uuid".$this->metadata['identifier']."\"</dc:identifier>\n";
		$result .= "<dc:title>".$this->metadata['title']."\</dc:title>\n";
		$result .= "<dc:language>".$this->metadata['language']."</dc:language>\n"; 
		if( isset($this->metadata['creator']) )
		$result .= "<dc:creator id=\"creator\">".$this->metadata['creator']."</dc:creator>\n";
		if( isset($this->metadata['subject']) )
		$result .= "<dc:subject>".$this->metadata['subject']."</dc:subject>\n";
		if( isset($this->metadata['description']) )
		$result .= "<dc:description>".$this->metadata['description']."</dc:description>\n";
		if( isset($this->metadata['publisher']) )
		$result .= "<dc:publisher>".$this->metadata['publisher']."</dc:publisher>\n";
		if( isset($this->metadata['contributor']) )
		$result .= "<dc:contributor>".$this->metadata['contributor']."</dc:contributor>\n";
		if( isset($this->metadata['date']) )
		$result .= "<dc:date>".$this->metadata['date']."</dc:date>\n";
		if( isset($this->metadata['type']) )
		$result .= "<dc:type>".$this->metadata['type']."</dc:type>\n";
		if( isset($this->metadata['format']) )
		$result .= "<dc:format>".$this->metadata['format']."</dc:format>\n";
		if( isset($this->metadata['source']) )
		$result .= "<dc:source>".$this->metadata['source']."</dc:source>\n";
		if( isset($this->metadata['relation']) )
		$result .= "<dc:relation>".$this->metadata['relation']."</dc:relation>\n";
		if( isset($this->metadata['coverage']) )
		$result .= "<dc:coverage>".$this->metadata['coverage']."</dc:coverage>\n";
		if( isset($this->metadata['copyright']) )
		$result .= "<dc:rights>".$this->metadata['copyright']."</dc:rights>\n";
		$result .= "</metadata>\n";
*/
	// HTML meta
		if( isset($this->metadata['creator']) )
		$result .= "<meta name=\"author\" content=\"".$this->metadata['creator']."\" />\n";
		if( isset($this->metadata['description']) )
		$result .= "<meta name=\"description\" content=\"".$this->metadata['description']."\" />\n";
		if( isset($this->metadata['keywords']) )
		$result .= "<meta name=\"keywords\" content=\"".$this->metadata['keywords']."\" />\n"; 
		if( isset($this->metadata['identifier']) )
		$result .= "<meta name=\"identifier\" content=\"".$this->metadata['identifier']."\" />\n";
		
		$result .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />\n";
		
		return $result;
	}	

	
	// page break before
	protected function createPageBreakXhtml() {
		return "<span epub:type=\"pagebreak\" ".
		       "class=\"pagebreak\" />\n";
	}
	
	
}