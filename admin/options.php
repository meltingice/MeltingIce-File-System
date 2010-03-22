<?php
session_start();
include_once('../includes/dbconnect.php');
include_once('../includes/security.php');
include_once('../includes/utilities.php');
include_once('info.php');

if(!isset($_SESSION['adminID'])){ exit; }

if($_POST['resync']){ resyncAllData(); }
if($_POST['option']=='newQuota'){ getNewQuotaForm(); }
if($_POST['newQuota']){ setDefaultQuota($_POST['newQuota']); }
if($_POST['publist']){ togglePublicListing(); }
if($_POST['newuseremail']){ toggleNewUserEmail(); }
if($_POST['userreg']){ toggleUserReg(); }
if($_POST['userlogin']){ toggleUserLogin(); }

function getDefaultQuota()
{
	$query = "SELECT defaultQuota FROM adminoptions LIMIT 1";
	$result = mysql_query($query);
	return reset(mysql_fetch_row($result));
}

function setDefaultQuota($quota)
{
	$sizeType = strtoupper(substr($quota,-2));
	$quota = substr($quota,0,-2);
	
	/* Quota is stored in bytes, so lets convert */
	if($sizeType=="KB")
	{
		$quota = $quota*1024;
	}
	if($sizeType=="MB")
	{
		$quota = $quota*1024*1024;
	}
	if($sizeType=="GB")
	{
		$quota = $quota*1024*1024*1024;
	}
	
	$quota = secureContent($quota);
	$query = "UPDATE adminoptions SET defaultQuota='$quota' LIMIT 1";
	$result = mysql_query($query);
	if($result){ echo "1"; }
	else{ echo "0"; }
}

function getNewQuotaForm()
{
?>
	<p>The current default quota is: <?php echo getPrettyFilesize(getDefaultQuota()); ?></p>
	<p>Enter new quota below, for example:</p>
	<p>100MB or 1.5GB or 512KB</p>
	<form action="javascript:setDefaultQuota()">
		<p><input type="text" id="newDefaultQuota" /></p>
		<p><input type="button" value="Submit" onclick="setDefaultQuota()" /></p>
	</form>
<?php	
}

function togglePublicListing()
{
	$query = "UPDATE adminoptions SET publicListing=(SELECT CASE publicListing WHEN 'enabled' THEN 'disabled' ELSE 'enabled' END) LIMIT 1";
	$result = mysql_query($query);
	if($result){ echo "1"; }
	else{ echo "0"; }
}

function toggleNewUserEmail()
{
	$query = "UPDATE admin SET emailOnReg=(SELECT CASE emailOnReg WHEN 'true' THEN 'false' ELSE 'true' END) WHERE userID=".$_SESSION['adminID']." LIMIT 1";
	$result = mysql_query($query);
	if($result){ echo "1"; }
	else{ echo "0"; }
}

function toggleUserReg()
{
	$query = "UPDATE adminoptions SET userReg=(SELECT CASE userReg WHEN 'enabled' THEN 'disabled' ELSE 'enabled' END) LIMIT 1";
	$result = mysql_query($query);
	if($result){ echo "1"; }
	else{ echo "0"; }
}

function toggleUserLogin()
{
	$query = "UPDATE adminoptions SET userLogin=(SELECT CASE userLogin WHEN 'enabled' THEN 'disabled' ELSE 'enabled' END) LIMIT 1";
	$result = mysql_query($query);
	if($result){ echo "1"; }
	else{ echo "0"; }
}

/*
*	resyncAllData() - completely resynchonizes all of MFS2
*	Cleans up both database and hard drive
*/
function resyncAllData()
{
	$dummypath = realpath('../user/index.php');
	$usersDir = reset(explode('index.php',$dummypath));
    
	/* Step 1: get all users folders from the hard drive */
	$dh = opendir($usersDir);
	while ($folder = readdir($dh))
	{
		if($folder!='.'&&$folder!='..'&&is_dir($usersDir.$folder))
		{
			$userFolders[] = $folder;
		}
	}
	closedir($dh);
	
	/* Step 2: Check physical user folders against MySQL database for non-existing users */
	echo "<h4>Checking physical user folders against database and vice versa.</h4>\n";
	if(is_array($userFolders))
	{
		/* Step 2a: Scan directory of users and check against database */
		$count=0;
		foreach($userFolders as $user)
		{
			$user = secureContent($user);
			$query = "SELECT COUNT(*) FROM users WHERE username='$user' LIMIT 1";
			$result = mysql_query($query);
			if(!reset(mysql_fetch_row($result)))
			{
				echo "<p>$user does not exist in db, deleting folder and files.</p>\n";
				$path=$usersDir.$user;

				advancedRmdir($path);
				
				$userFolders[$count] = false;
			}
			$count++;
		}
		$userFolders = array_filter($userFolders);
		
		/* Step 2b: Get userlist from database and check against users directory */
		$query = "SELECT username FROM users";
		$result = mysql_query($query);

		if($result)
		{
			while(list($username)=mysql_fetch_row($result))
			{
				if(!in_array($username,$userFolders))
				{
					echo "<p>$username is missing a data folder, deleting from db.</p>\n";
					$username = secureContent($username);
					$userID = getUserID($username);
					/* Take care of quota table first */
					$query1 = "DELETE FROM quotas WHERE userID='$userID' LIMIT 1";
					$delResult1 = mysql_query($query1);
					/* Next, the users table */
					$query = "DELETE FROM users WHERE username='$username' LIMIT 1";
					$delResult = mysql_query($query);
					if(!$delResult){ echo "<p>Error deleting $username from db</p>\n"; }
				}
			}
		}
		
		echo "<p>Folder check completed.</p>\n";
	}
	
	/* Step 3: Check all user files for database consistency */
	if(is_array($userFolders))
	{
		echo "<h4>File check against db and vice versa.</h4>\n";
		foreach($userFolders as $user)
		{
			/* Step 3a: Scan user folder and check files against database */
			$userID = getUserID($user);
			$dh = opendir($usersDir.$user);
			while($file = readdir($dh))
			{
				if($file!='.'&&$file!='..'&&$file!='index.php'&&is_file($usersDir.$user.'/'.$file))
				{
					/* Gather some info about the file */
					$size = filesize($usersDir.$user."/".$file);
					$file = secureContent($file);
					
					$query = "SELECT filesize FROM files WHERE ownerID='$userID' AND filename='$file' LIMIT 1";
					$result = mysql_query($query);
					if(mysql_num_rows($result)>0)
					{
						$dbSize = reset(mysql_fetch_row($result));
						if($dbSize!=$size)
						{
							/* Filesize mismatch: update database */
							$update = "UPDATE files SET filesize=$dbSize WHERE ownerID='$userID' AND filename='$file'";
							$result = mysql_query($update);
							if($result){ echo "<p>$user/$file filesize updated.</p>\n"; }
							else{ echo "<p>Error updating $user/$file.</p>\n"; }
						}
					}
					elseif(mysql_num_rows($result)==0)
					{
						unlink($usersDir.$user."/".$file);
						echo "<p>$user/$file missing from db, deleted from disk.</p>\n";
					}
					else
					{
						/* Do nothing, yay! */
					}
				}
			}
			
			/* Step 3b: Get filelist from database and check against users folder */
			$query = "SELECT fileID,filename FROM files WHERE ownerID='$userID'";
			$result = mysql_query($query);
			while(list($fileID,$filename)=mysql_fetch_row($result))
			{
				if(!file_exists($usersDir.$user."/".$filename))
				{
					echo "<p>$user/$filename missing from disk, removing db entry.</p>\n";
					$query = "DELETE FROM files WHERE fileID='$fileID' LIMIT 1";
					$delresult = mysql_query($query);
					if(!$delresult){ echo "<p>Error deleting $user/$filename from db.</p>\n"; }
				}
			}
		}
	}
	echo "<p>File/database system check complete.</p>\n";
	
	/* Step 4: Check quota table against users table */
	echo "<h4>Checking quota table against users table</h4>\n";
	$query = "SELECT userID FROM quotas";
	$result = mysql_query($query);
	while(list($userID)=mysql_fetch_row($result))
	{
		$check = "SELECT COUNT(*) FROM users WHERE userID='$userID' LIMIT 1";
		$checkResult = mysql_query($check);
		if(!reset(mysql_fetch_row($checkResult)))
		{
			/* User doesn't exist, remove from quota table */
			echo "<p>User ID #$userID missing from users, deleted from quotas.</p>\n";
			$delQuery = "DELETE FROM quotas WHERE userID='$userID' LIMIT 1";
			$delResult = mysql_query($delQuery);
			if(!$delResult){ echo "<p>Error deleting user #$userID from db</p>\n"; }
		}
	}
	echo "<p>Finished checking quota table</p>\n";
	
	/* Step 5: Check total user disk space against MySQL quota table */
	echo "<h4>Checking user quotas.</h4>\n";
	if(is_array($userFolders))
	{
		$count=0;
		foreach($userFolders as $user)
		{
			/* First, get the amount of space being used from the disk */
			$space = 0;
			$dh = opendir($usersDir.$user);
			while($file = readdir($dh))
			{
				if($file!='.'&&$file!='..'&&$file!='index.php'&&is_file($usersDir.$user.'/'.$file))
				{
					$space = $space + filesize($usersDir.$user."/".$file);
				}
			}
			/* Next, update the database */
			$userID = getUserID($user);
			$query = "UPDATE quotas SET spaceUsed='$space' WHERE userID='$userID' LIMIT 1";
			$result = mysql_query($query);
			if($result){ $count++; }
			else{ echo "<p>Quota update failed for $user.</p>\n"; }
		}
		echo "<p>Quota check completed on $count users.</p>\n";
	}
	
	/* Step 6: Check to make sure 'thumbs' folder is present */
	echo "<h4>Checking for thumbnail folders</h4>\n";
	if(is_array($userFolders))
	{
		foreach($userFolders as $user)
		{
			if(!is_dir($usersDir.$user."/thumbs"))
			{
				mkdir($usersDir.$user."/thumbs");
				echo "<p>Thumbnail directory for $user missing, created.</p>\n";
			}
		}
	}
	echo "<p>Thumbnail folder check complete.</p>\n";
	
	/* Holy crap that was a lot of work */
	echo "<h4>MFS2 system resync complete!</h4>\n";
}

?>