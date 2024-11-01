<?php 
/**
 ** Shortcodes for managings a list of books 
 **/
include_once(WTRH_INCLUDE_DIR . '/classes/class_book.php');
	
// Display all published books
function wtr_shortcodes_books($atts) {
	$a = shortcode_atts( array(), $atts );
	
	// get books
	$books = WH_Book::getAll_LibraryBooks("title", "asc");
	
	// Display books
	$html = "<div class='whsBooksDiv'>\n";
	$html .= "<table class='whsBooksList'>\n";
	foreach( $books as $b ) {
		$html .= "<tr>"."<td>\n";
		$html .= wtrh_getBookPanel($b->id);
		$html .= "</td></tr>\n"; // end book
		
		$html .= "<tr><td colspan='2'><hr/></td></tr>\n"; 
	}
	$html .= "</table>\n</div>";	
	return $html;
}
add_shortcode('writerhelper_books', 'wtr_shortcodes_books');

?>