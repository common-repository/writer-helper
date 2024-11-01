<?php 
	include_once(WTRH_INCLUDE_DIR . '/classes/class_book.php');
	include_once(WTRH_INCLUDE_DIR . '/classes/class_category.php');
	include_once(WTRH_INCLUDE_DIR . '/functions/buttons_functions.php');
	
	$types      = WH_Category::get_BookTypes();
	$disabled = "";
?>

<br>
<h1>
  <?php _e('New book','wtr_helper'); ?>
</h1>
<br>
<table> 
<!-- ------------------------------------- -->                    
<!--         BOOK INFO                 -->                    
<!-- ------------------------------------- -->                    
    <tr><td>
	  <label class='whLabel'>
        <?php _e('Title','wtr_helper'); ?>&nbsp;
	  </label>
      </td><td>
		<input type='text' id='whBookTitle' class='whInput'>
    </td></tr>
    <tr><td>
      </td><td>
    </td></tr>
    <tr><td>
	  <label class='whLabel'>
        <?php _e('Type','wtr_helper'); ?>&nbsp;
	  </label>
      </td><td>
        <select id='whBookType' class='whInput whSelect'>
		<?php 
          foreach( $types as $type ) {
			echo "<option value='".esc_attr($type->title)."'>".__($type->title,'wtr_helper')."</option>\n";
		  }
		 ?>
        </select>
    </td></tr>
    <tr><td>
	  <label class='whLabel'>
        <?php _e('Game book','wtr_helper'); ?>&nbsp;
		<span class='dashicons dashicons-editor-help whQuestionMark' 
				title='<?php _e('Book where you are the hero','wtr_helper'); ?>'></span>
	  </label>
      </td><td>
        <input type='checkbox' id='whBookGame' class='whBookGameBookCheckbox'  />
		
    </td></tr>
    <tr><td>
    </td><td>
    </td></tr>

</table>

<!-- ------------------------------------- -->                    
<!--         BOOK RESUME                   -->                    
<!-- ------------------------------------- -->                    
<div class="whBookResume">
  <label class='whLabel'><?php _e('Resume','wtr_helper');?></label><br/>
  <textarea id='whResume'></textarea>
</div>
<br/><br/><br/>

<!-- ------------------------------------- -->                    
<!--         ACTION BUTTONS                -->                    
<!-- ------------------------------------- -->   

<div class='whGotoButtons whGotoButtonsbook'>
<div class='whGotoButtonRow2'>
	<div class='whButtonGoToBook whGotoButtonbook'>
		<button type='button' class='whGotoButton'
				<?php echo $disabled; ?>
				onclick='wtr_manageBook("create", 0, 0, 0, "<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&book_id='); ?>");'>
			<?php _e('Save & Go to Book','wtr_helper'); ?>
		</button>
	</div>
	<div class='whButtonGoToBook whGotoButtonbook'>
		<button type='button' class='whGotoButton'
				<?php echo $disabled; ?>
				onclick='wtr_manageBook("create", 0, 0, 0, "<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&scene=new&book_id='); ?>");'>
			<?php _e('Save & Go to Scene','wtr_helper'); ?>
		</button>
	</div>
	<div class='whButtonGoToBook whGotoButtonbook'>
<?php
	// "Storyboard" module exists ?
	if( class_exists("WH_Storyboard") ) {
?>
<!-- ---------------------------------------------- -->
<!--                 STORYBOARD                     -->
<!-- ---------------------------------------------- -->
		<button type='button' class='whGotoButton'
				<?php echo $disabled; ?>
				onclick='wtr_manageBook("create", 0, 0, 0, "<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&tab=book_storyboard&book_id='); ?>");'>
			<?php _e('Save & Go to Storyboard','wtr_helper'); ?>
		</button>
<?php
	} 
?>
	</div>
</div></div>