<?php
include_once('includes/errormsg.php');
$error = getErrorMsg();
include_once('includes/design.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>MeltingIce File System 2</title>
	<link rel="stylesheet" href="css/template.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<link rel="stylesheet" href="css/indexstyle.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<script src="js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery-ui.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/backend.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/index.js" type="text/javascript" charset="utf-8"></script>
</head>
<body onload="setInitialPage();<?php if($error){ echo "displayErrorMsg();"; } ?>">
<div id="modalcontent" style="display:none"></div>
<div class="folderinfo" style="display:none"></div>
<div id="header"><p>MeltingIce File System 2</p></div>
<div id="content-container">
	<div id="content-header">
		<div id="left-content-header">
			<div id="ajaxindicator" style="display:none"><img src="img/ajax-loader.gif" alt="loading" /><p>Loading...</p></div>
		</div>
		<div id="right-content-header">
		</div>
	</div>
	<div id="content">
		<div id="left-content">
			<ul id="pages">
				<li id="login_link" class="page active-page" onclick="loadPage('login'); window.location='#login';"><img src="img/icons/key.png" alt="login" />Login</li>
				<li id="register_link" class="page" onclick="loadPage('register'); window.location='#register';"><img src="img/icons/group_edit.png" alt="home" />Register</li>
				<li id="about_link" class="page" onclick="loadPage('about'); window.location='#about';"><img src="img/icons/information.png" alt="home" />About</li>
			</ul>
		</div>
		<div id="middle-content">
			<?php loadContent('login'); ?>
		</div>
		<div id="right-content">
			<div id="news">
			
			</div>
		</div>
	</div>
</div>
<?php include('footer.php'); ?>
</body>
</html>