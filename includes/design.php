<?php
/*
*	design.php
*	A lot of code relating to the visual content of MFS2
*	Code copyright Ryan "MeltingIce" LeFevre
*/
session_start();
include_once("dbconnect.php");
include_once("security.php");
include_once("filemgmt.php");
include_once("foldermgmt.php");

if($_POST['showFolder']){ loadContent('folder',$_POST['showFolder']); }
if($_POST['loadHome']){ loadContent('home'); }
if($_POST['loadPage']){ loadContent($_POST['loadPage']); }

function loadContent($type,$ID=null,$user=null)
{
	global $error;
	if($type=='login'):
	?>
		<div id="welcome"><h4>Welcome to <span style="color: #515558;">MeltingIce File System</span></h4></div>
		
		<div class="content-item" id="msgbox" style="display:none">
		<h4>System Message:</h4>
			<p><?php if($error){ echo $error; } ?></p>
		</div>
		
		<div class="content-item">
			<h4>Login:</h4>
			<form action="includes/login.php" enctype="multipart/form-data" method="post" id="loginform" name="loginform">
				<p><input type="text" name="username" value="username" class="largeform" onfocus="onTxt(this)" onblur="offTxt(this)" /></p>
				<p><input type="text" value="password" name="password" class="largeform" onfocus="onTxt(this)" onblur="offTxt(this)" /></p>
				<p><input type="submit" value="Login" name="submitbutton" class="submitbutton" /></p>
			</form>
		</div>
	<?php endif;
	if($type=='register'):
	?>
		<div id="welcome"><h4>Register for <span style="color: #515558;">MeltingIce File System</span></h4></div>
		
		<div class="content-item" id="msgbox" style="display:none">
		<h4>System Message:</h4>
			<p><?php if($error){ echo $error; } ?></p>
		</div>

		<div class="content-item">
			<h4>Required Information:</h4>
			<form action="includes/register.php" enctype="multipart/form-data" method="post" id="registerform" name="registerform">
				<p><input type="text" name="username" value="username" class="largeform" onfocus="onTxt(this)" onblur="offTxt(this)" /></p>
				<p><input type="text" name="email" value="email" class="largeform" onfocus="onTxt(this)" onblur="offTxt(this)" /></p>
				<p><input type="text" name="emailagain" value="email again" class="largeform" onfocus="onTxt(this)" onblur="offTxt(this)" /></p>
				<p><input type="text" value="password" name="password" class="largeform" onfocus="onTxt(this)" onblur="offTxt(this)" /></p>
				<?php
					/* Lets see if admin wants to use reCaptcha */
					$query = "SELECT reCaptcha_enabled,reCaptcha_public,reCaptcha_private FROM adminoptions WHERE optionID='1'";
					$result = mysql_query($query);
					if($result)
					{
						while(list($reCaptcha_enabled,$reCaptcha_public,$reCaptcha_private)=mysql_fetch_row($result))
						{
							if($reCaptcha_enabled=='true')
							{
								require_once('recaptchalib.php');
								?>
								<script>
									Recaptcha.create(<?php echo $reCaptcha_public; ?>),
										"recaptcha_div", {
   										theme: "white",
   										callback: Recaptcha.focus_response_field
									});
								</script>
								<?php
							}
						}
					}
				?>
				<p><input type="submit" value="REGISTER" name="submitbutton" class="submitbutton" /></p>
			</form>
		</div>
	<?php endif;
	if($type=='folder'):
	?>
		<div id="navbar">
			<div id="navbar-left">
				<div id="navbar-sort"></div>
				<div id="navbar-name" onclick="changeSort('<?php echo $_SESSION['activeUser']; ?>','name')"><img src="img/navbar-name.png" alt="name" /></div>
				<div id="navbar-size" onclick="changeSort('<?php echo $_SESSION['activeUser']; ?>','size')"><img src="img/navbar-size.png" alt="size" /></div>
				<div id="navbar-date" onclick="changeSort('<?php echo $_SESSION['activeUser']; ?>','update')"><img src="img/navbar-date.png" alt="date" /></div>
			</div>
			<div id="navbar-right">
				<?php if($_SESSION['username']==$_SESSION['activeUser']): ?>
				<a href="javascript:changeFileAction('delete')" title="Delete File Mode"><img src="img/icons/delete.png" alt="delete" /></a>
				<a href="javascript:changeFileAction('rename')" title="Rename File Mode"><img src="img/icons/link_edit.png" alt="rename" /></a>
				<a href="javascript:changeFileAction('perms')" title="Permission Change Mode"><img src="img/icons/group_edit.png" alt="perms" /></a>
				<?php endif; ?>
			</div>
		</div>
		<div id="filelist">
			<ul id="files">
				<?php getUserFiles($_SESSION['activeUser'],'all', 'filename', "ASC", false, $ID); ?>
			</ul>
		<div>
	<?php
	endif;
	if($type=='home'):
	?>
		<script>
			setSpacebarAmt(<?php getSpaceUsed(true,false); ?>,<?php getSpaceAvail(true,false); ?>);
		</script>
		<?php if($_SESSION['username']==$_SESSION['activeUser']): ?>
			<div id="welcome"><h4>Welcome back, <span style="color: #515558;"><?php echo $_SESSION['username']; ?></span></h4></div>
		<?php else: ?>
			<div id="welcome"><h4><span style="color: #515558;"><?php echo $_SESSION['activeUser']; ?>'s</span> public file listing</h4></div>
		<?php endif; ?>
			<div class="content-item">
				<h4>Latest uploaded <?php if($_SESSION['username']!=$_SESSION['activeUser']){ echo "public"; } ?> files:</h4>
				<ul>
					<?php getLastUploadedFiles(3); ?>
				</ul>
			</div>
			<div class="content-item">
				<h4>Storage Information:</h4>
				<div id="spacebar-container">
					<div id="spacebar"></div>
				</div>
				<p style="text-align:center;">Files: <span style="color:#000000"><?php echo getUserNumFiles(); ?></span> | Space Used: <span style="color:#000000"><?php getSpaceUsed(true,true); ?></span> | Space Free: <span style="color:#000000"><?php echo getPrettyFilesize(getSpaceAvail()-getSpaceUsed()); ?></span></p>
			</div>
	<?php
	endif;
}
?>