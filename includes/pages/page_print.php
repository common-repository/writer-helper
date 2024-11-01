<?php
/*************************
 * Print a book
 *************************/
include_once(WTRH_INCLUDE_DIR . "/functions/cmn_functions.php");
include_once(WTRH_INCLUDE_DIR . "/classes/class_book.php");

	// get Book
	$book_id = isset($_GET['book_id'])? wtr_sanitize($_GET['book_id'],'int'):0;
	$book    = new WH_Book($book_id);
	
	if( $book_id == 0 || ! $book->isOk ) {
		echo sprintf(__('Book reference not found : %s','wtr_helper'), $book_id);
		exit;
	}
	
	
	// Display book cover
	$cover_url = $book->get_CoverUrl();
	if( strlen(trim($cover_url)) > 0 ) {
		echo "<p class='whPrintBook_Cover'>".
			 "<img src='".$cover_url.
			 "' alt='".__('Cover','wtr_helper')."' />".
			 "</p>\n";
	} else {
		echo "<p class='whPrintBook_Cover'>".
			 "<span class='whPrintBook_CoverTitle'>".$book->title."</span>";

		foreach( $book->get_BookAuthors() as $a )
			echo "<span class='whPrintBook_CoverAuthor'>".$a['name']."</span><br/>";
		
		echo "</p>";
	}
	echo "<p class='whPageBreak'>&nbsp;</p>\n";
	
	
	// Book resume
	echo "<h1 class='whPrintBook_ResumeTitle'>".
			__('Resume','wtr_helper')."</h1>";
	echo $book->resume;
	echo "<p class='whPageBreak'>&nbsp;</p>\n";
	
	
	// Book summary
	echo "<h1 class='whPrintBook_SummaryTitle'>".
			__('Summary','wtr_helper')."</h1><br/>";
	echo "<ul class='whPrintBook_SummaryList'>\n";
	foreach( $book->get_ChaptersText() as $key => $ch ) 
		echo "<li><a href='#ct-".$key."'>".
				str_replace('<br/>',' : ',$ch['title'])."</a></li>\n";
	echo "</ul>";
	echo "<p class='whPageBreak'>&nbsp;</p>\n";
	
	
	// Book chapters
	foreach( $book->get_ChaptersText() as $key => $ch ) {
		$ch_text = "";
		// Group text scenes
		foreach( $ch['scenes'] as $sc )
			$ch_text .= $sc['text'].
						"\n<br/>".
						"<p class='sceneSeparator'>***</p>".
						"<br/>\n";
		
		// Display Chapter
		echo "<h1 class='whPrintBook_ChapterTitle' id='ct-".$key."'>".$ch['title']."</h1>\n";
		echo $ch_text;
		echo "<p class='whPageBreak'>&nbsp;</p>\n";
	}
	
?>