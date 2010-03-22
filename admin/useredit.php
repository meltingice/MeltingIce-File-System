<?php
session_start();
include_once('../includes/dbconnect.php');
include_once('../includes/security.php');
include_once('../includes/utilities.php');
include_once('info.php');

if(!isset($_SESSION['adminID'])){ exit; }

if($_POST['option']=='admin'){ toggleAdminPriv($_POST['user']); }
if($_POST['option']=='ban'){ toggleUserBan($_POST['user']); }
if($_POST['option']=='banip'){ toggleIPBan($_POST['ip']); }
if($_POST['option']=='delfiles'){ deleteUserFiles($_POST['user']); }
if($_POST['option']=='deluser'){ deleteUser($_POST['user']); }

function toggleAdminPriv($user)
{
	$user = secureContent($user);
	$userID = getUserID($user);
	
	/* Check to see if user is admin first */
	$query = "SELECT COUNT(*) FROM admin WHERE userID='$userID'";
	$result = mysql_query($query);
	if(reset(mysql_fetch_row($result)))
	{
		/* Revoke admin privileges */
		$query2 = "DELETE FROM admin WHERE userID='$userID' LIMIT 1";
		$result2 = mysql_query($query2);
		echo "0";
	}
	else
	{
		/* Give admin access */
		$query2 = "INSERT INTO admin (userID,adminType) VALUES ('$userID','full')";
		$result2 = mysql_query($query2);
		echo "1";
	}
}

function toggleUserBan($user)
{
	$user = secureContent($user);
	
	/* Check to see if user is banned first */
	$query = "SELECT COUNT(*) FROM banlist WHERE username='$user'";
	$result = mysql_query($query);
	if(reset(mysql_fetch_row($result)))
	{
		/* Unban user */
		$query2 = "DELETE FROM banlist WHERE username='$user' LIMIT 1";
		$result2 = mysql_query($query2);
		echo "0";
	}
	else
	{
		/* Ban user */
		$IPquery = "SELECT ipaddress FROM users WHERE username='$user' LIMIT 1";
		$IPresult = mysql_query($IPquery);
		$userIP = reset(mysql_fetch_row($IPresult));
		
		$userID = getUserID($user);
		$query2 = "INSERT INTO banlist (userID,username,bandate,ipaddress) VALUES ('$userID','$user','".time()."','$userIP')";
		$result2 = mysql_query($query2);
		echo "1";
	}
}

function toggleIPBan($ip)
{
	$ip = secureContent($ip);
	/* Check to see if IP is already banned */
	$query1 = "SELECT COUNT(*) FROM banlist WHERE username='' AND ipaddress='$ip'";
	$result1 = mysql_query($query1);
	if(reset(mysql_fetch_row($result1)))
	{
		/* Unban IP */
		$query2 = "DELETE FROM banlist WHERE username='' AND ipaddress='$ip'";
		$result2 = mysql_query($query2);
		echo "0";
	}
	else
	{
		/* Ban IP */
		$query2 = "INSERT INTO banlist (userID,username,bandate,ipaddress) VALUES ('','','".time()."','$ip')";
		$result2 = mysql_query($query2);
		echo "1";
	}
}

function deleteUserFiles($username)
{
	$userDir = getUserDir($username);

	/* First empty base user directory */
	$dh = opendir($userDir);
	while($file = readdir($dh))
	{
		if($file!='.'&&$file!='..'&&$file!='index.php'&&is_file($userDir."/".$file))
		{
			unlink($userDir."/".$file);
		}
	}
	closedir($dh);
	
	/* Next empty thumbs folder */
	$dh = opendir($userDir."/thumbs");
	while($file = readdir($dh))
	{
		if($file!='.'&&$file!='..')
		{
			unlink($userDir."/thumbs/".$file);
		}	
	}
	closedir($dh);
	
	/* Update files table */
	$userID = getUserID($username);
	$query = "DELETE FROM files WHERE ownerID='$userID'";
	$result = mysql_query($query);
	
	/* Update quota table */
	$query = "UPDATE quotas SET spaceUsed='0' WHERE userID='$userID' LIMIT 1";
	$result = mysql_query($query);
	if($result){ echo "1"; }
	else{ echo "0"; }
}

function deleteUser($username)
{
	/* First, delete their files */
	deleteUserFiles($username);
	
	/* Next, remove their user folder */
	$userDir = getUserDir($username);
	unlink($userDir.'/index.php');
	rmdir($userDir.'/thumbs');
	rmdir($userDir);
	
	/* Now, delete them from the database */
	$userID = getUserID($username);
	$query = "DELETE FROM users WHERE userID='$userID' LIMIT 1";
	$result = mysql_query($query);
	$query2 = "DELETE FROM quotas WHERE userID='$userID' LIMIT 1";
	$result2 = mysql_query($query2);
	$query3 = "DELETE FROM admin WHERE userID='$userID' LIMIT 1";
	$result3 = mysql_query($query3);
	$query4 = "DELETE FROM folders WHERE ownerID='$userID' LIMIT 1";
	$result4 = mysql_query($query4);
	
	echo "1";
}
?>