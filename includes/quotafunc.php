<?php
/*
*	quotafunc.php
*	Functions managing a users filespace quota
*	Code copyright Ryan "MeltingIce" LeFevre
*/

session_start();
include_once('dbconnect.php');
include_once('security.php');
include_once('fileinfo.php');

if($_GET['refresh']){ refreshSpaceUsed(); }

function getSpaceUsed($echo=false, $pretty=false)
{
	if(!isset($_SESSION['activeUser'])){ exit; }
	if($_SESSION['username']!=$_SESSION['activeUser'])
	{
		$userquery = "SELECT userID FROM users WHERE username='".$_SESSION['activeUser']."' LIMIT 1";
		$userresult = mysql_query($userquery);
		$userID = reset(mysql_fetch_row($userresult));
	}
	else
	{
		$userID = $_SESSION['userID'];
	}
	$query = "SELECT spaceUsed FROM quotas WHERE userID='$userID'";
	$result = mysql_query($query);
	$spaceUsed = reset(mysql_fetch_row($result));
	
	if($pretty)
	{
		if($echo){ echo getPrettyFilesize($spaceUsed); }
		else{ return getPrettyFilesize($spaceUsed); }
	}
	else
	{
		if($echo){ echo $spaceUsed; }
		else{ return $spaceUsed; }
	}
}

function getSpaceAvail($echo=false, $pretty=false)
{
	if(!isset($_SESSION['activeUser'])){ exit; }
	if($_SESSION['username']!=$_SESSION['activeUser'])
	{
		$userquery = "SELECT userID FROM users WHERE username='".$_SESSION['activeUser']."' LIMIT 1";
		$userresult = mysql_query($userquery);
		$userID = reset(mysql_fetch_row($userresult));
	}
	else
	{
		$userID = $_SESSION['userID'];
	}
	$query = "SELECT spaceAvail FROM quotas WHERE userID='$userID'";
	$result = mysql_query($query);
	$spaceAvail = reset(mysql_fetch_row($result));
	
	if($pretty)
	{
		if($echo){ echo getPrettyFilesize($spaceAvail); }
		else{ return getPrettyFilesize($spaceAvail); }
	}
	else
	{
		if($echo){ echo $spaceAvail; }
		else{ return $spaceAvail; }
	}
}

function refreshSpaceUsed()
{
	if(!isset($_SESSION['username'])){ exit; }
	$spaceUsed = 0;
	$query1 = "SELECT filesize FROM files WHERE ownerID='".$_SESSION['userID']."'";
	$result1 = mysql_query($query1);
	
	while(list($filesize)=mysql_fetch_row($result1))
	{
		$spaceUsed = $spaceUsed + $filesize;
	}
		
	$query2 = "UPDATE quotas SET spaceUsed='$spaceUsed' WHERE userID='".$_SESSION['userID']."'";
	$result2 = mysql_query($query2);
	
	if($result1&&$result2)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function getUserNumFiles()
{
	if(!isset($_SESSION['activeUser'])){ exit; }
	if($_SESSION['username']!=$_SESSION['activeUser'])
	{
		$userquery = "SELECT userID FROM users WHERE username='".$_SESSION['activeUser']."' LIMIT 1";
		$userresult = mysql_query($userquery);
		$userID = reset(mysql_fetch_row($userresult));
	}
	else
	{
		$userID = $_SESSION['userID'];
	}
	$query = "SELECT fileID FROM files WHERE ownerID='$userID'";
	$result = mysql_query($query);
	
	if($result){ return mysql_num_rows($result); }
	else{ return 0; }
}
?>