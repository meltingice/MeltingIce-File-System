var installStarted = false;
var installValid = true;

/* Time for lots of ajax */
function startInstall()
{
	if(!installStarted)
	{
		/* Step 1: Disable multiple submits */
		installStarted = true;
		
		/* Step 2: Inform user install has started */
		$('#installprogress').show();
		appendSuccess("The install process has started.");
		
		/* Step 3: Check to make sure users directory is intact and correct */
		$.ajax({
		    type: 'POST',
		    url: 'includes/install.php',
		    data: 'dircheck=true',
		    success: function(msg){
		    	if(msg=='false')
		    	{
		    		appendError("Directory structure not valid.");
		    	}
		    	else
		    	{
		    		appendSuccess("Directory structure confirmed");
		    		
		    		/* Step 4: Connect to MySQL and select database */
		    		$.ajax({
		    			type: 'POST',
		    			url: 'includes/install.php',
		    			data: 'checkmysql=true',
		    			success: function(msg){
		    				if(msg=='false'||msg=='Could not connect to MySQL Server!'||msg=='Could not select database!')
		    				{
		    					appendError("Unable to connect to MySQL. Check your settings in 'includes/dbconnect.php'");
		    				}
		    				else
		    				{
		    					appendSuccess("MySQL connection established.");
		    					
		    					/* Step 5: Create 'files' MySQL table */
		    					$.ajax({
		    						type: 'POST',
		    						url: 'includes/install.php',
		    						data: 'createtable=files',
		    						success: function(msg){
		    							if(msg=='true'||msg=='skip')
		    							{
		    								appendSuccess("MySQL table 'files' created.");
		    								
		    								/* Step 6: Create 'folders' MySQL table */
		    								$.ajax({
		    									type: 'POST',
		    									url: 'includes/install.php',
		    									data: 'createtable=folders',
		    									success: function(msg){
		    										if(msg=='true'||msg=='skip')
		    										{
		    											appendSuccess("MySQL table 'folders' created.");
		    											
		    											/* Step 7: Create 'users' MySQL table */
		    											$.ajax({
		    												type: 'POST',
		    												url: 'includes/install.php',
		    												data: 'createtable=users',
		    												success: function(msg){
		    													if(msg=='true'||msg=='skip')
		    													{
		    														appendSuccess("MySQL table 'users' created.");
		    														
		    														/* Step 8: Create 'admin' MySQL table */
		    														$.ajax({
		    															type: 'POST',
		    															url: 'includes/install.php',
		    															data: 'createtable=admin',
		    															success:function(msg){
		    																if(msg=='true')
		    																{
		    																	appendSuccess("MySQL table 'admin' created.");
		    																	
		    																	/* Step 8: Create 'adminoptions' MySQL table */
		    																	$.ajax({
		    																		type: 'POST',
		    																		url: 'includes/install.php',
		    																		data: 'createtable=adminoptions',
		    																		success: function(msg){
		    																			if(msg=='true')
		    																			{
		    																				appendSuccess("MySQL table 'adminoptions' created.");
		    																				
		    																				/* Step 9: Create 'quotas' MySQL table */
		    																				$.ajax({
		    																					type: 'POST',
		    																					url: 'includes/install.php',
		    																					data: 'createtable=quotas',
		    																					success: function(msg){
		    																						if(msg=='true')
		    																						{
		    																							appendSuccess("MySQL table 'quotas' created");
		    																							
		    																							/* Step 10: Finish installation */
		    																							appendSuccess("Install completed!");
											    														$('#finishmessage').fadeIn('slow');
		    																						}
		    																						else
		    																						{
		    																							appendError("Error creating MySQL table 'quotas'");
		    																						}
		    																					}
		    																				});
		    																			}
		    																			else
		    																			{
		    																				appendError("Error creating MySQL table 'adminoptions'");
		    																			}
		    																		}
		    																	});
		    																}
		    																else
		    																{
		    																	appendError("Error creating MySQL table 'admin'");
		    																}
		    															}
		    														});
		    													}
		    													else
		    													{
		    														appendError("Error creating MySQL table 'users'");
		    													}
		    												}
		    											});
		    										}
		    										else
		    										{
		    											appendError("Error creating MySQL table 'folders'");
		    										}
		    									}
		    								});
		    							}
		    							else
		    							{
		    								appendError("Error creating MySQL table 'files'");
		    							}
		    						}
		    					});
		    				}
		    			}
		    		});
		    	}
		    }
		});
	}
}

function appendError(msg)
{
	$('#install-list').append("<li><img src=\"img/icons/exclamation.png\" alt=\"fail\" />&nbsp;"+msg+"</li>");
}

function appendSuccess(msg)
{
	$('#install-list').append("<li><img src=\"img/icons/accept.png\" alt=\"ok\" />&nbsp;"+msg+"</li>");
}