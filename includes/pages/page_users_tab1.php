<br><br>
<?php _e('Add an administrator to Writer Helper','wtr_helper'); ?><br/>
<!--?php _e('N.B. : A Writer Helper administrator must be a WordPress administrator','wtr_helper'); ?><br/><br/-->
<input type='text' id='whAddUserInput' 
	oninput='wtr_listUser()' value='' 
	placeholder='<?php _e('Enter an administrator name','wtr_helper'); ?>'>&nbsp;&nbsp;&nbsp;
<input id='whUserRole' type='hidden' value='<?php echo WTRH_ROLE_ADMIN; ?>'>
<div id='whAddUserListDiv'></div>

<br><br><br><br>
<div id='whUsersTable'>
<?php 	
	// If current user is admin and not in the list of WH admin => add user as WH admin
	$wp_user_id = get_current_user_id();
	if( count(getWordPressAdminUsers($wp_user_id)) == 0 )
		addWriterHelperAdministrator($wp_user_id);

	// Get the list of Writer Helper Administrators
	$admins = WH_User::getAll_Admins();
	
	// If no Writer Helper admin found => create WH admins from WordPress admin
	if( count($admins) == 0 ) {
		addWriterHelperAdministrators();
		$admins = WH_User::getAll_Admins();
	}
	
	echo "<table class='wh_usersList'>\n";

	echo "<tr class='wh_userInfoHeader'>";
	echo "<th>".__('Role','wtr_helper')."</th>";
	echo "<th>".__('WordPress display name','wtr_helper')."</th>";
	echo "<th></th>";
	echo "</tr>";

	foreach( $admins as $a ) {
		echo "<tr class='wh_userInfo'>";
		echo "<td>".$a->meta_key."</td>";
		echo "<td>".WH_User::getWpUserName($a->user_id)."</td>";
		echo "<td><button class='whDeleteUser' ".
				"onclick='wtr_manageUser(\"delete\",".$a->id.",\"whAdminList\")'>".
				__('Delete','wtr_helper')."</button></td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	
?> 
</div>