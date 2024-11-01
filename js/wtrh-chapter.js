// Chapter JS

function wtr_dragEndChapter(event) {
	var i;
	var x =  document.getElementsByClassName("whBookChapterNumber");
	for (i = 0; i < x.length; i++) {		
/*		document.getElementById('whMsg').innerHTML = 
		document.getElementById('whMsg').innerHTML +
		x[i].id+"/"+
		x[i].className+"/"+x[i].innerHTML+"<br>";
*/		x[i].innerHTML = i+1;
	}
	wtr_dragEndScene(event);
	wtr_changeScene();
	wtr_changeChapter();
	wtr_changeBook();
}
function wtr_changeChapter() {
	if( document.getElementById('whChapterSaved') != null )
		if( document.getElementById('whChapterSaved').value != "no" )
			document.getElementById('whChapterSaved').value = "no";
}


function wtr_saveChapter(id) {
	var book_id = 0;
	if( document.getElementById('book_id') != null )
		book_id  = document.getElementById('book_id').value;

	wtr_manageChapter('update', 0, id, book_id);
}

 
function wtr_createChapterPost(id) {
	wtr_manageChapter("createPost", "", id, 0);
}
function wtr_deleteChapterPost(id) {
	wtr_manageChapter("deletePost", "", id, 0);
}

// Save, create, delete, status
function wtr_manageChapter(action, cstatus, id, book_id) {
	var title  = '';
	var number = 0;
	var snum   = 0;
	var stit   = 0;
	var s_list = '';
	
	if( document.getElementById('whTitle') != null )
		title  = document.getElementById('whTitle').value;
	if( document.getElementById('whNumber') != null )
		number = document.getElementById('whNumber').value;
	if( document.getElementById('whShowNumber') != null )
		if( document.getElementById('whShowNumber').checked )
		snum   = 1;
	if( document.getElementById('whShowTitle') != null )
		if( document.getElementById('whShowTitle').checked )
		stit   = 1;
	if( action == "update" ) {	
		// set chapters & scenes list
		var x =  document.getElementsByClassName("whBookCSlist");
		for (i = 0; i < x.length; i++) {		
			if( x[i].id.substr(2,1) == "S" ) // If scene
				s_list = s_list + "s" + x[i].id.substr(13) + "-" + x[i].innerHTML;
		}
	}
	
	// send action
    var data = {
		'action'  : 'wtrh_ajax_manageChapter',
		'type'    : action,
		'id'      : id,
		'status'  : cstatus,
		'title'   : title,
		'number'  : number,
		'showN'   : snum,
		'showT'   : stit,
		's_list'  : s_list,
		'book_id' : book_id
	};
	
/*	if( document.getElementById('whMsg') )
		document.getElementById('whMsg').innerHTML = 
		"<img src='"+wtr_getLoadingGif()+"' width='30'>";
*/	popup('wtrh_wait_div');
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			
			popup('wtrh_wait_div');
			document.getElementById('whMsg').innerHTML = "";
			if( ret == "KO" ) {
				alert(msg);
				return false;
			} 
			
			if( action == "createPost" || action == "deletePost" ) {
				if( document.getElementById('whChapterPostButton'+id) ) {
					document.getElementById('whChapterPostButton'+id).innerHTML = msg;
				} else if( document.getElementById('whChapterPostButton') ) 
					document.getElementById('whChapterPostButton').innerHTML = msg;
			}
			
			if( action == "refreshStatus" ) {
				document.getElementById('whChapterStatus'+id).innerHTML = msg;
				if( book_id == 0 )
					book_id = document.getElementById('book_id').value;
				wtr_manageBook('refreshStatus', 0, book_id, 0);
				return false;
			}
			
			if( action == "status" ) {
				if( book_id == 0 )
					book_id = document.getElementById('book_id').value;
				wtr_manageBook('refreshStatus', 0, book_id, 0);
			}
			
			if( msg.length > 0 && action != "createPost" && action != "deletePost") {
				document.getElementById('whChapters').innerHTML = msg;
			}
								
			if( action == "update" ) {
				if( document.getElementById('whChapterSaved') != null )
					document.getElementById('whChapterSaved').value = "yes";
			}
								
			if( action == "delete" ) {
				location.reload();
				return false;
			}

			return false;		
	});		
    return false;
}

