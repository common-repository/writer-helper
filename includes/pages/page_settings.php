<?php 

$tab1_selected = "";
$tab2_selected = "";
$tab3_selected = "";
$tab4_selected = "";
$tab5_selected = "";

if( ! isset($_GET['tab']) ) {
	$tab1_selected = "_selected";
	$_GET['tab'] = 'settings_general';
} else {
	
	switch($_GET['tab']) {
		case 'settings_general'   : $tab1_selected = "_selected"; break;
		case 'settings_books'     : $tab2_selected = "_selected"; break;
		case 'settings_books_usr' : $tab3_selected = "_selected"; break;
		case 'settings_arcs' : $tab4_selected = "_selected"; break;
		case 'settings_archetyps' : $tab5_selected = "_selected"; break;
		default                   : $tab1_selected = "_selected"; break;
	}
}

?>

<br>
<h1><?php _e('Settings','wtr_helper'); ?>
</h1>
<br>
<div>
<!-- --------------------------------------------------------------------------- -->
<!--                              Tabs Menu                                      -->
<!-- --------------------------------------------------------------------------- -->

	<div id="wtr_tabs">
		<div class="wtr_tab<?php echo $tab1_selected; ?>" 
			id="settings_general"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_SETTINGS_MENU.'&tab=settings_general') ?>')">
				<?php _e('General','wtr_helper'); ?>

		</div>
		<div class="wtr_tab<?php echo $tab2_selected; ?>" 
			id="settings_books"	
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_SETTINGS_MENU.'&tab=settings_books') ?>')">
				<?php _e('Books','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab3_selected; ?>" 
			id="settings_books"	
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_SETTINGS_MENU.'&tab=settings_books_usr') ?>')">
				<?php _e('Books Users','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab4_selected; ?>" 
			id="settings_arcs"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_SETTINGS_MENU.'&tab=settings_arcs') ?>')">
				<?php _e('Story Arcs','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab5_selected; ?>" 
			id="settings_archetyps"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_SETTINGS_MENU.'&tab=settings_archetyps') ?>')">
				<?php _e('Archetyps','wtr_helper'); ?>
			
		</div>
	</div>
<!-- --------------------------------------------------------------------------- -->
<!--                                Contents                                     -->
<!-- --------------------------------------------------------------------------- -->
	<?php		
		if( strlen($tab1_selected) > 0 )
			include_once('page_settings_tab1.php');
	?>

	<?php
		if( strlen($tab2_selected) > 0 )
			include_once('page_settings_tab2.php');
	?>

	<?php
		if( strlen($tab3_selected) > 0 )
			include_once('page_settings_tab3.php');
	?>

	<?php
		if( strlen($tab4_selected) > 0 )
			include_once('page_settings_tab4.php');
	?>

	<?php
		if( strlen($tab5_selected) > 0 )
			include_once('page_settings_tab5.php');
	?>

</div>
