<?php 
	include_once(WTRH_INCLUDE_DIR . '/classes/class_scene.php');
	include_once(WTRH_INCLUDE_DIR . '/functions/db_functions.php');
	include_once(WTRH_INCLUDE_DIR . '/functions/buttons_functions.php');
	
	$scene = null;
	
	// Create a new scene for new book
	// ----------------------------------------------
	$book_id = 0;
	if( isset($_GET['book_id']) )
		$book_id = wtr_sanitize($_GET['book_id'],'int');

	if( isset($_GET['scene']) && $book_id != 0 ) {
		$book    = new WH_Book($book_id);
		// Create 1st chapter
		$chapter = new WH_Chapter(0, array('title'   => '', 
										   'number'  => 1, 
										   'book_id' => $book_id));
		$chapter->save();
		// Create 1st scene
		$scene = new WH_Scene(0, array('number'     => 1, 
									   'chapter_id' => $chapter->id), true);
		if( $scene->save() )
			WH_Book::refreshNumbers(array('chapter_id'=>$scene->chapter_id));
		$scene->refresh_data();
	}
	
	// create a new scene from an existing scene
	// ----------------------------------------------
	$scene_id = 0;
	if( isset($_GET['scene_id']) )
		$scene_id = wtr_sanitize($_GET['scene_id'],'int');
	
	if( isset($_GET['scene']) && $scene_id != 0 ) {
		// Get old scene
		$s_old = new WH_Scene($scene_id, null, true);
		$number = WH_DB_Scene::getDB_NumberMax($s_old->chapter->book_id)+1;
		// Create next scene
		$scene = new WH_Scene(0, array('number'     => $number, 
									   'chapter_id' => $s_old->chapter_id), true);
		if( $scene->save() )
			WH_Book::refreshNumbers(array('chapter_id'=>$scene->chapter_id));
		$scene->refresh_data();
	}
	
	// display an existing scene
	// ----------------------------------------------
	if( !isset($_GET['scene']) && $scene_id != 0 ) {
		$scene = new WH_Scene($scene_id, null, true);
	}

	$book_id  = $scene->chapter->book_id;
	$book     = new WH_Book($scene->chapter->book_id);

	$isDisabled = ($scene->status != WH_Status::DRAFT)?true:false;
	$disabled = ($scene->status != WH_Status::DRAFT)?'disabled ':'';

?>
<br>
<h1><?php 
		echo $book->title." / ".__('Chapter n&deg;','wtr_helper');
		echo $scene->chapter->number." / ".__('Scene n&deg;','wtr_helper').
			 $scene->number." / ";
		echo "<span class='whStatus' style='".WH_Status::getStatusStyle($scene->status)."'>".
			 WH_Status::getStatusName($scene->status)."</span>";
	?>
  <input type="hidden" id="book_id" value="<?php echo $book_id; ?>">
  <input type="hidden" id="scene_id" value="<?php echo $scene->id; ?>">
  <input type="hidden" id="scene_status" value="<?php echo $scene->status; ?>">
  <input type="hidden" id="whSceneNumber" value="<?php echo $scene->number; ?>">
  <input type="hidden" id="whSceneSaved" value="yes">
</h1>
<br/>

<!-- ---------------------------------------------- -->
<!--                 STORYBOARD LINE                -->
<!-- ---------------------------------------------- -->
<?php
	// "Storyboard" module exists ?
	if( class_exists("WH_Storyboard") ) {
		include_once(WTRH_STORYBOARD_DIR."/js/whs_storyboard_js.php");

		// exists storyboard for book ?
		$s = WH_Storyboard::get_BookStoryboard($book_id);

		// if not exists => create storyboard
		if( empty($s) ) {
			echo "<h3>".__('No storyboard for this book','wtr_helper')."</h3>\n";
		} else {
			echo "<h3>".__('Storyboard line','wtr_helper')."</h3>\n";
			echo whs_getHtmlStoryboardLine($scene_id, $scene->status, $book_id);
		}
	}
?>

<!-- ---------------------------------------------- -->
<!--                 SCENE INFOS                    -->
<!-- ---------------------------------------------- -->
<table class="whSceneTable">
<tr><td class="whSideStoryboardTd" rowspan="2">
<div class="whSideStoryboard">
<?php
	echo "<label class='whStoryboardInfo'>".
		__('Chapters','wtr_helper')."</label><br><br>";
		
	echo "<table class='whSceneStoryboard'>";
	$old_chap = 0;
	$old_beat = 0;
	$book->get_BookChapters(true); // read chapters + scenes

	foreach( $book->chapters as $chap ) {
				
		if( count( $chap->scenes) == 0 ) {
			echo "<tr>";
			// Chapter column
			if( $old_chap != $chap->number ) {
				echo "<td class='whSSchap1'>";
				echo $chap->number;
				$old_chap = $chap->number;
			} else
				echo "<td class='whSSchap0'>";
			echo "</td>";

			echo "<td class='whSSbeat1'>";
			// No scene
			$desc = __('No scene created','wtr_helper');
			echo "<div class='whSSlineNoScene' title=\"".$desc."\">?</div>";
			echo "</td>";
			echo "</tr>";
		}
		
		foreach( $chap->scenes as $sc ) {
			echo "<tr>";
			
			// Chapter column
			if( $old_chap != $chap->number ) {
				echo "<td class='whSSchap1'>";
				echo $chap->number;
				$old_chap = $chap->number;
			} else
				echo "<td class='whSSchap0'>";
			echo "</td>";

			if( $old_beat != $sc->number ) {
				$old_beat = $sc->number;
				echo "<td class='whSSbeat1'>";
			} else
				echo "<td class='whSSbeat0'>";
			
			// If it's displayed scene
			$desc = stripcslashes($sc->description);
			$msg = __('Changes not saved','wtr_helper')." ".
			       __('Quit without save','wtr_helper');
			if( strlen($desc) == 0 )
				$desc = __('No description','wtr_helper');
			if( $scene->id == $sc->id )
				echo "<div class='whSSline1' title=\"".$desc."\" ".
					 "onclick='wtr_goTo(\"".
					  admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&scene_id='.$sc->id)."\")'".
					 ">X</div>";
			else
				echo "<div class='whSSline0' title=\"".$desc."\" ".
					 "onclick='wtr_goTo(\"".
					  admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&scene_id='.$sc->id)."\")'".
					 ">&nbsp;</div>";
			echo "</td>";
		
			echo "</tr>";
		}
	}
	echo "<tr><td class='whSSlast'></td><td class='whSSlast'></td></tr>";
	echo "</table><br>";
?>
</div>
</td>
<td colspan="2">
	<div class="whSceneDesc">
		<label><?php _e('Quick scene description','wtr_helper');?></label>
		<textarea id="whSceneDesc" onchange='wtr_changeScene()'><?php echo $scene->description; ?></textarea>
	</div><br/>
</td></tr>
<tr><td>	
	<div class="whSceneText">
		<label><?php _e('Scene Text','wtr_helper'); ?></label>
		<?php wp_editor($scene->get_Text('text'), 
							"whSceneText", 
							array('textarea_name' => 'whSceneText',
								  'editor_class'  => 'whSceneTextClass',
								  'media_buttons' => true,
								  'teeny'         => true)); ?>
	</div>
</td>
<?php if($scene->status == WH_Status::EDITING || 
        ($scene->status == WH_Status::DRAFT && strlen(trim($scene->editing_text))>0 ) ) { ?>
<td>
	<div class="whSceneEditing">
		<label class="whSceneEditingLabel"><?php _e('Editing notes','wtr_helper'); ?></label>
		<?php wp_editor($scene->get_EditingText('text'), 
							"whEditingText", 
							array('textarea_name' => 'whEditingText',
								  'editor_class'  => 'whEditingTextClass',
								  'media_buttons' => false,
								  'teeny'         => true)); ?>
	</div>
</td>
<?php } ?>
</tr>
<tr><td></td>
<td colspan="2">
	<div id="whSceneGameBook" class="whSceneGameBook">
		<?php 
			if( $book->isGameBook && $scene->status == WH_Status::DRAFT)
				echo getHTML_GameBookScenes($scene->id, $isDisabled); 
		?>
	</div><br/>
</td></tr>
</table>
<?php if( $book->isGameBook && $scene->status == WH_Status::DRAFT) { ?>
<script>
// 
var nestedSortables = [].slice.call(document.querySelectorAll('.nested-scene'));

for (var i = 0; i < nestedSortables.length; i++) {
	new Sortable(nestedSortables[i], {
		group: 'nested-scene',
		animation: 150
	});
}
</script>
<?php } ?>
<br/><br/>
<div class="whMsg" id="whMsg"></div>

<br/><br/>
<?php 
	echo getActionButtons("scene", $scene->status, "save", $scene->id, $book->id);
	echo getGotoButtons("scene", $scene->status, $book->id, $scene->chapter_id); 
?>

<script>

	tinymce.get('whSceneText').on('change', function(e) {
		wtr_changeScene();
	});

	tinymce.get('whEditingText').on('change', function(e) {
		wtr_changeScene();
	});

</script>
