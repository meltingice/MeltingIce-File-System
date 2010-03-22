function ajaxBookmark()
{
	var curUrl = new String(window.location);
	if(curUrl.indexOf('#')==-1)
	{
		return false;
	}
	else
	{
		var splitUrl = curUrl.split('#');
		return splitUrl[1];
	}
}

var indicatorLock = false;
function showAjaxIndicator()
{
	if($("#ajaxindicator").css('display')=='none')
	{
		$("#ajaxindicator").fadeIn("fast");
	}
}

function hideAjaxIndicator()
{
	if(!indicatorLock)
	{
		$("#ajaxindicator").fadeOut("fast");
	}
}

function displayErrorMsg()
{
	$('#msgbox').slideDown('slow');
}

function closeErrorMsg()
{
	$('#msgbox').slideUp('slow');
}

function initStyles()
{
	if($.browser.mozilla)
	{
		$('.foldernumcontent').css({margin:"-16px 40px 0 0"});
	}
	if($.browser.safari)
	{
		$('.foldernumcontent').css({margin:"2px 40px 0 0"});
	}
	
	var leftHeight = $('#left-content').height();
	var middleHeight = $('#middle-content').height();
	var folderHeight = $('#folders').height();
	
	if(leftHeight>middleHeight)
	{
		$('#middle-content').css({height:leftHeight+"px"});
		$('#right-content').css({height:leftHeight+"px"});
	}
	else
	{
		//$('#left-content').css({height:middleHeight+"px"});
		$('#right-content').css({height:middleHeight+"px"});
	}
}

function showClickChange(docObj)
{
	$(docObj).append('<span class="hover-info"> (Click to toggle)</span>');
}

function hideClickChange()
{
	$('.hover-info').remove();
}