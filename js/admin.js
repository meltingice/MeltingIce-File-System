$().ready(function(){
	$('#search-username').autocomplete('usersearch.php',{
		selectFirst: false
	});
	$('#debug-window').draggable({
		handle:'#debug-header'
	});
});

function loadPage(page)
{
	showAjaxIndicator();
	$('#middle-content').load('design.php',{content:page},function(){
		$('.active-page').removeClass('active-page');
		$('#'+page+'_link').addClass('active-page');
		$('#user-actions').hide();
		if(page=='users')
		{
			$('#middle-content').css({width:'470px'});
			$('#right-content').css({width:'244px'});
			$('#ip-ban-form').hide();
			hideAjaxIndicator();
			initStyles();
		}
		else
		{
			if(page=='banlist')
			{
				$('#ip-ban-form').show();
			}
			else
			{
				$('#ip-ban-form').hide();
			}
			$('#middle-content').css({width:'530px'});
			$('#right-content').css({width:'184px'});
			hideAjaxIndicator();
			initStyles();
		}
	});
}

function loadUserlist(page)
{	
	showAjaxIndicator();
	$('#users').load('info.php',{userlist:true,page:page},function(){
		$('#cur_page').html(page);
		hideAjaxIndicator();
	});
}

var curUser = '';
function getUserInfo(username)
{
	showAjaxIndicator();
	$('#middle-content').load('design.php',{content:'userinfo',username:username},function(){
		$('.active-page').removeClass('active-page');
		$('#users_link').addClass('active-page');
		$('#middle-content').css({width:'530px'});
		$('#right-content').css({width:'184px'});
		initStyles();
		curUser = username;
		showUserActions();
		hideAjaxIndicator();
	});
}

function changeUserPage(dir)
{
	var curPage = Number($('#cur_page').html());
	var totPage = Number($('#tot_page').html());
	
	if(dir=='first')
	{
		loadUserlist(1);
	}
	if(dir=='previous')
	{
		if(curPage>1)
		{
			loadUserlist(curPage-1);
		}
	}
	if(dir=='next')
	{
		if(curPage<totPage)
		{
			loadUserlist(curPage+1);
		}
	}
	if(dir=='last')
	{
		loadUserlist(totPage);
	}
}

function showUserActions()
{
	/* Admin check */
	$.ajax({
		type:'POST',
		url:'info.php',
		data:"adminCheck=true&user="+curUser,
		success:function(msg){
			if(msg=='1')
			{
				$('#user_admin').html('Revoke Admin Access');
			}
			else
			{
				$('#user_admin').html('Give Admin Access');
			}
		}
	});
	
	/* Ban check */
	$.ajax({
		type:'POST',
		url:'info.php',
		data:"banCheck=true&user="+curUser,
		success:function(msg){
			if(msg=='1')
			{
				$('#user_ban').html('Unban User');
			}
			else
			{
				$('#user_ban').html('Ban User');
			}
		}
	});
	
	$('#user-actions').show();
}
 var myTimer;
function holdActivate(option)
{
	$('#user_'+option).html('Hold for 2 seconds');
	myTimer = setTimeout(function(){
		userOptions(option);
	},2000);
}

function cancelHoldActivate(option)
{
	clearTimeout(myTimer);
	if(option=='delfiles')
	{
		$('#user_'+option).html('Delete User Files');
	}
	if(option=='deluser')
	{
		$('#user_'+option).html('Delete User');
	}
}

function userOptions(option)
{
	$.ajax({
		type:'POST',
		url:'useredit.php',
		data:'option='+option+'&user='+curUser,
		success:function(msg){
			if(option=='admin')
			{
				if(msg=='1')
				{
					$('#user_admin').html('Revoke Admin Access');
					$('#user_admin').effect('highlight',{},1000);
				}
				else
				{
					$('#user_admin').html('Give Admin Access');
					$('#user_admin').effect('highlight',{},1000);
				}
			}
			if(option=='ban')
			{
				if(msg=='1')
				{
					$('#user_ban').html('Unban User');
					$('#user_ban').effect('highlight',{},1000);
				}
				if(msg=='0')
				{
					$('#user_ban').html('Ban User');
					$('#user_ban').effect('highlight',{},1000);
				}
			}
			if(option=='delfiles')
			{
				if(msg=='1')
				{
					$('#user_delfiles').html('Files Deleted');
					$('#user_delfiles').effect('highlight',{},1000);
					setTimeout(function(){
						$('#user_delfiles').html('Delete User Files');
					},3000);
					getUserInfo(curUser);
				}
				else
				{
					$('#user_delfiles').html('Error');
					$('#user_delfiles').effect('highlight',{},1000);
				}
			}
			if(option=='deluser')
			{
				if(msg=='11')
				{
					loadPage('users');
				}
			}
		}
	});
}

function banIPToggle(ip)
{
	$.ajax({
		type:'POST',
		url:'useredit.php',
		data:'option=banip&ip='+ip,
		success:function(msg){
			loadPage('banlist');
		}
	});
}

function setDefaultQuota()
{
	var newQuota = $("#newDefaultQuota").attr("value");
	$.ajax({
		type:'POST',
		url:'options.php',
		data:'newQuota='+newQuota,
		success:function(msg){
			$('#modalcontent').dialog('close');
		}
	});
}

function executeOption(option)
{
	if(option=='newQuota')
	{
		$('.ui-dialog').css({'background-image':'url("../img/modal-bk.png")'});
		$('#modalcontent').dialog({
			modal: true,
			title: 'Change Default Quota',
			overlay: { 
    		    opacity: 0.5, 
    		    background: "black" 
    		},
    		resizable: false,
    		height: '345px',
    		width: '400px',
    		open:function(){
    			$('#modalcontent').load('options.php',{option:'newQuota'},function(){
		    		$('#modalcontent').show();
		    	});
    		}
		});	
	}
	if(option=='publist')
	{
		$.ajax({
			type:'POST',
			url:'options.php',
			data:'publist=true',
			success:function(msg){
				if(msg=='1')
				{
					var cur = $('#option_publicfiles').html();
					if(cur=='Enabled')
					{
						$('#option_publicfiles').html('Disabled');
					}
					else
					{
						$('#option_publicfiles').html('Enabled');
					}
				}
				else
				{
					$('#option_publicfiles').html('Error');
				}
			}
		});
	}
	if(option=='newuseremail')
	{
		$.ajax({
			type:"POST",
			url:'options.php',
			data:'newuseremail=true',
			success:function(msg){
				if(msg=='1')
				{
					var cur = $('#option_email').html();
					if(cur=='Enabled')
					{
						$('#option_email').html('Disabled');
					}
					else
					{
						$('#option_email').html('Enabled');
					}
				}
				else
				{
					$('#option_email').html('Error');
				}
			}
		});
	}
	if(option=='resync')
	{
		$.ajax({
			type:"POST",
			url:"options.php",
			data:"resync=true",
			success:function(msg){
				var offset = $('#option_resync').offset();
				showDebug(msg,offset.top,(offset.left+200));
			}
		});
	}
	if(option=='userreg')
	{
		$.ajax({
			type:"POST",
			url:"options.php",
			data:"userreg=true",
			success:function(msg){
				if(msg=='1')
				{
					var cur = $('#option_reg').html();
					if(cur=='Enabled')
					{
						$('#option_reg').html('Disabled');
					}
					else
					{
						$('#option_reg').html('Enabled');
					}
				}
				else
				{
					$('#option_reg').html('Error');
				}
			}
		});
	}
	if(option=='userlogin')
	{
		$.ajax({
			type:"POST",
			url:"options.php",
			data:"userlogin=true",
			success:function(msg){
				if(msg=='1')
				{
					var cur = $('#option_userlogin').html();
					if(cur=='Enabled')
					{
						$('#option_userlogin').html('Disabled');
					}
					else
					{
						$('#option_userlogin').html('Enabled');
					}
				}
				else
				{
					$('#option_userlogin').html('Error');
				}
			}
		});
	}
}

function showDebug(content,offsetTop,offsetLeft)
{
	$('#debug-content').html(content);
	$('#debug-window').css({top:offsetTop+"px",left:offsetLeft+"px"});
	$('#debug-window').show("drop",{direction:'up'},500);
}

function closeDebug()
{
	$('#debug-window').hide('drop',{direction:'down'},500);
}