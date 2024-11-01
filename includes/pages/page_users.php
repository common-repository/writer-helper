<?php 

$tab1_selected = "";
$tab2_selected = "";
$tab3_selected = "";
$tab4_selected = "";

if( ! isset($_GET['tab']) ) {
	$tab1_selected = "_selected";
	$_GET['tab'] = 'users_admin';
} else {
	
	switch($_GET['tab']) {
		case 'users_admin'   : $tab1_selected = "_selected"; break;
		case 'users_writers' : $tab2_selected = "_selected"; break;
		case 'users_editors' : $tab3_selected = "_selected"; break;
		case 'users_readers' : $tab4_selected = "_selected"; break;
		default              : $tab1_selected = "_selected"; break;
	}
}

?>

<br>
<h1><?php _e('Writer Helper Users','wtr_helper'); ?>
</h1>
<br>
<div>
<!-- --------------------------------------------------------------------------- -->
<!--                              Tabs Menu                                      -->
<!-- --------------------------------------------------------------------------- -->

	<div id="wtr_tabs">
		<div class="wtr_tab<?php echo $tab1_selected; ?>" 
			id="users_admin"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_USERS_MENU.'&tab=users_admin') ?>')">
				<?php _e('Administrators','wtr_helper'); ?>

		</div>
		<div class="wtr_tab<?php echo $tab2_selected; ?>" 
			id="users_writers"	
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_USERS_MENU.'&tab=users_writers') ?>')">
				<?php _e('Writers','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab3_selected; ?>" 
			id="users_editors"	
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_USERS_MENU.'&tab=users_editors') ?>')">
				<?php _e('Editors','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab4_selected; ?>" 
			id="users_readers"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_USERS_MENU.'&tab=users_readers') ?>')">
				<?php _e('Readers','wtr_helper'); ?>
			
		</div>
	</div>
<!-- --------------------------------------------------------------------------- -->
<!--                                Contents                                     -->
<!-- --------------------------------------------------------------------------- -->
	<?php		
		if( strlen($tab1_selected) > 0 )
			include_once('page_users_tab1.php');
	?>

	<?php
		if( strlen($tab2_selected) > 0 )
			include_once('page_users_tab2.php');
	?>

	<?php
		if( strlen($tab3_selected) > 0 )
			include_once('page_users_tab3.php');
	?>

	<?php
		if( strlen($tab4_selected) > 0 )
			include_once('page_users_tab4.php');
	?>

</div>
