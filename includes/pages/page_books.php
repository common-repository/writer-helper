<h1><?php _e('My Books','wtr_helper');?></h1>
<table style='padding:0;margin:0;border-collapse:collapse;'>
<tr><td><?php _e('Search by title','wtr_helper'); ?> </td><td>
<input type='text' class='whSearchInput' id='wtrh_book_title' oninput='wtrh_GetBooksList()'>
</td></tr>
<tr><td><?php _e('Search by status','wtr_helper'); ?></td><td>
<?php 		
		foreach ( WH_Status::BOOK_STATUSES as $k => $st ) {
			echo "<span class='wh_buttonCBSpan'>";
			echo "<input type='checkbox' class='wh_buttonCB wh_buttonCBBookStatus'".
						" id='".$st."' name='".$st."'";
			echo " onclick='wtrh_GetBooksList()'";
			echo ">";
			echo "<label for='".$st."'>".WH_Status::getStatusName($st)."</label>";
			echo "</span>&nbsp;";
			if( $k > 1 &&  $k%5 == 0 )
				echo "<br>";
		}
?>		
	</td></tr>
</table>
<br>
<div id='wtrh_booksList'><?php echo wtrh_getPageHTMLBooksList(""); ?></div>
<br/><br/>
