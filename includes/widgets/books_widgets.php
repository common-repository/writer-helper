<?php 
/**
 ** Widgets for managings a list of books 
 **/
include_once(WTRH_INCLUDE_DIR . '/classes/class_book.php');
	
// Display all books
class WH_Library extends WP_Widget {

  // sets up widget name and args
  function __construct() {
    $widget_args = array(
      'classname'   => 'widget_wh_library',
      'description' => __('Display all books','wtr_helper')
    );

    $control_args = array();

    parent::__construct('wh_library', __('Writer Helper - Library','wtr_helper'),
						$widget_args, $control_args
    );
  }

  // outputs widget content
  function widget($args, $instance) {	
	
	// Display books
		echo "<div class='whwLibraryDiv'>";
		echo "<label class='whwLibraryLabel'>".
			 __('Library','wtr_helper')."</label>";
		echo "<ul class='whwLibraryList'>";
		foreach( WH_Book::getAll_LibraryBooks("title", "asc" ) as $b ) {
			$authors = array();
			foreach( $b->get_BookAuthors() as $a )
				$authors[] = $a['name'];
			echo "<li>".
//			"[<label class='whwBookType'>".__($b->get_Type('text'),'wtr_helper')."</label>]&nbsp;".
				 $b->get_Title()." ".__('by','wtr_helper')." ".
				 implode(", ",$authors);
			
			$lib = "";
			if( $b->status == WH_Status::PREVIEW )
				$lib = __('Preview','wtr_helper');
			if( $b->status == WH_Status::HIDDEN )
				$lib = __('Hidden','wtr_helper');
			if( $lib != "" )
				echo " <span class='whwComingSoon'>(".$lib.")</span>";
			
			echo "</li>";
		}
		echo "</ul></div>";
  }

  // save widget config
  function update( $new_instance, $old_instance ) {
		return $old_instance;
	}

  // outputs widget's options form on admin
  function form($instance) {
  }
}
function init_wtrh_library() {
  register_widget('WH_Library');
}
add_action('widgets_init', 'init_wtrh_library');


// Display the lastest publications
class WH_LastestPublications extends WP_Widget {

  // sets up widget name and args
  function __construct() {
    $widget_args = array(
      'classname'   => 'widget_wh_lastestpubli',
      'description' => __('Display lastest publications','wtr_helper')
    );

    $control_args = array();

    parent::__construct('wh_lastestpubli', __('Writer Helper - Lastest publication','wtr_helper'),
						$widget_args, $control_args
    );
  }

  // outputs widget content
  function widget($args, $instance) {
	  // retrieve nb 
		$nb_publi = isset($instance['nb_publi']) ? (int)$instance['nb_publi']:5;
		$title    = isset($instance['title']) ?  (string)$instance['title']:__('Lastest publications','wtr_helper');
		$books    = WH_Book::get_LatestPublishedBooks($nb_publi);
		$chapters = WH_Chapter::get_LatestPublishedChapters($nb_publi);
		
		// Order lastest publications
		$last = array();
		$nb = 0;
		foreach( $books as $b ) {
			$nb++;
			if( $nb <= $nb_publi ) {
				$url  = $b->get_BookPostUrl();
				$lib  = "[".__('Book','wtr_helper')."] ";
				if( $url != "" )
					$lib .= "<a href='".$url."'>";
				$lib .= $b->get_Title();
				if( $url != "" )
					$lib .= "</a>";

				$lib_status = "";
				if( $b->status == WH_Status::PREVIEW )
					$lib_status = __('Preview','wtr_helper');
				if( $b->status == WH_Status::HIDDEN )
					$lib_status = __('Hidden','wtr_helper');
				if( $lib_status != "" )
					$lib .= " <span class='whwComingSoon'>(".$lib_status.")</span>";
				
				$last[$b->get_PublicationDate("Y-m-d")."/b".$b->id] = $lib;
			} else
				break;
		}
		$nb = 0;
		foreach( $chapters as $c ) {
			$c_b = new WH_Book($c->book_id);
			// if chapter's book not published
			if( WH_Status::isPublishStatus($c_b->status) == 2 ) {
				$nb++;
				if( $nb <= $nb_publi ) {
					$url  = $c->get_ChapterPostUrl();
					if( $url == "" )
						$url = $c_b->get_BookPostUrl($c->number);
					
					$lib  = "[".__('Chapter','wtr_helper')."] ";
					if( $url != "" )
						$lib .= "<a href='".$url."'>";
					$lib .= $c_b->get_Title()." - ".$c->getChapterTitle(true, true);
					if( $url != "" )
						$lib .= "</a>";
					$last[$c->get_PublicationDate("Y-m-d")."/c".$c->id] = $lib;
				} else
					break;
			}
		}
		
		if( count($last) > 0 ) {
			// Order by key ascending
			krsort($last, SORT_STRING);
			// get date format
			$dateFormat = WH_Category::get_DateFormat();
			
			// Display lastest publications
			echo "<div class='whwLastestPubliDiv'>";
			echo "<label class='whwLastestPubliTitle'>".$title."</label><br>";
			echo "<ul class='whwLastestPubliList'>";
			$nb = 0;
			foreach( $last as $key => $b ) {
				$nb++;
				if( $nb > $nb_publi )
					break;
				$ar   = explode("/", $key);
				$date = $ar[0];
				if( $date == __('Coming soon','wtr_helper')) 
					echo "<li>[".__('Coming soon','wtr_helper')."] ".$b."</li>";
				else
					echo "<li>[".date_format(date_create($date), $dateFormat).
						 "] ".$b."</li>";
			}
			echo "</ul></div>";
		}
  }

  // save widget config
  function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['nb_publi'] = isset( $new_instance['nb_publi'] ) ? (int)$new_instance['nb_publi'] : 0;
		$instance['title']    = isset( $new_instance['title'] ) ?  (string)$new_instance['title'] : __('Lastest publications','wtr_helper');

		return $instance;
	}

  // outputs widget's options form on admin
  function form($instance) {
	  // retrieve nb_publi if update
		$nb_publi = isset($instance['nb_publi']) ? (int)$instance['nb_publi']:0;
		$title    = isset($instance['title']) ? wtr_sanitize($instance['title'],'title'):__('Lastest publications','wtr_helper');

		// display books' list
		echo "<p><label>".__('Enter a title to display before the publications','wtr_helper')." : </label><br>";
		echo "<input type='text' id='".$this->get_field_id('title')."'";
		echo " name='".$this->get_field_name('title')."'";
		echo " value='".esc_attr($title)."'><br>";
		echo "<label>".__('Enter the number of publications to display','wtr_helper')." : </label><br>";
		echo "<input type='text' id='".$this->get_field_id('nb_publi')."'";
		echo " name='".$this->get_field_name('nb_publi')."'";
		echo " value='".$nb_publi."'>";
		echo "</p>";
  }
}
function init_wtrh_lastest_publi() {
  register_widget('WH_LastestPublications');
}
add_action('widgets_init', 'init_wtrh_lastest_publi');


?>