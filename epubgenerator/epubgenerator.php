<?php
/************************************
 **      EPUB Generator class      **
 ************************************/

//Path to this directory
if ( !defined('EPUB_DIR') ){
	define('EPUB_DIR', dirname(__FILE__));
}
//Path to the include directory
if ( !defined('EPUB_INCLUDE_DIR') ){
	define('EPUB_INCLUDE_DIR', EPUB_DIR . "/include");
}
//Path to the include directory
if ( !defined('EPUB_CSS_DIR') ){
	define('EPUB_CSS_DIR', EPUB_DIR . "/css");
}

include_once(EPUB_INCLUDE_DIR."/epub_generator.class.php");

?>