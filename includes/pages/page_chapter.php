<?php 
	include_once(WTRH_INCLUDE_DIR . '/classes/class_chapter.php');
	include_once(WTRH_INCLUDE_DIR . '/functions/buttons_functions.php');
	
	$chapter    = new WH_Chapter(wtr_sanitize($_GET['chapter_id'],'int'), null, true);
	$disabled   = ($chapter->status > WH_Status::EDITED)?'disabled ':'';
	$isDisabled = ($chapter->status > WH_Status::EDITED)?true:false;
	$checked_t  = ($chapter->show_title)?'checked':'';
	$checked_n  = ($chapter->show_number)?'checked':'';
?>
<br>
<h1><?php echo $chapter->book->title; ?> / 
	<?php _e('Chapter n&deg;','wtr_helper'); ?>
	<?php echo $chapter->number; ?> / 
    <div id="whChapterStatus<?php echo $chapter->id; ?>" style='display: inline'>
	<span class="whStatus" style="<?php echo WH_Status::getStatusStyle($chapter->status); ?>">	
		<?php echo WH_Status::getStatusName($chapter->status); ?></span>
	</div>
</h1>
<input type="hidden" id="book_id" value="<?php echo $chapter->book_id; ?>">
<input type="hidden" id="chapter_id" value="<?php echo $chapter->id; ?>">
<input type="hidden" id="whNumber" value="<?php echo $chapter->number; ?>">
<input type="hidden" id="whChapterSaved" value="yes">
<input type="hidden" id="whChapterNumber1" value="<?php echo (isset($chapter->scenes[0]))?$chapter->scenes[0]->number:""; ?>">
<br>
<br/>
<div class="whChapterTitle">
  <label><?php _e('Chapter title','wtr_helper'); ?> : </label>
  <input id="whTitle" 
		value="<?php echo $chapter->title; ?>" 
		size="50"
		onchange='wtr_changeChapter()'
		<?php echo $disabled; ?>/>
	<span id="whChapterPostButton">
<?php
	if( $chapter->chapter_post == null ) {
?>
		<a class="wh_buttonDashicon" onclick="wtr_createChapterPost(<?php echo $chapter->id; ?>)">
		<span class="dashicons dashicons-admin-links" 
			title="<?php _e('Create a post for your chapter','wtr_helper'); ?>"></span>
		</a>
<?php
	} else {
?>
		<a class="wh_buttonDashicon" href="<?php echo $chapter->get_ChapterPostUrl(); ?>" target="_blank">
		<span class="dashicons dashicons-visibility" title="<?php _e('Open post','wtr_helper'); ?>"></span>
		</a>
		<a class="wh_buttonDashicon wh_buttonDashiconDel" onclick="wtr_deleteChapterPost(<?php echo $chapter->id; ?>)">
		<span class="dashicons dashicons-editor-unlink" 
			title="<?php _e('Delete the post containing your chapter','wtr_helper'); ?>"></span>
		</a>
<?php
	}
?>
	</span>
</div>
<br/>
<div class="whChapterShowN">
  <input type="checkbox" id="whShowNumber" 
		onchange='wtr_changeChapter()' <?php echo $checked_n; ?>
		<?php echo $disabled; ?>>
  <label><?php _e('Show number','wtr_helper'); ?></label>
</div>
<br/>
<div class="whChapterShowTitle">
  <input type="checkbox" id="whShowTitle" 
		onchange='wtr_changeChapter()' <?php echo $checked_t; ?>
		<?php echo $disabled; ?>>
  <label><?php _e('Show title','wtr_helper'); ?></label>
</div>
<br/><br/>

<span class="whChapterScenes"><?php _e('Scenes','wtr_helper'); ?></span>
<?php 
	echo getActionButtons("chapter", $chapter->status, "chapter", $chapter->id, $chapter->book_id);
?>
<br/>
<div class="whChapterScenesList" id="whScenes<?php echo $chapter->id; ?>">
 <?php echo getPageHTMLScenesList($chapter->id, "chapter", $isDisabled); ?>

</div>
<?php if( ! $isDisabled ) { ?>
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
<br/>
<div class="whMsg" id="whMsg"></div>
<br/>
<?php 
	echo getActionButtons("chapter", $chapter->status, "save", $chapter->id, $chapter->book_id);
	echo getGotoButtons("chapter", $chapter->status, $chapter->book_id); 
?>
