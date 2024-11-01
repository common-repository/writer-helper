// Books JS

/*
 * BOOKS
 */ 
function wtr_saveBook(id) {
	if( document.getElementById('whBookChaptersTab') != null )
		wtr_manageBook('updateChaptersList', 0, id, 0);
	else if( document.getElementById('whBookDisplaySettingsTab') != null )
		wtr_manageBook('updateBookDisplaySettings', 0, id, 0);
	else if( document.getElementById('whBookSettingsTab') != null )
		wtr_manageBook('updateBookSettings', 0, id, 0);
	else
		wtr_manageBook('update', 0, id, 0);
}
function wtr_changeBook() {
	if( document.getElementById('whBookSaved') != null )
		if( document.getElementById('whBookSaved').value != "no" )
			document.getElementById('whBookSaved').value = "no";
}
function wtr_exportBookForm(book_id) {
	wtr_manageBook("exportBookForm", "", book_id, 0, "");
}
function wtr_exportBook(book_id) {
	wtr_manageBook("exportBook", "", book_id, 0, "");
}
function wtr_printBook(book_id) {
	wtr_manageBook("printBook", "", book_id, 0, "");
}
function wtr_changePDate() {
	document.getElementById("whBookPublicationDate").style.display = "none";
	document.getElementById("whBookPublicationDateDiv").style.display = "block";
}
function wtr_changePublicationDate() {
	book_id = 0;
	pDate = "";
	if( document.getElementById('book_id') != null )
		book_id = document.getElementById('book_id').value;
	if( document.getElementById('whPublicationDate') != null )
		pDate = document.getElementById('whPublicationDate').value;
	
	wtr_publishBook("changeDate", book_id, pDate, "");
	wtr_showHide("whBookPublicationDate");
	wtr_showHide("whBookPublicationDateDiv");
}

function wtr_addAuthor(user_id, user_name) {
	var book_id = 0;
	if( document.getElementById('whAddAuthorName') != null )
		document.getElementById('whAddAuthorName').value = user_name;
	if( document.getElementById('book_id') != null )
		book_id = document.getElementById('book_id').value;
	
	wtr_manageBook("addAuthor", "", book_id, user_id, "");
}
function wtr_deleteAuthor(user_id) {
	var book_id = 0;
	if( document.getElementById('book_id') != null )
		book_id = document.getElementById('book_id').value;
	
	wtr_manageBook("deleteAuthor", "", book_id, user_id, "");
}
function wtr_changeAuthorName(user_id) {
	var book_id = 0;
	if( document.getElementById('book_id') != null )
		book_id = document.getElementById('book_id').value;
		
	wtr_manageBook("changeAuthorName", "", book_id, user_id, "");
}

function wtr_addEditor(user_id, user_name) {
	var book_id = 0;
	if( document.getElementById('whAddEditorName') != null )
		document.getElementById('whAddEditorName').value = user_name;
	if( document.getElementById('book_id') != null )
		book_id = document.getElementById('book_id').value;
	
	wtr_manageBook("addEditor", "", book_id, user_id, "");
}
function wtr_deleteEditor(user_id) {
	var book_id = 0;
	if( document.getElementById('book_id') != null )
		book_id = document.getElementById('book_id').value;
	
	wtr_manageBook("deleteEditor", "", book_id, user_id, "");
}
function wtr_changeEditorName(user_id) {
	var book_id = 0;
	if( document.getElementById('book_id') != null )
		book_id = document.getElementById('book_id').value;
	
	wtr_manageBook("changeEditorName", "", book_id, user_id, "");
}
 
function wtr_createBookPost(id) {
	wtr_manageBook("createPost", "", id, 0, "");
}
function wtr_deleteBookPost(id) {
	wtr_manageBook("deletePost", "", id, 0, "");
}

function wtr_cancelStatusDiv() {
	popup('wh_popupDiv');
}

// Manage book (status, delete, create, update, addAuthor, addEditor)
function wtr_manageBook(action, bstatus, id, user_id, url) {
	var title = '', type = '', resume = '', saleUrl = '', promoUrl = '', opinionUrl = '';
	var isbn       = '';
	var user_name  = '';
	var cs_list    = '';
	var img_id     = 0;
	var freeC = '-1', pubB = '00', seeH = '00', seeP = '00', seeBW = '00';
	var labelNextC = '', labelPrevC = '', labelNextCU = '', labelBookEnd = '', 
		labelReadMore = '', bookInfo = '', customStatus = '', bookStatuses= '';
	
	if( action == 'delete' ) {
		if ( ! confirm("Deletion is definive.\nAll chapters and scenes will be deleted.\nAre you sure ?")) 
			return false;
	}
	
	if( action == 'search' ) {
		if( document.getElementById('whSearchField') != null )
			title  = document.getElementById('whSearchField').value;
	}
	
	if( action == 'create' ) {
		if( document.getElementById('whBookTitle') != null )
			title  = document.getElementById('whBookTitle').value;
		if( document.getElementById('whBookType') != null )
			type   = document.getElementById('whBookType').value;
		if( document.getElementById('whResume') != null )
			resume = document.getElementById('whResume').value;
		if( document.getElementById('whBookGame') != null )
			if( document.getElementById('whBookGame').checked )
				id = 1;
	}
	
	if( action == 'update' ) {
		if( document.getElementById('whBookTitle') != null )
			title  = document.getElementById('whBookTitle').value;
		if( document.getElementById('whBookCoverId') != null )
			img_id = document.getElementById('whBookCoverId').value;
		if( document.getElementById('whBookType') != null )
			type   = document.getElementById('whBookType').value;
		if( document.getElementById('whResume') != null )
			resume = document.getElementById('whResume').value;
		if( document.getElementById('whSaleUrl') != null )
			saleUrl = document.getElementById('whSaleUrl').value;
		if( document.getElementById('whPromoUrl') != null )
			promoUrl = document.getElementById('whPromoUrl').value;
		if( document.getElementById('whOpinionUrl') != null )
			opinionUrl = document.getElementById('whOpinionUrl').value;
		if( document.getElementById('whIsbn') != null )
			isbn     = document.getElementById('whIsbn').value;
	}		
	if( action == 'updateBookDisplaySettings') {
		
		if( document.getElementById('BS_nextC_label') )
			labelNextC = document.getElementById('BS_nextC_label').value;
		
		if( document.getElementById('BS_nextCU_label') )
			labelNextCU = document.getElementById('BS_nextCU_label').value;
		
		if( document.getElementById('BS_prevC_label') )
			labelPrevC = document.getElementById('BS_prevC_label').value;
		
		if( document.getElementById('BS_bookEnd_label') )
			labelBookEnd = document.getElementById('BS_bookEnd_label').value;
		
		if( document.getElementById('BS_readMore_label') )
			labelReadMore = document.getElementById('BS_readMore_label').value;
		
		if( document.getElementById('BS_customStatus_label') )
			customStatus = document.getElementById('BS_customStatus_label').value;
		
		if( document.getElementById('whDefaultBooksSettings') ) {
			bookInfo = '';
			
			var x =  document.getElementsByClassName("whBookInfoCB");
			for (i = 0; i < x.length; i++) {
				if( x[i].checked ) {
					if( bookInfo.length == 0 )
						bookInfo = bookInfo + x[i].id ;
					else
						bookInfo = bookInfo + ',' + x[i].id ;
				}
			}
			
			bookStatuses = '[';
			
			var x =  document.getElementsByClassName("whBookStatusCB");
			for (i = 0; i < x.length; i++) {
				if( x[i].checked ) {
					if( bookStatuses.length == 1 )
						bookStatuses = bookStatuses + x[i].id ;
					else
						bookStatuses = bookStatuses + ',' + x[i].id ;
				}
			}
			bookStatuses = bookStatuses + ']';
			
		}
		
		cs_list = '{"book_info":"'+bookInfo+'",'+
			   '"book_statuses":'+bookStatuses+','+
			   '"nextChapter_label":"'+labelNextC+'","nextChapter_style":"",'+
			   '"nextChapterU_label":"'+labelNextCU+'","nextChapterU_style":"",'+
			   '"prevChapter_label":"'+labelPrevC+'","prevChapter_style":"",'+
			   '"book_ending_label":"'+labelBookEnd+'","book_ending_style":"",'+
			   '"read_more_label":"'+labelReadMore+'","read_more_style":"",'+
			   '"custom_status_label":"'+customStatus+'","custom_status_style":""}';
		
	}
	if( action == 'updateBookSettings' ) {

		if( document.getElementById('whNbFreeChapter') && document.getElementById('whNbFreeChapter').value.length > 0 )
			freeC = document.getElementById('whNbFreeChapter').value;
		if( isNaN(freeC) )
			freeC = -1;
		
		var list = document.getElementsByName("whPublishedBook");
		for( i = 0; i < list.length; i++ ) {
			if( list[i].checked )
				pubB = list[i].value;
		}
		list = document.getElementsByName("whSeeHidden");
		for( i = 0; i < list.length; i++ ) {
			if( list[i].checked )
				seeH = list[i].value;
		}
		list = document.getElementsByName("whSeePreview");
		for( i = 0; i < list.length; i++ ) {
			if( list[i].checked )
				seeP = list[i].value;
		}
		list = document.getElementsByName("whSeeBookworld");
		for( i = 0; i < list.length; i++ ) {
			if( list[i].checked )
				seeBW = list[i].value;
		}
		
		cs_list = '{"freeChapter":'+freeC+',"seePublishedBook":'+pubB+',"seeHidden":'+seeH+',"seePreview":'+seeP+',"seeBookworld":'+seeBW+'}';
	}
	if( action == 'updateChaptersList' ) {
		// set chapters & scenes list
		var x =  document.getElementsByClassName("whBookCSlist");
		for (i = 0; i < x.length; i++) {
			if( x[i].id.substr(2,1) == "C" ) // If chapter
				cs_list = cs_list + "c" + x[i].id.substr(15) + "-" + x[i].innerHTML;
			if( x[i].id.substr(2,1) == "S" ) // If scene
				cs_list = cs_list + "s" + x[i].id.substr(13) + "-" + x[i].innerHTML;
		}
	}
	
	if(action == "changeAuthorName" ) {
		if( document.getElementById('whAuthorName'+user_id) != null )
			user_name = document.getElementById('whAuthorName'+user_id).value;

	}
	if(action == "changeEditorName" ) {
		if( document.getElementById('whEditorName'+user_id) != null )
			user_name = document.getElementById('whEditorName'+user_id).value;

	}
	
	if( action == "addAuthor" || action == "deleteAuthor"  ) {
		document.getElementById('whBookAuthors').innerHTML = 
		"<img src='"+wtr_getLoadingGif()+"' width='30'>";
	}
	if( action == "addEditor" || action == "deleteEditor" ) {
		document.getElementById('whBookEditors').innerHTML = 
		"<img src='"+wtr_getLoadingGif()+"' width='30'>";
	}
	
	if( document.getElementById('whAddEditorNameDiv') )
		document.getElementById('whAddEditorNameDiv').innerHTML = "";
	if( document.getElementById('whAddAuthorNameDiv') )
		document.getElementById('whAddAuthorNameDiv').innerHTML = "";
	
	if( action == 'exportBookForm' ) 
		popup('wh_popupDiv');
	if( action == 'exportBook' ) {
		popup('wh_popupDiv');
		// set statuses list
		var x =  document.getElementsByClassName("wh_buttonCBStatus");
		for (i = 0; i < x.length; i++) {
			if( x[i].checked ) {// If status is checked
				if( cs_list == "" ) 
					cs_list = x[i].id;
				else
					cs_list = cs_list + "," + x[i].id;
			}
		}
	}
	
	// send action
    var data = {
		'action'    : 'wtrh_ajax_manageBook',
		'type'      : action,
		'id'        : id,
		'img_id'    : img_id,
		'status'    : bstatus,
		'title'     : title,
		'btype'     : type,
		'resume'    : resume,
		'saleUrl'   : saleUrl,
		'promoUrl'  : promoUrl,
		'opinionUrl': opinionUrl,
		'isbn'      : isbn,
		'cs_list'   : cs_list,
		'user_id'   : user_id,
		'user_name' : user_name
	};
	
	if( action != "exportBookForm" ) 
		popup('wtrh_wait_div');
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			
			if( action != "exportBookForm" ) 
				popup('wtrh_wait_div');

			if( ret == "KO" ) {
				alert(msg);
				return false;
			} else {
				if( action == "update" ) {
					if( document.getElementById('whBookSaved') != null )
						document.getElementById('whBookSaved').value = "yes";
				}
				
				if( action == "createPost" || action == "deletePost" ) {
					if( document.getElementById('whBookPostButton') )
						document.getElementById('whBookPostButton').innerHTML = msg;
				}
			}
			
			if( action == "exportBook" )
				location.reload();
			
			if( action == "printBook" ) {
				// open a new windows containing book text
				var w = window.open("", "_blank", 
						"top=0,width=700,height=700,menubar=no,status=no,titlebar=no,tollbar=no");
				w.document.open();
				w.document.write(msg);
				w.document.close();
				return false;
			}
			
			if( action == "search" )
				document.getElementById('whSearchResult').value = msg;
			
			if( action == "create" )
				window.location.href = url + msg;
			
			if( action == "status" || action == "delete" )
				wtrh_GetBooksList();
			
			if( action == "refreshStatus" ) {
				if( document.getElementById('whBookStatus') )
				document.getElementById('whBookStatus').innerHTML = msg;
				
				location.reload();
			}
			if( action == "update" || action == "updateChaptersList" )
				location.reload();
			
			if( action == "changeAuthorName" ) {
				document.getElementById('whAuthorName'+user_id).value = msg;
			}
			if( action == "changeEditorName" ) {
				document.getElementById('whEditorName'+user_id).value = msg;
			}

			if( action == "addAuthor" || action == "deleteAuthor" ) {
				if( document.getElementById('whBookAuthors') != null )
					document.getElementById('whBookAuthors').innerHTML = msg;
				document.getElementById('whAddAuthorName').innerHTML = "";				
			}
			if( action == "addEditor" || action == "deleteEditor" ) {
				if( document.getElementById('whBookEditors') != null )
					document.getElementById('whBookEditors').innerHTML = msg;
				document.getElementById('whAddEditorName').innerHTML = "";
			}
				
			if( action == "exportBookForm" ) {
				if( document.getElementById("wh_popupDiv") )
					document.getElementById("wh_popupDiv").innerHTML = msg;
			}

			return false;		
	});		
    return false;
}



// Publish a book
function wtr_publishBook(action, book_id, pDate, msg) {
	var date = "";
	
	if( action == 'publish' ) {
		// get publication date
		date = prompt(pDate, msg);
		
		// if cancel publishing
		if( date == null )
			return false;
	}
	if( action == 'changeDate' ) {
		date = pDate;
	}
	
	// send action
    var data = {
		'action'    : 'wtrh_ajax_publishBook',
		'type'      : action,
		'id'        : book_id,
		'date'      : date
	};
	
	if( document.getElementById('wtrh_booksList') )
		document.getElementById('wtrh_booksList').innerHTML = 
		"<img src='"+wtr_getLoadingGif()+"' width='30'>";
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			
			if( ret == "KO" )
				alert(msg);
			else {
				if( action == 'changeDate' )
					if( document.getElementById('whDisplayedPDate') != null )
						document.getElementById('whDisplayedPDate').innerHTML = date;
			}
			if( action == 'publish' )
				wtrh_GetBooksList();

			return false;		
	});		
    return false;

}

// Get books' list
function wtrh_GetBooksList() {
	var ret;
	var msg;
	var bookTitle; 
	
	bookTitle = document.getElementById('wtrh_book_title').value;
	var st_list    = '';
	// set statuses list
	var x =  document.getElementsByClassName("wh_buttonCBBookStatus");
	for (i = 0; i < x.length; i++) {
		if( x[i].checked ) {// If status is checked
			if( st_list == "" ) 
				st_list = x[i].id;
			else
				st_list = st_list + "," + x[i].id;
		}
	}
	
//alert("title="+bookTitle);	
	document.getElementById('wtrh_booksList').innerHTML = 
		"<img src='"+wtr_getLoadingGif()+"' width='30'>";
	
	// send action
    var data = {
		'action': 'wtrh_ajax_getBooksList',
		'title' : bookTitle,
		'status': st_list
	};
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );

			document.getElementById('wtrh_booksList').innerHTML = 
						"<br>" + msg + "<br>";
						
			return false;
		
	});
		
    return false;
}



