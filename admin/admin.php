<?php
session_start();
if(!isset($_SESSION['adminID'])){ header('Location: index.php?login=needed'); exit; }
include_once('info.php');
include_once('design.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>MeltingIce File System 2 - Admin Panel</title>
	<link rel="stylesheet" href="../css/template.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<link rel="stylesheet" href="../css/homestyle.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<link rel="stylesheet" href="../css/adminstyle.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<link rel="stylesheet" href="../css/modalbox.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<link rel="stylesheet" href="../css/jquery.autocomplete.css" type="text/css" media="screen" title="no title" charset="utf-8" />
	<script src="../js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="../js/jquery-ui.js" type="text/javascript" charset="utf-8"></script>
	<script src="../js/backend.js" type="text/javascript" charset="utf-8"></script>
	<script src="../js/admin.js" type="text/javascript" charset="utf-8"></script>
	<script src="../js/jquery.autocomplete.js" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<div id="modalcontent" style="display:none"></div>
<div id="debug-window" style="display:none">
	<div id="debug-header"><h4>MFS2 Debug</h4><p><a href="javascript:closeDebug()">Close [x]</a></p></div>
	<div id="debug-content"></div>
</div>
<div id="header"><p>MeltingIce File System 2 - Admin Panel</p></div>
<div id="content-container">
	<div id="content-header">
		<div id="left-content-header">
			<div id="ajaxindicator" style="display:none"><img src="../img/ajax-loader.gif" alt="loading" /><p>Loading...</p></div>
		</div>
		<div id="right-content-header">
			<a href="../includes/logout.php"><img src="../img/logout.png" alt="Logout" /></a>
		</div>
	</div>
	<div id="content">
		<div id="left-content">
			<ul id="pages">
				<li id="info_link" class="page active-page" onclick="loadPage('info')"><img src="../img/icons/information.png" alt="info" />Information</li>
				<li id="users_link" class="page" onclick="loadPage('users')"><img src="../img/icons/group.png" alt="users" />Users</li>
				<li id="banlist_link" class="page" onclick="loadPage('banlist')"><img src="../img/icons/stop.png" alt="banlist" />Banlist</li>
				<li id="options_link" class="page" onclick="loadPage('options')"><img src="../img/icons/cog.png" alt="options" />Options</li>
			</ul>
		</div>
		<div id="middle-content">
			<?php loadContent('info'); ?>
		</div>
		<div id="right-content">
			<div id="search">
				<h4>User Search</h4>
				<form id="usersearch" action="javascript:getUserInfo($('#search-username').attr('value'))">
					<p><input type="text" id="search-username" />
					<input type="button" value="Go" onclick="getUserInfo($('#search-username').attr('value'))" /></p>
				</form>
			</div>
			<div id="user-actions" style="display:none">
				<h4>User Actions</h4>
				<ul>
					<li id="user_admin" onclick="userOptions('admin')">Give Admin Access</li>
					<li id="user_ban" onclick="userOptions('ban')">Ban User</li>
					<li id="user_delfiles" onmousedown="holdActivate('delfiles')" onmouseup="cancelHoldActivate('delfiles')">Delete User Files</li>
					<li id="user_deluser" onmousedown="holdActivate('deluser')" onmouseup="cancelHoldActivate('deluser')">Delete User</li>
				</ul>
			</div>
			<div id="ip-ban-form" style="display:none">
				<h4>Ban IP Address</h4>
				<form id="banip" action="javascript:banIPToggle($('#banip-input').attr('value'))">
					<p><input type="text" id="banip-input" />
					<input type="button" value="Go" onclick="banIPToggle($('#banip-input').attr('value'))" /></p>
				</form>
			</div>
		</div>
	</div>
</div>
<?php include('../footer.php'); ?>
</body>
</html>