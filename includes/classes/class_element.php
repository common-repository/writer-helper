<?php
/************************************
 **         Element class          **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");

abstract class WH_Element {
	
	public $id;
	public $status;
		
	/**
	* Class constructor 
	**/
    public function __construct() {}

	
	/* Refresh element's status by looking in subelements status  */
	public function refreshStatus($new_status = 99, $refreshUpper = true) {
//wtr_info_log(__METHOD__,"class: ".get_called_class()."  new_status: $new_status");
		$result     = true;
		$num_status = 99;
		$old_status = $this->status;
		$subClass = null;
		
		if( $new_status != 99 && WH_Status::existsStatus($new_status) )
			$this->status == $new_status;
		else {
			
			// read all subelements
			$subElements = $this->getSubElements();
			foreach($subElements as $elt) {	
				
				$subClass = get_class($elt);
				// If subelement status must affect current element status
				if( WH_Status::affectUpperElement($elt->status) ) {
					// If lowest status 
					if( $elt->status < $num_status  ) 
						$num_status = $elt->status; 
				}
				
			}
		
			$new_status = $num_status;
		}
		
		if( $new_status == 99 )
			$new_status = $old_status;
		
		// If Chapter refresh & new_status = EDITIED & old_status > EDITED
		// => keep old_status
		if( get_class($this) == "WH_Chapter" &&
			$new_status == WH_Status::EDITED && 
			$old_status > WH_Status::EDITED )
			$new_status = $old_status;
		
		// If status changed
		if( $old_status != $new_status ) {

			$this->status = $new_status;
			$result = $this->save();
			
			// create activity
			WH_Activity::changeStatus(wtr_get_class($this), 
										$this->id, 
										$new_status);
		}
		
		// If new status must impact upper element
		if( $result && $refreshUpper && 
			WH_Status::affectUpperElement($new_status) ) {
			$result = $this->refreshUpperElementStatus();
		}
		
		return $result;
	}

	/* Change element status  */
	// All subelements can be impacted
	public function changeStatus($new_status, $subElementStatus = -1) {
//wtr_info_log(__METHOD__,"class: ".get_called_class()."  new_status: $new_status");
		$changed_status = false;
		$subelt_ok = true;
		
		// get element possible status
		$properties = get_object_vars($this);
		$book_id    = 0;
		if( isset($properties['book_id']) )
			$book_id = $properties['book_id'];
		else if( isset($properties['id']) )
			$book_id = $properties['id'];
		$possible_statuses = WH_Status::getPossibleStatuses(get_called_class(), $book_id);
		
//wtr_info_log(__METHOD__,"possible_statuses: ".print_r($possible_statuses,true));
		if( ! in_array($new_status, $possible_statuses) ) {
			$last = WH_Status::DRAFT;
			foreach( $possible_statuses as $st ) {
				if( $new_status < $st ) {
					$new_status = $last;
					break;
				}
				$last = $st;
			}
//wtr_info_log(__METHOD__,"new_status changes to ".$new_status);
		}
		
		if( $subElementStatus == -1 ) {
			$subElementStatus = $new_status;
			
			if( get_called_class() == "WH_Chapter" && 
			    $new_status  > WH_Status::EDITED   &&
				$new_status != WH_Status::TRASHED	)
				$subElementStatus = WH_Status::EDITED;
		}
		
		// If new status must impact sub elements
		if( WH_Status::affectSubElement($new_status) ) {
			
			// read all subelements
			$subElements = $this->getSubElements();
			
			foreach($subElements as $elt) {	
				
				// If subelement status affects upper (current elt)
				if( WH_Status::affectUpperElement($elt->status) ) {
					if( false === $elt->changeStatus($subElementStatus) ) {
//wtr_info_log(__METHOD__,"class: ".get_class($elt)."  elt status: ".$elt->status);
						$subelt_ok = false;
						break;
					}
				}
			}
		}
		
//wtr_info_log(__METHOD__,"subelt_ok: ".($subelt_ok?'true':'false'));
		if( $subelt_ok ) {
			$this->refreshStatus($new_status, false);
			$changed_status = $this->save();
			// refresh subelements status if class properties
			$this->getSubElements();
		}
		
		return $changed_status;
	}

	/* End drafting for the element and subElements  */
	// Draft => ToEdit
	public function endDrafting() {
		return $this->changeStatus(WH_Status::TOEDIT);
	}
	
	/* Start editing for the element and subElements  */
	// ToEdit => Editing
	public function startEditing() {
		return $this->changeStatus(WH_Status::EDITING);
	}
	
	/* End editing for the element and subElements  */
	// Editing => Edited
	public function endEditing() {
		return $this->changeStatus(WH_Status::EDITED);
	}
	
	/* End editing for the element and subElements  */
	// Edited => Hidden
	public function hide() {
		return $this->changeStatus(WH_Status::HIDDEN);
	}
	
	/* To publish for the element and subElements  */
	// Edited => To Publish
	public function toPublish() {
		return $this->changeStatus(WH_Status::TOPUBLISH);
	}
	
	/* Preview for the element and subElements  */
	// Edited => Preview
	public function preview() {
		return $this->changeStatus(WH_Status::PREVIEW);
	}
	
	/* Publish for the element and subElements  */
	// * => Publish
	public function publish($publicationDate = "") {
		
		$this->set_PublicationDate($publicationDate);
		
		return $this->changeStatus(WH_Status::PUBLISHED);
	}
	
	/* Archive an element and subElements  */
	// * => Archive
	public function archive() {
		return $this->changeStatus(WH_Status::ARCHIVED);
	}
	
	/* Archive & unpublish for the element and subElements  */
	// * => Archive & unpublish
	public function archiveUnpublish() {
		
		$this->reset_PublicationDate();
		
		return $this->changeStatus(WH_Status::ARC_UNP);
	}
	
	/* Unpublish for the element and subElements  */
	// Publish => Draft
	public function unpublish() {
		
		$this->reset_PublicationDate();
		
		return $this->changeStatus(WH_Status::DRAFT, WH_Status::DRAFT);
	}
	
	/* Trash an element and subElements  */
	// * => Trashed
	public function trash() {
		
		$this->reset_PublicationDate();
		
		return $this->changeStatus(WH_Status::TRASHED, WH_Status::EDITED);
	}

	/* UnTrash an element and subElements  */
	// * => Draft
	public function untrash() {
		return $this->changeStatus(WH_Status::DRAFT);
	}


	abstract function save();
	abstract function getSubElements();
	abstract function refreshUpperElementStatus();

	abstract function get_PublicationDate($dateFormat = "");
	abstract function set_PublicationDate($date = "");
	abstract function reset_PublicationDate();

}
?>