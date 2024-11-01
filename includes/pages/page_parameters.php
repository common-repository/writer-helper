<?php 
	include_once(WTRH_INCLUDE_DIR . '/classes/class_category.php');
	
?>
<br>
<h1><?php _e('Parameters','wtr_helper'); ?>
</h1>
<br>
<br>
<div class="whCategories">
	<label class='whCategoriesTitle'>
		<?php _e('Categories','wtr_helper'); ?>
	</label><br><br>
	
	<div class="whCategoryDate">
<?php  
		_e('Date format','wtr_helper');
		echo "<span class='dashicons dashicons-editor-help whQuestionMark' ".
				"onclick='wtr_showHide(\"helpDateFormat\")'></span>";
		echo " : ";
		echo "<input type='text' id='whDateFormat' ".
				"class='whCategoryInput' value='".
				WH_Category::get_DateFormat()."'>";
?>
		&nbsp;&nbsp;
		<button type='button' class='whCategoryButton'
		        onclick='wtr_changeDateFormat()' oninput='wtr_clear("whTestDateFormat")'>
		     <?php _e('Save','wtr_helper'); ?> </button>
		&nbsp;&nbsp;
		<button type='button' class='whCategoryButton'
		        onclick='wtr_testDateFormat()' oninput='wtr_clear("whTestDateFormat")'>
		     <?php _e('Test format','wtr_helper'); ?> </button>
		&nbsp;&nbsp;
		<label class='whTestFormat' id='whTestDateFormat'></label>
		<br>
		<div class='whHelp' id='helpDateFormat'>
			<?php _e('Enter a date format using the following characters.','wtr_helper'); ?><br>
			<table>
			<tr><td>&nbsp;d&nbsp;&nbsp;</td><td><?php _e('Day of the month, 2 digits with leading zeros','wtr_helper'); ?></td><td>(01 <?php _e('to','wtr_helper'); ?> 31)</td></tr>
			<tr><td>&nbsp;j&nbsp;&nbsp;</td><td><?php _e('Day of the month without leading zeros','wtr_helper'); ?></td><td>(1 <?php _e('to','wtr_helper'); ?> 31)</td></tr>
			<tr><td>&nbsp;m&nbsp;&nbsp;</td><td><?php _e('Numeric representation of a month, with leading zeros','wtr_helper'); ?></td><td>(01 <?php _e('to','wtr_helper'); ?> 12)</td></tr>
			<tr><td>&nbsp;M&nbsp;&nbsp;</td><td><?php _e('A short textual representation of a month, three letters','wtr_helper'); ?></td><td>(<?php _e('Jan through Dec','wtr_helper'); ?>)</td></tr>
			<tr><td>&nbsp;F&nbsp;&nbsp;</td><td><?php _e(' 	A full textual representation of a month, such as January or March','wtr_helper'); ?></td><td></td></tr>
			<tr><td>&nbsp;n&nbsp;&nbsp;</td><td><?php _e('Numeric representation of a month, without leading zeros','wtr_helper'); ?></td><td>(1 <?php _e('to','wtr_helper'); ?> 12)</td></tr>
			<tr><td>&nbsp;Y&nbsp;&nbsp;</td><td><?php _e('A full numeric representation of a year, 4 digits','wtr_helper'); ?></td><td>(Ex: 1999 <?php _e('or','wtr_helper'); ?> 2003)</td></tr>
			<tr><td>&nbsp;y&nbsp;&nbsp;</td><td><?php _e('A two digit representation of a year','wtr_helper'); ?></td><td>(Ex: 99 <?php _e('or','wtr_helper'); ?> 03)</td></tr>
			</table>
		</div>
	</div>
	<br/>
	<div class="whCategoryTime">
<?php  
		_e('Time format','wtr_helper');
		echo "<span class='dashicons dashicons-editor-help whQuestionMark' ".
				"onclick='wtr_showHide(\"helpTimeFormat\")'></span>";
		echo " : ";
		echo "<input type='text'id='whTimeFormat' ".
				"class='whCategoryInput' value='".
				WH_Category::get_TimeFormat()."'>";
?>
		&nbsp;&nbsp;
		<button type='button' class='whCategoryButton'
		        onclick='wtr_changeTimeFormat()'>
		     <?php _e('Save','wtr_helper'); ?> </button>
		&nbsp;&nbsp;
		<button type='button' class='whCategoryButton'
		        onclick='wtr_testTimeFormat()' oninput='wtr_clear("whTestTimeFormat")'>
		     <?php _e('Test format','wtr_helper'); ?> </button>
		&nbsp;&nbsp;
		<label class='whTestFormat' id='whTestTimeFormat'></label>
		<br>
		<div class='whHelp' id='helpTimeFormat'>
			<?php _e('Enter a time format using the following characters.','wtr_helper'); ?><br>
			<table>
			<tr><td>&nbsp;a&nbsp;&nbsp;</td><td><?php _e('Lowercase Ante meridiem and Post meridiem','wtr_helper'); ?></td><td>(am <?php _e('or','wtr_helper'); ?> pm)</td></tr>
			<tr><td>&nbsp;A&nbsp;&nbsp;</td><td><?php _e('Uppercase Ante meridiem and Post meridiem','wtr_helper'); ?></td><td>(AM <?php _e('or','wtr_helper'); ?> PM)</td></tr>
			<tr><td>&nbsp;g&nbsp;&nbsp;</td><td><?php _e('12-hour format of an hour without leading zeros','wtr_helper'); ?></td><td>(1 <?php _e('through','wtr_helper'); ?> 12)</td></tr>
			<tr><td>&nbsp;G&nbsp;&nbsp;</td><td><?php _e('24-hour format of an hour without leading zeros','wtr_helper'); ?></td><td>(0 <?php _e('through','wtr_helper'); ?> 23)</td></tr>
			<tr><td>&nbsp;h&nbsp;&nbsp;</td><td><?php _e('12-hour format of an hour with leading zeros','wtr_helper'); ?></td><td>(01 <?php _e('through','wtr_helper'); ?> 12)</td></tr>
			<tr><td>&nbsp;H&nbsp;&nbsp;</td><td><?php _e('24-hour format of an hour with leading zeros','wtr_helper'); ?></td><td>(00 <?php _e('through','wtr_helper'); ?> 23)</td></tr>
			<tr><td>&nbsp;i&nbsp;&nbsp;</td><td><?php _e('Minutes with leading zeros','wtr_helper'); ?></td><td>(00 <?php _e('to','wtr_helper'); ?> 59)</td></tr>
			</table>
		</div>
	</div>


</div>

<br><br>
<div class="whBookTypes">
	<label class='whBookTypesTitle'>
		<?php _e('Book types','wtr_helper'); ?>
	</label><br><br>
	
	<div id='whBookTypesList'>
	</div>
	<input type='text' id='whNewBookType' value =''>
	<button type='button' class='whCategoryButton' 
			onclick='wtr_manageCategory("add",0,"<?php echo WTRH_CAT_BOOKTYPE; ?>")'>
		<?php _e('Add','wtr_helper'); ?>
	</button>
	<script>wtr_manageCategory("",0,"<?php echo WTRH_CAT_BOOKTYPE; ?>");</script>
</div>
