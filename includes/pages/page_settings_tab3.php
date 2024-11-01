<br><br>

<?php
	// "Writers&Editors" module exists ?
	$activeWE = false;
	if( file_exists(WTRH_WRITEDIT_DIR."/writerhelper_writerseditors.php") ) 
		$activeWE = true;
	// "Bookworld" module exists ?
	$activeBW = false;
	if( class_exists("WH_Bookworld") ) 
		$activeBW = true;
	// "Storyboard" module exists ?
	$activeSB = false;
	if( class_exists("WH_Storyboard") ) 
		$activeSB = true;
	
	// Get default values
	$nbBA     = "Unlimited";
	$useBW    = "";
	$nbBWA    = "Unlimited";
	$useSB    = "";
	$useStatA = "";
	$useToDoA = "";
	$editAll  = "checked";
	$useStatE = "";
	$useToDoE = "";
	$idAS = 0;	// author's settings category id
	$idES = 0;  // editor's settings category id
	$books_settings = WH_Category::get_BooksSettings();
	foreach( $books_settings as $s ) {
		if( $s->title == "Authors" ) {
			$idAS    = $s->id;
			$s_array = json_decode($s->description, true);
			$nbBA    = (isset($s_array['nbBooks']) && $s_array['nbBooks'] > -1)?$s_array['nbBooks']:"Unlimited";
			$useBW   = (isset($s_array['useBookworld']) && $s_array['useBookworld'] == true)?"checked":"";
			$nbBWA   = (isset($s_array['nbBookworlds']) && $s_array['nbBookworlds'] > -1)?$s_array['nbBookworlds']:"Unlimited";
			$useSB   = (isset($s_array['useStoryboard']) && $s_array['useStoryboard'] == true)?"checked":"";
			$useStatA= (isset($s_array['useStatistics']) && $s_array['useStatistics'] == true)?"checked":"";
			$useToDoA= (isset($s_array['useToDoList'])   && $s_array['useToDoList'] == true)  ?"checked":"";
		}
		if( $s->title == "Editors" ) {
			$idES    = $s->id;
			$s_array = json_decode($s->description, true);
			$editAll = (isset($s_array['editAllBooks'])  && $s_array['editAllBooks'] == true) ?"checked":"";
			$useStatE= (isset($s_array['useStatistics']) && $s_array['useStatistics'] == true)?"checked":"";
			$useToDoE= (isset($s_array['useToDoList'])   && $s_array['useToDoList'] == true)  ?"checked":"";
		}
	}
?>
<div>
	<!-- --------------------------------------------------------- -->	
	<!--            AUTHOR DEFAULT SETTINGS                        -->	
	<!-- --------------------------------------------------------- -->	
	<label class='whCategoriesTitle'>
		<?php _e('Authors default settings','wtr_helper'); ?>
	</label><br><br>
	
	<table>
	<tr><td>
		<label>
			<?php _e('Books per author','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='text' id='whNbBooksAuthor' 
				value='<?php echo $nbBA; ?>' 
				style='width:90px' <?php echo ($activeWE)?'':'disabled'; ?>>
	</td></tr>
	<tr><td>
		<label>
			<?php _e('Authors can use Bookworlds','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='checkbox' id='whUseBookworlds' <?php echo $useBW; ?> <?php echo ($activeWE && $activeBW)?'':'disabled'; ?>>
		
	</td></tr>
	<tr><td>
		<label>
			<?php _e('Bookworlds per author','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='text' id='whNbBookworldsAuthor' 
				value='<?php echo $nbBWA; ?>' 
				style='width:90px' <?php echo ($activeWE && $activeBW)?'':'disabled'; ?>>

	</td></tr>
	
	<tr><td>	
		<label>
			<?php _e('Authors can use Storyboard','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='checkbox' id='whUseStoryboard' <?php echo $useSB; ?> <?php echo ($activeWE && $activeSB)?'':'disabled'; ?>>
	</td></tr>
	
	<tr><td>	
		<label>
			<?php _e('Authors can use Statistics','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='checkbox' id='whUseStatA' <?php echo $useStatA; ?> <?php echo ($activeWE)?'':'disabled'; ?>>
	</td></tr>
	
	<tr><td>	
		<label>
			<?php _e('Authors can use To Do List','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='checkbox' id='whUseToDoA' <?php echo $useToDoA; ?> <?php echo ($activeWE)?'':'disabled'; ?>>
	</td></tr>

	<tr><td></td><td>
	<input type='button' value='<?php _e('Save','wtr_helper'); ?>' 
			onclick='wtr_manageCategory("modifyAuthorsSettings",<?php echo $idAS; ?>,"")' <?php echo ($activeWE)?'':'disabled'; ?>>
	
	</td></tr>

	<!-- --------------------------------------------------------- -->	
	<!--            EDITOR DEFAULT SETTINGS                        -->	
	<!-- --------------------------------------------------------- -->	
	<tr><td colspan='2'>
	<br><br>
	<label class='whCategoriesTitle'>
		<?php _e('Editors default settings','wtr_helper'); ?>
	</label><br><br>
	
	</td></tr>
	<tr><td>
		<label>
			<?php _e('Edit all books','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='checkbox' id='whEditAllBooks' <?php echo $editAll; ?> <?php echo ($activeWE)?'':'disabled'; ?>>
	</td></tr>
	
	<tr><td>	
		<label>
			<?php _e('Editors can use Statistics','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='checkbox' id='whUseStatE' <?php echo $useStatE; ?> <?php echo ($activeWE)?'':'disabled'; ?>>
	</td></tr>
	
	<tr><td>	
		<label>
			<?php _e('Editors can use To Do List','wtr_helper'); ?>
		</label> : 
	</td><td>
		<input type='checkbox' id='whUseToDoE' <?php echo $useToDoE; ?> <?php echo ($activeWE)?'':'disabled'; ?>>
	</td></tr>
	
	<tr><td></td><td>
	<input type='button' value='<?php _e('Save','wtr_helper'); ?>' 
			onclick='wtr_manageCategory("modifyEditorsSettings",<?php echo $idES; ?>,"")' <?php echo ($activeWE)?'':'disabled'; ?>>
	
	</td></tr>
	</table>
</div>
