<br><br>
<?php
	// Get Default Book Settings
	$settings   = new WH_BookSettings($book->id);
	// Get mandatory statuses
	$m_statuses = WH_Status::getMandatoryStatuses();
	// Get this book possible statuses
	$b_statuses = WH_Status::getBookStatuses($book->id);
?>
<div>
	<!-- --------------------------------------------------------- -->	
	<!--             BOOKS DISPLAY SETTINGS                        -->	
	<!-- --------------------------------------------------------- -->	
	<input type="hidden" id="whBookDisplaySettingsTab" value="yes">
	
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
				<td><?php _e('Next Chapter Label','wtr_helper'); ?></td>
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
		
			<tr>
				<td><br/></td>
				<td></td>
			</tr>
		
			<tr>
				<td><?php _e("Book Customised status","wtr_helper"); ?></td>
				<td><input type="text" size="50" id="BS_customStatus_label" value="<?php echo $settings->get_CustomStatusLabel(); ?>"></td>
			</tr>
		
		</table>
		
	</div>

</div>

<!-- ------------------------------------- -->                    
<!--             BUTTONS                   -->                    
<!-- ------------------------------------- -->                    
<br/><br/>
<div class="whMsg" id="whMsg"></div>

<br/><br/>
<?php 
	echo getActionButtons("book", $book->status, "saveSettings", $book->id, $book->id);
	echo getGotoButtons("book", $book->status, $book->id); ?>
<br/><br/>
