/* SWFUpload related stuff */
function uploadStart(file)
{ 
	curFileNum++;
	var continue_with_upload = true;
	return continue_with_upload;
}

function uploadSuccessEventHandler(file, server_data)
{
	$("#files").load("includes/filemgmt.php",{scope: curScope,folder:activeFolder},function(msg){
		indicatorLock=false;
		if(numQueued==0)
		{
			//hideAjaxIndicator();
		}
	});
}

var numQueued=0;
var curFileNum=0;
function fileDialogComplete(numFilesSelected, numFilesQueued)
{
	try {
		if (numFilesSelected > 0) {
			indicatorLock = true;
			numQueued += numFilesQueued;
			$('#upload-progress-container').fadeIn('fast');
			$('#upload-data').fadeIn('fast');
			this.startUpload();		}		
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadProgressHandler(file, complete, total)
{
	var completeKB = Math.round(complete/1024);
	var totalKB = Math.round(total/1024);
	var percent = completeKB/totalKB;
	var divWidth = percent*200;
	$('#upload-progress').css({width:divWidth+'px'});
	$("#upload-amt").html(completeKB+"KB/"+totalKB+"KB");
	$("#filequeue").html('('+curFileNum+' of '+numQueued+')');
}

function queueComplete(numFilesUploaded)
{
	$("#upload-progress-container").fadeOut('fast');
	$("#upload-data").fadeOut('fast');
	$("#"+activeFolder+"_numcontent").load("includes/foldermgmt.php",{numContents:activeFolder});
	numQueued=0;
	curFileNum=0;
}
/* End SWFUpload handlers */

function loadHome()
{
	showAjaxIndicator();
	$('#fileinfo').hide();
	$("#middle-content").load("includes/design.php",{"loadHome":"true"},function(){
		$('#middle-content').css({width:'530px'});
		$('#right-content').css({width:'184px'});
		$('#news').show();
		hideAjaxIndicator();
	});
	$('.active-folder').removeClass('active-folder');
	$('#home-folder').addClass('active-folder');
	
}

function addUploadInput()
{
	if($('.fileinput').length<5)
	{
		$('.fileinput:last').after("<p><input type=\"file\" class=\"fileinput\" name=\"Filedata[]\" /></p>\n");
	}
}

var curScope = 'all';
function changeScope(scope)
{
	if(scope!=curScope)
	{
		showAjaxIndicator();
		if($('#msgbox').css('display')!='none'){ closeErrorMsg(); }
		
		$("#userfiles").load("includes/filemgmt.php",{scope: scope},function(msg){ hideAjaxIndicator(); adjustStyles(); });
		curScope=scope;
		if(scope=='public'){ $('#filescope').html("(PUBLIC ONLY)"); }
		else if(scope=='private'){ $('#filescope').html("(PRIVATE ONLY)"); }
		else{ $('#filescope').html("(ALL)"); }
	}
}

function changeSort(user,sortType)
{
	showAjaxIndicator();
	/* Retrieve sort order from form */
	//if(document.sortform.sort[0].checked){ var sortOrder = 'ASC'; }
	//else{ var sortOrder = 'DESC'; }
	
	/* Convert sort type into something usable */
	if(sortType=='name'){ sortType='filename'; }
	if(sortType=='size'){ sortType='filesize'; }
	if(sortType=='update'){ sortType='dateuploaded'; }
	if(sortType=='lastmod'){ sortType='lastmodified'; }
	
	$("#files").load("includes/filemgmt.php",{scope: curScope,user: user, sort: sortType, order: 'ASC', folder: activeFolder},function(){  hideAjaxIndicator(); });
}

function getFileInfo(fileID)
{
	showAjaxIndicator();
	$('#fileinfo').load('includes/fileinfo.php',{fileID: fileID},function(msg){ hideAjaxIndicator(); });
}

function changeFileAction(type)
{
	if(type=='delete')
	{
		$('#navbar-right').html('<a href="javascript:deleteFiles()"><img src="img/delete_selected.png" alt="delete selected" style="margin:0 5px 0 0;"/></a>');
		$('.filelink a').attr('href','javascript:void(0)');
		
		$('.file').click(function(){
			if($(this).hasClass('deleteme'))
			{
				$(this).removeClass('deleteme');
				
			}
			else
			{
				$(this).addClass('deleteme');
			}
		});
	}
	if(type=='normal')
	{
		$('#navbar-right').html('\
			<a href="javascript:changeFileAction(\'delete\')" title="Delete File Mode"><img src="img/icons/delete.png" alt="delete" /></a>\
			<a href="javascript:changeFileAction(\'rename\')" title="Rename File Mode"><img src="img/icons/link_edit.png" alt="rename" /></a>\
			<a href="javascript:changeFileAction(\'perms\')" title="Permission Change Mode"><img src="img/icons/group_edit.png" alt="perms" /></a>');
		loadFolder(activeFolder);
	}
	if(type=='perms')
	{	
		showAjaxIndicator();
		
		$('#navbar-right').html('<a href="javascript:changeFileAction(\'normal\')"><img src="img/finished.png" alt="finished" style="margin:0 5px 0 0;"/></a>');
		
		$("#files").load("includes/filemgmt.php",{scope: curScope, showperms: 'true',folder: activeFolder},function(msg){ 
				hideAjaxIndicator(); 
				initStyles();
				
				$('.file').click(function(){
					var temp = $(this).attr('id').split("_");
					var fileID = temp[1];
					changeFilePerms(fileID);
				});
		});
		
	}
	if(type=='rename')
	{
		$('#navbar-right').html('<a href="javascript:changeFileAction(\'normal\')"><img src="img/finished.png" alt="finished" style="margin:0 5px 0 0;"/></a>');
		
		$('.file a').attr('href','javascript:void(0)');
		
		$('.file').click(function(){
			var temp = $(this).attr('id').split("_");
			var fileID = temp[1];
			
			showEditInPlace(fileID);
		});
	}
}

function deleteFile(fileID)
{
	$.ajax({
		beforeSend:function(){ $('#modalcontent').dialog('close'); },
		type:'POST',
		url:'includes/filemgmt.php',
		data:'delfile[]='+fileID,
		success:function(msg){
			updateNumContents(activeFolder);
			loadFolder(activeFolder);
			$('#fileinfo').html('<h4>File Information</h4><p style="text-align: center;">Select a file to view info</p>');
		}
	});
}

function deleteFiles()
{
	showAjaxIndicator();
	var delFiles = $('.deleteme');
	var postQuery = '';
	var fileID = '';
	var idSplit = '';
	
	if(delFiles.length==0)
	{
		changeFileAction('normal');
	}
	else
	{
		for(var i=0;i<delFiles.length;i++)
		{
			idSplit = $(delFiles[i]).attr('id').split("_");
			fileID = idSplit[1];
			if(i!=0){ postQuery += "&"; }
			postQuery += 'delfile[]='+fileID;
		}
		
		$.ajax({
			type: 'POST',
			url: 'includes/filemgmt.php',
			data: postQuery,
			success: function(msg)
			{
				$('.deleteme').fadeOut('slow');
				updateNumContents(activeFolder);
				changeFileAction('normal');
			}
		});
	}
	
}

function changeFilePerms(fileID)
{	
	$.ajax({
		type: 'POST',
		url: 'includes/filemgmt.php',
		data: 'changeperms='+fileID,
		success: function(msg)
		{
			$('#file_'+fileID).effect('highlight',{},1500);
			toggleFilePermDisplay(fileID);
		}
	});
}

function toggleFilePermDisplay(fileID)
{
	var curPerm = $('#perm_'+fileID).html();
	if(curPerm == '(public)'){ nextPerm = '(private)'; }
	else{ nextPerm = '(public)'; }
	
	$('#perm_'+fileID).html(nextPerm);
}

function showEditInPlace(fileID)
{	
	$('#file_'+fileID).unbind('click');
	var filename = $('#file_'+fileID+' .filename').html();
	
	$('#file_'+fileID+' .filelink').html("\
		<form action=\"javascript:renameFile("+fileID+")\" id=\"newnameform_"+fileID+"\">\
			<input type=\"text\" name=\"newfilename\" id=\"newfilename_"+fileID+"\" value=\""+filename+"\" />\
			<input type=\"submit\" value=\"Rename\" />\
			<input type=\"button\" value=\"Cancel\" onclick=\"hideEditInPlace("+fileID+")\">\
		</form>\
	");
	
	$('#newfilename_'+fileID).focus();
}

function hideEditInPlace(fileID)
{	
	refreshFile(fileID);
}

function renameFile(fileID)
{
	var newfilename = $('#newfilename_'+fileID).attr('value');
	
	if(newfilename=='')
	{
		hideEditInPlace(fileID);
	}
	else
	{
		$.ajax({
			type: 'POST',
			url: 'includes/filemgmt.php',
			data: 'renamefile='+fileID+'&newfilename='+escape(newfilename),
			success: function(msg){
			refreshFile(fileID);
			}
		});
	}
}

function refreshFilelist()
{
	showAjaxIndicator();
	$('#userfiles').load('includes/filemgmt.php',{scope:curScope, refresh:'true'},function(){ hideAjaxIndicator(); });
}

function refreshFile(fileID)
{
	showAjaxIndicator();
	$('#file_'+fileID).load('includes/filemgmt.php',{refreshFile: fileID}, function(msg){
		$('#file_'+fileID).effect('highlight',{},1500);
		$('#file_'+fileID).click(function(){
			var temp = $(this).attr('id').split("_");
			var fileID = temp[1];
			
			showEditInPlace(fileID);
		});
		
		hideAjaxIndicator();
	});
}

function setSpacebarAmt(used,avail)
{   
    if(used>avail){ /* alert("Invalid values"); */ }
    else
    {
    	var percentFree = (used/avail);
    	
    	$('#spacebar').animate({width: (percentFree*400)+"px"}, 1500, function(){
    		//$('#spacebar-amt').html((Math.round(percentFree*100)/100)+"%");
    	});
    }
}

function crossloadFile()
{
	var fileURL = escape($('#crossload-url').attr('value'));
	var folderID = $('#crossload-folder').attr('value');
	
	$.ajax({
		beforeSend:function(){ $('#modalcontent').append('<img src="img/ajax-loader2.gif" alt="loading" />'); },
		type:'POST',
		url:'includes/crossload.php',
		data:'URL='+fileURL+'&folderID='+folderID,
		success:function(msg){
			$('#modalcontent').dialog('close');
			updateNumContents(folderID);
			loadFolder(folderID);
		}
	});
}

/* Folder related functions */
function newFolder()
{
	showAjaxIndicator();
	var newFoldername = $("#newfoldername").attr('value');
	if(newFoldername=='')
	{
		toggleFolderForm();
	}
	else
	{
		$.ajax({
			type: 'POST',
			url: 'includes/foldermgmt.php',
			data: 'newfolder='+escape(newFoldername),
			success: function(msg){
				toggleFolderForm();
				refreshFolderList();
				hideAjaxIndicator();
				initStyles();
			}
		});
	}
}

function updateNumContents(folderID)
{
	$("#"+folderID+"_numcontent").load("includes/foldermgmt.php",{numContents:folderID});
}

function refreshFolderList()
{
	showAjaxIndicator();
	$('#editable-folders').load("includes/foldermgmt.php",{"refresh":"true"},function(){
		initStyles();
		hideAjaxIndicator();
		$('#folder_'+activeFolder).addClass('active-folder');
	});
}

var activeFolder = -1;
function loadFolder(folderNum)
{
	showAjaxIndicator();
	$('#news').hide();
	$("#middle-content").load("includes/design.php", {"showFolder":folderNum},function(){
		$('#middle-content').css({width:'470px'});
		$('#right-content').css({width:'244px'});
		$('#fileinfo').show();
		hideAjaxIndicator();
		initStyles();
	});
	$('.active-folder').removeClass('active-folder');
	$('#folder_'+folderNum).addClass('active-folder');
	
	activeFolder = folderNum;
}

function toggleFolderForm()
{
	if($('#create-folder').is(':visible')){ $('#create-folder').fadeOut('normal'); }
	else{ $('#create-folder').fadeIn('normal'); }
	
	$('#newfoldername').focus();
}

function moveFile(folderID,fileID)
{
	if($("#"+folderID).hasClass('active-folder'))
	{
		$('.active-folder').css({'background-image':'url(\"img/activefolder.jpg\")','background-repeat':'repeat-x'});
		loadFolder(activeFolder);
	}
	else
	{
		var temp1 = folderID.split("_");
		var folderNum = temp1[1];
		var postQuery = 'movefile=true&folderID='+folderNum;
		
		if($("#"+fileID).hasClass('multi-select'))
		{
			$('.multi-select').each(function(i){
				var temp = $(this).attr('id').split("_");
				var fileNum = temp[1];
				
				postQuery += '&fileID[]='+fileNum;
			});
		}
		else
		{
			var temp2 = fileID.split("_");
			var fileNum = temp2[1];
			postQuery += '&fileID[]='+fileNum;
		}
		
		$.ajax({
			type:'POST',
			url:'includes/filemgmt.php',
			data: postQuery,
			success: function(msg)
			{
				refreshFolderList();
				loadFolder(activeFolder);
			}
		})
	}
	
	$('#'+folderID).removeClass('droppable-hover');
}

function showFolderEditIcon(folderObj)
{
	var folderID = $(folderObj).attr('id');
	var temp = folderID.split("_");
	var folderNum = temp[1];
	
	$("#"+folderID+" img").attr('src','img/icons/folder_edit.png');
	$("#"+folderID+">a:first").attr('href','javascript:showFolderInfo('+folderNum+');');
}

function hideFolderEditIcon(folderObj)
{
	var folderID = $(folderObj).attr('id');
	$("#"+folderID+" img").attr('src','img/icons/folder.png');
	$("#"+folderID+">a:first").attr("href","javascript:void(0)");
}

var folderInfoLock = false;
function forceFolderInfo()
{
	folderInfoLock = true;
}

function showFolderInfo(folderNum)
{
	var folderID = '#folder_'+folderNum;
	var offset = $(folderID).offset();
	
	$('.folderinfo').css({top:(offset.top-70)+"px",left:(offset.left+200)+"px"});
	$('.folderinfo').fadeIn('fast');
		
	$('.folderinfo').load('includes/modalbox.php',{modalbox:'folder',folderID:folderNum});
}

function hideFolderInfo()
{
	if(!folderInfoLock)
	{
		$('.folderinfo').fadeOut('fast');
	}
}

function saveFolderEdit(folderNum)
{
	var newFoldername = $('#foldereditname').attr('value');
	folderInfoLock = false;
	
	if(newFoldername==''){ hideFolderInfo(); }
	else
	{
		if(document.dialog_foldereditform.folderperms[0].checked){ var folderPerms = 'public'; }
		else{ var folderPerms = 'private'; }
		
		$.ajax({
			beforeSend:function(){ hideFolderInfo(); },
			type: 'POST',
			url: 'includes/foldermgmt.php',
			data: 'editFolder='+folderNum+'&foldername='+newFoldername+'&folderPerms='+folderPerms,
			success: function(msg){
				refreshFolderList();
			}
		});
	}
}

function deleteFolder(folderID,option)
{
	$.ajax({
		beforeSend:function(){ $('#modalcontent').dialog('close'); },
		type:'POST',
		url:'includes/foldermgmt.php',
		data:'delfolder='+folderID+'&fileOption='+option,
		success:function(msg){
			refreshFolderList();
			loadFolder(-1);
		}
	});
}
/* End folder related functions */