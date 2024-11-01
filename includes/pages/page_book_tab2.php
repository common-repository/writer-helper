<!-- ------------------------------------- -->                    
<!--         BOOK CHAPTERS                 -->                    
<!-- ------------------------------------- -->                    
<br><br>
<input type="hidden" id="whBookChaptersTab" value="yes">
<label class="whBookChapters"><?php _e('Chapters','wtr_helper');?></label>
<div id="whChapters">
	<?php echo getPageHTMLChaptersList($book->id, "book"); ?>
</div>

<?php if( ! $isDisabled ) { ?>
<script>
// 
var nestedSortables = [].slice.call(document.querySelectorAll('.nested-chapter'));

for (var i = 0; i < nestedSortables.length; i++) {
	new Sortable(nestedSortables[i], {
		group: 'nested-chapter',
		animation: 150
	});
}
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
<!-- ------------------------------------- -->                    
<!--             BUTTONS                   -->                    
<!-- ------------------------------------- -->                    
<br/><br/>
<div class="whMsg" id="whMsg"></div>

<br/><br/>
<?php 
	echo getActionButtons("book", $book->status, "save", $book->id, $book->id);
	echo getGotoButtons("book", $book->status, $book->id); ?>
</div>
<br/><br/>
