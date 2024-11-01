<?php
/************************************
 **          Chapter class         **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_stat_author.php");

class WH_Stat_Author {
	
	public $user_id;
	
	public $count_books;
	public $count_gameBooks;
	public $count_chapters;
	public $count_scenes;
	public $count_status_books;
	public $count_status_chapters;
	public $count_status_scenes;
	
	public $word_count_books;
	
	public $isOk;       /* init ok ? */
	
	/**
	* Class constructor 
	*  $args : array()
	*			'user_id' => int
	**/
    public function __construct($args = array())    {
		$this->user_id        = isset($args['user_id'])         ? (int)$args['user_id']:0;
		$this->count_books           = 0;
		$this->count_gameBooks       = 0;
		$this->count_chapters        = 0;
		$this->count_scenes          = 0;
		$this->count_status_books    = 0;
		$this->count_status_chapters = 0;
		$this->count_status_scenes   = 0;
		$this->word_count_books      = 0;
		
		$this->isOk           = false;
		
		if( WH_User::userExists($this->user_id, WTRH_ROLE_AUTHOR) )
			$this->isOk = true;
	}

	
	/* Get Chapter's Scenes */
	public function calc_stats() {
		$this->count_books           = WH_DB_Stat_Author::getDB_CountBooks($this->user_id);
		$this->count_gameBooks       = WH_DB_Stat_Author::getDB_CountGameBooks($this->user_id);
		$this->count_chapters        = WH_DB_Stat_Author::getDB_CountChapters($this->user_id);
		$this->count_scenes          = WH_DB_Stat_Author::getDB_CountScenes($this->user_id);

		$this->count_status_books    = WH_DB_Stat_Author::getDB_CountBookStatus($this->user_id);
		$this->count_status_chapters = WH_DB_Stat_Author::getDB_CountChapterStatus($this->user_id);
		$this->count_status_scenes   = WH_DB_Stat_Author::getDB_CountSceneStatus($this->user_id);

		$this->word_count_books      = WH_DB_Stat_Author::getDB_WordCount_byBook($this->user_id);
	}
	
}
?>