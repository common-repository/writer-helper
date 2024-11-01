<?php
/*********************************************
 **    EPUB Generator common functions      **
 *********************************************/
/***** Common constants *****/
// 
const EpubGen_Summary = "Sommaire";
const EpubGen_Resume  = "Résumé";

// Chapter Types
const EpubGen_CT_COPYRIGHT  = 0;
const EpubGen_CT_DEDICATION = 1;
const EpubGen_CT_PREFACE    = 2;
const EpubGen_CT_FOREWORD   = 3;
const EpubGen_CT_INTRO      = 4;
const EpubGen_CT_CHAPTER    = 5;
const EpubGen_CT_CONCLUSION = 6;
const EpubGen_CT_EPILOGUE   = 7;
const EpubGen_CT_NOTE       = 8;
const EpubGen_CT_GLOSSARY   = 9;
const EpubGen_CT_CONTRIBUTOR= 10;
const EpubGen_CT_INDEX      = 11;
const EpubGen_CT_BIBLIO     = 12;

global $eg_chapterTypes;
$eg_chapterTypes = array (EpubGen_CT_COPYRIGHT   => "Copyright"   ,
						  EpubGen_CT_DEDICATION  => "Dedication"  ,
						  EpubGen_CT_PREFACE     => "Preface"     ,
						  EpubGen_CT_FOREWORD    => "Foreword"    ,
						  EpubGen_CT_INTRO       => "Introduction",
						  EpubGen_CT_CHAPTER     => "Chapter"     ,
						  EpubGen_CT_CONCLUSION  => "Conclusion"  ,
						  EpubGen_CT_EPILOGUE    => "Epilogue"    ,
						  EpubGen_CT_NOTE        => "Annotations" ,
						  EpubGen_CT_GLOSSARY    => "Glossary"    ,
						  EpubGen_CT_CONTRIBUTOR => "Contributors",
						  EpubGen_CT_INDEX       => "Index"       ,
						  EpubGen_CT_BIBLIO      => "Bibliography");

/*
 * Get mimetype of a file
 */
function epubgen_getMimeType($file, $type = "") {
	$mimetype = "";
	
	if( function_exists('mime_content_type') )
		$mimetype = mime_content_type($file);
	else {
		$mimetype = $type.'/'.substr($file, strrpos($file, '.')+1);
	}
	
	return sanitize_mime_type($mimetype);
}

/***********************************************/	
/* List files and directories from a directory */
/***********************************************/	
function epubgen_listDirectory($dir, $depth = 0, &$nb_dir = 0, &$nb_files = 0) {
	$list = array();
	if( is_dir($dir) ) {
		// for each item in directory
		foreach( scandir($dir) as $item ) {
			if( $item != "." && $item != ".." ) {
				
				if( is_dir($dir."/".$item) ) {
					$nb_dir = $nb_dir + 1;
					$list[] = array(
								'type'   => "d",
								'item'   => $item,
								'name'   => $item,
								'ext'    => "",
								'fitem'  => $dir."/".$item,
								'fdir'   => $dir."/".$item,
								'size'   => 0,
								'depth'  => $depth,
								'fmode'  => stat($dir."/".$item)[2], 
								'fuser'  => stat($dir."/".$item)[4], 
								'fgroup' => stat($dir."/".$item)[5], 
								'fdate'  => stat($dir."/".$item)[9]);
					$list = array_merge($list, epubgen_listDirectory($dir."/".$item, $depth+1, $nb_dir, $nb_files));
				}
				else {
					$ext  = strtolower(substr($item, strrpos($item, ".") ));
					$name = substr($item, 0, strrpos($item, ".") );
					$nb_files = $nb_files + 1;
					
					// name, file mode, file userid, file groupid, file modification date
					$list[] = array(
								'type'   => "f",
								'item'   => $item,
								'name'   => $name,
								'ext'    => $ext,
								'fitem'  => $dir."/".$item,
								'fdir'   => $dir,
								'size'   => filesize($dir."/".$item),
								'depth'  => $depth,
								'fmode'  => stat($dir."/".$item)[2], 
								'fuser'  => stat($dir."/".$item)[4], 
								'fgroup' => stat($dir."/".$item)[5], 
								'fdate'  => stat($dir."/".$item)[9]);
				}
				
			}
		}
	}
	
	rsort($list);
	return $list;	
}



/*************************************/	
/* Delete a directory                */
/*************************************/	
function epubgen_deleteDir($dir) {

	if( is_dir($dir) ) {
		// for each item in directory
		foreach( scandir($dir) as $item ) {
			if( $item != "." && $item != ".." ) {
				if( is_dir($dir."/".$item) ) {
					if( ! epubgen_deleteDir($dir."/".$item) )
						return false;
					
				} else {
//error_log('Delete file: '.$dir.'/'.$item);
					if( ! unlink($dir."/".$item) )
						return false;
				}
			}
		}
		
//error_log('Delete directory: '.$dir);
		if( ! rmdir($dir) )
			return false;
	} else {
		if( file_exists($dir) ) {
//error_log('Delete file: '.$dir.'/'.$item);
			if( ! unlink($dir) )
				return false;
		}
	}
	return true;
}


/*************************************/	
/* Create a ZIP file from a directory */
/*************************************/	
function epubgen_zip_a_dir($dir, $myzip, $overwrite = false, 
					&$nbfiles = 1, &$myfiles = array()) {
									
	$myfiles = array();
	$mylist  = epubgen_listDirectory($dir);
	$mydir   = $dir;
	$zip     = new ZipArchive;

	if( file_exists($myzip) && $overwrite ) 
		if( ! unlink($myzip)) 
			return false;
	
	if( file_exists($myzip) )
		$res = $zip->open($myzip, ZipArchive::OVERWRITE);
	else
		$res = $zip->open($myzip, ZipArchive::CREATE);

	if ($res === TRUE) {
		foreach( $mylist as $item) {
			if( $item['type'] != "d" ) {
//error_log('ZIP a dir :: file '.$item['fitem']);
				$new_file = substr(explode($mydir, $item['fitem'])[1],1);
				if( ! $zip->addFile($item['fitem'], $new_file) ) {
					$zip->close();
					error_log('Error adding file ('.$item['fitem'].') to zip ('.$myzip.')');
					return false;
				}
			} 
		}
		$zip->close();
	} else {
		error_log('Error openning zip ('.$myzip.')');
		return false;
	}
	return true;
}
?>