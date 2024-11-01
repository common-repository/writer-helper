<br><br>
<?php
	// "Readers" module exists ?
	$disabledR = "disabled";
	if( class_exists("WH_Readers") ) 
		$disabledR = "";
	// "Bookworld" module exists ?
	$disabledBW = "disabled";
	if( class_exists("WH_Bookworld") ) 
		$disabledBW = "";
	
	$ba = $book->get_BookInfo();
	if( ! isset($ba['freeChapter']) )
		$ba['freeChapter'] = -1;
	if( ! isset($ba['seePublishedBook']) )
		$ba['seePublishedBook'] = WTRH_BACCESS_ALL;
	if( ! isset($ba['seeHidden']) )
		$ba['seeHidden'] = WTRH_BACCESS_NONE;
	if( ! isset($ba['seePreview']) )
		$ba['seePreview'] = WTRH_BACCESS_NONE;
	if( ! isset($ba['seeBookworld']) )
		$ba['seeBookworld'] = WTRH_BACCESS_NONE;
	
	$nbFC = __('All','wtr_helper');
	if( is_numeric($ba['freeChapter']) )
		$nbFC = $ba['freeChapter'];
	
?>
<input type="hidden" id="whBookSettingsTab" value="yes">
<label><?php _e('How many free Chapters','wtr_helper'); ?></label>
&nbsp;:&nbsp;<input type='text' id='whNbFreeChapter' 
	value='<?php echo $nbFC; ?>' 
	placeholder='<?php _e('Enter a number ("All" by default)','wtr_helper'); ?>'>
&nbsp;&nbsp;
<span class="whNotaBene"><?php _e('Enter -1 if all chapters are free','wtr_helper'); ?></span>
<br><br>

<label><?php _e('Users who can see the published book/chapter','wtr_helper'); ?></label><br>
<?php 
	global $wtr_book_access;
	foreach( $wtr_book_access as $key => $a ) {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' ".
			 "name='whPublishedBook' id='".$key."' value='".$key."' ";
		if( $key == WTRH_BACCESS_ALL && $ba['seePublishedBook'] == WTRH_BACCESS_ALL )
			echo "checked";
		if( $key == WTRH_BACCESS_READER && $ba['seePublishedBook'] == WTRH_BACCESS_READER )
			echo "checked";
		if( $key == WTRH_BACCESS_READERP && $ba['seePublishedBook'] == WTRH_BACCESS_READERP )
			echo "checked";
		if( $key == WTRH_BACCESS_SEL && $ba['seePublishedBook'] == WTRH_BACCESS_SEL )
			echo "checked";
		if( $key == WTRH_BACCESS_NONE && $ba['seePublishedBook'] == WTRH_BACCESS_NONE )
			echo "checked";
		echo " ".$disabledR.">".$a."<br>\n";
	}
?>
<br><br>

<label><?php _e('Users who can see the hidden scenes/chapters','wtr_helper'); ?></label><br>
<?php 
	global $wtr_book_access;
	foreach( $wtr_book_access as $key => $a ) {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' ".
			 "name='whSeeHidden' id='".$key."' value='".$key."' ";
		if( $key == WTRH_BACCESS_ALL && $ba['seeHidden'] == WTRH_BACCESS_ALL )
			echo "checked";
		if( $key == WTRH_BACCESS_READER && $ba['seeHidden'] == WTRH_BACCESS_READER )
			echo "checked";
		if( $key == WTRH_BACCESS_READERP && $ba['seeHidden'] == WTRH_BACCESS_READERP )
			echo "checked";
		if( $key == WTRH_BACCESS_SEL && $ba['seeHidden'] == WTRH_BACCESS_SEL )
			echo "checked";
		if( $key == WTRH_BACCESS_NONE && $ba['seeHidden'] == WTRH_BACCESS_NONE )
			echo "checked";
		echo " ".$disabledR.">".$a."<br>\n";
	}
?>
<br><br>

<label><?php _e('Users who can see the book preview','wtr_helper'); ?></label><br>
<?php 
	global $wtr_book_access;
	foreach( $wtr_book_access as $key => $a ) {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' ".
			 "name='whSeePreview' id='".$key."' value='".$key."' ";
		if( $key == WTRH_BACCESS_ALL && $ba['seePreview'] == WTRH_BACCESS_ALL )
			echo "checked";
		if( $key == WTRH_BACCESS_READER && $ba['seePreview'] == WTRH_BACCESS_READER )
			echo "checked";
		if( $key == WTRH_BACCESS_READERP && $ba['seePreview'] == WTRH_BACCESS_READERP )
			echo "checked";
		if( $key == WTRH_BACCESS_SEL && $ba['seePreview'] == WTRH_BACCESS_SEL )
			echo "checked";
		if( $key == WTRH_BACCESS_NONE && $ba['seePreview'] == WTRH_BACCESS_NONE )
			echo "checked";
		echo " ".$disabledR.">".$a."<br>\n";
	}
?>
<br><br>

<label><?php _e('Users who can see the bookworld','wtr_helper'); ?></label><br>
<?php 
	global $wtr_book_access;
	foreach( $wtr_book_access as $key => $a ) {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' ".
			 "name='whSeeBookworld' id='".$key."' value='".$key."' ";
		if( $key == WTRH_BACCESS_ALL && $ba['seeBookworld'] == WTRH_BACCESS_ALL )
			echo "checked";
		if( $key == WTRH_BACCESS_READER && $ba['seeBookworld'] == WTRH_BACCESS_READER )
			echo "checked";
		if( $key == WTRH_BACCESS_READERP && $ba['seeBookworld'] == WTRH_BACCESS_READERP )
			echo "checked";
		if( $key == WTRH_BACCESS_SEL && $ba['seeBookworld'] == WTRH_BACCESS_SEL )
			echo "checked";
		if( $key == WTRH_BACCESS_NONE && $ba['seeBookworld'] == WTRH_BACCESS_NONE )
			echo "checked";
		echo " ".$disabledBW.">".$a."<br>\n";
	}
?>

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
