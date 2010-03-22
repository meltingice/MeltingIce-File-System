<?php
include_once('../includes/errormsg.php');
$error = getErrorMsg();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>MeltingIce File System 2</title>
	<link rel="stylesheet" href="../css/template.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<link rel="stylesheet" href="../css/indexstyle.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<script src="../js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="../js/jquery-ui.js" type="text/javascript" charset="utf-8"></script>
	<script src="../js/backend.js" type="text/javascript" charset="utf-8"></script>
	<script src="../js/index.js" type="text/javascript" charset="utf-8"></script>
</head>
<body onload="setInitialPage();<?php if($error){ echo "displayErrorMsg();"; } ?>">
<div id="header"><p>MeltingIce File System 2 - Admin Panel</p></div>
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
				<li id="login_link" class="page active-page"><img src="../img/icons/key.png" alt="login" />Login</li>
				<li id="return_link" class="page" onclick="window.location='../index.php'"><img src="../img/icons/arrow_left.png" alt="home" />Return to MFS</li>
			</ul>
		</div>
		<div id="middle-content">
			<div id="welcome"><h4>MeltingIce File System <span style="color: #515558;">admin panel</span></h4></div>
		
			<div class="content-item" id="msgbox" style="display:none">
			<h4>System Message:</h4>
				<p><?php if($error){ echo $error; } ?></p>
			</div>
			
			<div class="content-item">
				<h4>Login:</h4>
				<form action="login.php" enctype="multipart/form-data" method="post" id="loginform" name="loginform">
					<p><input type="text" name="username" value="username" class="largeform" onfocus="onTxt(this)" onblur="offTxt(this)" /></p>
					<p><input type="text" value="password" name="password" class="largeform" onfocus="onTxt(this)" onblur="offTxt(this)" /></p>
					<p><input type="submit" value="Login" name="submitbutton" class="submitbutton" /></p>
				</form>
			</div>
		</div>
		<div id="right-content">
			<div id="news">
			
			</div>
		</div>
	</div>
</div>
<?php include('../footer.php'); ?>
</body>
</html>