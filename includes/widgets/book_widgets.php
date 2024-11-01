<?php 
/**
 ** Widgets for managings a list of books 
 **/
include_once(WTRH_INCLUDE_DIR . '/classes/class_book.php');

// Display the summary of a selected book
class WH_BookSummary extends WP_Widget {

  // sets up widget name and args
  function __construct() {
    $widget_args = array(
      'classname'   => 'widget_wh_book_summary',
      'description' => __('Display the summary of the selected book','wtr_helper')
    );

    $control_args = array();

    parent::__construct('wh_book_summary', __('Writer Helper - Book\'s summary','wtr_helper'),
						$widget_args, $control_args
    );
  }

  // outputs widget content
  function widget($args, $instance) {
	  // retrieve book_id if update
		$book_id = isset($instance['book_id']) ? (int)$instance['book_id']:0;
		
	if( $book_id != 0 ) {
		// get book's info
		$book = new WH_Book($book_id);
		if( ! $book->isOk ) {
			echo sprintf(__('Book not found with id %s','wtr_helper'), $book_id);
			return false;
		}
		
		// Display summary
		echo $book->get_BookSummaryHTML(get_current_user_id());
		
	}
  }

  // save widget config
  function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['book_id'] = isset( $new_instance['book_id'] ) ? intval( $new_instance['book_id'] ) : 0;

		return $instance;
	}

  // outputs widget's options form on admin
  function form($instance) {
	  // retrieve book_id if update
		$book_id = isset($instance['book_id']) ? intval($instance['book_id']):0;
		// get all books
		$books = WH_Book::getAll_Books("", null, "title", "asc");
		// display books' list
		echo "<p><label>".__('Choose a book','wtr_helper')." : </label><br>";
		echo "<select id='".$this->get_field_id('book_id')."'";
		echo " name='".$this->get_field_name('book_id')."'>";
		echo "<option value='' ".(($book_id==0)?'selected':'')."> </option>";
		foreach( $books as $b ) {
			echo "<option value='".$b->id."'";
			if( $book_id == $b->id )
				echo " selected";
			echo ">".$b->title."</option>";
		}
		echo "</select>";
  }
}
function init_wtrh_book_summary() {
  register_widget('WH_BookSummary');
}
add_action('widgets_init', 'init_wtrh_book_summary');


// Display book's summary when a chapter's page is displayed
class WH_ChapterBookSummary extends WP_Widget {

  // sets up widget name and args
  function __construct() {
    $widget_args = array(
      'classname'   => 'widget_wh_chapter_book_summary',
      'description' => __('Display the summary of the book when a chapter\'s page is displayed','wtr_helper')
    );

    $control_args = array();

    parent::__construct('wh_chapter_book_summary', __('Writer Helper - Book\'s summary when on a chapter page','wtr_helper'),
						$widget_args, $control_args
    );
  }

  // outputs widget content
  function widget($args, $instance) {
	global $_GET;
	$display = false;
	$book    = null;
	$chapter_number = 0;
	$nbFreeChapters = 0;
	
	// retrieve chapter number
	if( isset($_GET['chapter']) ) {
		$chapter_number = wtr_sanitize($_GET['chapter'],'int');
	}
	
	// retrieve book
	if( isset($_GET['book']) ) {
		$book = new WH_Book(wtr_sanitize($_GET['book'],'int'));
		$nbFreeChapters = $book->book_info['freeChapter'];
	}
	
	if( $book != null ) {
		// Display summary
		echo $book->get_BookSummaryHTML(get_current_user_id(), $chapter_number);
	}
  }

  // save widget config
  function update( $new_instance, $old_instance ) {
		return $old_instance;
	}

  // outputs widget's options form on admin
  function form($instance) {
  }
}
function init_wtrh_chapter_book_summary() {
  register_widget('WH_ChapterBookSummary');
}
add_action('widgets_init', 'init_wtrh_chapter_book_summary');


// Display the info of a selected book
class WH_BookInfo extends WP_Widget {

  // sets up widget name and args
  function __construct() {
    $widget_args = array(
      'classname'   => 'widget_wh_book_info',
      'description' => __('Display the info of the selected book','wtr_helper')
    );

    $control_args = array();

    parent::__construct('wh_book_info', __('Writer Helper - Book\'s info','wtr_helper'),
						$widget_args, $control_args
    );
  }

  // outputs widget content
  function widget($args, $instance) {
	// retrieve book_id if update
	$book_id = isset($instance['book_id']) ? intval($instance['book_id']):0;
//wtr_info_log(__METHOD__,'book_id='.$book_id);		
		
	if( $book_id != 0 ) {
		// get book's info
		$book = new WH_Book($book_id);
		if( ! $book->isOk ) {
			echo sprintf(__('Book not found with id %s','wtr_helper'), $book_id);
			return false;
		}

		$dateFormat = WH_Category::get_DateFormat();
		
		// Display info
		echo wtrh_getBookPanel($book_id, "widget");
	}
  }

  // save widget config
  function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['book_id'] = isset( $new_instance['book_id'] ) ? intval( $new_instance['book_id'] ) : 0;

		return $instance;
	}

  // outputs widget's options form on admin
  function form($instance) {
	  // retrieve book_id if update
		$book_id = isset($instance['book_id']) ? intval($instance['book_id']):0;
		// get all books
		$books = WH_Book::getAll_Books("", null, "title", "asc");
		// display books' list
		echo "<p><label>".__('Choose a book','wtr_helper')." : </label><br>";
		echo "<select id='".$this->get_field_id('book_id')."'";
		echo " name='".$this->get_field_name('book_id')."'>";
		echo "<option value='' ".(($book_id==0)?'selected':'')."> </option>";
		foreach( $books as $b ) {
			echo "<option value='".$b->id."'";
			if( $book_id == $b->id )
				echo " selected";
			echo ">".$b->title."</option>";
		}
		echo "</select>";
  }
}
function init_wtrh_book_info() {
  register_widget('WH_BookInfo');
}
add_action('widgets_init', 'init_wtrh_book_info');


// Display book's info when a chapter's page is displayed
class WH_ChapterBookInfo extends WP_Widget {

  // sets up widget name and args
  function __construct() {
    $widget_args = array(
      'classname'   => 'widget_wh_chapter_book_info',
      'description' => __('Display the info of the book when a chapter\'s page is displayed','wtr_helper')
    );

    $control_args = array();

    parent::__construct('wh_chapter_book_info', __('Writer Helper - Book\'s info when on a chapter page','wtr_helper'),
						$widget_args, $control_args
    );
  }

  // outputs widget content
  function widget($args, $instance) {
	global $_GET;
	$display = false;
	$book = null;
	
	// retrieve book
	if( isset($_GET['book']) )
		$book = new WH_Book(wtr_sanitize($_GET['book'],'int'));
	
	if( $book != null ) {
		$dateFormat = WH_Category::get_DateFormat();
		
		// Display info
		echo wtrh_getBookPanel($book->id, "widget");
	} 
  }

  // save widget config
  function update( $new_instance, $old_instance ) {
		return $old_instance;
	}

  // outputs widget's options form on admin
  function form($instance) {
  }
}
function init_wtrh_chapter_book_info() {
  register_widget('WH_ChapterBookInfo');
}
add_action('widgets_init', 'init_wtrh_chapter_book_info');


// Display "search book" input
class WH_SearchBook extends WP_Widget {

  // sets up widget name and args
  function __construct() {
    $widget_args = array(
      'classname'   => 'widget_wh_search_book',
      'description' => __('Display a search book field','wtr_helper')
    );

    $control_args = array();

    parent::__construct('wh_search_book', __('Writer Helper - Search book field','wtr_helper'),
						$widget_args, $control_args
    );
  }

  // outputs widget content
  function widget($args, $instance) {
	global $_SERVER;
	global $_GET;
	
	$title = isset($_GET['title'])? wtr_sanitize($_GET['title'],'title'):"";
	
	echo "<form action='' method='get'>";
	echo "<input type='text' class='whwSearchBookField' name='title' ".
		 "placeholder='".__('Book title','wtr_helper')."' />\n";
	echo "<input type='submit' class='whwSearchBookButton' ".
		 "value='".__('Search','wtr_helper')."' /><br>\n";
	echo "</form>";
	
	echo "<div class='whwSearchResult' id='whSearchResult'>";
	if( $title != "" ) {
		$books    = WH_Book::getAll_Books($title, WH_Status::getPublishStatuses());
		$chapters = WH_Chapter::getAll_Chapters(0, WH_Status::getPublishStatuses(), "publication_date", "desc");
		$books_id    = array();
		$books_title = array();
		$books_url   = array();
		foreach( $books as $key => $b ) {
			$books_id[$key]    = $b->id;
			$books_title[$key] = $b->getTitle();
			$books_url[$key]   = $b->get_BookPostUrl();
		}
		foreach( $chapters as $c ) {
			if( ! in_array($c->book_id, $books_id) ) {
				$b = new WH_Book($c->book_id);
				if( wtr_instr($b->title, $title) ) {
					$books_id[] = $b->id;
					$books_title[] = $b->title;
				}
			}
		}
		sort($books_title);
		
		echo "<label>".__('Search results','wtr_helper')."</label>\n";
		echo "<ul class='whwBooksList'>\n";
		foreach( $books_title as $key => $t ) {
			$url = $books_url[$key];
			
			echo "<li>";
			if( $url != "" )
				echo "<a href='".$url."'>";
			echo $t;
			if( $url != "" )
				echo "</a>";
			echo "</li>\n";
		}
		echo "</ul>\n";
	}
	echo "<br/><br/></div>\n";
  }

  // save widget config
  function update( $new_instance, $old_instance ) {
		return $old_instance;
	}

  // outputs widget's options form on admin
  function form($instance) {
	_e('Display a field to search published books','wtr_helper');
  }
}
function init_wtrh_search_book() {
  register_widget('WH_SearchBook');
}
add_action('widgets_init', 'init_wtrh_search_book');

?>