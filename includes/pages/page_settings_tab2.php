<br><br>

<?php
	// Get Default Book Settings
	$settings = new WH_BookSettings(0);
	// Get mandatory statuses
	$m_statuses = WH_Status::getMandatoryStatuses();
	// Get this book possible statuses
	$b_statuses = WH_Status::getBookStatuses();
?>
<div>
	<!-- --------------------------------------------------------- -->	
	<!--             DEFAULT BOOKS SETTINGS                        -->	
	<!-- --------------------------------------------------------- -->	
	<label class='whCategoriesTitle'>
		<?php _e('Default Books Settings','wtr_helper'); ?>
	</label><br><br>
	
	<div id="whDefaultBooksSettings">
		<table class='whDefaultBooksSettings'>
			<tr>
				<td><?php _e('Book/Chapter/Scene statuses','wtr_helper'); ?></td>
				<td><table><tr><td>
	<?php 
		$i = 0;
		foreach(WH_Status::STATUSES_NAME as $num => $status) {
			$checked  = in_array($num, $b_statuses)?'checked':'';
			$disabled = in_array($num, $m_statuses)?'disabled':'';
			$alt      = "";
			if( $disabled )
				$alt = __('This is a mandatory option','wtr_helper');
			if( ($i % 4) == 0)
				echo "</td><td>";
	?>
			<input type='checkbox' class='whBookStatusCB' title='<?php echo $alt;?>'
					id='<?php echo $num; ?>' <?php echo $checked." ".$disabled; ?>>
			<?php _e($status,'wtr_helper'); ?>
			<br/>
	<?php 
			$i++;
		}
	?>				
					</td></tr></table>
				</td>
			</tr>
		
			<tr><td colspan='2'><hr/></td>
			</tr>
		
			<tr>
				<td><?php _e('Book info to display via shortcodes','wtr_helper'); ?></td>
				<td><table><tr><td>
	<?php 
		foreach(WH_BookSettings::BookInfoList as $i => $bi) {
			$checked  = in_array($bi, $settings->get_BookInfo())?'checked':'';
			$disabled = in_array($bi, WH_BookSettings::minimum_display)?'disabled':'';
			$alt      = "";
			if( $disabled )
				$alt = __('This is a mandatory option','wtr_helper');
			if( ($i % 4) == 0)
				echo "</td><td>";
	?>
			<input type='checkbox' class='whBookInfoCB' title='<?php echo $alt;?>'
					id='<?php echo $bi; ?>' <?php echo $checked." ".$disabled; ?>>
			<?php _e($bi,'wtr_helper'); ?>
			<br/>
	<?php 
		}
	?>				
					</td></tr></table>
				</td>
			</tr>
		
			<tr><td colspan='2'><hr/></td>
			</tr>
		
			<tr>
				<td><?php _e("Next Chapter Label","wtr_helper"); ?></td>
				<td><input type="text" size="50" id="BS_nextC_label" value="<?php echo $settings->get_NextChapterLabel(); ?>"></td>
			</tr>
		
			<tr><td><?php _e("Previous Chapter Label","wtr_helper"); ?></td>
				<td><input type="text" size="50" id="BS_prevC_label" value="<?php echo $settings->get_PreviousChapterLabel(); ?>"></td>
			</tr>
		
			<tr>
				<td><?php _e("Book Ending Label","wtr_helper"); ?></td>
				<td><input type="text" size="50" id="BS_bookEnd_label" value="<?php echo $settings->get_BookEndingLabel(); ?>"></td>
			</tr>
		
			<tr><td><?php _e("Read more Label","wtr_helper"); ?></td>
				<td><input type="text" size="50" id="BS_readMore_label" value="<?php echo $settings->get_ReadMoreLabel(); ?>"></td>
			</tr>
		
			<tr>
				<td><?php _e("Next Chapter Unpublished Label","wtr_helper"); ?></td>
				<td><input type="text" size="50" id="BS_nextCU_label" value="<?php echo $settings->get_NextChapterUnpublishedLabel(); ?>"></td>
			</tr>
		
		</table>
		
	</div>
	<br/>
	<button type='button' class='whActionButton' 
			style='width:300px;height:40px;'
			onclick='wtr_manageCategory("update",0,"<?php echo WTRH_CAT_BOOKSETTINGS; ?>", "settings")'>
		<?php _e('Save parameters','wtr_helper'); ?>
	</button>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<button type='button' class='whActionButton' 
			style='width:300px;height:40px;'
			title='<?php _e('Replace specifc settings in all your books by default settings','wtr_helper'); ?>'
			onclick='wtr_manageCategory("delete",0,"<?php echo WTRH_CAT_BOOKSETTINGS; ?>", "settings")'>
		<?php _e('Apply default settings in all books','wtr_helper'); ?>
	</button>

</div>

<br><br>
	<!-- --------------------------------------------------------- -->	
	<!--                    BOOK TYPES                             -->	
	<!-- --------------------------------------------------------- -->
<div class="whBookTypes">
	<label class='whBookTypesTitle'>
		<?php _e('Book types','wtr_helper'); ?>
	</label><br><br>
	
	<div id='whBookTypesList'>
		<img src='<?php echo WTRH_IMG_URL."/loading.gif";?>' width='30'>
	</div>
	<input type='text' id='whNewBookType' value =''>
	<button type='button' class='whCategoryButton' 
			onclick='wtr_manageCategory("add",0,"<?php echo WTRH_CAT_BOOKTYPE; ?>")'>
		<?php _e('Add','wtr_helper'); ?>
	</button>
	<script>wtr_manageCategory("",0,"<?php echo WTRH_CAT_BOOKTYPE; ?>");</script>
</div>
