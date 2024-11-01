<?php
/* Functions for scene */

// Return the page HTML listing scenes
function getPageHTMLScenesList($chapter_id, $entryElement = "book", $isDisabled = true) {
	$page = "";
	$chapter   = new WH_Chapter($chapter_id, null, true);
	$my_scenes = $chapter->scenes;
//	wtr_info_log(__METHOD__, "chapter_id = $chapter_id  /  nb scenes=".count($my_scenes));
	
	$page .= " <div class='whScenesList ".($isDisabled?"":"nested-scene")."'>\n";
	
	if( count($my_scenes) == 0 ) {
/*		$page .= "  <label class='whMsg'>".
				__('No Scene yet !','wtr_helper').
				"</label>";*/
	}
	
	$entryElement = "scene";
	
	foreach( $my_scenes as $scene ) {
//		wtr_info_log(__METHOD__, "scene number =".$scene->number);
		$page .= "   <div ondragend='wtr_dragEndScene(event)'>\n";
		$page .= "    <div class='whBookChapterScenes'>\n";
		$page .= "     <div class='whBookSceneLabel'>";

		if( ! $isDisabled )
		$page .= "<span class='dashicons dashicons-menu whHandler'></span>";

		$page .= "<label>".__('Scene','wtr_helper')."</label></div>\n";
		$page .= "     <div class='whBookSceneNumber whBookCSlist' ".
					"id='whSceneNumber".$scene->id."'>".
					$scene->number."</div>\n";
		$page .= "     <div class='whBookSceneTitle'>".
				 $scene->description."</div>\n";
		$page .= "     <div class='whBookSceneStatus' style='".WH_Status::getStatusStyle($scene->status)."'>".
		         WH_Status::getStatusName($scene->status)."</div>\n";
		$page .= "     <div class='whBookSceneButtons'>";
		$page .= getActionButtons($entryElement, $scene->status, "scene", 
								  $scene->id, $chapter->book_id, $chapter_id);
		$page .= "     </div>\n".
				 "    </div>\n".
				 "   </div>\n";
		 
	}
	
	$page .= "  </div>\n";
	return $page;
}


// Return HTML links of scenes 
function getHTML_GameBookScenes($scene_id, $isDisabled = true) {
	$html  = "";
	$scene = new WH_Scene($scene_id);
	$bscenes = WH_Scene::getAll_BookScenes($scene->book_id);
	
	$html .= "<b><label>".__('Add links to next scenes','wtr_helper')."</label></b>\n";
	
	$html .= "<div class='whScenesList nested-scene'>";
	$linkedScenes_id = array($scene_id);
	foreach( $scene->gameBook as $i => $sc ) {
		$linkedScenes_id[] = $sc['scene_id'];
		$lsc = new WH_Scene($sc['scene_id']);
		
		$html .= "   <div ondragend='wtr_dragEndLinkedScene(event)'>\n";
		$html .= "    <div class='whBookChapterScenes'>\n";
		$html .= "     <div class='whBookSceneLabel'>";

		if( ! $isDisabled )
		$html .= "<span class='dashicons dashicons-menu whHandler'></span>";

		$html .= "<label>".__('Scene','wtr_helper')." ".
				 $lsc->number."</label> ".
		         $lsc->description."\n";
		$html .= "<br>\n";
		$html .= "<input type='hidden' id='whLinkSceneId".$lsc->id."' class='whLinkedSceneId' value='".$sc['scene_id']."'>\n";
		$html .= "<input type='hidden' id='whLinkSceneOrder".$lsc->id."' class='whLinkedSceneNumber' value='".$i."'>\n";
		$html .= "<input type='text' class='whLinkSceneDesc' id='whLinkSceneDesc".$lsc->id."' ";
		$html .= "value=".'"'.$sc['libelle'].'" '.($isDisabled?"disabled":"")."/>\n";
		$html .= "&nbsp;&nbsp;  <div class='whBookSceneButtons'>";
		if( ! $isDisabled ) {
		$html .= "     <a class='wh_button whActionButtonSmall' ".
					"    onclick='wtr_manageScene(\"updateLinkedScene\",0,".$scene_id.",".$sc['scene_id'].")'>".
					      __('Save','wtr_helper')."</a>\n";
		$html .= "     <a class='wh_button whActionButtonSmall' ".
					"    onclick='wtr_manageScene(\"deleteLinkedScene\",0,".$scene_id.",".$sc['scene_id'].")'>".
					      __('Delete','wtr_helper')."</a>\n";
		}
		$html .= "     </div>\n".
				 "    </div>\n".
				 "   </div>\n";
		
	}
	$html .= "</div>";
	
	// New Link
	$bookScenes_id = array();
	foreach( $bscenes as $i => $sc )
		if( ! in_array($sc->id, $linkedScenes_id) )
			$bookScenes_id[] = $sc->id;
		else
			unset($bscenes[$i]);
		
	if( count($bookScenes_id) > 0 ) {
		$html .= "<div>";
		$html .= "<select class='whNewLinkedScene' id='whNewLink'>\n";
		foreach( $bscenes as $sc )
			$html .= "<option value='".$sc->id."'>".
					 __('Scene','wtr_helper')." ".$sc->number.
					 ": ".substr($sc->description,0,50).
					 "</option>\n";
		$html .= "</select><br>\n";
		$html .= "<input type='text' class='whLinkSceneDesc' id='whNewLinkedSceneDesc' ";
		$html .= "value='' ".($isDisabled?"disabled":"")."/><br>\n";
		if( ! $isDisabled )
		$html .= "<a class='wh_button whActionButtonSmall' ".
					"onclick='wtr_manageScene(\"addLinkedScene\",0,0,0,0)'>".
					__('Add this new link','wtr_helper')."</a>\n";
		$html .= "</div>\n";
	} else
		$html .= __('No more different scene to add','wtr_helper');
	
	return $html;
}


// Return an array of Linked Scenes (GameBook)
// book_id   : id of WH_Book
// singleton : return each scene only once
function getArray_GameBook($book_id, $cascade = true, $singleton = false) {
	
	$result = array();
	
	$book = new WH_Book($book_id);
	if( ! $book->isOk || ! $book->isGameBook )
		return $result;
	
	$scenes     = WH_Scene::getAll_BookScenes($book_id);
	
	// get scenes and their children
	$linkedScenes = array();
	$scenes_ids   = array();
	$i = 0;
	foreach( $scenes as $sc ) {
		if( ! $singleton || ($singleton && ! in_array($sc->id, $scenes_ids)) ) {
			$linkedScenes[$i] = getLinkedSceneArray($sc, $scenes_ids, $singleton);
			
			if( in_array($sc->id, $scenes_ids) ) {
				$linkedScenes[$i]['type'] = 'node';
				if( count($linkedScenes[$i]['children']) == 0 ) {
					$linkedScenes[$i]['type'] = 'end';
					$linkedScenes[$i]['border'] = 'black';
				}
			} else { // change beginnings color
				$linkedScenes[$i]['border'] = 'black';
				$linkedScenes[$i]['color']  = 'white';
			}
		}
		$i++;
	}
	
	// get max depth
	foreach( $linkedScenes as $i => $sc )
		$linkedScenes[$i]['max_depth'] = getLinkedSceneMaxDepth($sc['id'], $linkedScenes);
	
	// get all parents
	foreach( $linkedScenes as $i => $sc )
		$linkedScenes[$i]['parents'] = getLinkedSceneParents($sc['id'], $linkedScenes);
	
	// sort by depth
	$linkedScenes = sortLinkedScenes($linkedScenes, 'max_depth');
	
	// get result
	$result = $linkedScenes;
	if( $cascade ) { // return only beginnings and their children
		$result = array();
		foreach( $linkedScenes as $ls )
			if( $ls['type'] == 'beginning' )
				$result[] = $ls;
	}
	return $result;
}

function sortLinkedScenes($linkedScenes, $field) {
	
	$tmp_ar = array();
	foreach( $linkedScenes as $sc ) {
		$key = "#NA";
		if( isset($sc[$field]) )
			if( is_int($sc[$field]) )
				$key = sprintf("%05d", $sc[$field]);
			else
				$key = $sc[$field];
		$key .= sprintf("%10d", $sc['id']);
		$tmp_ar[$key] = $sc;
	}
	ksort($tmp_ar);
	
	$result = array();
	foreach( $tmp_ar as $sc )
		$result[] = $sc;
		
	return $result;
}

// return scene and its children 
// scene     : WH_Scene
// scenes_ids: array of WH_Scene id (to prevente double if not wanted)
// singleton : return each scene only once
function getLinkedSceneArray($scene, &$scenes_ids, $singleton = false, $depth = 0) {
	
	// Avoid infinite loop
	if( $depth > 9999 )
		return false;
	
	$result   = array();
	
	// Get scene info
	$color = "black";
	if( class_exists('WH_Storyboard') ) {
		$plot = WH_StoryboardLine::get_PlotineFromScene($scene->id);
		if( $plot )
			$color = $plot->color;
	}
	$type   = 'beginning';
	if( $depth > 0 ) {
		$type = 'node';
		$scenes_ids[] = $scene->id;
	}
	$result =
			array( 'id'       => $scene->id,
				   'name'     => __('Scene','wtr_helper').$scene->number,
				   'desc'     => substr(html_entity_decode($scene->description,ENT_QUOTES),0,20),
				   'type'     => $type,
				   'depth'    => $depth,
				   'max_depth'=> $depth,
				   'border'   => $color,
				   'color'    => $color,
				   'parents'  => array(),
				   'children' => array()
				);
	
	// Get children info
	$sc_ls = $scene->get_GameBook();
//wtr_info_log(__METHOD__, "Scene ".$scene->id." linked scenes: ".print_r($sc_ls,true));	
	if( count( $sc_ls ) == 0 )
		$result['type'] = 'end';
	
	foreach( $sc_ls as $sc_a ) {
		$sc = new WH_Scene($sc_a['scene_id']);
		if( ! $singleton || ! in_array($sc->id, $scenes_ids) ) {
			$color = "black";
			if( class_exists('WH_Storyboard') ) {
				$plot = WH_StoryboardLine::get_PlotineFromScene($sc->id);
				if( $plot )
					$color = $plot->color;
			}
			
			$result['children'][] = getLinkedSceneArray($sc, $scenes_ids, $singleton, $depth+1);
			
			foreach( $result['children'] as $i => $child )
				$result['children'][$i]['parents'][] = $scene->id;
		}
	}
	
	return $result;
}


// return max depth of a scene
// scene_id     : integer
// linkedScenes : array
function getLinkedSceneMaxDepth($scene_id, $linkedScenes, &$depth = 0) {
	
	foreach( $linkedScenes as $sc ) {
		if( $scene_id == $sc['id'] && $depth < $sc['depth'])
			$depth = $sc['depth'];
		getLinkedSceneMaxDepth($scene_id, $sc['children'], $depth);
	}
	
	return $depth;
}

// return all parents of a scene
// scene_id     : integer
// linkedScenes : array
function getLinkedSceneParents($scene_id, $linkedScenes, &$depth = 0, &$parents = array()) {
	
	foreach( $linkedScenes as $sc ) {
		if( $scene_id == $sc['id'] ) {
			foreach( $sc['parents'] as $p )
				if( ! in_array($p, $parents) )
					$parents[] = $p;
		}
		getLinkedSceneParents($scene_id, $sc['children'], $depth, $parents);
	}
	
	return $parents;
}


?>