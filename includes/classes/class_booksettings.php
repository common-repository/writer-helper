<?php
/************************************
 **      Book Settings class       **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_book.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_category.php");

class WH_BookSettings {
	
	/* Category fields */
	const CategoryElement = WTRH_CAT_BOOKSETTINGS;
	const CatTitle_display= "DisplaySettings";
	const CatTitle_status = "Statuses";
	
	/* Metadata fields */
	const MetaObj   	  = "Book";
	const MetaKey_display = "DisplaySettings";
	const MetaKey_status  = "Statuses";
	
	/* Book settings constantes */
	const BI_Title        = "Title";
	const BI_Resume       = "Resume";
	const BI_Type         = "Type";
	const BI_Cover        = "Cover";
	const BI_Status       = "Book Status";
	const BI_CustomStatus = "Customised Status";
	const BI_Authors      = "Authors";
	const BI_Editors      = "Editors";
	const BI_PubDate      = "Publication Date";
	const BI_SaleUrl      = "Sale Url";
	const BI_PromoUrl     = "Promotion Url";
	const BI_OpinionUrl   = "Opinion Url";
	const BI_Isbn         = "Isbn";
	
	const BookInfoList    = array(WH_BookSettings::BI_Title, WH_BookSettings::BI_Resume, 
	WH_BookSettings::BI_Type, WH_BookSettings::BI_Cover, WH_BookSettings::BI_Status, 
	WH_BookSettings::BI_CustomStatus, WH_BookSettings::BI_Authors, WH_BookSettings::BI_Editors, 
	WH_BookSettings::BI_PubDate, WH_BookSettings::BI_SaleUrl, WH_BookSettings::BI_PromoUrl, 
	WH_BookSettings::BI_OpinionUrl, WH_BookSettings::BI_Isbn);
	
	/* default book settings */
	const default_display  = array( WH_BookSettings::BI_Cover,
	                                WH_BookSettings::BI_Title,
								    WH_BookSettings::BI_PubDate,
								    WH_BookSettings::BI_Authors,
								    WH_BookSettings::BI_Resume);	
	const minimum_display  = array( WH_BookSettings::BI_Title);	

	
	public $book_info; 			/* array of ordered book info to display */
	public $book_labels; 		/* array of labels to display */
	public $book_statuses; 		/* array of statuses */
		
	public  $book_id; 			/* int */
	private $catDisplay_id;
	private $catStatus_id;
	private $metaDisplay_id;  
	private $metaStatus_id;  

	public $isOk;       /* init ok ? */
	
	public $settings;	/* array to store in DB */
	public $statuses;	/* array to store in DB */
	
	private $DB_Category_display;    /* object if general display settings */
	private $DB_Category_status;     /* object if default statuses */
	private $DB_Metadata_display;    /* object if one book display settings */
	private $DB_Metadata_status;     /* object if one book's statuses */
		
	/**
	* Class constructor 
	*  $args : array()
	*			'book_id'       				 => int
	*			'book_info'     				 => array
	*			'book_labels'     				 => array
	*			'book_statuses'  				 => array
	**/
    public function __construct($id, $args = array(), $cascade = false)    {
		$this->book_id      = isset($args['book_id'])      ? (int)$args['book_id']:(int)$id;
		$this->book_info    = isset($args['book_info'])    ? (array)$args['book_info']:array();
		$this->book_labels  = isset($args['book_labels'])  ? (array)$args['book_labels']:array();
		$this->book_statuses= isset($args['book_statuses'])? (array)$args['book_statuses']:array();
		
		$this->isOk         = false;
		
		$this->metaDisplay_id       = 0;
		$this->metaStatus_id        = 0;
		$this->catDisplay_id        = 0;
		$this->catStatus_id         = 0;
		$this->DB_Category_display  = null;
		$this->DB_Category_status   = null;
		$this->DB_Metadata_display  = null;
		$this->DB_Metadata_status   = null;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
			return false;
		} else {
			$this->get_BookSettings($id);
		}
		
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = false;

		// Save Display Settings
		if( $this->metaDisplay_id != 0 ) { // save Metadata 
		
			$result = $this->DB_Metadata_display->save();
			if( $result ) // copy id
				$this->metaDisplay_id = $this->DB_Metadata_display->id;
				
		} else {
			if( $this->catDisplay_id != 0 ) { // save Category 
				$result = $this->DB_Category_display->save();
				if( $result ) // copy id
					$this->catDisplay_id = $this->DB_Category_display->id;
			}
		}
		
		// Save Statuses
		if( $this->metaStatus_id != 0 ) { // save Metadata 
		
			$result = $this->DB_Metadata_status->save();
			if( $result ) // copy id
				$this->metaStatus_id = $this->DB_Metadata_status->id;
				
		} else {
			if( $this->catStatus_id != 0 ) { // save Category 
				$result = $this->DB_Category_status->save();
				if( $result ) // copy id
					$this->catStatus_id = $this->DB_Category_status->id;
			}
		}
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$ret = true;
		
		if( $this->metaDisplay_id != 0 )
			$ret = $this->DB_Metadata_display->delete();
		if( $this->catDisplay_id != 0 )
			$ret = $this->DB_Category_display->delete();
		
		if( $this->metaStatus_id != 0 )
			$ret = $this->DB_Metadata_status->delete();
		if( $this->catStatus_id != 0 )
			$ret = $this->DB_Category_status->delete();
		
		return $ret;
	}
	
	/* Read DB */
	private function get_BookSettings($id) {
		
		// Get general Book Settings (in Category table)
		$this->get_DefaultBookSettings();
		
		// If Book id
		if( $id != 0 ) { 
				
			// Get Display Settings
			$result = WH_Metadata::getAll_OneMetadata_ofObject(
							WH_BookSettings::MetaObj, 
							$id, 
							WH_BookSettings::MetaKey_display);
			
			if( count ($result) == 1 ) {

				$this->DB_Metadata_display = $result[0];

				$this->metaDisplay_id = $this->DB_Metadata_display->id; 
				$this->catDisplay_id  = 0;
				$this->settings       = json_decode($this->DB_Metadata_display->meta_value, true);
				
				$this->book_info      = $this->settings['book_info'];
				$this->book_labels    = $this->settings['book_labels'];
				
			} else { // No display settings

				$this->metaDisplay_id = -1; /* to save Book Metadata */
				$this->catDisplay_id  = 0;  // no saving Category
			}
			
			// Get Statuses
			$result = WH_Metadata::getAll_OneMetadata_ofObject(
							WH_BookSettings::MetaObj, 
							$id, 
							WH_BookSettings::MetaKey_status);
			
			if( count ($result) == 1 ) {

				$this->DB_Metadata_status = $result[0];

				$this->metaStatus_id = $this->DB_Metadata_status->id; 
				$this->catStatus_id  = 0;
				$this->statuses      = json_decode($this->DB_Metadata_status->meta_value, true);
				
				$this->book_statuses = $this->statuses;
				
				$this->isOk = true;
				
			} else { // No statuses

				$this->metaStatus_id = -1; /* to save Book Metadata */
				$this->catStatus_id  = 0;  // no saving Category
			}
		}
		
		$this->isOk = true;
	}

	
	/* Read DB */
	private function get_DefaultBookSettings() {
		
		// Get general Book Settings (in Category table)
		$this->settings = array();
		$this->statuses = array();
		$categories     = WH_BookSettings::get_GeneralBookSettings();
		
		// If Display Settings not found => create them
		if( ! isset($categories[WH_BookSettings::CatTitle_display]) ) {
			
			$this->metaDisplay_id = 0;
			$this->catDisplay_id  = -1; /* to get a proper Category Id when save occurs */ 

			$this->book_info    = WH_BookSettings::default_display;
			$this->book_labels  = array("nextChapter"  => array("label" => __('Next chapter','wtr_helper'), "style" => ""),
										"prevChapter"  => array("label" => __('Previous chapter','wtr_helper'), "style" => ""),
										"customStatus" => array("label" => "", "style" => ""),
										"bookEnding"   => array("label" => __('The End','wtr_helper'), "style" => ""),
										"readMore"     => array("label" => __('To read more, contact the author','wtr_helper'), "style" => ""),
										"nextChapterUn"=> array("label" => __('Next chapter not yet published','wtr_helper'), "style" => "")
										);
										
			$this->settings     = array();
			
			$this->isOk = $this->save();
			
		} else {
			
			$this->metaDisplay_id 	= 0;
			$this->catDisplay_id 	= $categories[WH_BookSettings::CatTitle_display]['cat_id'];
			$this->settings         = json_decode($categories[WH_BookSettings::CatTitle_display]['description'], true);

			$this->book_info    = $this->settings['book_info'];
			$this->book_labels  = $this->settings['book_labels'];
				
			$this->isOk = true;
		}
		
		
		// If Statuses not found => create them
		if( ! isset($categories[WH_BookSettings::CatTitle_status]) ) {
			
			$this->metaStatus_id = 0;
			$this->catStatus_id  = -1; /* to get a proper Category Id when save occurs */ 

			$this->book_statuses = WH_Status::DEFAULT_STATUSES;
										
			$this->statuses      = array();
			
			$this->isOk = $this->save();
			
		} else {
			
			$this->metaStatus_id	= 0;
			$this->catStatus_id 	= $categories[WH_BookSettings::CatTitle_status]['cat_id'];
			$this->statuses         = json_decode($categories[WH_BookSettings::CatTitle_status]['description'], true);
			
			$this->book_statuses    = $this->statuses;
				
			$this->isOk = true;
		}
		
	}

	
	/* Update DB object */
	private function updateDB_Object() {
		$this->settings['book_info']   = $this->book_info          ; 
		$this->settings['book_labels'] = $this->book_labels        ; 
		$this->statuses                = $this->book_statuses      ; 

		// Update Book Settings Metadata
		if( $this->metaDisplay_id != 0 ) {
			if( $this->DB_Metadata_display == null ) {
				$this->DB_Metadata_display = new WH_Metadata(0,array(
				                                        'obj_id'     => $this->book_id,
				                                        'meta_obj'   => WH_BookSettings::MetaObj,
				                                        'meta_key'   => WH_BookSettings::MetaKey_display,
				                                        'meta_value' => '',
				                                        'user_id'    => 0));
			}
			$this->DB_Metadata_display->meta_value = json_encode($this->settings);
		}

		// Update Book Settings Category
		if( $this->catDisplay_id != 0 ) {
			if( $this->DB_Category_display == null ) {
				$this->DB_Category_display = new WH_Category(0,array(
				                                        'element'     => WH_BookSettings::CategoryElement,
				                                        'number'      => 0,
				                                        'title'       => WH_BookSettings::CatTitle_display,
				                                        'description' => '',
				                                        'parent_id'   => 0));
			}
			$this->DB_Category_display->description = json_encode($this->settings);
		}


		// Update Book Statuses Metadata
		if( $this->metaStatus_id != 0 ) {
			if( $this->DB_Metadata_status == null ) {
				$this->DB_Metadata_status = new WH_Metadata(0,array(
				                                        'obj_id'     => $this->book_id,
				                                        'meta_obj'   => WH_BookSettings::MetaObj,
				                                        'meta_key'   => WH_BookSettings::MetaKey_status,
				                                        'meta_value' => '',
				                                        'user_id'    => 0));
			}
			$this->DB_Metadata_status->meta_value = json_encode($this->statuses);
		}

		// Update Book Statuses Category
		if( $this->catStatus_id != 0 ) {
			if( $this->DB_Category_status == null ) {
				$this->DB_Category_status = new WH_Category(0,array(
				                                        'element'     => WH_BookSettings::CategoryElement,
				                                        'number'      => 0,
				                                        'title'       => WH_BookSettings::CatTitle_status,
				                                        'description' => '',
				                                        'parent_id'   => 0));
			}
			$this->DB_Category_status->description = json_encode($this->statuses);
		}

	}

	
	/* Update settings via a json string */
	public function updateSettings($str)  {
		
		$ret = true;
		
		$settings = json_decode($str, true);
//wtr_info_log(__METHOD__, "string=".$str);
//wtr_info_log(__METHOD__, "settings=".print_r($settings, true));
		
		if( isset($settings['book_info']) )
			$this->set_BookInfo(explode(",", $settings['book_info']));
		
		if( isset($settings['nextChapter_label']) )
			$this->set_NextChapterLabel($settings['nextChapter_label']);
		if( isset($settings['nextChapter_style']) )
			$this->set_NextChapterStyle($settings['nextChapter_style']);
		
		if( isset($settings['nextChapterU_label']) )
			$this->set_NextChapterUnpublishedLabel($settings['nextChapterU_label']);
		if( isset($settings['nextChapterU_style']) )
			$this->set_NextChapterUnpublishedStyle($settings['nextChapterU_style']);
		
		if( isset($settings['prevChapter_label']) )
			$this->set_PreviousChapterLabel($settings['prevChapter_label']);
		if( isset($settings['prevChapter_style']) )
			$this->set_PreviousChapterStyle($settings['prevChapter_style']);
		
		if( isset($settings['custom_status_label']) )
			$this->set_CustomStatusLabel($settings['custom_status_label']);
		if( isset($settings['custom_status_style']) )
			$this->set_CustomStatusStyle($settings['custom_status_style']);
		
		if( isset($settings['book_ending_label']) )
			$this->set_BookEndingLabel($settings['book_ending_label']);
		if( isset($settings['book_ending_style']) )
			$this->set_BookEndingStyle($settings['book_ending_style']);
		
		if( isset($settings['read_more_label']) )
			$this->set_ReadMoreLabel($settings['read_more_label']);
		if( isset($settings['read_more_style']) )
			$this->set_ReadMoreStyle($settings['read_more_style']);
		
		if( isset($settings['book_statuses']) )
			$this->book_statuses = $settings['book_statuses'];
		
		return $ret;
	}
	
	
	/* Get Book Info to display */
	public function get_BookInfo() {
		return $this->book_info;
	}
	/* Set Book Info to display */
	public function set_BookInfo($bi) {
		if( is_array($bi) )
			$this->book_info = $bi;
		else
			$this->book_info = array();
	}
	
	/* Get "next chapter" label */
	public function get_NextChapterLabel() {
		return $this->book_labels["nextChapter"]["label"];
	}
	/* Set "next chapter" label */
	public function set_NextChapterLabel($str) {
		$this->book_labels["nextChapter"]["label"] = $str;
	}
	/* Get "next chapter" style */
	public function get_NextChapterStyle() {
		return $this->book_labels["nextChapter"]["style"];
	}
	/* Set "next chapter" style */
	public function set_NextChapterStyle($str) {
		$this->book_labels["nextChapter"]["style"] = $str;
	}
	
	/* Get "next chapter unpublished" label */
	public function get_NextChapterUnpublishedLabel() {
		return $this->book_labels["nextChapterUn"]["label"];
	}
	/* Set "next chapter unpublished" label */
	public function set_NextChapterUnpublishedLabel($str) {
		$this->book_labels["nextChapterUn"]["label"] = $str;
	}
	/* Get "next chapter unpublished" style */
	public function get_NextChapterUnpublishedStyle() {
		return $this->book_labels["nextChapterUn"]["style"];
	}
	/* Set "next chapter unpublished" style */
	public function set_NextChapterUnpublishedStyle($str) {
		$this->book_labels["nextChapterUn"]["style"] = $str;
	}
	
	/* Get "prev chapter" label */
	public function get_PreviousChapterLabel() {
		return $this->book_labels["prevChapter"]["label"];
	}
	/* Set "prev chapter" label */
	public function set_PreviousChapterLabel($str) {
		$this->book_labels["prevChapter"]["label"] = $str;
	}
	/* Get "prev chapter" style */
	public function get_PreviousChapterStyle() {
		return $this->book_labels["prevChapter"]["style"];
	}
	/* Set "prev chapter" style */
	public function set_PreviousChapterStyle($str) {
		$this->book_labels["prevChapter"]["style"] = $str;
	}
	
	/* Get "custom status" label */
	public function get_CustomStatusLabel() {
		return $this->book_labels["customStatus"]["label"];
	}
	/* Set "custom status" label */
	public function set_CustomStatusLabel($str) {
		$this->book_labels["customStatus"]["label"] = $str;
	}
	/* Get "custom status" style */
	public function get_CustomStatusStyle() {
		return $this->book_labels["customStatus"]["style"];
	}
	/* Set "custom status" style */
	public function set_CustomStatusStyle($str) {
		$this->book_labels["customStatus"]["style"] = $str;
	}
	
	/* Get "book ending" label */
	public function get_BookEndingLabel() {
		return $this->book_labels["bookEnding"]["label"];
	}
	/* Set "book ending" label */
	public function set_BookEndingLabel($str) {
		$this->book_labels["bookEnding"]["label"] = $str;
	}
	/* Get "book ending" style */
	public function get_BookEndingStyle() {
		return $this->book_labels["bookEnding"]["style"];
	}
	/* Set "book ending" style */
	public function set_BookEndingStyle($str) {
		$this->book_labels["bookEnding"]["style"] = $str;
	}
	
	/* Get "read more" label */
	public function get_ReadMoreLabel() {
		return $this->book_labels["readMore"]["label"];
	}
	/* Set "read more" label */
	public function set_ReadMoreLabel($str) {
		$this->book_labels["readMore"]["label"] = $str;
	}
	/* Get "read more" style */
	public function get_ReadMoreStyle() {
		return $this->book_labels["readMore"]["style"];
	}
	/* Set "read more" style */
	public function set_ReadMoreStyle($str) {
		$this->book_labels["readMore"]["style"] = $str;
	}
	
	
	
	/* Get Books Settings */
	public static function get_GeneralBookSettings() {
		$mySettings  = array();
		$dbSettings  = WH_Category::get_BooksSettings();
		
		foreach( $dbSettings as $s ) {
			$mySettings[$s->title] = array('cat_id'      => $s->id, 
										   'description' => $s->description);
		}
		                  
		return $mySettings;
	}
	
	/* Delete all Books' display settings and statuses */
	public static function deleteAllBooksSettings() {
		$result = true;
		$books_ds_meta = WH_DB_Metadata::getAllDB_Metadatas(array(
				                                        'meta_obj'   => WH_BookSettings::MetaObj,
				                                        'meta_key'   => WH_BookSettings::MetaKey_display
														));
		$books_st_meta = WH_DB_Metadata::getAllDB_Metadatas(array(
				                                        'meta_obj'   => WH_BookSettings::MetaObj,
				                                        'meta_key'   => WH_BookSettings::MetaKey_status
														));
		$settings = array_merge($books_ds_meta, $books_st_meta);
		foreach( $settings as $meta ) {
			$obj = new WH_Metadata($meta->meta_id);
			if( $obj->isOk ) {
				if( ! $obj->delete() ) {
					wtr_error_log(__METHOD__,'Error while deleting Metadata: '.print_r($meta,true));
					$result = false;
				}
			} else {
				wtr_error_log(__METHOD__,'Error while reading Metadata: '.print_r($meta,true));
				$result = false;
			}
		}
		return $result;
	}
}
?>