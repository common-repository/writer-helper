<?php 
	include_once(WTRH_INCLUDE_DIR . '/classes/class_book.php');
	include_once(WTRH_INCLUDE_DIR . '/classes/class_category.php');
	include_once(WTRH_INCLUDE_DIR . '/functions/buttons_functions.php');
	
	$book       = new WH_Book(wtr_sanitize($_GET['book_id'],'int'), null, true);
	$types      = WH_Category::get_BookTypes();
	$disabled   = '';
	$isDisabled = false;
	$isAuthor   = $book->isAnAuthor(get_current_user_id());
	$isAdmin    = WH_User::userExists(get_current_user_id(), WTRH_ROLE_ADMIN);
	$isGameBook = $book->isGameBook;
	$gameBook   = ($book->isGameBook)?'checked':'';
	
	if( $book->status != WH_Status::DRAFT ) 
		$isDisabled = true;
	
	if( (! $isAdmin && ! $isAuthor) || $book->status > WH_Status::EDITED ) {
		$disabled = 'disabled ';
	}

	// Select tab to display
	$tab1_selected = "";
	$tab2_selected = "";
	$tab3_selected = "";
	$tab4_selected = "";
	$tab5_selected = "";

	if( ! isset($_GET['tab']) ) {
		$tab1_selected = "_selected";
		$_GET['tab'] = 'book_general';
	} else {
		
		switch($_GET['tab']) {
			case 'book_general'       : $tab1_selected = "_selected"; break;
			case 'book_chapters'      : $tab2_selected = "_selected"; break;
			case 'book_storyboard'    : $tab3_selected = "_selected"; break;
			case 'book_settings'      : $tab4_selected = "_selected"; break;
			case 'book_users'         : $tab5_selected = "_selected"; break;
			default                   : $tab1_selected = "_selected"; break;
		}
	}

?>

<br>
<h1 class="whBookTitle">
	  <?php if( $book->isGameBook ) { ?>
		<span class='dashicons dashicons-games whGameBookBig' 
				title='<?php _e('Game Book','wtr_helper'); ?>'> </span>
	  <?php } ?>
  <?php echo $book->title; ?>
  <div id="whBookStatus" style='display: inline'>
	  <span class="whStatus" style="<?php echo WH_Status::getStatusStyle($book->status); ?>">
		&nbsp;
		<?php echo WH_Status::getStatusName($book->status); ?>
		&nbsp;
	  </span>
  </div>
</h1>
<input type="hidden" id="book_id" value="<?php echo $book->id; ?>">
<input type="hidden" id="whBookSaved" value="yes">
<br>
<div>
<!-- --------------------------------------------------------------------------- -->
<!--                              Tabs Menu                                      -->
<!-- --------------------------------------------------------------------------- -->

	<div id="wtr_tabs">
		<div class="wtr_tab<?php echo $tab1_selected; ?>" 
			id="book_general"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&book_id='.$book->id.'&tab=book_general') ?>')">
				<?php _e('General','wtr_helper'); ?>

		</div>
		<div class="wtr_tab<?php echo $tab2_selected; ?>" 
			id="book_chapters"	
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&book_id='.$book->id.'&tab=book_chapters') ?>')">
				<?php _e('Chapters List','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab3_selected; ?>" 
			id="book_storyboard"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&book_id='.$book->id.'&tab=book_storyboard') ?>')">
				<?php _e('Storyboard','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab4_selected; ?>" 
			id="book_settings"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&book_id='.$book->id.'&tab=book_settings') ?>')">
				<?php _e('Book Settings','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab5_selected; ?>" 
			id="book_users"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&book_id='.$book->id.'&tab=book_users') ?>')">
				<?php _e('Book Users Settings','wtr_helper'); ?>
			
		</div>
	</div>
<!-- --------------------------------------------------------------------------- -->
<!--                                Contents                                     -->
<!-- --------------------------------------------------------------------------- -->
	<?php		
		if( strlen($tab1_selected) > 0 )
			include_once('page_book_tab1.php');
	?>

	<?php
		if( strlen($tab2_selected) > 0 )
			include_once('page_book_tab2.php');
	?>

	<?php
		if( strlen($tab3_selected) > 0 )
			include_once('page_book_tab3.php');
	?>

	<?php
		if( strlen($tab4_selected) > 0 )
			include_once('page_book_tab4.php');
	?>

	<?php
		if( strlen($tab5_selected) > 0 )
			include_once('page_book_tab5.php');
	?>

</div>
