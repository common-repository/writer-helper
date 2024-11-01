<div class="whGotoButtons whGotoButtons<?php echo $entryElement; ?>">

  <div class="whGotoButtonRow1">
  </div>

  <div class="whGotoButtonRow2">

    <?php if( $entryElement != 'book' ) { ?>
	<div class="whButtonGoToBook whGotoButton<?php echo $entryElement; ?>">
      <button type="button" <?php echo ($status == WH_Status::TRASHED)?'disabled':''; ?>
              class="whGotoButton"
              onclick="wtr_goTo('<?php echo admin_url('admin.php?page='.WTRH_BOOKS_MENU.'&book='.$book_id); ?>')">
          <?php _e('Go to Book','wtr_helper');?></button>
    </div>
	<?php } ?>
	
    </div>
</div>

<br/><br/>
<?php if( $entryElement != 'books' ) { ?>
<span class="whGoBackButton">
<button (click)="wtr_goBack()"><?php _e('Go Back','wtr_helper');?></button>
</span>
<?php } ?>
