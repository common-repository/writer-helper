<?php
/************************************
 **      Status class       **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");

class WH_Status {
	
	/******** Constants ********/
	const DEF_DELETE= -1; // Definitive delete
	const DRAFT     = 00;
	const TOEDIT    = 10;
	const EDITING   = 20;
	const EDITED    = 30;
	const TOPUBLISH = 40;
	const HIDDEN    = 45; // not published 
	const PREVIEW   = 50; // Published for selected & registered users
	const PUBLISHED = 60; // Published for all registered users & visitors
	const ARCHIVED  = 70; // Archived and published
	const ARC_UNP   = 80; // Archived and unpublished
	const TRASHED   = 90;
	
	// ALL possible statuses
	const ALL_STATUSES 		= array(WH_Status::DRAFT    ,
									WH_Status::TOEDIT   ,
									WH_Status::EDITING  ,
									WH_Status::EDITED   ,
									WH_Status::TOPUBLISH,
									WH_Status::HIDDEN   ,
									WH_Status::PREVIEW  ,
									WH_Status::PUBLISHED,
									WH_Status::ARCHIVED ,
									WH_Status::ARC_UNP  ,
									WH_Status::TRASHED  );	
	const STATUSES_NAME	   = array (WH_Status::DRAFT     => "Draft"    ,
									WH_Status::TOEDIT    => "ToEdit"   ,
									WH_Status::EDITING   => "Editing"  ,
									WH_Status::EDITED    => "Edited"   ,
									WH_Status::TOPUBLISH => "ToPublish",
									WH_Status::HIDDEN    => "Hidden"   ,
									WH_Status::PREVIEW   => "Preview"  ,
									WH_Status::PUBLISHED => "Published",
									WH_Status::ARCHIVED  => "Archived & Published" ,
									WH_Status::ARC_UNP   => "Archived & Unpublished"  ,
									WH_Status::TRASHED   => "Trashed"  );
	const STATUSES_STYLE   = array (
					WH_Status::DRAFT     => "background-color:brown;color:white;" ,
					WH_Status::TOEDIT    => "background-color:lightblue;"   ,
					WH_Status::EDITING   => "background-color:lightsalmon;"  ,
					WH_Status::EDITED    => "background-color:green;color:white;" ,
					WH_Status::TOPUBLISH => "background-color:greenyellow;",
					WH_Status::HIDDEN    => "background-color:white;color:dimgrey;",
					WH_Status::PREVIEW   => "background-color:white;color:#00cc00;"  ,
					WH_Status::PUBLISHED => "background-color: gold;",
					WH_Status::ARCHIVED  => "background-color:lightgray;color:#ffff99;" ,
					WH_Status::ARC_UNP   => "background-color:lightgray;color:brown;"  ,
					WH_Status::TRASHED   => "background-color:lightgray;"  );


	// Statuses by default
	const DEFAULT_STATUSES 	= array(WH_Status::DRAFT    ,
									WH_Status::TOEDIT   ,
									WH_Status::EDITING  ,
									WH_Status::EDITED   ,
									WH_Status::TOPUBLISH,
									WH_Status::PUBLISHED,
									WH_Status::TRASHED  );	
	// book possible statuses
	const BOOK_STATUSES 	= array(WH_Status::DRAFT    ,
									WH_Status::TOEDIT   ,
									WH_Status::EDITING  ,
									WH_Status::EDITED   ,
									WH_Status::TOPUBLISH,
									WH_Status::HIDDEN   ,
									WH_Status::PREVIEW  ,
									WH_Status::PUBLISHED,
									WH_Status::ARCHIVED ,
									WH_Status::ARC_UNP  ,
									WH_Status::TRASHED  );	
	// chapter possible statuses
	const CHAPTER_STATUSES = array( WH_Status::DRAFT    ,
									WH_Status::TOEDIT   ,
									WH_Status::EDITING  ,
									WH_Status::EDITED   ,
									WH_Status::TOPUBLISH,
									WH_Status::HIDDEN   ,
									WH_Status::PREVIEW  ,
									WH_Status::PUBLISHED,
									WH_Status::ARCHIVED ,
									WH_Status::ARC_UNP  ,
									WH_Status::TRASHED  );	
	// scene possible statuses
	const SCENE_STATUSES   = array( WH_Status::DRAFT    ,
									WH_Status::TOEDIT   ,
									WH_Status::EDITING  ,
									WH_Status::EDITED   ,
									WH_Status::HIDDEN   ,
									WH_Status::TRASHED  );	
	
	// Statuses order description
	const STATUSES_ORDER   = array(  10 => array('level' => 10, 'status' => WH_Status::DRAFT    , 'oblig' => true , 'affectUpperElement' => true , 'affectSubElement' => true , 'nextStatuses' => array(WH_Status::TRASHED => '11', 
																																																		   WH_Status::TOEDIT  => '12', WH_Status::EDITING => '12', WH_Status::EDITED => '12')),
									 20 => array('level' => 20, 'status' => WH_Status::TOEDIT   , 'oblig' => false, 'affectUpperElement' => true , 'affectSubElement' => true , 'nextStatuses' => array(WH_Status::DRAFT   => '11', WH_Status::EDITING => '12', WH_Status::EDITED => '12')),
									 30 => array('level' => 20, 'status' => WH_Status::EDITING  , 'oblig' => false, 'affectUpperElement' => true , 'affectSubElement' => true , 'nextStatuses' => array(WH_Status::EDITED  => '12')),
									 40 => array('level' => 20, 'status' => WH_Status::EDITED   , 'oblig' => false, 'affectUpperElement' => true , 'affectSubElement' => true , 'nextStatuses' => array(WH_Status::DRAFT   => '11', WH_Status::EDITING => '21', 
																																																		   WH_Status::PREVIEW => '12', WH_Status::HIDDEN  => '22', 
																																																		   WH_Status::PUBLISHED => '13', 
																																																		   WH_Status::ARC_UNP   => '23')),
									 50 => array('level' => 30, 'status' => WH_Status::TOPUBLISH, 'oblig' => false, 'affectUpperElement' => true , 'affectSubElement' => true , 'nextStatuses' => array(WH_Status::DRAFT   => '11', WH_Status::PUBLISHED => '13')),
									 60 => array('level' => 31, 'status' => WH_Status::HIDDEN   , 'oblig' => false, 'affectUpperElement' => false, 'affectSubElement' => false, 'nextStatuses' => array(WH_Status::PREVIEW => '12', WH_Status::PUBLISHED => '13', WH_Status::EDITED => '00')),
									 70 => array('level' => 32, 'status' => WH_Status::PREVIEW  , 'oblig' => false, 'affectUpperElement' => false, 'affectSubElement' => true, 'nextStatuses' => array(WH_Status::HIDDEN  => '12', WH_Status::PUBLISHED => '13')),
									 80 => array('level' => 39, 'status' => WH_Status::PUBLISHED, 'oblig' => true , 'affectUpperElement' => true , 'affectSubElement' => true , 'nextStatuses' => array(WH_Status::DRAFT   => '11', 
																																																		   WH_Status::PREVIEW => '12', WH_Status::HIDDEN  => '22', 
																																																		   WH_Status::ARCHIVED=> '13', WH_Status::ARC_UNP => '23')),
									 90 => array('level' => 50, 'status' => WH_Status::ARCHIVED , 'oblig' => false, 'affectUpperElement' => true , 'affectSubElement' => true , 'nextStatuses' => array(WH_Status::DRAFT   => '11',   WH_Status::ARC_UNP => '13',   WH_Status::PUBLISHED => '00')),
									100 => array('level' => 51, 'status' => WH_Status::ARC_UNP  , 'oblig' => false, 'affectUpperElement' => true , 'affectSubElement' => true , 'nextStatuses' => array(WH_Status::DRAFT   => '11',   WH_Status::ARCHIVED => '13', WH_Status::EDITED => '00')),
									110 => array('level' => 60, 'status' => WH_Status::TRASHED  , 'oblig' => true , 'affectUpperElement' => false, 'affectSubElement' => false, 'nextStatuses' => array(WH_Status::DEF_DELETE => '11',WH_Status::DRAFT => '13'))
								);	
	
	/******** Properties ********/
	public $book_id;
	public $chapter_id;
	public $element; 	/* WH_Book, WH_Chapter, WH_scene */
	public $element_id; 
	
	public $status; 		
	public $next_status; 	
	public $previous_status;
		
	public $statuses;	/* array */
	
	
	/**
	* Class constructor 
	*  $args : array()
	*			'book_id'       				 => int
	*			'chapter_id'       				 => int
	*			'element'     					 => string
	*			'element_id'   					 => int
	*			'status'	     				 => int
	**/
    public function __construct($id, $args = array())    {
		$this->book_id    = isset($args['book_id'])    ? (int)$args['book_id']:(int)$id;
		$this->chapter_id = isset($args['chapter_id']) ? (int)$args['chapter_id']:0;
		$this->element    = isset($args['element'])    ? (string)$args['element']:"";
		$this->element_id = isset($args['element_id']) ? (int)$args['element_id']:0;
		$this->status     = isset($args['status'])     ? (int)$args['status']:WH_Status::DRAFT;

//wtr_info_log(__METHOD__, $this->element." ".$this->element_id." current status=".$this->status);
		if( $this->element == 'WH_Book' ) {
			if( $this->element_id == 0 )
				$this->element_id = $this->book_id;
			if( $this->book_id == 0 )
				$this->book_id = $this->element_id;
		}
		
		$this->isOk       = false;
		
		// If status not known
		if( ! in_array($this->status, WH_Status::ALL_STATUSES) )
			$this->status = WH_Status::DRAFT;
		
		// If element not use status
		if( ! in_array($this->element, array('WH_Book','WH_Chapter','WH_Scene')) )
			return false;
		
		// Get possible statuses for element
		$this->statuses = WH_Status::getPossibleStatuses($this->element, $this->book_id);
//wtr_info_log(__METHOD__, "Possible statuses: ".print_r($this->statuses,true));
		
		$this->get_NextStatuses();
		$this->get_PreviousStatus();
		
		$this->isOk    = true;
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = false;
		
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$ret = true;
		
		return $ret;
	}
	
	/* Update DB object */
	private function updateDB_Object() {
		
	}
	
	/* Get possible next statuses */
	public function get_NextStatuses() {
		$next_st = array();
		$levels  = array();
		$list_st = WH_Status::getNextPossibleStatuses($this->status);

//wtr_info_log(__METHOD__, "Possible statuses: ".print_r($this->statuses,true));
//wtr_info_log(__METHOD__, "Next statuses: ".print_r($list_st,true));
		
		// If more than one status on a level => keep the lowest status
		foreach( $list_st as $st => $button_position ) {
			$st_level = WH_Status::getStatusLevel($st);

			// If status in element statuses list AND no status of same level
			if( in_array($st, array_merge(array(WH_Status::DEF_DELETE),$this->statuses)) 
			&& ! in_array($st_level, $levels) ) {
				$levels[] = $st_level;
				$next_st[$button_position] = $st;
			}
		}
		
//wtr_info_log(__METHOD__, "Next statuses 2 : ".print_r($next_st,true));
		// If no status found OR only status is Trashed => get mandatory statuses
		if( count($next_st) == 0 || 
		   (count($next_st) == 1 && array_values($next_st)[0] == WH_Status::TRASHED) ) {
			$lig = 1;
			$col = 0;
			$next_st = array();
			foreach( WH_Status::STATUSES_ORDER as $st )
				if( $st['oblig'] == true && $st['status'] != $this->status ) {
					$col++;
					if( $col > 3 ) {
						$lig++;
						$col = 1;
					}
					$next_st[$lig.$col] = $st['status'];
				}
		}
//wtr_info_log( __METHOD__,'Final next statuses:'.print_r($next_st,true));
		$this->next_status = $next_st;
		return $next_st;
	}
	
	/* Get possible previous status */
	public function get_PreviousStatus() {
		$st = WH_Status::DRAFT;
		
		// Get changeStatus activities for element
		$activities = WH_Activity::getAll_ActivitiesByElementAction(
										$this->element, 
										$this->element_id, 
										'changeStatus');
		$nb_act = count($activities);
		if( $nb_act > 1 ) // more than one status change
			$st = intval($activities[$nb_act-2]->comment);
			
		// If previous status not in possible statuses list
		if( ! in_array($st, $this->statuses) ) {
			// search the logical previous status
			foreach( $this->statuses as $pst ) {
				if( $st >= $pst ) 
					break;
				$st = $pst;
			}
		}
		
		$this->previous_status = $st;
		return $st;
	}
	
	
	public function get_NextStatuses_buttons() {
		$next_statuses = $this->next_status;
		if( ! is_array($next_statuses) || count($next_statuses) == 0 )
			$next_statuses = $this->get_NextStatuses();

		$html = array();
		$js_fct = "";
		$button_class = "";
		
		switch( $this->element ) {
			case "WH_Book":
							$button_class = "whBookPanelButton";
							$js_fct = "wtr_manageBook('%s', %d, ".$this->element_id.",0,'')";
							break;
			case "WH_Chapter":
							$button_class = "whActionButton whActionButtonSmall";
							$js_fct = "wtr_manageChapter('%s', %d, ".$this->element_id.", 0)";
							break;
			case "WH_Scene":
							$button_class = "whActionButton whActionButtonSmall";
							$js_fct = "wtr_manageScene('%s', %d, ".$this->element_id.", ".$this->chapter_id.")";
							break;
		}
		
		foreach( $next_statuses as $st ) {
			
			if( $st == WH_Status::DRAFT ) {
				$lib = __('To draft','wtr_helper');
				if( $this->element == "WH_Chapter" && $this->status == WH_Status::PUBLISHED)
					$lib = __('Unpublish','wtr_helper');
				$html[$st] = "<button type='button' class='".$button_class."' ".
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 $lib."</button>\n";
			}
			
			if( $st == WH_Status::TOEDIT ) 
				$html[$st] = "<button type='button' class='".$button_class."' ".
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('End drafting','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::EDITING ) 
				$html[$st] = "<button type='button' class='".$button_class."' ".
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('Edit','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::EDITED ) {
				$lib = __('End editing','wtr_helper');
				if( $this->element == "WH_Scene" && $this->status == WH_Status::HIDDEN )
					$lib = __('Unhide','wtr_helper');
				
				$html[$st] = "<button type='button' class='".$button_class."' ". 
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 $lib."</button>\n";
			}
			
			if( $st == WH_Status::TOPUBLISH ) 
				$html[$st] = "<button type='button' class='".$button_class."' ".
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('To publish','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::HIDDEN ) 
				$html[$st] = "<button type='button' class='".$button_class."' ".
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('Hide','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::PREVIEW ) 
				$html[$st] = "<button type='button' class='".$button_class."' ".
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('Preview','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::PUBLISHED ) 
				$html[$st] = "<button type='button' class='".$button_class."' ". 
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('Publish','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::ARCHIVED ) 
				$html[$st] = "<button type='button' class='".$button_class."' ". 
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('Archive','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::ARC_UNP ) 
				$html[$st] = "<button type='button' class='".$button_class."' ". 
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('Archive & unpublish','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::TRASHED ) 
				$html[$st] = "<button type='button' class='".$button_class." whDeleteButton' ".
							 "onclick=\"".sprintf($js_fct, "status", $st)."\">".
							 __('Delete','wtr_helper')."</button>\n";
			
			if( $st == WH_Status::DEF_DELETE ) 
				$html[$st] = "<button type='button' class='".$button_class." whDeleteButton' ". 
							 "onclick=\"".sprintf($js_fct, "delete", $st)."\">".
							 __('Defenitive Delete','wtr_helper')."</button>\n";
		}
		
		return $html;
	}
	
	
	
	
	/* Return next possible statuses from a status */
	public static function getNextPossibleStatuses($status) {
		$next_st = array();
		
		foreach( WH_Status::STATUSES_ORDER as $pst ) {
			if( $pst['status'] == $status ) {
				$next_st = $pst['nextStatuses'];
				break;
			}
		}
		
		return $next_st;
	}

	
	/* Return mandatory statuses */
	public static function getMandatoryStatuses() {
		$st = array();

		foreach( WH_Status::STATUSES_ORDER as $pst ) 
			if( $pst['oblig'] == true ) 
				$st[] = $pst['status'];
		
		return $st;
	}
	
	/* Return possible statuses of a book */
	public static function getPossibleStatuses($class_name, $book_id = 0) {
		
		$statuses = array();
		
		switch($class_name) {
			case 'WH_Book':		$statuses = WH_Status::getBookStatuses($book_id);
								break;
			case 'WH_Chapter':	$statuses = WH_Status::getChapterStatuses($book_id);
								break;
			case 'WH_Scene': 	$statuses = WH_Status::getSceneStatuses($book_id);
								break;
		}
		
		return $statuses;
	}
	
	/* Return possible statuses of a book */
	public static function getBookStatuses($book_id = 0) {
		
		$bs = new WH_BookSettings($book_id);
		$elementStatuses = WH_Status::BOOK_STATUSES;
		return array_intersect($elementStatuses, $bs->book_statuses);
	}
	/* Return possible statuses of a chapter */
	public static function getChapterStatuses($book_id) {
		$bs = new WH_BookSettings($book_id);
		$elementStatuses = WH_Status::CHAPTER_STATUSES;
		return array_intersect($elementStatuses, $bs->book_statuses);
	}
	/* Return possible statuses of a scene */
	public static function getSceneStatuses($book_id) {
		$bs = new WH_BookSettings($book_id);
		$elementStatuses = WH_Status::SCENE_STATUSES;
		return array_merge(array_intersect($elementStatuses, $bs->book_statuses), array(WH_Status::EDITED));
	}
	
	/* Return true/false if status exists */
	public static function existsStatus($status) {
		return in_array($status, WH_Status::ALL_STATUSES);
	}
	
	/* Return the status name */
	public static function getStatusName($status) {
		$name = "";
		
		if( in_array($status, WH_Status::ALL_STATUSES) )
			$name = WH_Status::STATUSES_NAME[$status];
		else
			$name = __("Status unknown",'wtr_helper');
		
		return $name;
	}
	
	/* Return the status style */
	public static function getStatusStyle($status) {
		$style = "";
		
		if( in_array($status, WH_Status::ALL_STATUSES) )
			$style = WH_Status::STATUSES_STYLE[$status];
		
		return $style;
	}
	
	/* Return a status level */
	public static function getStatusLevel($status) {
		$level = 0;
		
		foreach( WH_Status::STATUSES_ORDER as $pst ) {
			if( $pst['status'] == $status ) {
				$level = $pst['level'];
				break;
			}
		}
		
		return $level;
	}

	/* Return true/false if a status affects upper element status */
	public static function affectUpperElement($status) {
		$affect = true;
		
		foreach( WH_Status::STATUSES_ORDER as $pst ) {
			if( $pst['status'] == $status ) {
				$affect = $pst['affectUpperElement'];
				break;
			}
		}
		
		return $affect;
	}

	/* Return true/false if a status affects subelement status */
	public static function affectSubElement($status) {
		$affect = true;
		
		foreach( WH_Status::STATUSES_ORDER as $pst ) {
			if( $pst['status'] == $status ) {
				$affect = $pst['affectSubElement'];
				break;
			}
		}
		
		return $affect;
	}

	/* Return true/false if status is mandatory */
	public static function isMandatoryStatus( $status ) {
		$oblig = true;
		
		foreach( WH_Status::STATUSES_ORDER as $pst ) {
			if( $pst['status'] == $status ) {
				$oblig = $pst['oblig'];
				break;
			}
		}
		
		return $oblig;
	}

	/* Return 0/1/2 if status permits publication on website */
	public static function isPublishStatus( $status ) {
		$publish = 0;
		
		switch( $status ) {
			case WH_Status::DRAFT    :	$publish = 0; break;
			case WH_Status::TOEDIT   :	$publish = 0; break;
			case WH_Status::EDITING  :	$publish = 0; break;
			case WH_Status::EDITED   :	$publish = 0; break;
			case WH_Status::TOPUBLISH:	$publish = 0; break;
			case WH_Status::PREVIEW  :	$publish = 1; break; // publish for selected users only
			case WH_Status::HIDDEN   :	$publish = 1; break; // publish for selected users only
			case WH_Status::PUBLISHED:	$publish = 2; break; // publish for all
			case WH_Status::ARCHIVED :	$publish = 2; break; // publish for all
			case WH_Status::ARC_UNP  :	$publish = 0; break;
			case WH_Status::TRASHED  :	$publish = 0; break;
			default: $msg = sprintf(__('Status unknown : %s','wtr_helper'), $status);
		}
		
		return $publish;
	}

	/* Return array of statuses which permit publication on website */
	public static function getPublishStatuses( ) {
		
		return array(WH_Status::PREVIEW,   WH_Status::HIDDEN, 
					 WH_Status::PUBLISHED, WH_Status::ARCHIVED);
	}
}
?>