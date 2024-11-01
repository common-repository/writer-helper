<?php
/************************************
 **      EPUB XHTML File class     **
 ************************************/

class EPUB_Log {

	public function info_log($msg) {
		error_log(get_class($this)." :: INFO :: ".$msg);
	}
	public function error_log($msg) {
		error_log(get_class($this)." :: ERROR :: ".$msg);
	}
}