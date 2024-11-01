<?php
/************************************
 **      Main class      **
 ************************************/
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_activity.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_book.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_category.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_chapter.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_metadata.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_scene.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_stat_author.php");
include_once(WTRH_INCLUDE_DIR . "/classes/db_class_user.php");

include_once(WTRH_INCLUDE_DIR . "/classes/class_element.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_activity.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_book.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_booksettings.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_category.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_chapter.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_metadata.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_scene.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_stat_author.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_status.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_user.php");

$classesList   = array('WH_DB_Book', 'WH_DB_Category', 
                        'WH_DB_Chapter', 'WH_DB_Scene', 
						'WH_DB_User', 'WH_DB_Activity', 
						'WH_DB_Metadata');
$dbTablesList   = array(WH_DB_Book::tableName, WH_DB_Category::tableName, 
                        WH_DB_Chapter::tableName, WH_DB_Scene::tableName, 
						WH_DB_User::tableName, WH_DB_Activity::tableName,
						WH_DB_Metadata::tableName);
global $dbCreateReqList;
$dbCreateReqList = array( WH_DB_Book::createReq, WH_DB_Category::createReq, 
						WH_DB_Chapter::createReq, WH_DB_Scene::createReq, 
						WH_DB_User::createReq, WH_DB_Activity::createReq,
						WH_DB_Metadata::createReq);

?>