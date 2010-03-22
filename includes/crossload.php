<?php
/*
*	crossload.php
*	Transfers a file from another server to a users MFS2 account via URL
*	Code copyright Ryan "MeltingIce" LeFevre
*/
session_start();
include_once('dbconnect.php');
include_once('security.php');
include_once('utilities.php');

if($_POST['URL']){ crossloadFile($_POST['URL'],$_POST['folderID']); }

function crossloadFile($url,$folderID)
{
	if(!isset($_SESSION['username'])||$_SESSION['username']!=$_SESSION['activeUser']){ exit; }
	
	$folderID = secureContent($folderID);
	$userdir = getUserDir();
	$temp = explode('/',$url);
	$filename = $temp[count($temp)-1];
	
	$copyResult = copy($url,$userdir."/".$filename);
	
	if($copyResult)
	{
		if(!fileSecurityCheck($filename))
		{
			$newFilename = cleanseFilename(changeFileExtention($filename,'txt'));
			rename($userdir."/".$filename,$userdir."/".$newFilename);
			$filename = $newFilename;
		}
		
		$filename = secureContent($filename);
		
		$query = "INSERT INTO files (`ownerID`, `filename`, `filesize`, `dateuploaded`, `lastmodified`, `perms`, `parentFolder`) VALUES ";
		$query .= "(".$_SESSION['userID'].", '".$filename."', ".filesize($userdir."/".$filename).", ".time().", ".time().", 'private', '$folderID')";
    	    
    	$result = mysql_query($query);
    	if($result)
    	{
    		echo "$filename successfully downloaded";
    	}
    	else
    	{
    		echo "crossload fail";
    	}
	}
}
?>