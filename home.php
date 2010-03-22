<?php
session_start();

include_once('includes/init.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>MeltingIce File System 2</title>
	<link rel="stylesheet" href="css/template.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="css/homestyle.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="css/modalbox.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<script src="js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery-ui.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/swfupload.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/swfupload.queue.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/backend.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/modalbox.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/home.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript">
		var myTimer;
		// Create the SWFUpload Object
		var swfu;
		function initSWFU(){
			swfu = new SWFUpload({
				upload_url : "../filemgmt.php",
				flash_url : "includes/swfupload/swfupload_f9.swf",
				post_params: {"PHPSESSID" : "<?php echo session_id(); ?>","fileupload" : "true","fileperms" : 'private'	, "swfupload" : "true","folder":activeFolder},
				file_size_limit : "0",
				upload_start_handler : uploadStart,
				upload_success_handler : uploadSuccessEventHandler,
				file_dialog_complete_handler : fileDialogComplete,
				upload_progress_handler : uploadProgressHandler,
				queue_complete_handler : queueComplete
			});
		}
	</script>
</head>
<body onload="initSWFU(); initStyles(); <?php echo "setSpacebarAmt(".getSpaceUsed().",".getSpaceAvail().");"; ?>">
<div id="modalcontent" style="display:none"></div>
<div class="folderinfo" style="display:none"></div>
<div id="header"><p>MeltingIce File System 2</p></div>
<div id="content-container">
	<div id="content-header">
		<div id="left-content-header">
			<?php if($_SESSION['username']==$_SESSION['activeUser']): ?>
			<a href="#" onclick="swfu.selectFiles()" style="float: left;"><img src="img/upload.png" alt="Upload" /></a>
			<a href="javascript:crossloadDialog()" style="float: left;"><img src="img/crossload.png" alt="Crossload" /></a>
			<div id="upload-progress-container" style="display:none">
				<div id="upload-progress"></div>
			</div>
			<div id="upload-data" style="display:none">
				<p><span id="upload-amt"></span> <span id="filequeue"></span></p>
			</div>
			<?php endif; ?>
			<div id="ajaxindicator" style="display:none"><img src="img/ajax-loader.gif" alt="loading" /><p>Loading...</p></div>
		</div>
		<div id="right-content-header">
			<?php if(isset($_SESSION['username'])): ?>
			<a href="javascript:myAccount()"><img src="img/my-account.png" alt="My Account" /></a><a href="includes/logout.php"><img src="img/logout.png" alt="Logout" /></a>
			<?php endif; ?>
		</div>
	</div>
	<div id="content">
		<div id="left-content">
			<ul id="folders">
				<li id="home-folder" class="active-folder" onclick="loadHome()"><img src="img/icons/house.png" alt="home" />Home</li>
					<?php getUsersFolders(); ?>
			</ul>
			<?php if($_SESSION['username']==$_SESSION['activeUser']): ?>
			<div id="add-folder-container">
				<div id="add-folder" onclick="toggleFolderForm()">
					<p>+</p>
				</div>
				<div id="create-folder" style="display:none">
					<form action="javascript:newFolder()">
						<p><input type="text" id="newfoldername" name="newfoldername" style="width: 100px" />
						<input type="submit" value="Go" /></p>
					</form>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<div id="middle-content">
			<?php loadContent('home'); ?>
		</div>
		<div id="right-content">
			<div id="news">
			
			</div>
			<div id="fileinfo" style="display:none">
				<h4>File Information</h4>
				<p style="text-align: center">Select a file to view info</p>
			</div>
		</div>
	</div>
</div>
<?php include('footer.php'); ?>
</body>
</html>