<?php
/*
*	folderinfo.php
*	Retrieves information about a folder from MySQL
*	Code copyright Ryan "MeltingIce" LeFevre
*/
session_start();
include_once('dbconnect.php');
include_once('security.php');
include_once('quotafunc.php');
include_once('utilities.php');

function getFolderInfo($folderID)
{
	$folderID = secureContent($folderID);
	$folder = array();
	
	$query1 = "SELECT ownerID,foldername,datecreated,perms FROM folders WHERE folderID='$folderID' LIMIT 1";
	$result1 = mysql_query($query1);
	
	if($result1)
	{
		while(list($ownerID,$foldername,$datecreated,$perms)=mysql_fetch_row($result1))
		{
			if($ownerID!=$_SESSION['userID']){ exit; }
			else
			{
				$folder['name'] = $foldername;
				$folder['created'] = $datecreated;
				$folder['perms'] = $perms;
			}
		}
	}
	
	$spaceUsed = 0;
	$query2 = "SELECT filesize FROM files WHERE ownerID='".$_SESSION['userID']."' AND parentFolder='$folderID'";
	$result2 = mysql_query($query2);
	
	if($result2)
	{
		while(list($filesize)=mysql_fetch_row($result2))
		{
			$spaceUsed = $spaceUsed + $filesize;
		}
		
		$folder['spaceUsed'] = getPrettyFilesize($spaceUsed);
		$folder['numFiles'] = mysql_num_rows($result2);
	}
	
	return $folder;
}
?>