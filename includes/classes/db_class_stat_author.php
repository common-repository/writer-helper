<?php
/************************************
 **      User class      **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_book.php");

class WH_DB_Stat_Author {

	// count books, chapters, scenes
    const countBooks = 'SELECT count(*) FROM '.
							  '   %1$s'.WH_DB_Book::tableName.' a'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' b'.
							  ' WHERE b.user_id=%2$s'.
							  '   AND b.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND b.meta_key="'.WH_DB_Book::meta_key_author.'"'.
							  '   AND a.id=b.obj_id';
    const countGameBooks = 'SELECT count(*) FROM '.
							  '   %1$s'.WH_DB_Book::tableName.' a'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' b'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' c'.
							  ' WHERE b.user_id=%2$s'.
							  '   AND b.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND b.meta_key="'.WH_DB_Book::meta_key_author.'"'.
							  '   AND c.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND c.meta_key="'.WH_DB_Book::meta_key_game.'"'.
							  '   AND a.id=b.obj_id'.
							  '   AND a.id=c.obj_id';
    const countChapters = 'SELECT count(*) FROM '.
							  '   %1$s'.WH_DB_Book::tableName.' a'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' b'.
							  ' , %1$s'.WH_DB_Chapter::tableName.' c'.
							  ' WHERE b.user_id=%2$s'.
							  '   AND b.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND b.meta_key="'.WH_DB_Book::meta_key_author.'"'.
							  '   AND a.id=b.obj_id'.
							  '   AND a.id=c.book_id';
    const countScenes = 'SELECT count(*) FROM '.
							  '   %1$s'.WH_DB_Book::tableName.' a'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' b'.
							  ' , %1$s'.WH_DB_Chapter::tableName.' c'.
							  ' , %1$s'.WH_DB_Scene::tableName.' d'.
							  ' WHERE b.user_id=%2$s'.
							  '   AND b.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND b.meta_key="'.WH_DB_Book::meta_key_author.'"'.
							  '   AND a.id=b.obj_id'.
							  '   AND a.id=c.book_id'.
							  '   AND c.id=d.chapter_id';
	
	// count by status
    const countBookStatus = 'SELECT a.status, count(*) FROM '.
							  '   %1$s'.WH_DB_Book::tableName.' a'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' b'.
							  ' WHERE b.user_id=%2$s'.
							  '   AND b.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND b.meta_key="'.WH_DB_Book::meta_key_author.'"'.
							  '   AND a.id=b.obj_id'.
							  ' GROUP BY 1 '.
							  ' ORDER BY 1 ASC';
    const countChapterStatus = 'SELECT c.status, count(*) FROM '.
							  '   %1$s'.WH_DB_Book::tableName.' a'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' b'.
							  ' , %1$s'.WH_DB_Chapter::tableName.' c'.
							  ' WHERE b.user_id=%2$s'.
							  '   AND b.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND b.meta_key="'.WH_DB_Book::meta_key_author.'"'.
							  '   AND a.id=b.obj_id'.
							  '   AND a.id=c.book_id'.
							  ' GROUP BY 1 '.
							  ' ORDER BY 1 ASC';
    const countSceneStatus = 'SELECT d.status, count(*) FROM '.
							  '   %1$s'.WH_DB_Book::tableName.' a'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' b'.
							  ' , %1$s'.WH_DB_Chapter::tableName.' c'.
							  ' , %1$s'.WH_DB_Scene::tableName.' d'.
							  ' WHERE b.user_id=%2$s'.
							  '   AND b.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND b.meta_key="'.WH_DB_Book::meta_key_author.'"'.
							  '   AND a.id=b.obj_id'.
							  '   AND a.id=c.book_id'.
							  '   AND c.id=d.chapter_id'.
							  ' GROUP BY 1 '.
							  ' ORDER BY 1 ASC';
	
	// count words
    const countWordsByBook = 'SELECT a.id, sum(d.word_count) FROM '.
							  '   %1$s'.WH_DB_Book::tableName.' a'.
							  ' , %1$s'.WH_DB_Metadata::tableName.' b'.
							  ' , %1$s'.WH_DB_Chapter::tableName.' c'.
							  ' , %1$s'.WH_DB_Scene::tableName.' d'.
							  ' WHERE b.user_id=%2$s'.
							  '   AND b.meta_obj="'.WH_DB_Book::meta_obj_book.'"'.
							  '   AND b.meta_key="'.WH_DB_Book::meta_key_author.'"'.
							  '   AND a.id=b.obj_id'.
							  '   AND a.id=c.book_id'.
							  '   AND c.id=d.chapter_id'.
							  ' GROUP BY 1 '.
							  ' ORDER BY 1 ASC';
	
	public $isOk;
	
	/**
	* Class constructor 
	*  $args : array()
	*			'user_id'   => int
	*			'user_name' => string
	*			'role'      => string
	*			'book_id'   => int
	**/
    public function __construct()    {
		
		$this->isOk = true;
		
	}

	/* Count books */
	public static function getDB_CountBooks($user_id) {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Stat_Author::countBooks, $wpdb->prefix, $user_id);		
		$result   = wtr_getResults($query, array(), ARRAY_N);
		return $result[0][0];
	}
	/* Count Game Books */
	public static function getDB_CountGameBooks($user_id) {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Stat_Author::countGameBooks, $wpdb->prefix, $user_id);		
		$result   = wtr_getResults($query, array(), ARRAY_N);
		return $result[0][0];
	}
	/* Count chapters */
	public static function getDB_CountChapters($user_id) {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Stat_Author::countChapters, $wpdb->prefix, $user_id);		
		$result   = wtr_getResults($query, array(), ARRAY_N);
		return $result[0][0];
	}
	/* Count scenes */
	public static function getDB_CountScenes($user_id) {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Stat_Author::countScenes, $wpdb->prefix, $user_id);		
		$result   = wtr_getResults($query, array(), ARRAY_N);
		return $result[0][0];
	}

	/* Count books by status */
	public static function getDB_CountBookStatus($user_id) {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Stat_Author::countBookStatus, $wpdb->prefix, $user_id);		
		$result   = wtr_getResults($query, array(), ARRAY_N);
		return $result;
	}
	/* Count chapters by status */
	public static function getDB_CountChapterStatus($user_id) {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Stat_Author::countChapterStatus, $wpdb->prefix, $user_id);		
		$result   = wtr_getResults($query, array(), ARRAY_N);
		return $result;
	}
	/* Count scenes by status */
	public static function getDB_CountSceneStatus($user_id) {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Stat_Author::countSceneStatus, $wpdb->prefix, $user_id);		
		$result   = wtr_getResults($query, array(), ARRAY_N);
		return $result;
	}

	/* Count words by book */
	public static function getDB_WordCount_byBook($user_id) {
		global $wpdb;
		$result   = false;
		$query    = sprintf(WH_DB_Stat_Author::countWordsByBook, $wpdb->prefix, $user_id);		
		$result   = wtr_getResults($query, array(), ARRAY_N);
		return $result;
	}
}
?>