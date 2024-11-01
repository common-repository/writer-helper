<?php 

$tab1_selected = "";
$tab2_selected = "";
$tab3_selected = "";

if( ! isset($_GET['tab']) ) {
	$tab1_selected = "_selected";
	$_GET['tab'] = 'all';
} else {
	
	switch($_GET['tab']) {
		case 'all'         : $tab1_selected = "_selected"; break;
		case 'guests'      : $tab2_selected = "_selected"; break;
		case 'subscribers' : $tab3_selected = "_selected"; break;
		default                        : $tab1_selected = "_selected"; break;
	}
}

?>

<br>
<h1><?php _e('Writer Helper Communities','wtr_helper'); ?>
</h1>
<br>
<div>
<!-- --------------------------------------------------------------------------- -->
<!--                              Tabs Menu                                      -->
<!-- --------------------------------------------------------------------------- -->

	<div id="wtr_tabs">
		<div class="wtr_tab<?php echo $tab1_selected; ?>" 
			id="all"
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_COMMUNITIES_MENU.'&tab=all') ?>')">
				<?php _e('All communities','wtr_helper'); ?>

		</div>
		<div class="wtr_tab<?php echo $tab2_selected; ?>" 
			id="guests"	
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_COMMUNITIES_MENU.'&tab=guests') ?>')">
				<?php _e('Guests','wtr_helper'); ?>
			
		</div>
		<div class="wtr_tab<?php echo $tab3_selected; ?>" 
			id="subscribers"	
			onclick="wtr_openTab('<?php echo admin_url('admin.php?page='.WTRH_COMMUNITIES_MENU.'&tab=subscribers') ?>')">
				<?php _e('Subscribers','wtr_helper'); ?>
			
		</div>
	</div>
<!-- --------------------------------------------------------------------------- -->
<!--                                Contents                                     -->
<!-- --------------------------------------------------------------------------- -->
	<?php		
		if( strlen($tab1_selected) > 0 )
			include_once('page_communities_tab1.php');
	?>

	<?php
		if( strlen($tab2_selected) > 0 )
			include_once('page_communities_tab2.php');
	?>

	<?php
		if( strlen($tab3_selected) > 0 )
			include_once('page_communities_tab3.php');
	?>

</div>
