<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>MeltingIce File System 2</title>
	<link rel="stylesheet" href="css/template.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<link rel="stylesheet" href="css/indexstyle.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<script src="js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/backend.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/install.js" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<div id="container">
	<div id="header">
		<h4>MELTINGICE FILE SYSTEM 2</h4>
		<h4 id="sitetitle"><small>INSTALL SCRIPT</small></h4>
	</div>
	<div id="content-container">
		<div id="content">
			<div id="msgbox" style="display:none">
				<p><?php if($error){ echo $error; } ?></p>
				<p id="msgboxclose"><a href="#" onclick="closeErrorMsg();">X</a></p>
			</div>
			<div id="installcontent">
				<h4>INSTALL MFS2</h4>
				<p>Before starting the automated install process, make sure you have edited "includes/dbconnect.php" for your server's settings.</p>
				<form action="install.php" enctype="multipart/form-data" method="post" id="installform" name="installform">
					<p><input type="button" id="startinstall" name="startinstall" value="Start Install!" class="submitbutton" onclick="startInstall()" /></p>
				</form>
				<div id="installprogress" style="display:none">
					<h4>INSTALL PROGRESS</h4>
					<ul id="install-list">
						
					</ul>
					<div id="finishmessage" style="display:none">
						<p>Thats it, MFS2 is installed!  Were you expecting more?  Although they can't do any harm, I would recommend you delete 'install.php', 'js/install.js', and 'includes/install.php' just in case.  Hope you enjoy MFS2!</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>