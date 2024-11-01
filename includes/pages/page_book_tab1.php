<br>
<div class="whBookBlock1"> 
<!-- ------------------------------------- -->                    
<!--         BOOK COVER                 -->                    
<!-- ------------------------------------- -->
<table class="whBookBlock1_table">
<tr><td class="whBookBlock1_left">
    <div class="whBookCover" id="whBookCover"> 
	<?php
		wp_enqueue_media();
		
		$upload_link = esc_url( get_upload_iframe_src( 'image',0,'image' ) );

		$hasCover = (strlen(trim($book->cover)) == 0)?false:true;
		$imageArray = json_decode($book->cover);
		$imageId  = 0;
		$imageUrl = WTRH_IMG_URL."/NoCover2.png";
		if( $imageArray ) {
			$imageId  = $imageArray->ID;
			$imageUrl = $imageArray->guid;
		}
		
		echo '<img src="'.$imageUrl.'" alt="'.__('Book cover','wtr_helper').'"'.">\n";
      ?>
    </div>
</td><td>
<!-- ------------------------------------- -->                    
<!--         BOOK INFO                     -->                    
<!-- ------------------------------------- -->                    
    <div class="whBookInfo"> 
	
	  <div class="whBookChangeCover"> 
		<?php
			if( $disabled == "" ) {
		?>
		<a class="upload-custom-img <?php if ( $hasCover  ) { echo 'hidden'; } ?>"
			href="<?php echo $upload_link ?>" >
			<?php _e('Change cover','wtr_helper'); ?></a>
			
		<a class="delete-custom-img <?php if ( ! $hasCover  ) { echo 'hidden'; } ?>" 
		  href="#">
			<?php _e('Remove this cover','wtr_helper') ?>
		</a>
		<?php
			}
		?>
		<input class="whBookCoverId" id="whBookCoverId" type="hidden" 
				value="<?php echo $imageId; ?>" />	
	  </div><br>

      <div class="whBookTitleInput">
        <label><?php _e('Title','wtr_helper'); ?></label>
		<input type='text' id="whBookTitle" 
				value="<?php echo $book->getTitle(); ?>"
				 <?php echo $disabled; ?>>
		<span id="whBookPostButton">
<?php
if( $book->status != WH_Status::TRASHED ) {
	if( $book->book_post == null ) {
?>
		<a class="wh_buttonDashicon" onclick="wtr_createBookPost(<?php echo $book->id; ?>)">
		<span class="dashicons dashicons-admin-links" 
			title="<?php _e('Create a post for your book','wtr_helper'); ?>"></span>
		</a>
<?php
	} else {
?>
		<a class="wh_buttonDashicon" href="<?php echo $book->get_BookPostUrl(); ?>" target="_blank">
		<span class="dashicons dashicons-visibility" title="<?php _e('Open post','wtr_helper'); ?>"></span>
		</a>
		<a class="wh_buttonDashicon wh_buttonDashiconDel" onclick="wtr_deleteBookPost(<?php echo $book->id; ?>)">
		<span class="dashicons dashicons-editor-unlink" 
			title="<?php _e('Delete the post containing your book','wtr_helper'); ?>"></span>
		</a>
<?php
	}
}
?>
		</span>
      </div><br>

      <div class="whBookBookType">
        <label><?php _e('Book type','wtr_helper'); ?></label>
        <select id="whBookType" <?php echo $disabled; ?>>
		<?php 
          foreach( $types as $type ) {
			echo "<option value=\"".$type->title."\"";
			if( $book->get_Type('text') == $type->title ) {
				echo " selected";
			}
			echo ">".__($type->get_Title(),'wtr_helper')."</option>\n";
		  }
		 ?>
        </select>
      </div><br>

      <div class="whBookGameBook">
        <label><?php _e('GameBook','wtr_helper'); ?></label>
        <input type='checkbox' id='whBookGame' class='whBookGameBookCheckbox' <?php echo $gameBook; ?> disabled />
      </div><br>

	  <div class="whBookAuthors">
        <label><?php _e('Author','wtr_helper'); ?></label>
        <div id="whBookAuthors">
			<?php
				foreach( $book->authors as $au) {
			?>
				<div class='whBookAuthor'>
				<span> - </span>
				<span>
					<input type='text' id='whAuthorName<?php echo $au['id']; ?>' 
					title="<?php echo sprintf(__('WP User is %s','wtr_helper'), WH_User::getWpUserName($au['id'])); ?>"
					value="<?php echo $au['name']; ?>" >
				</span>
				<span>
				<button type='button' onclick='wtr_changeAuthorName(<?php echo $au['id']; ?>)' <?php echo $disabled; ?>>
					<?php _e('Change name','wtr_helper'); ?>
				</button>
				</span>
				<span><button type='button'
					title="<?php echo sprintf(__('Delete','wtr_helper'), WH_User::getWpUserName($au['id'])); ?>"
					onclick='wtr_deleteAuthor(<?php echo $au['id']; ?>)'>X</button>
				</span>
				</div>
			<?php } ?>
				<div class='whBookAuthor'>
				<span> - </span>
				<span>
				<input type='text' id="whAddAuthorName" value="" 
					oninput='wtr_listAuthors()'
					placeholder="<?php _e('Enter an author name','wtr_helper'); ?>" <?php echo $disabled; ?>>
					<div id="whAddAuthorNameDiv"></div>
				</span>
				<span>&nbsp;&nbsp;<?php _e('Add author*','wtr_helper');?>
				</span>
				</div>
		</div>
      </div>

      <div class="whBookEditors">
		<label><?php _e('Editor','wtr_helper'); ?></label>
		<div id="whBookEditors">
            
			<?php
				foreach( $book->editors as $i => $ed) {
			?>
				<div class='whBookEditor'>
				<span> - </span>
				<span>
				<input type='text' id='whEditorName<?php echo $au['id']; ?>' 
					title="<?php echo sprintf(__('WP User is %s','wtr_helper'), WH_User::getWpUserName($ed['id'])); ?>"
					value="<?php echo $ed['name']; ?>" >
				</span>
				<span>
				<button type='button' onclick='wtr_changeEditorName(<?php echo $ed['id']; ?>)' <?php echo $disabled; ?>>
					<?php _e('Change name','wtr_helper'); ?>
				</button>
				</span>
				<span><button type='button'
					title="<?php echo sprintf(__('Delete','wtr_helper'), WH_User::getWpUserName($ed['id'])); ?>"
					onclick='wtr_deleteEditor(<?php echo $ed['id']; ?>)'>X</button>
				</span>
				</div>
			<?php } ?>
			<div class='whBookEditor'>
				<span> - </span>
				<span>
				<input type='text' id="whAddEditorName" value="" 
				oninput='wtr_listEditors()'
				placeholder="<?php _e('Enter an author name','wtr_helper'); ?>" <?php echo $disabled; ?>>
				<div id="whAddEditorNameDiv"></div>
				</span>
				<span>&nbsp;&nbsp;<?php _e('Add editor*','wtr_helper');?></span>
			</div>
		</div>
      </div>
<span class="whNotaBene"><?php _e('*Authors and editors must be registered Writer Helper Users.','wtr_helper');?></span>

	  <?php
		if( $book->status == WH_Status::PUBLISHED ) {
		?>
		<br/><br/>
      <div class="whBookPubDateDiv">
		<label id="whBookPubDateDiv"><?php _e('Publication date','wtr_helper'); ?></label>
		<div id="whBookPublicationDate">
            <?php
				$pDate = $book->get_PublicationDate();
				$format = WH_Category::get_DateFormat();
			?>
			<label id="whDisplayedPDate"><?php echo $pDate; ?></label>
			<button type='button' onclick='wtr_changePDate()'>
				<?php _e('Change publication date','wtr_helper'); ?>
			</button>
		</div>
		<div class="whBookPubDateDivDiv" id="whBookPublicationDateDiv">
			<label class="whDisplayedPDate">
				<?php echo sprintf(__('Enter a date (date format is %s)','wtr_helper'),$format); 
				?>
			</label>
			&nbsp;&nbsp;
			<input type='text' id="whPublicationDate" value="<?php echo wtr_getFormatedDate(); ?>">
			<button type='button' onclick='wtr_changePublicationDate()'>
				<?php _e('Change','wtr_helper'); ?>
			</button>
		</div>
      </div>
	  <?php
		}
		?>
	  
  </div>
</td></tr>
</table>  

</div>


<!-- ------------------------------------- -->                    
<!--         BOOK RESUME                 -->                    
<!-- ------------------------------------- -->                    
<br>
<div class="whBookResume">
  <label><?php _e('Resume','wtr_helper');?></label><br/>
  <textarea id='whResume' <?php echo $disabled; ?>><?php echo $book->getResume('text'); ?></textarea>
</div><br/>

<!-- ------------------------------------- -->                    
<!--         BOOK URLS                 -->                    
<!-- ------------------------------------- -->                    
<div class="whBookUrls">
  <table>
    <tr>
      <td><label><?php _e('Sale URL','wtr_helper');?></label></td>
      <td><input id="whSaleUrl"
				 placeholder="http://my-sale-url.com" size="50"
				 value="<?php echo $book->sale_url; ?>"
				  <?php echo $disabled; ?>/></td>
    </tr>
    <tr>
      <td><label><?php _e('Promotion URL','wtr_helper');?></label></td>
      <td><input id="whPromoUrl"
				 placeholder="http://my-promo-url.com" size="50"
				 value="<?php echo $book->promo_url; ?>"
				  <?php echo $disabled; ?>/></td>
    </tr>
    <tr>
      <td><label><?php _e('Notation URL','wtr_helper');?></label></td>
      <td><input id="whOpinionUrl"
				 placeholder="http://my-notation-url.com" size="50"
				 value="<?php echo $book->opinion_url; ?>"
				  <?php echo $disabled; ?>/></td>
    </tr>
    <tr>
      <td><label><?php _e('ISBN','wtr_helper');?></label></td>
      <td><input id="whIsbn"
				 placeholder="<?php _e('Book unique identifier','wtr_helper');?>" size="50"
				 value="<?php echo $book->isbn; ?>"
				  <?php echo $disabled; ?>/></td>
    </tr>
  </table>
</div><br/>


<!-- ------------------------------------- -->                    
<!--             BUTTONS                   -->                    
<!-- ------------------------------------- -->                    
<br/><br/>
<div class="whMsg" id="whMsg"></div>

<?php 
	echo getActionButtons("book", $book->status, "save", $book->id, $book->id);
	echo getGotoButtons("book", $book->status, $book->id); ?>

<br/><br/>


<script type='text/javascript'>
jQuery(function($){

  // Set all variables to be used in scope
  var frame = null,
      metaBox = $('.whBookBlock1'), // Your meta box id here
      addImgLink = metaBox.find('.upload-custom-img'),
      delImgLink = metaBox.find( '.delete-custom-img'),
      imgContainer = metaBox.find( '.whBookCover'),
      imgIdInput = metaBox.find( '.whBookCoverId' );
  
  // ADD IMAGE LINK
  addImgLink.on( 'click', function( event ){
    
    event.preventDefault();
    
    // If the media frame already exists, reopen it.
    if ( frame ) {
      frame.open();
      return;
    }
    
    // Create a new media frame
    frame = wp.media({
      title: "<?php _e('Select or Upload your Cover','wtr_helper'); ?>",
      button: {
        text: "<?php _e('Use this media','wtr_helper'); ?>"
      },
      multiple: false  // Set to true to allow multiple files to be selected
    });

    
    // When an image is selected in the media frame...
    frame.on( 'select', function() {
      
      // Get media attachment details from the frame state
      var attachment = frame.state().get('selection').first().toJSON();

      // Send the attachment URL to our custom image input field.
      imgContainer.html( '<img class="whBookCover" src="'+attachment.url+'" alt="<?php _e('Book cover','wtr_helper'); ?>"/>' );

      // Send the attachment id to our hidden input
      imgIdInput.val( attachment.id );

      // Hide the add image link
      addImgLink.addClass( 'hidden' );

      // Unhide the remove image link
      delImgLink.removeClass( 'hidden' );
    });

    // Finally, open the modal on click
    frame.open();
  });
  
  
  // DELETE IMAGE LINK
  delImgLink.on( 'click', function( event ){

    event.preventDefault();

    // Clear out the preview image
    imgContainer.html( '<img src="<?php echo WTRH_IMG_URL.'/NoCover2.png'; ?>" alt="<?php _e('No cover','wtr_helper'); ?>">' );

    // Un-hide the add image link
    addImgLink.removeClass( 'hidden' );

    // Hide the delete image link
    delImgLink.addClass( 'hidden' );

    // Delete the image id from the hidden input
    imgIdInput.val( '0' );

  });

});
</script>
