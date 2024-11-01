// Common functions

function wtr_goBack() {
	window.history.back();
}
function wtr_goTo(url) {
	var go = false;
	var notfound = true;
	var msg = "";
	
	if( document.getElementById('whMsgObjectNotSaved') != null )
		msg = document.getElementById('whMsgObjectNotSaved').value;
	
	if( document.getElementById('whBookSaved') != null ) {
		notfound = false;
		if( document.getElementById('whBookSaved').value == "no" ) {
			if( confirm(msg) ) 
				go = true;
		} else
			go = true;
	}
	
	if( document.getElementById('whChapterSaved') != null ) {
		notfound = false;
		if( document.getElementById('whChapterSaved').value == "no" ) {
			if( confirm(msg) )
				go = true;
		} else
			go = true;
	}
	if( document.getElementById('whSceneSaved') != null ) {
		notfound = false;
		if( document.getElementById('whSceneSaved').value == "no" ) {
			if( confirm(msg) )
				go = true;
		} else
			go = true;
	}
	if( notfound )
		go = true;
	
	if( go )
		window.location.href=url;
}
function wtr_disableEnable(box, toChange) {
	if( document.getElementById(box).checked )
		document.getElementById(toChange).disabled = false;
	else
		document.getElementById(toChange).disabled = true;
}
function wtr_showHide(div) {
	if(document.getElementById(div).style.display == "block" )
		document.getElementById(div).style.display = "none";
	else
		document.getElementById(div).style.display = "block";
}
function wtr_clear(div) {
	document.getElementById(div).value = "";
}


function addToDiv(id, title, div) {
	
	// See if title is not in div
	if( document.getElementById("wh_selectedBook"+id) )
		return false;
	
	// Add to div
	document.getElementById(div).innerHTML = 
		document.getElementById(div).innerHTML + 
		"<div class='whDivA whSelectedBook' id='wh_selectedBook"+id+"'>&nbsp;"+
		"<span class='whDeleteX' onclick='wtr_deleteDiv(\"wh_selectedBook"+id+"\")'>X</span>"+
		"&nbsp;&nbsp;"+title+"<br/></div>";
}

function wtr_deleteDiv(id) {
	if( document.getElementById(id) )
		document.getElementById(id).remove();
}


function wtr_openTab(url) {

	window.location.href = url;
	
}

function wtr_getLoadingGif() {
	var loading_gif = '';
	
	if( document.getElementById('whLoadingGif') != null )
		loading_gif  = document.getElementById('whLoadingGif').value;
		
	return loading_gif;
}
/*
 * CATEGORIES
 */ 
// Date & time format
function wtr_testDateFormat() {
	// send action
    var data = {
		'action': 'wtrh_ajax_formatDate',
		'type'  : 'date',
		'format': document.getElementById('whDateFormat').value
	};
	popup('wtrh_wait_div');
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			document.getElementById('whTestDateFormat').innerHTML = msg;						
			popup('wtrh_wait_div');
			return false;		
	});		
    return false;
}
function wtr_testTimeFormat() {
	// send action
    var data = {
		'action': 'wtrh_ajax_formatDate',
		'type'  : 'time',
		'format': document.getElementById('whTimeFormat').value
	};
	popup('wtrh_wait_div');
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			document.getElementById('whTestTimeFormat').innerHTML = msg;						
			popup('wtrh_wait_div');
			return false;		
	});		
    return false;
}
function wtr_changeDateFormat() {
	// send action
    var data = {
		'action': 'wtrh_ajax_changeFormatDate',
		'type'  : 'date',
		'format': document.getElementById('whDateFormat').value
	};
	
	popup('wtrh_wait_div');
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			popup('wtrh_wait_div');
//			document.getElementById('whTestTimeFormat').innerHTML = msg;						
			return false;		
	});		
    return false;
}
function wtr_changeTimeFormat() {
	// send action
    var data = {
		'action': 'wtrh_ajax_changeFormatDate',
		'type'  : 'time',
		'format': document.getElementById('whTimeFormat').value
	};
	popup('wtrh_wait_div');
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			popup('wtrh_wait_div');
//			document.getElementById('whTestTimeFormat').innerHTML = msg;						
			return false;		
	});		
    return false;
}

// Categories
function wtr_manageCategory(action, id, elt, div="types") {
	var bookType = '';
	var desc     = '';
	var nbB = '', useBW = '', nbBW = '', useSB = '';
	var useStatA = '', useStatE ='', useToDoA = '', useToDoE = '';
	var editAll  = '';
	var labelNextC = '', labelPrevC = '', labelNextCU = '', labelBookEnd = '', 
	    labelReadMore = '', bookInfo = '', bookStatuses = '';
		
	if( document.getElementById('whNewBookType') )
		bookType = document.getElementById('whNewBookType').value;
	
	if( action == "modifyAuthorsSettings" || 
		action == "modifyEditorsSettings" ) {
		popup('wtrh_wait_div');
	}
	
	if( action == "modifyAuthorsSettings" ) {
		bookType = '';
		
		if( document.getElementById('whNbBooksAuthor').value == "Unlimited" ||
		    document.getElementById('whNbBooksAuthor').value.trim.length == 0 )
			nbB = -1;
		else
			nbB = document.getElementById('whNbBooksAuthor').value;
		
		if( document.getElementById('whNbBookworldsAuthor').value == "Unlimited" ||
		    document.getElementById('whNbBookworldsAuthor').value.trim.length == 0  )
			nbBW = -1;
		else
			nbBW = document.getElementById('whNbBookworldsAuthor').value;
		
		if( document.getElementById('whUseBookworlds').checked )
			useBW = 'true';
		else
			useBW = 'false';
		
		if( document.getElementById('whUseStoryboard').checked )
			useSB = 'true';
		else
			useSB = 'false';
		
		if( document.getElementById('whUseStatA').checked )
			useStatA = 'true';
		else
			useStatA = 'false';
		
		if( document.getElementById('whUseToDoA').checked )
			useToDoA = 'true';
		else
			useToDoA = 'false';
		
		desc = '{"nbBooks":'+nbB+', "useBookworld":'+useBW+
				',"nbBookworlds":'+nbBW+',"useStoryboard":'+useSB+
				',"useStatistics":'+useStatA+',"useToDoList":'+useToDoA+'}';
		
	} 
	if( action == "modifyEditorsSettings" ) {
		bookType = '';
		
		if( document.getElementById('whEditAllBooks').checked )
			editAll = 'true';
		else
			editAll = 'false';
		
		if( document.getElementById('whUseStatE').checked )
			useStatE = 'true';
		else
			useStatE = 'false';
		
		if( document.getElementById('whUseToDoE').checked )
			useToDoE = 'true';
		else
			useToDoE = 'false';
			
		desc = '{"editAllBooks":'+editAll+
				',"useStatistics":'+useStatE+',"useToDoList":'+useToDoE+'}';
			
	}

	if( (action == "update" || action == "delete")
      && elt == "Settings::Books" ) {
		popup('wtrh_wait_div');
		bookType = '';
		
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
		
		desc = '{"book_info":"'+bookInfo+'",'+
			   '"book_statuses":'+bookStatuses+','+
			   '"nextChapter_label":"'+labelNextC+'","nextChapter_style":"",'+
			   '"nextChapterU_label":"'+labelNextCU+'","nextChapterU_style":"",'+
			   '"prevChapter_label":"'+labelPrevC+'","prevChapter_style":"",'+
			   '"book_ending_label":"'+labelBookEnd+'","book_ending_style":"",'+
			   '"read_more_label":"'+labelReadMore+'","read_more_style":"",'+
			   '"custom_status_label":"","custom_status_style":""}';
			
	}
	
	
	// send action
    var data = {
		'action' : 'wtrh_ajax_manageCategory',
		'type'   : action,
		'id'     : id,
		'element': elt,
		'title'  : bookType,
		'number' : 0,
		'desc'   : desc,
		'parent_id' : 0
	};
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			
			if( action == "modifyAuthorsSettings" || 
			    action == "modifyEditorsSettings"  ) {
				popup('wtrh_wait_div');
				alert(msg);
				
			} else {
				
				if( div == "types" ) {
					document.getElementById('whBookTypesList').innerHTML = msg;
					document.getElementById('whNewBookType').value = "";
				}
				
				if( div == "settings") {
					popup('wtrh_wait_div');
					if( ret == "KO" )
						alert(msg);
				}
			}
			return false;		
	});		
    return false;
}

// Users 
function wtr_manageUser(action, id, book_id=0) {
	var user_id = 0;

	popup('wtrh_wait_div');
	
	if( document.getElementById('whAddUserInput') )
		document.getElementById('whAddUserInput').value = '';

	if( document.getElementById('whAddEditorNameDiv') )
		document.getElementById('whAddEditorNameDiv').innerHTML = "";
	if( document.getElementById('whAddAuthorNameDiv') )
		document.getElementById('whAddAuthorNameDiv').innerHTML = "";
		
	// send action
    var data = {
		'action' : 'wtrh_ajax_manageUser',
		'type'   : action,
		'id'     : id,
		'book_id': book_id
	};
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			
			popup('wtrh_wait_div');
			if( ret == "OK" ) {
				document.getElementById('whUsersTable').innerHTML = msg;
			} else {
				alert(msg);
			}
			return false;		
	});		
    return false;
}

// List WordPress Users
function wtr_listEditors() {
	wtr_listUser("listEditors");
}
function wtr_listAuthors() {
	wtr_listUser("listAuthors");
}
function wtr_listUser(action="") {
	var name = "";
	var role = "";
	var book_id = 0;
	
	if( action.length == 0 )
		action = "listUsers";
	
	if( document.getElementById('whAddUserListDiv') )
		document.getElementById('whAddUserListDiv').innerHTML = "";
	if( document.getElementById('whAddEditorNameDiv') )
		document.getElementById('whAddEditorNameDiv').innerHTML = "";
	if( document.getElementById('whAddAuthorNameDiv') )
		document.getElementById('whAddAuthorNameDiv').innerHTML = "";
	
	if( document.getElementById('whUserRole') )
		role = document.getElementById('whUserRole').value;
	
	if( document.getElementById('book_id') != null )
		book_id = document.getElementById('book_id').value;
	
	// Book pages
	if( action == "listAuthors" ) 
		name = document.getElementById('whAddAuthorName').value;
	if( action == "listEditors" ) 
		name = document.getElementById('whAddEditorName').value;
		
	// Users pages
	if( document.getElementById('whAddUserInput') ) {
		name = document.getElementById('whAddUserInput').value;
	}

	if( name.length == 0 )
		return false;
	
	// send action
    var data = {
		'action' : 'wtrh_ajax_listWordPressUsers',
		'type'   : action,
		'name'   : name,
		'role'   : role,
		'book_id': book_id
	};

	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );

			if( ret == "OK" && action == "listUsers" ) {
				document.getElementById('whAddUserListDiv').innerHTML = msg;
			}
			
			// Book pages
			if( ret == "OK" && action == "listEditors" ) 
				document.getElementById('whAddEditorNameDiv').innerHTML = msg;
			if( ret == "OK" && action == "listAuthors" ) 
				document.getElementById('whAddAuthorNameDiv').innerHTML = msg;
			
			return false;		
	});		
    return false;
	
}


// Install a module new version
function wtr_installVersion(file, module_id, new_version) {
	
	// send action
    var data = {
		'action' : 'wtrh_ajax_installVersion',
		'file'   : file
	};

	document.getElementById("install_"+module_id).innerHTML = "<img src='"+wtr_getLoadingGif()+"' width='50'>";
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			document.getElementById("install_"+module_id).innerHTML = msg;
			if( ret == "OK" )
				document.getElementById("version_"+module_id).innerHTML = new_version;
			
			return false;		
	});		
    return false;
	
}
