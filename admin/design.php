<?php
session_start();
if(!isset($_SESSION['adminID'])){ echo "<div id=\"welcome\"><h4>Please log in.</h4></div>"; exit; }
include_once("../includes/dbconnect.php");
include_once("../includes/security.php");
include_once('info.php');

if($_POST['content']&&!$_POST['username']){ loadContent($_POST['content']); }
if($_POST['username']){ loadContent($_POST['content'],$_POST['username']); }

function loadContent($type,$username=null)
{
	global $error;
	
	if($type=='info'): ?>
		<div id="welcome"><h4>System Information</h4></div>
		
		<div class="content-item" id="msgbox" style="display:none">
		<h4>System Message:</h4>
			<p><?php if($error){ echo $error; } ?></p>
		</div>
			
		<div class="content-item">
				<h4>User Information</h4>
				<ul>
					<li>Number of Users: <span style="color:#000000"><?php echo getNumUsers(); ?></span></li>
					<li>Number of Active Users: <span style="color:#000000"><?php echo numActiveAccts(); ?></span></li>
					<li>Last Registered User: <span style="color:#000000"><?php echo getLastRegUser(); ?></span></li>
					<li>Last Users Logged In: <span style="color:#000000"><?php
						$users = getLastUserLogin(3);
						for($i=0;$i<count($users);$i++)
						{
							echo $users[$i];
							if($i!=(count($users)-1)){ echo ", "; }
						}
						?></span>
					</li>
					<li>User with Most Data: <span style="color:#000000"><?php echo getUserMostData(); ?></span></li>
				</ul>
		</div>
		
		<div class="content-item">
			<h4>Disk Space Information</h4>
			<ul>
				<li>Total Space Used: <span style="color:#000000"><?php echo getSpaceUsed(); ?></span></li>
				<li>Total Space Available: <span style="color:#000000"><?php echo getSpaceAvail(); ?></span></li>
				<li>Average Space Usage (including inactive users): <span style="color:#000000"><?php echo getAvgSpace(true); ?></span></li>
				<li>Average Space Usage (without inactive users): <span style="color:#000000"><?php echo getAvgSpace(false); ?></span></li>
			</ul>
		</div>
	<?php endif;
	if($type=='users'): ?>
		<div id="navbar">
			<div id="navbar-left">
				
			</div>
			<div id="navbar-right">
				<?php $pages = getPagination(16); ?>
				<a href="javascript:changeUserPage('last')"><img src="../img/icons/resultset_last.png" alt="last" title="Last Page" /></a>
				<a href="javascript:changeUserPage('next')"><img src="../img/icons/resultset_next.png" alt="next" title="Next Page" /></a>
				<a href="javascript:changeUserPage('previous')"><img src="../img/icons/resultset_previous.png" alt="previous" title="Previous Page" /></a>
				<a href="javascript:changeUserPage('first')"><img src="../img/icons/resultset_first.png" alt="first" title="First Page" /></a>
				<p>Page <span id="cur_page">1</span> of <span id="tot_page"><?php echo $pages; ?></span></p>
			</div>
		</div>
		<div id="userlist">
			<ul id="users">
				<?php outputUserList(1); ?>
			</ul>
		<div>
	<?php endif;
	if($type=='userinfo'): ?>
		<?php if(userExists($username)): ?>
			<div id="welcome"><h4>User Information for <span style="color: #515558;"><?php echo $username; ?></span></h4></div>
			
			<div class="content-item" id="msgbox" style="display:none">
			<h4>System Message:</h4>
				<p><?php if($error){ echo $error; } ?></p>
			</div>
				
			<div class="content-item">
					<h4>User Information</h4>
					<ul>
						<li>Joined On: <span style="color: #000000"><?php echo getUserJoinedOn($username); ?></span></li>
						<li>Email Address: <span style="color: #000000"><?php echo getUserEmailAddr($username); ?></span></li>
						<li>User Last Logged In: <span style="color: #000000"><?php echo getUserLastLogin($username); ?></span></li>
						<li>Last Login IP Address: <span style="color: #000000"><?php echo getUserLastIP($username); ?></span></li>
					</ul>
			</div>
			
			<div class="content-item">
				<h4>User Data Information</h4>
				<ul>
					<li>Number of Files: <span style="color: #000000"><?php echo getUserNumFiles($username); ?></span></li>
					<ul style="margin-top: 1px">
						<li>Private: <span style="color: #000000"><?php echo getUserNumFiles($username,'private'); ?></span></li>
						<li>Public: <span style="color: #000000"><?php echo getUserNumFiles($username,'public'); ?></span></li>
					</ul>
					<li>Number of Folders: <span style="color: #000000"><?php echo getUserNumFolders($username); ?></span></li>
					<ul style="margin-top: 1px">
						<li>Private: <span style="color: #000000"><?php echo getUserNumFolders($username,'private'); ?></span></li>
						<li>Public: <span style="color: #000000"><?php echo getUserNumFolders($username,'public'); ?></span></li>
					</ul>
					<li>Space in Use: <span style="color: #000000"><?php echo getUserSpaceUsed($username); ?></span></li>
					<li>Space Quota: <span style="color: #000000"><?php echo getUserSpaceQuota($username); ?></span></li>
					<li>Largest File Size: <span style="color: #000000"><?php echo getLargestFileSize($username); ?></span></li>
					<li>Number of Images: <span style="color: #000000"><?php echo getNumberOfFiletype($username,'images'); ?></span></li>
					<li>Number of Audio Files: <span style="color: #000000"><?php echo getNumberOfFiletype($username,'audio'); ?></span></li>
					<li>Number of Videos: <span style="color: #000000"><?php echo getNumberOfFiletype($username,'video'); ?></span></li>
				</ul>
			</div>
		<?php else: ?>
			<div id="welcome"><h4>User <span style="color: #515558;"><?php echo $username; ?></span> Does Not Exist</h4></div>
		<?php endif; ?>
	<?php endif;
	if($type=='banlist'): ?>
		<div id="welcome"><h4>Banlist for <span style="color: #515558">MeltingIce File System</span></h4></div>
		
		<div class="content-item" id="msgbox" style="display:none">
			<h4>System Message:</h4>
			<p><?php if($error){ echo $error; } ?></p>
		</div>
		
		<div class="content-item">
			<h4>Banned Users</h4>
			<ul>
				<?php
					$users = getBannedUsers();
					if(is_array($users))
					{
	    				foreach($users as $user)
	    				{
	    					echo "<li id=\"ban_".$user['banID']."\"><a href=\"javascript:getUserInfo('".$user['username']."')\">".$user['username']."</a> (Since: ".$user['bandate'].", IP: ".$user['ipaddr'].")</li>";
	    				}
					}
					else
					{
						echo "<li id=\"ban_-1\">No banned users!</li>";
					}
				?>
			</ul>
		</div>
		
		<div class="content-item">
			<h4>Banned IP Addresses</h4>
			<ul>
				<?php
					$ips = getBannedIPs();
					if(is_array($ips))
					{
	    				foreach($ips as $ip)
	    				{
	    					echo "<li id=\"ban_".$ip['banID']."\" class=\"bannedIP\"><a href=\"javascript:banIPToggle('".$ip['addr']."')\">".$ip['addr']."</a> <span class=\"ip_info\">(Since: ".$ip['bandate'].")</span></li>";
	    				}
					}
					else
					{
						echo "<li id=\"ban_-1\">No banned IP's!</li>";
					}
				?>
			</ul>
		</div>
		<script>
			$('.bannedIP').mouseover(function(){
				var banID = '#'+$(this).attr('id');
				$(banID+' .ip_info').hide();
				$(this).append(' <span class="removeBan">(Click to Remove Ban)</span>');
			});
			$('.bannedIP').mouseout(function(){
				var banID = '#'+$(this).attr('id');
				$('.removeBan').remove();
				$(banID+' .ip_info').show();
			});
		</script>
	<?php endif;
	if($type=='options'): ?>
		<div id="welcome"><h4>Options for <span style="color: #515558">MeltingIce File System</span></h4></div>
		
		<div class="content-item" id="msgbox" style="display:none">
			<h4>System Message:</h4>
			<p><?php if($error){ echo $error; } ?></p>
		</div>
		
		<div class="content-item">
			<h4>General Options</h4>
			<ul>
				<li onmouseover="showClickChange(this)" onmouseout="hideClickChange()">Public File Listing: <span class="click-change" id="option_publicfiles" onclick="executeOption('publist')"><?php echo getPublicFileStatus(); ?></span></li>
				<li onmouseover="showClickChange(this)" onmouseout="hideClickChange()">Email on New User Registration: <span class="click-change" id="option_email" onclick="executeOption('newuseremail')"><?php echo getEmailOnRegStatus(); ?></span></li>
				<li><a href="javascript:executeOption('newQuota')">Set New User Filespace Quota</a></li>
			</ul>
		</div>
		
		<div class="content-item">
			<h4>System Maintenance</h4>
			<ul>
				<li id="option_resync"><a href="javascript:executeOption('resync')">Resynchronize Entire System</a></li>
				<li onmouseover="showClickChange(this)" onmouseout="hideClickChange()">User Registration: <span class="click-change" id="option_reg" onclick="executeOption('userreg')"><?php echo getUserRegStatus(); ?></span></li>
				<li onmouseover="showClickChange(this)" onmouseout="hideClickChange()">User Logins: <span class="click-change" id="option_userlogin" onclick="executeOption('userlogin')"><?php echo getUserLoginStatus(); ?></span></li>
				<li>Override All Filespace Quotas</li>
			</ul>
		</div>
		
		<div class="content-item">
			<h4>System Reset</h4>
			<ul>
				<li>Delete All Files</li>
				<li>Delete All Users</li>
				<li>Delete All Users and Files</li>
			</ul>
		</div>
	<?php endif;
}
?>