<?php
/************************************
 **           Metadata class           **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_metadata.php");

class WH_Metadata {
	
	public $id;
	public $obj_id;
	public $meta_obj;
	public $meta_key;
	public $meta_value;
	public $user_id;
	
	public $isOk;       /* init ok ? */
	
	private $DB_Metadata;    /* object */
		
	/**
	* Class constructor 
	*  $args : array()
	*			'obj_id'     => integer
	*			'meta_obj'   => string
	*			'meta_key'   => string
	*			'meta_value' => string
	*			'user_id'    => integer
	**/
    public function __construct($id, $args = array())    {
		$this->id              = isset($args['meta_id'])    ? (int)$args['meta_id']:(int)$id;
		$this->obj_id          = isset($args['obj_id'])     ? (int)$args['obj_id']:0;
		$this->meta_obj        = isset($args['meta_obj'])   ? (string)$args['meta_obj']:'';
		$this->meta_key        = isset($args['meta_key'])   ? (string)$args['meta_key']:'';
		$this->meta_value      = isset($args['meta_value']) ? (string)$args['meta_value']:'';
		$this->user_id         = isset($args['user_id'])    ? (int)$args['user_id']:0;
		
		$this->isOk            = false;
		
		$this->DB_Metadata     = null;
		
		if( ! is_numeric($id) ) {
			wtr_error_log(__METHOD__, "id incorrect : ".$id);
			return false;
		} else {
			if( $this->id != 0 ) {
				$this->get_Metadata($this->id);

			} else {
				$this->DB_Metadata = new WH_DB_Metadata(0, $args);
				$this->isOk = $this->DB_Metadata->isOk;
			}		
		}
	}

	/* Update DB */
	public function save() {
		$this->updateDB_Object();
		$result = $this->DB_Metadata->save();
		if( $result ) // copy id
			$this->id = $this->DB_Metadata->id;
		return $result;
	}
	
	/* Delete object from DB */
	public function delete() {
		$ret = true;
		$this->updateDB_Object();
		$ret = $this->DB_Metadata->delete();
		
		return $ret;
	}
	
	
	/* Get metadata value */
	public function get_Value($format = 'raw') {
		$value = $this->meta_value;
		
		switch( $format ) {
			case 'array':
						$value = json_decode($this->meta_value, true);
						break;
			case 'raw':
			default:
						break;
		}
		
		return $value;
	}
	
	
	/* Read DB */
	private function get_Metadata($id) {
		$this->DB_Metadata = new WH_DB_Metadata($id);

		if( $this->DB_Metadata->isOk ) {
			$this->id              = $this->DB_Metadata->id    ;
			$this->obj_id          = $this->DB_Metadata->obj_id     ;
			$this->meta_obj        = $this->DB_Metadata->meta_obj   ;
			$this->meta_key        = $this->DB_Metadata->meta_key   ;
			$this->meta_value      = $this->DB_Metadata->meta_value ;
			$this->user_id         = $this->DB_Metadata->user_id    ;
			
			$this->isOk = true;
			
		} else {
			$this->isOk = false;
		}
	}
	
	/* Update DB object */
	private function updateDB_Object() {
		$this->DB_Metadata->id           = $this->id    ;
		$this->DB_Metadata->obj_id       = $this->obj_id     ;
		$this->DB_Metadata->meta_obj     = $this->meta_obj   ;
		$this->DB_Metadata->meta_key     = $this->meta_key   ;
		$this->DB_Metadata->meta_value   = $this->meta_value ;
		$this->DB_Metadata->user_id      = $this->user_id    ;
	}
	
	
	
	/* Get All Metadatas for an objet */
	public static function getAll_Metadatas_ofObject($obj_type, $obj_id, 
										$col = "meta_id", $direction = "asc") {
		$myMetadatas = array();
		$args = array( 'meta_obj' => $obj_type,
		               'obj_id'   => $obj_id);
		$dbMetadatas  = WH_DB_Metadata::getAllDB_Metadatas($args,
												$col, $direction);
		
		foreach( $dbMetadatas as $metadata ) {
			$myMetadatas[] = new WH_Metadata(0, get_object_vars($metadata));
		}
		                  
		return $myMetadatas;
	}
	
	/* Get a Metadata for an objet and a meta_key */
	public static function getAll_OneMetadata_ofObject($obj_type, $obj_id, $meta_key,
										$col = "meta_id", $direction = "asc") {
		$myMetadatas = array();
		$args = array( 'meta_obj' => $obj_type,
		               'obj_id'   => $obj_id,
		               'meta_key' => $meta_key);
		$dbMetadatas  = WH_DB_Metadata::getAllDB_Metadatas($args,
												$col, $direction);
		
		foreach( $dbMetadatas as $metadata ) {
			$myMetadatas[] = new WH_Metadata(0, get_object_vars($metadata));
		}
		                  
		return $myMetadatas;
	}
	
	/* Get All Metadatas for an objet and a user */
	public static function getAll_Metadatas_ofObjectAndUser($obj_type, $obj_id, $user_id,
										$col = "meta_id", $direction = "asc") {
		$myMetadatas = array();
		$args = array( 'meta_obj' => $obj_type,
		               'obj_id'   => $obj_id,
		               'user_id'  => $user_id);
		$dbMetadatas  = WH_DB_Metadata::getAllDB_Metadatas($args,
												$col, $direction);
		
		foreach( $dbMetadatas as $metadata ) {
			$myMetadatas[] = new WH_Metadata(0, get_object_vars($metadata));
		}
		                  
		return $myMetadatas;
	}
	
	/* Get a Metadata for an objet and a user and a meta key */
	public static function getAll_OneMetadata_ofObjectAndUser($obj_type, $obj_id, $user_id,
										 $meta_key, $col = "meta_id", $direction = "asc") {
		$myMetadatas = array();
		$args = array( 'meta_obj' => $obj_type,
		               'obj_id'   => $obj_id,
		               'user_id'  => $user_id,
					   'meta_key' => $meta_key);
		$dbMetadatas  = WH_DB_Metadata::getAllDB_Metadatas($args,
												$col, $direction);
		
		foreach( $dbMetadatas as $metadata ) {
			$myMetadatas[] = new WH_Metadata(0, get_object_vars($metadata));
		}
		                  
		return $myMetadatas;
	}
	
}
?>