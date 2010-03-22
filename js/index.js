function setInitialPage()
{
	var bookmark = ajaxBookmark();
	if(bookmark=='login')
	{
		
	}
	if(bookmark=='register')
	{
		
	}
}

function loadPage(pageName)
{
	showAjaxIndicator();
	$('.active-page').removeClass('active-page');
	$('#'+pageName+'_link').addClass('active-page');
	$('#middle-content').load('includes/design.php',{loadPage:pageName},function(){ hideAjaxIndicator(); });
}

function onTxt(formObj)
{
	if(formObj.value=='username'||formObj.value=='email'||formObj.value=='email again')
	{
		formObj.value='';
	}
	if(formObj.value=='password')
	{
		formObj.value = '';
		formObj.type = 'password';
	}
	
	$(formObj).css({color: '#515558','border': '1px #515558 solid','background-color': '#f6f6f6'});
}

function offTxt(formObj)
{
	//Reset if left blank
	if(formObj.value=='')
	{
		var orig = new String(formObj.name);
		if(orig=='emailagain')
		{
			formObj.value = "email again";
		}
		else
		{
			formObj.value = orig;
		}
		
		if(formObj.type=='password')
		{
			formObj.type='text';
		}
		
		$(formObj).css({color: '#e8e9e9',backgroundColor: '#b2b2b2'});
	}
	else
	{
		var urlCheck = ajaxBookmark();
		if(urlCheck=='register')
		{
			//Data validation
			if(formObj.name=='username') //Check username for availability
			{	
				var username = formObj.value;
				if(username.indexOf('#')==-1&&username.indexOf('&')==-1)
				{
					$.ajax({
						type: 'POST',
						url: 'includes/formcheck.php',
						data: 'username='+escape(formObj.value),
						success: function(msg){
							if(msg=='true') //If username is available
							{
								$(formObj).css({backgroundColor: '#c5ddcc'});
							}
							else{
								$(formObj).css({backgroundColor: '#c3a9a4'});
							}
						}
					});		
				}
				else
				{
					$(formObj).css({backgroundColor: '#c3a9a4'});
				}
			}
			if(formObj.name=='email'||formObj.name=='emailagain') //Check for proper email syntax
			{
				var emailaddr = formObj.value;
				if(emailaddr.indexOf('@')==-1||emailaddr.indexOf('.')==-1)
				{
					$(formObj).css({backgroundColor: '#c3a9a4'});
				}
				else
				{
					$(formObj).css({backgroundColor: '#c5ddcc'});
				}
				
				if(formObj.name=='emailagain')
				{
					var email1 = document.registerform.email.value;
					var email2 = formObj.value;
					if(email1==email2)
					{
						$(formObj).css({backgroundColor: '#c5ddcc'});
					}
					else
					{
						$(formObj).css({backgroundColor: '#c3a9a4'});
					}
				}
			}
		}
	}
}