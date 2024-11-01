// Scene JS

function wtr_dragEndScene(event) {
	var i;
	var nb = 1;
	var x =  document.getElementsByClassName("whBookSceneNumber");
	
	if( document.getElementById('whChapterNumber1') != null 
	 && document.getElementById('whChapterNumber1').type == "hidden" ) 
		nb = parseInt(document.getElementById('whChapterNumber1').value);
	
	for (i = 0; i < x.length; i++) {		
		x[i].innerHTML = nb;
		nb=nb+1;
	}
	wtr_changeChapter();
	wtr_changeBook();
}
function wtr_dragEndLinkedScene(event) {
	var i;
	var x =  document.getElementsByClassName("whLinkedSceneNumber");
	
	for (i = 0; i < x.length; i++) {		
		x[i].value = i;
	}
	wtr_changeScene();
}
function wtr_goToScene(url, msg) {
	if( document.getElementById('whSceneSaved') != null )
		if( document.getElementById('whSceneSaved').value == "no" )
			if( confirm(msg) )
				wtr_goTo(url);
}
function wtr_changeScene() {
	if( document.getElementById('whSceneSaved') != null )
		if( document.getElementById('whSceneSaved').value != "no" )
			document.getElementById('whSceneSaved').value = "no";
}

function wtr_refreshGraphData(book_id) {
	wtr_manageScene("refreshGraphData", 0, book_id, 0);
}

function wtr_saveScene(id) {
	var sstatus = 0;
	sstatus = document.getElementById('scene_status').value;
	wtr_manageScene('update', sstatus, id, 0);
}
// Save, create, delete, status
function wtr_manageScene(action, sstatus, id, chapter_id) {
	var desc  = '';
	var ltext = '';
	var ttext = '';
	var etext = '';
	var number = 0;
	var page = 0; // book page or chapter page
	var book_id = 0;
	var ls_id = 0;
	
	if( document.getElementById('whSceneDesc') != null )
		desc   = document.getElementById('whSceneDesc').value;
	if( document.getElementById('whSceneText') != null ) {
		ltext  = tinyMCE.get('whSceneText').getContent();	
/*alert('value :'+document.getElementById('whSceneText').value);
alert('getContent() :'+tinyMCE.get('whSceneText').getContent());
alert('getContent(html) :'+tinyMCE.get('whSceneText').getContent({format : 'html'}));
alert('getContent(text) :'+tinyMCE.get('whSceneText').getContent({format : 'text'}));
alert('getContent(raw) :'+tinyMCE.get('whSceneText').getContent({format : 'raw'}));*/
	}
	if( document.getElementById('whSceneText') != null )
		ttext  = tinyMCE.get('whSceneText').getContent({format : 'text'});	
	if( document.getElementById('whEditingText') != null )
		etext  = tinyMCE.get('whEditingText').getContent({format : 'text'});
	if( document.getElementById('whSceneNumber') != null )
		number = document.getElementById('whSceneNumber').value;
	if( document.getElementById('chapter_id') != null )
		page = 1;
	
	if( action == "addLinkedScene" ) {
		id = document.getElementById('scene_id').value;
		// linked scene id
		chapter_id = document.getElementById('whNewLink').options[document.getElementById('whNewLink').options.selectedIndex].value;
		// link description
		desc = document.getElementById('whNewLinkedSceneDesc').value;
	}
	if( action == "updateLinkedScene" ) {
		// linked scene order number
		number = document.getElementById('whLinkSceneOrder'+chapter_id).value;
		// link description
		desc = document.getElementById('whLinkSceneDesc'+chapter_id).value;
	}
	// create linked scenes list
	if( action == "update" && sstatus == 0 ) {
		var x =  document.getElementById("whSceneGameBook").getElementsByClassName("whLinkedSceneId");
		etext = "[";
		for (i = 0; i < x.length; i++) {
			if( i > 0 )
				etext = etext + ",";
			
			ls_id = x[i].value;
			
			etext = etext + '{';
			
			etext = etext + '"scene_id": ' + ls_id ;
			etext = etext + ',"libelle": "' + document.getElementById('whLinkSceneDesc'+ls_id).value + '"';
			
			etext = etext + "}";
		}
		etext = etext + "]";
	}
	
	// send action
    var data = {
		'action'      : 'wtrh_ajax_manageScene',
		'type'        : action,
		'id'          : id,
		'status'      : sstatus,
		'description' : desc,
		'text'        : ltext,
		'ttext'       : ttext,
		'editingText' : etext,
		'number'      : number,
		'chapter_id'  : chapter_id
	};
	
/*	if( document.getElementById('whMsg') != null )
		document.getElementById('whMsg').innerHTML = 
		"<img src='"+wtr_getLoadingGif()+"' width='30'>";
*/	popup('wtrh_wait_div');
	
	jQuery.post(ajax_object.ajax_url, data, function(response) {
//alert("response="+response);	
			ret=response.substring(0,2);
			msg=response.substring(3, response.length );
			
			popup('wtrh_wait_div');
			if(document.getElementById('whMsg') != null)
			document.getElementById('whMsg').innerHTML = "";
			
			if( ret == "KO" ) {
				alert(msg);
				return false;
			}

			if( action == "create" ) {
				document.getElementById('whMsg').innerHTML = 
				"<img src='"+wtr_getLoadingGif()+"' width='30'>";
				wtr_goTo(msg);
				return false;
			}
								
			if( action == "delete" ) {
				location.reload();
				return false;
			}
				
			if( action == "update" ) {
				if( document.getElementById('whSceneSaved') != null )
					document.getElementById('whSceneSaved').value = "yes";
			}
			
			if(document.getElementById('whScenes'+chapter_id) != null)
				document.getElementById('whScenes'+chapter_id).innerHTML = msg;
			
			if( action == "status" ) {
				wtr_manageChapter('refreshStatus', page, chapter_id, 0);
			}
			
			if( action == "addLinkedScene" 
			 || action == "updateLinkedScene"
			 || action == "deleteLinkedScene" ) {
				document.getElementById('whSceneGameBook').innerHTML = msg;
			}
			
			if( action == "refreshGraphData" ) {
				graphData = JSON.parse(msg);
				createGraph(graphData);
			}

			return false;		
	});		
    return false;
}
