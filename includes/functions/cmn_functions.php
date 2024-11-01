<?php 
/***** Common constants *****/

// Categories name
const WTRH_CAT_BOOKTYPE    = "bookType";
//const WTRH_CAT_EXPORTBOOK  = "exportBookInfo";
const WTRH_CAT_DATEFORMAT  = "dateFormat";
const WTRH_CAT_TIMEFORMAT  = "timeFormat";
const WTRH_CAT_BOOKSETTINGS= "Settings::Books";

global $wtr_book_types;
$wtr_book_types = array(__('Action and adventure','wtr_helper'), 
__('Alternate history','wtr_helper'), __('Autobiography','wtr_helper'), 
__('Biography','wtr_helper'), __('Chick lit','wtr_helper'), 
__('Children\'s literature','wtr_helper'), __('Diary','wtr_helper'), 
__('Coming-of-age','wtr_helper'), __('Crime','wtr_helper'), 
__('Drama','wtr_helper'), __('Fairytale','wtr_helper'), 
__('Fantasy','wtr_helper'), __('History','wtr_helper'), 
__('Historical fiction','wtr_helper'), __('Horror','wtr_helper'), 
__('Memoir','wtr_helper'), __('Mystery','wtr_helper'), 
__('Poetry','wtr_helper'), __('Romance','wtr_helper'), 
__('Self help','wtr_helper'), __('Science fiction','wtr_helper'), 
__('Short story','wtr_helper'), __('Suspense','wtr_helper'), 
__('Thriller','wtr_helper'), __('Young adult','wtr_helper'));



/***** Common functions *****/

function wtr_error_log($origin, $msg) {
	error_log("WriterHelper :: ERROR :: ".$origin." :: ".$msg);
}

function wtr_info_log($origin, $msg) {
	error_log("WriterHelper :: INFO :: ".$origin." :: ".$msg);
}

// display an array info
function wtr_print_array($table, $depth = 0, $to_string = false, $cr = "<br>", $sp ="&nbsp;") {
	$my_str = "";
	$retr = "";
	if( $depth != 0 )
		$retr = str_repeat($sp, $depth * 4 );
	
	$tmp_table = $table;
	if( is_object($table) ) {
		$tmp_table = get_object_vars($table);
		$my_str .= $retr.get_class($table)." Object [ ".$cr;
	} else
		$my_str .= $retr."Array [ ".$cr;
	
	foreach( $tmp_table as $key => $line ) {
		$my_str .= $retr."    [".$key."] => ";
		$tmp_line = $line;
		if( is_object($line) ) {
			$tmp_line = get_object_vars($line);
			$my_str .= get_class($line) . " Object ".$cr;
		}
		if( is_array($tmp_line) ) {
			$my_str .= wtr_print_array( $tmp_line, $depth+1, true);
		} else
			$my_str .= $line." ".$cr;
			
	}
	$my_str .= $retr."]".$cr;
	
	if( ! $to_string )
		echo $my_str;
	else
		return $my_str;
}

// replace accent by letter
function wtr_noaccent($str, $encoding='utf-8')
{
    // transformer les caractères accentués en entités HTML
    $str = htmlentities($str, ENT_NOQUOTES, $encoding);
 
    // remplacer les entités HTML pour avoir juste le premier caractères non accentués
    // Exemple : "&ecute;" => "e", "&Ecute;" => "E", "à" => "a" ...
    $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
 
    // Remplacer les ligatures 
    // Exemple "œ" => "oe"
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    // Supprimer tout le reste
    $str = preg_replace('#&[^;]+;#', '', $str);
 
    return $str;
}

// upper case a string and delete accents
function wtr_ucase($str, $encoding='utf-8')
{ 
    return mb_strtoupper(wtr_noaccent($str, $encoding));
}

// search a string (no accent, upper cas) in another string (no accent, upper case)
function wtr_instr($haystack, $needle) {
	return (strpos(wtr_ucase($haystack), wtr_ucase($needle)) !== false);
}

// get writer helper class name
function wtr_get_class($obj) {
	$my_class = get_class($obj);
	$tmp = explode("_", $my_class);
	if( is_array($tmp) ) {
		$nb = intval(count($tmp)) - 1;
		$ret = $tmp[$nb];
	} else
		$ret = $my_class;
	
	return $ret;
}

// count words of a string
function wtr_word_count($text) {
	$nb = 0;
	$nb = count(preg_split('/\W+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
	
	return $nb;
}

// get status name from number
function wtr_getStatus($num_status) {
	return WH_Status::getStatusName($num_status);
}


function wtr_url_origin( $s, $use_forwarded_host = false )
{
    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
    $sp       = strtolower( $s['SERVER_PROTOCOL'] );
    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
    $port     = $s['SERVER_PORT'];
    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
    $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
    $host     = isset( $host ) ?  (string)$host : (string)$s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function wtr_full_url( $s, $use_forwarded_host = false )
{
    $a   = explode("?", $s['REQUEST_URI']);
	$req = $a[0];
	return wtr_url_origin( $s, $use_forwarded_host ) . $req;
}


// Sanitize a string in a specified format
function wtr_sanitize($str, $format='text') {
	$result = "";
	
	switch($format) {
		case 'int'  : $result = intval($str);
					break;
		case 'float': $result = floatval($str);
					break;
		case 'boolean' : 
		case 'bool' : $result = boolval($str);
					break;
		case 'url'  : $result = esc_url_raw(stripslashes($str));
					break;
		case 'user' : $result = sanitize_user(stripslashes($str));
					break;
		case 'file' : $result = sanitize_file_name(str_replace(' ', '_', strtolower(wtr_noaccent(stripslashes($str)))));
					break;
		case 'title': 
					$result = wp_kses_post(stripslashes($str));
					break;
		case 'html' : 
					$result = wp_kses_post(stripslashes($str));
					break;
		case 'text' : 
		default: 
//wtr_info_log(__METHOD__,'text before : '.$str);		
					$result = esc_html(sanitize_textarea_field(stripslashes($str)));
//wtr_info_log(__METHOD__,'text after  : '.$result);		
					break;
	}
	
	return $result;
}


// return a date which uses the user defined format
function wtr_getFormatedDate($date = "", $format = "" ) {
	$myDate = "";
//wtr_info_log(__METHOD__,'date='.$date.' format='.$format);		
	if( $date == "" || $date == null )
		$myDate = date_create(date('Y-m-d'));
	else {
		$myDate = date_create($date);
		if( $myDate === false )
			return false;
	}
//wtr_info_log(__METHOD__,'php date='.print_r($myDate,true));		
	if( $format == "" || $format == null )
		$format = WH_Category::get_DateFormat();
//wtr_info_log(__METHOD__,'format='.$format);		
	
	$fDate = date_format($myDate, $format);
//wtr_info_log(__METHOD__,'fDate='.$fDate);		
		
	return $fDate;
}
// return the message for entering a date 
function wtr_getMsgDate() {
	$format = WH_Category::get_DateFormat();
	$msg = sprintf(__('Enter a date of publication (format %s)','wtr_helper'), $format);
	
	return $msg;
}


// Get Latest Version Date of a module
function wtr_getLatestVersionData($module_id) {
	
	$version_data = array();
	$json_fn = WTRH_WEBSITE."/wp-content/uploads/".$module_id."_update.json";

	$head = get_headers($json_fn);
	if( ! stripos($head[0],"200 OK") )
		return false;

	$version_data = json_decode(file_get_contents($json_fn), true);
	if( ! is_array($version_data) )
		return false;

	return $version_data;
}
// Get Latest Version of a module
function wtr_getLatestVersion($module_id) {
	$latest_version = "";
	$version_data = wtr_getLatestVersionData($module_id);
	if( ! is_array($version_data) )
		return $latest_version;
	
	return (isset($version_data['new_version']))?$version_data['new_version']:false;
}
// Get Installed Version of a module
function wtr_getInstalledVersion($module_id) {
	$installed_version = "";
	$module_name = (isset(explode("_",$module_id)[1]))?explode("_",$module_id)[1]:$module_id;
	$index_fn = "";
	switch( $module_name ) {
		case "bookworld"  : $index_fn = WTRH_BOOKWORLDS_DIR ."/".$module_id.".php";  break;
		case "communities": $index_fn = WTRH_COMMUNITIES_DIR."/".$module_id.".php";  break;
		case "readers"    : $index_fn = WTRH_READERS_DIR    ."/".$module_id.".php";  break; 
		case "storyboard" : $index_fn = WTRH_STORYBOARD_DIR ."/".$module_id.".php";  break;
		case "writerseditors": $index_fn = WTRH_WRITEDIT_DIR."/".$module_id.".php";  break;
		default: 
	}
	if( ! file_exists($index_fn) )
		return $installed_version;
	
	$version_content = file_get_contents($index_fn);
	if( $version_content === false )
		return $installed_version;
	$version_lines = explode("\n", $version_content);
	foreach( $version_lines as $line ) {
		$version_data  = explode(" ", substr($version_lines[4], 3));
		if( is_array($version_data) && $version_data[0]=="Version:" )
			break;
	}
	
	return (is_array($version_data) && $version_data[0]=="Version:")?$version_data[1]:false;
}

// Compare an installed version vs latest version
function wtr_isLatestVersion($module_id) {
	$new_version = wtr_getLatestVersion($module_id);
	$ins_version = wtr_getInstalledVersion($module_id);

	$isLatest = true;
	if( $new_version == "" || $ins_version == "" ||
		$new_version <= $ins_version )
		$isLatest = false;
		
	return $isLatest;
}

?>