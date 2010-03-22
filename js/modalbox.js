function myAccount()
{
	$('.ui-dialog').css({'background-image':'url("img/modal-bk.png")'});
	$('#modalcontent').dialog({
		modal: true,
		title: 'My Account',
		overlay: { 
    	    opacity: 0.5, 
    	    background: "black" 
    	},
    	resizable: false,
    	height: '345px',
    	width: '400px',
    	open:function(){
    		$('#modalcontent').load('includes/modalbox.php',{modalbox:'myAccount'},function(){
	    		$('#modalcontent').show();
	    	});
    	}
	});
}

function deleteConfirm(fileID)
{
	$('.ui-dialog').css({'background-image':'url("img/modal-bk.png")'});
	$('#modalcontent').dialog({
		modal: true,
		title: 'Confirm Delete',
		overlay: { 
    	    opacity: 0.5, 
    	    background: "black" 
    	},
    	resizable: false,
    	height: '345px',
    	width: '400px',
    	open:function(){
    		$('#modalcontent').load('includes/modalbox.php',{modalbox:'deleteConfirm',type:'file',fileID:fileID},function(){
	    		$('#modalcontent').show();
	    	});
    	}
	});
}

function deleteFolderConfirm(folderID)
{
	hideFolderInfo();
	
	$('.ui-dialog').css({'background-image':'url("img/modal-bk.png")'});
	$('#modalcontent').dialog({
		modal: true,
		title: 'Folder Deletion',
		overlay: { 
    	    opacity: 0.5, 
    	    background: "black" 
    	},
    	resizable: false,
    	height: '345px',
    	width: '400px',
    	open:function(){
    		$('#modalcontent').load('includes/modalbox.php',{modalbox:'deleteFolderConfirm',type:'folder',folderID:folderID},function(){
	    		$('#modalcontent').show();
	    	});
    	}
	});
}

function crossloadDialog()
{
	$('#modalcontent').html('');
	$('#modalcontent').dialog({
		modal: true,
		title: 'Crossload a File',
		overlay: {
			opacity: 0.5,
			background: "black"
		},
		resizable: false,
		height: '296px',
		width: '600px',
		open:function(){
			$('#modalcontent').load('includes/modalbox.php',{modalbox:'crossload',type:'file',folderID:activeFolder},function(){
				$('.ui-dialog').css({'background-image':'url("img/modalwide-bk.png")'});
				$('.ui-dialog-titlebar').css({width:'560px'});
				$('.ui-dialog-title').css({width:'510px'});
				$('#modalcontent').css({width:'550px','text-align':'center'});
				$(this).show();
			});
		}
	});
}

function checkCrossloadURL()
{
	var content = $('#crossload-url').attr('value');
	var contentArr = content.split('.');
}