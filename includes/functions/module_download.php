<?php
		// Download form file
	if( isset($_FILES['whAddonFile']) ) {
		
		$error = $_FILES['whAddonFile']['error'];
		$error_msg = "";
		if( $error != 0 ) {
			switch( $error ) {
				case UPLOAD_ERR_INI_SIZE:
					$error_msg = __('File size is higher than PHP limit','wtr_helper');
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$error_msg = __('File size is higher than form limit','wtr_helper');
					break;
				case UPLOAD_ERR_PARTIAL:
					$error_msg = __('File partially downloaded','wtr_helper');
					break;
				case UPLOAD_ERR_NO_FILE:
					$error_msg = __('No file downloaded','wtr_helper');
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$error_msg = __('A temporary directory is missing','wtr_helper');
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$error_msg = __('Error while writing file on disk','wtr_helper');
					break;
				case UPLOAD_ERR_EXTENSION:
					$error_msg = __('A PHP extension has stopped the download','wtr_helper');
					break;					
			}
		}
		
		// if upload error
		if( $error != 0 ) {
			echo $error_msg;
		
		// else if no error but no file selected
		} else if( strlen(trim($_FILES['whAddonFile']['tmp_name'])) == 0 ) 
			_e('No file selected','wtr_helper');
	 
		else {
			$ffname   = $_FILES['whAddonFile']['tmp_name'];
			$filename = basename($_FILES['whAddonFile']['name']);
			$fname    = strtolower(substr($filename,0,strrpos($filename,".")));
			$ext      = strtolower(substr($filename,strrpos($filename,".")));
			$tmp = explode('_', $fname);
			if( count($tmp) == 3 )
				$addOn_name = $tmp[1];
			else if( count($tmp) == 2 )
				$addOn_name = $tmp[0];
			else
				$addOn_name = $fname;
			
			// if file is not a writer helper add-on
			if( $ext != ".zip" || 
			   ($addOn_name != "storyboard"      &&
				$addOn_name != "bookworld"       &&
				$addOn_name != "writers_editors" &&
				$addOn_name != "readers"         &&
				$addOn_name != "communities"  )) {
				echo sprintf(__('Unknown add-on: %s','wtr_helper'),$filename)."<br/>".
					 __('Nothing has been installed','wtr_helper');
				
			} else {
				$continue = true;
				$dirCible = "";
				$dirZip   = WTRH_MODULES_DIR;
				switch( $addOn_name ) {
					case "storyboard": 
						$dirCible = WTRH_MODULES_DIR."/storyboard/";
						break;
					case "bookworld":
						$dirCible = WTRH_MODULES_DIR."/bookworld/";
						break;
					case "writers_editors":
						$dirCible = WTRH_MODULES_DIR."/writers_editors/";
						break;
					case "readers":
						$dirCible = WTRH_MODULES_DIR."/readers/";
						break;
					case "communities":
						$dirCible = WTRH_MODULES_DIR."/communities/";
						break;
					default:
						$continue = false;
						echo sprintf(__('Unknown add-on name %s','wtr_helper'),$addOn_name)."<br/>".
							 __('Nothing has been installed','wtr_helper');
				}
				
				// create "modules" directory
				if( $continue && ! file_exists($dirZip) )
					if( ! mkdir( $dirZip ) ) {
						$continue = false;
						echo sprintf(__('Cannot create directory %s','wtr_helper'), $dirZip)."<br/>".
							 __('Nothing has been installed','wtr_helper');
					}
				
				// create module directory
				if( $continue && ! file_exists($dirCible) )
					if( ! mkdir( $dirCible ) ) {
						$continue = false;
						echo sprintf(__('Cannot create directory %s','wtr_helper'), $dirCible)."<br/>".
							 __('Nothing has been installed','wtr_helper');
					}
				
				// move zip file
				$uploadfile = $dirZip.$filename;
				if( $continue )
					if( move_uploaded_file($ffname, $uploadfile) ) {
					
						echo __('File downloaded','wtr_helper')."<br/>";
						// unzip file
						$zip = new ZipArchive;
						
						if( $continue && $zip->open($uploadfile) === false ) {
							$continue = false;
							echo sprintf(__('Cannot open file %s','wtr_helper'), $uploadfile)."<br/>".
								 __('Nothing has been installed','wtr_helper');
						}
							
						if( $continue && $zip->extractTo($dirCible) === false ){
							$continue = false;
							echo sprintf(__('Cannot unzip file %s to directory %s','wtr_helper'), $uploadfile, $dirCible)."<br/>".
								 __('Nothing has been installed','wtr_helper');
						} else
							echo __('Module installed','wtr_helper')."<br/>";
								
						$zip->close();
						
						// delete zip file
						if( ! unlink($uploadfile) ) {
							wtr_error_log("Download AddOn", "Cannot delete file ".$uploadfile);
						} else
							echo __('ZIP file deleted','wtr_helper')."<br/>";
						
					} else {
						$continue = false;
						echo sprintf(__('Cannot download file %s','wtr_helper'), $ffname)."<br/>".
							 __('Nothing has been installed','wtr_helper');
					}

			}
		}
	}
?>