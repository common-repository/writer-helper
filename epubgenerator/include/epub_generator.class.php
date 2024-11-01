<?php
/************************************
 **      EPUB Generator class      **
 ************************************/
include_once(EPUB_INCLUDE_DIR."/cmn_functions.php");
include_once(EPUB_INCLUDE_DIR."/epub_log.class.php");

include_once(EPUB_INCLUDE_DIR."/epub_book.class.php");
include_once(EPUB_INCLUDE_DIR."/epub_xhtmlchapter.class.php");
include_once(EPUB_INCLUDE_DIR."/epub_xhtmltitle.class.php");
include_once(EPUB_INCLUDE_DIR."/epub_xhtmlcover.class.php");
include_once(EPUB_INCLUDE_DIR."/epub_xhtmlnav.class.php");

class EPUB_Generator extends EPUB_Log {
	
	public $epub_dir;
	public $epub_name;
	
	public $metadata;
	public $book; 		/* EPUB_Book */
	public $media;
	
	/* tree view
		 - mimetype
		 - META-INF/container.xml
		 - OEBPS/scripts
		 - OEBPS/audioVideo
		 - OEBPS/images
		 - OEBPS/content.opf
		 - OEBPS/toc.ncx
		 - OEBPS/abstract.xhtml (resume) [fac]
		 - OEBPS/title.xhtml (title page)
		 - OEBPS/nav.xhtml (navigation) [fac]
		 - OEBPS/table.xhtml (summary)
		 - OEBPS/chap001.xhtml (chapter 1)
		 - OEBPS/
	*/
	
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
	*  $book : EPUB_Book
	*  $media : array()
	*			x => array()
	*				'type'     => string (image, audio, video)
	*				'title'    => string
	*				'filename' => string
	*				'url'      => string
	**/
    public function __construct($epub_dir, $epub_filename, 
								$metadata, $book, $media = array()){
		$this->epub_dir   = trim($epub_dir);
		$this->epub_name  = trim($epub_filename);
		$this->metadata   = is_array($metadata)?$metadata:array();
		$this->book       = $book;
		$this->media      = is_array($media)?$media:array();
		
		return $this->epubDataControl();
	}
	
	private function epubDataControl() {
		// Minimum data set 
		if( strlen($this->epub_dir) == 0 ){
			$this->error_log("EPUB dir is not set!");
			return false;
		}		
		if( strlen($this->epub_name) == 0 ){
			$this->error_log("EPUB file name is not set!");
			return false;
		}		
		
		if( strpos(basename($this->epub_name), ".epub") === false )
			$this->epub_name .= ".epub";
		
		if( count($this->metadata) == 0 ){
			$this->error_log("metadata is empty!");
			return false;
		}		
		if( get_class($this->book) != "EPUB_Book" ){
			$this->error_log("book is not an EPUB_Book class!");
			return false;
		}		
		
		// Minimum data set for metadata
		if( ! isset($this->metadata['title']) ){
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

	/*******************************
	* Generate EPUB file 
	********************************/
	public function generateEpub() {
		$ret = true;
						
		// verify directory
		if( ! file_exists(dirname($this->epub_name)) ) 
			if( ! mkdir(dirname($this->epub_name)) ) {
				$this->error_log("create dir has failed! (dir=".dirname($this->epub_name).")");
				$ret = false;
			}
			
		// verify directory
		if( file_exists($this->epub_dir) ) 
			if( ! epubgen_deleteDir($this->epub_dir) ) {
				$this->error_log("remove dir has failed! (dir=".$this->epub_dir.")");
				$ret = false;
			}		

		// create directory
		if( $ret && ! mkdir($this->epub_dir) ){
			$this->error_log("create dir has failed! (dir=".$this->epub_dir.")");
			$ret = false;
		}
		
		// create files
		if( $ret && ! $this->createEpubFiles() ){
			$this->error_log("create EPUB files has failed!");
			$ret = false;
		}		

		// create epub zip file
		if( $ret ) {
			// create zip
			$ret = epubgen_zip_a_dir($this->epub_dir, $this->epub_name);
			// delete epub dir
			if( $ret )
				$ret = epubgen_deleteDir($this->epub_dir);
		}
		
		return $ret;
	}
	
	/********************************
	* Create files
	********************************/
	private function createEpubFiles() {
		$ret = true;
		$ch_dir  = $this->epub_dir."/OEBPS";
		$scr_dir = $this->epub_dir."/OEBPS/scripts";
		$img_dir = $this->epub_dir."/OEBPS/images";
		$av_dir  = $this->epub_dir."/OEBPS/audioVideo";
		$has_cover = false;
		
		// Create mimetype file
		if( ! $this->createMimetypeFile() )
			return false;
		
		
		// Create container.xml file
		if( ! $this->createContainerXmlFile() )
			return false;

		
		// Create OEBPS dir
		if( ! file_exists($ch_dir) )
			if( ! mkdir($ch_dir) ){
				$this->error_log("Create directory error! (dir=".$ch_dir.")");
				return false;
			}
		// Create OEBPS/sripts
		if( ! file_exists($scr_dir) )
			if( ! mkdir($scr_dir) ){
				$this->error_log("Create directory error! (dir=".$scr_dir.")");
				return false;
			}
		// Create OEBPS/images
		if( ! file_exists($img_dir) )
			if( ! mkdir($img_dir) ){
				$this->error_log("Create directory error! (dir=".$img_dir.")");
				return false;
			}
		// Create OEBPS/audioVideo
		if( ! file_exists($av_dir) )
			if( ! mkdir($av_dir) ){
				$this->error_log("Create directory error! (dir=".$av_dir.")");
				return false;
			}

		
		// Copy CSS files
		if( count($this->metadata['css_files']) == 0 ) { // create mini css
			$this->metadata['css_files'][] = array('filename' => EPUB_CSS_DIR.'/styles.css');
		} 
		foreach($this->metadata['css_files'] as $key => $css) {
			$bn = basename($css['filename']);

			if( ! copy($css['filename'], $scr_dir."/".$bn) ){
				$this->error_log("Copy file error (source=".
									$css['filename']." destination=".
									$scr_dir."/".$bn.")");
				return false;
			}		
			
			$this->metadata['css_files'][$key] =
							array( 'filename' => $css['filename'],
								   'epub_fn'  => "./OEBPS/scripts/".$bn);
		}
		
		// Copy scripts files
		foreach($this->metadata['scripts'] as $key => $scr) {
			$bn = basename($scr['filename']);
			if( ! copy($scr['filename'], $scr_dir."/".$bn) ){
				$this->error_log("Copy file error (source=".
									$scr['filename']." destination=".
									$scr_dir."/".$bn.")");
				return false;
			}		
			
			$this->metadata['scripts'][$key] = 
						array( 'filename' => $scr['filename'],
							   'epub_fn'  => "./OEBPS/scripts/".$bn);
		}
		
		// Copy media files
		foreach($this->media as $key => $img) {
			$bn  = basename($img['filename']);
			$dir = $this->epub_dir."/OEBPS/images";
			$dir2= "images";
			
			if( $img['type'] != "image" ) {
				$dir = $this->epub_dir."/OEBPS/audioVideo";
				$dir2= "audioVideo";
			}
			
			if( ! copy($img['filename'], $dir."/".$bn) ){
				$this->error_log("Copy file error (source=".
									$img['filename']." destination=".
									$dir."/".$bn.")");
				return false;
			}
			
			$this->media[$key] = array( 'type'       => $img['type'],
										'filename'   => $img['filename'],
										'media_type' => epubgen_getMimeType($img['filename'], $img['type']),
										'epub_fn'    => $dir2."/".$bn,
										'old_url'    => $img['filename'],
										'new_url'    => $dir2."/".$bn);
		}
		
		
		// Create cover file
		if( strlen(trim($this->book->cover_img)) > 0 ) {
			$has_cover = true;
			// copy cover image file
			$bn = basename($this->book->cover_img);
			if( ! copy($this->book->cover_img, $img_dir."/".$bn) ){
				$this->error_log("Copy file error (source=".
									$this->book->cover_img." destination=".
									$img_dir."/".$bn.")");
				return false;
			}
			
			$this->media[] = array('type'       => 'image',
									'filename'   => $this->book->cover_img,
									'media_type' => epubgen_getMimeType($this->book->cover_img, 'image'),
									'epub_fn'    => "./OEBPS/images/".$bn,
									'old_url'    => $this->book->cover_img,
									'new_url'    => "./OEBPS/images/".$bn);
			
			// create xhtml file cover
			$cover = new EPUB_Xhtml_Cover($this->metadata, $img_dir."/".$bn);
			if( ! $cover->createXhtmlFile($ch_dir."/cover.xhtml") )
				return false;
		}

		
		// Create title file
		if( ! $has_cover ) {
		$title = new EPUB_Xhtml_Title($this->metadata, $this->book);
		if( ! $title->createXhtmlFile($ch_dir."/title.xhtml") )
			return false;
		}

		// Create resume file
		$title = new EPUB_Xhtml_Chapter($this->metadata, "", "resume",
		                                $this->book->title,
		                                $this->book->resume);
		if( ! $title->createXhtmlFile($ch_dir."/abstract.xhtml") )
			return false;

		
		// Create chapters files
		if( ! $this->createChaptersFiles() )
			return false;


		// Create nav file
		$title = new EPUB_Xhtml_Nav($this->metadata,  
		                                $this->book->chapters);
		if( ! $title->createXhtmlFile($ch_dir."/nav.xhtml") )
			return false;

		
		// Create content.opf
		if( ! $this->createContentFile() )
			return false;

		
		// Create toc file
		if( ! $this->createTableOfContentFile() )
			return false;
		
		
		return $ret;
	}
	
	/********************************
	* Create chapters files 
	********************************/
	private function createChaptersFiles() {
		$ret = true;
		$ch_dir  = $this->epub_dir."/OEBPS";

		// Create chapters id		
		// Change media URLs in chapters
		// Create chapters files
		foreach($this->book->chapters as $key => $ch) {
			
			$id = sprintf("%09d", (isset($ch['id'])?$ch['id']:$key));			
			$bn = "chap".$id.".xhtml";

			$this->book->chapters[$key]['id']      = $id;
			$this->book->chapters[$key]['epub_fn'] = "./OEBPS/".$bn;
			$chapter = new EPUB_Xhtml_Chapter($this->metadata, "",
			                                  $id, $ch['title'], 
											  $ch['text'], $ch['notes'], 
											  $ch['links']);
			
			$chapter->replaceMediaUrls($this->media);
			
			// create xhtml file
			if( ! $chapter->createXhtmlFile($ch_dir."/".$bn) )
				return false;
					
		}
		
		
		return $ret;
	}
	
	/********************************
	* MIMETYPE file
	********************************/
	private function createMimetypeFile() {
		$ret = true;
		
		$fh = fopen($this->epub_dir."/mimetype", "w");
		if( ! fwrite($fh, "application/epub+zip") ) {
			$this->error_log("Create mimetype file error");
			$ret = false;
		}
		fclose($fh);
		return $ret;
	}

	/********************************
	* CONTAINER.XML file
	********************************/
	private function createContainerXmlFile() { // container.xml
		$ret = true;
		$content = "<?xml version=\"1.0\" encoding='utf-8'?>\n".
		"<container version=\"1.0\" xmlns=\"urn:oasis:names:tc:opendocument:xmlns:container\">\n".
		"<rootfiles>\n".
		"<rootfile full-path=\"OEBPS/content.opf\" media-type=\"application/oebps-package+xml\" />\n".
		"</rootfiles>\n".
		"</container>\n";
		
		$dir = $this->epub_dir."/META-INF";
		// verify directory
		if( ! file_exists($dir) ) {
			if( ! mkdir($dir) ){
				$this->error_log("Create directory error! (dir=".$dir.")");
				return false;
			}
		}
		
		// create file
		$fh = fopen($dir."/container.xml", "w");
		if( ! fwrite($fh, $content) ){
			$this->error_log("Write file error! (file=".$dir."/container.xml".")");
			$ret = false;
		}
		fclose($fh);
		return $ret;
	}

	/********************************
	* TABLE OF CONTENT file
	********************************/
	private function createTableOfContentFile() { // toc.ncx
		$ret = true;
		$content = "<?xml version=\"1.0\" encoding='utf-8'?>\n".
		"<ncx xmlns=\"http://www.daisy.org/z3986/2005/ncx/\" ".
		" version=\"2005-1\" xml:lang=\"".$this->metadata['language']."\">\n".
		"<head>\n".
		"<meta content=\"urn:uuid:".$this->metadata['identifier']."\" name=\"dtb:uid\"/>\n".
		"<meta content=\"2\" name=\"dtb:depth\"/>\n".
		"<meta content=\"0\" name=\"dtb:totalPageCount\"/>\n".
		"<meta content=\"0\" name=\"dtb:maxPageNumber\"/>\n".
		"</head>\n".
		"<docTitle><text>".$this->book->title."</text></docTitle>\n";
		
		$content .= "<navMap>\n";
		$order = 1;
		
		// cover
		if( file_exists($this->epub_dir."/OEBPS/cover.xhtml") )
		$content .= "<navPoint id=\"np-".$order."\" playOrder=\"".$order."\">".
					" <navLabel><text>Cover</text></navLabel>".
					" <content src=\"cover.xhtml\" />".
					"</navPoint>\n";
		
		// title
		if( file_exists($this->epub_dir."/OEBPS/title.xhtml") )
		$content .= "<navPoint id=\"np-".$order."\" playOrder=\"".$order."\">".
					" <navLabel><text>Cover</text></navLabel>".
					" <content src=\"title.xhtml\" />".
					"</navPoint>\n";
		
		// chapters
		foreach( $this->book->chapters as $ch ) {
			$order++;
			$content .= "<navPoint id=\"np-".$order."\" playOrder=\"".$order."\">".
						" <navLabel><text>".str_replace("<br/>"," ",$ch['title'])."</text></navLabel>".
						" <content src=\"".basename($ch['epub_fn'])."\" />".
						"</navPoint>\n";
		}
		$content .= "</navMap>\n";	
		$content .= "</ncx>";	
		
		
		$dir = $this->epub_dir."/OEBPS";
		// verify directory
		if( ! file_exists($dir) ) {
			if( ! mkdir($dir) ){
				$this->error_log("Create directory error! (dir=".$dir.")");
				return false;
			}
		}
		
		// create file
		$fh = fopen($dir."/toc.ncx", "w");
		
		// write file
		if( ! fwrite($fh, $content) ) {
			$this->error_log("Write file error! (file=".$dir."/toc.ncx".")");
			$ret = false;
		}
		fclose($fh);
		return $ret;
	}
	
	/********************************
	* CONTENT.OPF file
	********************************/
	private function createContentFile() {
		$ret = true;
		$content = "<?xml version=\"1.0\" encoding='utf-8'?>\n".
		"<package xmlns=\"http://www.idpf.org/2007/opf\" ".
		" version=\"3.0\" unique-identifier=\"uuid_id\">\n";

		// metadata
	// Dublin Core meta (http://purl.org/dc/elements/1.1/creator)
		$content .= "<metadata xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
		$content .= "   <dc:identifier id=\"uuid_id\">urn:uuid:".$this->metadata['identifier']."</dc:identifier>\n";
		$content .= "   <dc:title>".$this->metadata['title']."\</dc:title>\n";
		$content .= "   <dc:language>".$this->metadata['language']."</dc:language>\n"; 
		if( isset($this->metadata['creator']) && strlen($this->metadata['creator']) > 0 )
		$content .= "   <dc:creator id=\"creator\">".$this->metadata['creator']."</dc:creator>\n";
		if( isset($this->metadata['subject']) && strlen($this->metadata['subject']) > 0 )
		$content .= "   <dc:subject>".$this->metadata['subject']."</dc:subject>\n";
		if( isset($this->metadata['description']) && strlen($this->metadata['description']) > 0 )
		$content .= "   <dc:description>".$this->metadata['description']."</dc:description>\n";
		if( isset($this->metadata['publisher']) && strlen($this->metadata['publisher']) > 0 )
		$content .= "   <dc:publisher>".$this->metadata['publisher']."</dc:publisher>\n";
		if( isset($this->metadata['contributor']) && strlen($this->metadata['contributor']) > 0 )
		$content .= "   <dc:contributor>".$this->metadata['contributor']."</dc:contributor>\n";
		if( isset($this->metadata['date']) && strlen($this->metadata['date']) > 0 )
		$content .= "   <dc:date>".$this->metadata['date']."</dc:date>\n";
		if( isset($this->metadata['type']) && strlen($this->metadata['type']) > 0 )
		$content .= "   <dc:type>".$this->metadata['type']."</dc:type>\n";
		if( isset($this->metadata['format']) && strlen($this->metadata['format']) > 0 )
		$content .= "   <dc:format>".$this->metadata['format']."</dc:format>\n";
		if( isset($this->metadata['source']) && strlen($this->metadata['source']) > 0 )
		$content .= "   <dc:source>".$this->metadata['source']."</dc:source>\n";
		if( isset($this->metadata['relation']) && strlen($this->metadata['relation']) > 0 )
		$content .= "   <dc:relation>".$this->metadata['relation']."</dc:relation>\n";
		if( isset($this->metadata['coverage']) && strlen($this->metadata['coverage']) > 0 )
		$content .= "   <dc:coverage>".$this->metadata['coverage']."</dc:coverage>\n";
		if( isset($this->metadata['copyright']) && strlen($this->metadata['copyright']) > 0 )
		$content .= "   <dc:rights>".$this->metadata['copyright']."</dc:rights>\n";
		$content .= "<meta property=\"dcterms:modified\">".date('Y-m-d\TH:i:s\Z')."</meta>\n";
		$content .= "</metadata>\n";
		
		// Manifest
		$content .= "\n<manifest>\n";
		$content .= "   <item href=\"nav.xhtml\" properties=\"nav\" id=\"nav\" media-type=\"application/xhtml+xml\" />\n";
		$content .= "   <item href=\"toc.ncx\" id=\"ncx\" media-type=\"application/x-dtbncx+xml\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/cover.xhtml") )
			$content .= "   <item href=\"cover.xhtml\" id=\"cover\" media-type=\"application/xhtml+xml\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/title.xhtml") )
			$content .= "   <item href=\"title.xhtml\" id=\"title\" media-type=\"application/xhtml+xml\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/abstract.xhtml") )
			$content .= "   <item href=\"abstract.xhtml\" id=\"abstract\" media-type=\"application/xhtml+xml\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/table.xhtml") )
			$content .= "   <item href=\"table.xhtml\" id=\"table\" media-type=\"application/xhtml+xml\" />\n";

		foreach( $this->book->chapters as $key => $ch ) {
			$content .= "   <item href=\"".basename($ch['epub_fn'])."\" ".
						"id=\"chap".$ch['id']."\" ".
						"media-type=\"application/xhtml+xml\"/>\n";	
		}
		foreach( $this->metadata['css_files'] as $key => $css ) {
			$content .= "   <item href=\"scripts/".basename($css['epub_fn'])."\" ".
						"id=\"stylesheet".$key."\" ".
						"media-type=\"text/css\"/>\n";	
		}
		foreach( $this->media as $key => $im ) {
			if( $im['type'] == "image" )
			$content .= "   <item href=\"images/".basename($im['epub_fn'])."\" ".
						"id=\"img".$key."\" ".
						"media-type=\"".$im['media_type']."\"/>\n";	
			else
			$content .= "   <item href=\"audioVideo/".basename($im['epub_fn'])."\" ".
						"id=\"img".$key."\" ".
						"media-type=\"".$im['media_type']."\"/>\n";	
		}
		foreach( $this->metadata['scripts'] as $key => $sc ) {
			$content .= "   <item href=\"scripts/".basename($sc['epub_fn'])."\" ".
						"id=\"script".$key."\" ".
						"media-type=\"text/javascript\"/>\n";	
		}

		$content .= "</manifest>\n";
					
					
		// Spine
		$content .= "\n<spine toc=\"ncx\">\n";
		$content .= "   <itemref idref=\"cover\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/nav.xhtml") )
			$content .= "   <itemref idref=\"nav\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/toc.ncx") )
			$content .= "   <itemref idref=\"ncx\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/abstract.xhtml") )
			$content .= "   <itemref idref=\"abstract\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/title.xhtml") )
			$content .= "   <itemref idref=\"title\" />\n";
		if( file_exists($this->epub_dir."/OEBPS/table.xhtml") )
			$content .= "   <itemref idref=\"table\" />\n";
		
		foreach( $this->book->chapters as $key => $ch ) {
			$content .= "   <itemref idref=\"chap".$ch['id']."\" />\n";	
		}
		$content .= "</spine>\n";
		
		
		$content .= "</package>\n";	
		
		
		$dir = $this->epub_dir."/OEBPS";
		// verify directory
		if( ! file_exists($dir) ) {
			if( ! mkdir($dir) ){
				$this->error_log("Create directory error! (dir=".$dir.")");
				return false;
			}
		}
		
		// create file
		$fh = fopen($dir."/content.opf", "w");
		
		// write file
		if( ! fwrite($fh, $content) ) {
			$this->error_log("Write file error! (file=".$dir."/content.opf".")");
			$ret = false;
		}
		fclose($fh);
		return $ret;
	}
		
	
}