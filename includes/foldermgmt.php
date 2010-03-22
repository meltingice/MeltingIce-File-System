<?php
/*
*	foldermgmt.php
*	Functions relating to managing a users folders
*	Code copyright Ryan "MeltingIce" LeFevre
*/

session_start();

include_once('dbconnect.php');
include_once('security.php');

if($_POST['getFolderForm']){ getFolderForm(); }
if($_POST['newfolder']){ addFolder($_POST['newfolder']); }
if($_POST['refresh']){ getUsersFolders(); }
if($_POST['numContents']){ getFolderNumContents($_POST['numContents'],'all',true); }
if($_POST['editFolder']){
	$args = array("foldername"=>$_POST['foldername'],"perms"=>$_POST['folderPerms']);
	editFolder($_POST['editFolder'],$args);
}
if($_POST['delfolder']){ deleteFolder($_POST['delfolder'],$_POST['fileOption']); }

function getUsersFolders()
{	
	echo "<div id=\"editable-folders\">\n";
	$userquery = "SELECT userID FROM users WHERE username='".$_SESSION['activeUser']."' LIMIT 1";
	$userresult = mysql_query($userquery);
	if($userresult)
	{
		$userID = reset(mysql_fetch_row($userresult));
		$query = "SELECT folderID, foldername, datecreated FROM folders WHERE ownerID='$userID'";
		if($_SESSION['username']!=$_SESSION['activeUser'])
		{
			$query .= " AND perms='public'";
		}
		$query .= " ORDER BY foldername ASC";
		$result = mysql_query($query);
		
		if($result)
		{
			if(mysql_num_rows($result)>0)
			{
				while(list($folderID,$foldername,$datecreated)=mysql_fetch_row($result))
				{
					echo "<li id=\"folder_$folderID\" class=\"folder\" onclick=\"loadFolder($folderID);\"";
					if($_SESSION['username']==$_SESSION['activeUser']){ echo "onmouseover=\"showFolderEditIcon(this);\" onmouseout=\"hideFolderEditIcon(this);\" "; }
					echo "><a href=\"javascript:void(0)\"><img src=\"img/icons/folder.png\" alt=\"folder\" /></a>$foldername<div id=\"".$folderID."_numcontent\" class=\"foldernumcontent\">".getFolderNumContents($folderID)."</div></li>\n";
				}
			}
			elseif(mysql_num_rows($result)==0&&$_SESSION['username']!=$_SESSION['activeUser'])
			{
				//echo "<li id=\"folder_-2\">No Folders</li>\n";
			}
			else{ /* do nothing */ }
		}
		
		echo "<li id=\"folder_-1\" class=\"folder\" onclick=\"loadFolder(-1)\"><img src=\"img/icons/folder.png\" alt=\"folder\" />Uncategorized <div id=\"-1_numcontent\" class=\"foldernumcontent\">".getFolderNumContents(-1)."</div></li>";
				
		echo "</div>\n";				
		
		/* Make the folders jQuery droppables */
		if($_SESSION['username']==$_SESSION['activeUser'])
		{
		    echo "<script>
		    		$('.folder').droppable({
		    			accept: '.file',
		    			tolerance: 'pointer',
		    			activate: function(e,ui)
		    			{
		    				$('.folder>img').attr('src','img/icons/arrow_right.png');
		    			},
		    			deactivate: function(e,ui)
		    			{
		    				$('.folder>img').attr('src','img/icons/folder.png');
		    			},
		    			over: function(e,ui)
		    			{
		    				if($(this).hasClass('active-folder'))
		    				{
		    					$(this).css({background:'#c68282'});
		    				}
		    				else
		    				{
		    					$(this).addClass('droppable-hover');
		    				}
		    			},
		    			out: function(e,ui)
		    			{
		    				if($(this).hasClass('active-folder'))
		    				{
		    					$(this).css({'background-image':'url(\"img/activefolder.jpg\")','background-repeat':'repeat-x'});
		    				}
		    				else
		    				{
		    					$(this).removeClass('droppable-hover');
		    				}
		    			},
		    			drop: function(e,ui)
		    			{
		    				var folderID = $(this).attr('id');
		    				var fileID = $(ui.draggable).attr('id');
		    				
		    				moveFile(folderID,fileID);
		    			}
		    		});
		    	</script>
		    ";
		}
	}
}

function getUserFolderArray()
{
	$query = "SELECT folderID,foldername FROM folders WHERE ownerID='".$_SESSION['userID']."' ORDER BY foldername ASC";
	$result = mysql_query($query);
	
	$folders = array();
	$i = 0;
	while(list($folderID,$foldername)=mysql_fetch_row($result))
	{
		$folders[$i]['folderID'] = $folderID;
		$folders[$i]['foldername'] = $foldername;
		$i++;
	}
	
	return $folders;
}

function addFolder($foldername)
{
	if(!isset($_SESSION['username'])){ exit; }
	
	$foldername = secureContent($foldername);
	
	/* First check to make sure folder doesn't already exist */
	$query1 = "SELECT folderID FROM folders WHERE foldername='$foldername' AND ownerID='".$_SESSION['userID']."' LIMIT 1";
	$result1 = mysql_query($query1);
	
	if(mysql_num_rows($result1)>0)
	{
		echo "folder already exists";
	}
	else
	{
		$query2 = "INSERT INTO folders (`ownerID`,`foldername`,`datecreated`) VALUES (".$_SESSION['userID'].", '$foldername', ".time().")";
		$result2 = mysql_query($query2);
		
		if($result2)
		{
			echo "folder added";
		}
		else
		{
			echo "folder add failed";
		}
	}
}

function deleteFolder($folderID,$fileOption)
{
	if(!isset($_SESSION['username'])||$_SESSION['activeUser']!=$_SESSION['username']){ exit; }
	
	$folderID = secureContent($folderID);
	$fileOption = secureContent($fileOption);
	
	/* Case 1: User wants to keep files, lets move them to uncategorized */
	if($fileOption=='keep')
	{
		$updateQuery = "UPDATE files SET parentFolder='-1' WHERE parentFolder='$folderID' AND ownerID='".$_SESSION['userID']."'";
		$updateResult = mysql_query($updateQuery);
		if($updateResult)
		{
			$delFolder = true;
		}
		else
		{
			$delFolder = false;
		}
	}
	
	/* Case 2: User wants to delete files in the folder as well */
	if($fileOption=='delete')
	{
		include_once('filemgmt.php');
		
		$fileGet = "SELECT fileID FROM files WHERE parentFolder='$folderID' AND ownerID='".$_SESSION['userID']."'";
		$getResult = mysql_query($fileGet);
		
		$files = array();
		while(list($fileID)=mysql_fetch_row($getResult))
		{
			$files[] = $fileID;
		}
		
		if(count($files)>0)
		{
			$delresult = deleteFile($files);
		}
		else
		{
			$delresult = true;
		}

		if($delresult) // If files were able to be deleted from disk
		{
			$fileDel = "DELETE FROM files WHERE parentFolder='$folderID' AND ownerID='".$_SESSION['userID']."'";
			$fileResult = mysql_query($fileDel);
			if($fileResult)
			{
				$delFolder = true;
				echo "Files deleted\n";
			}
			else
			{
				$delFolder = false;
				echo "File delete error\n";
			}
		}
	}
	
	if($delFolder)
	{
		$query = "DELETE FROM folders WHERE folderID='$folderID' AND ownerID='".$_SESSION['userID']."' LIMIT 1";
		$result = mysql_query($query);
	
		if($result)
		{
			echo "folder deleted";
		}
		else
		{
			echo "folder delete fail";
		}
	}
}

function editFolder($folderID, $args)
{
	if(!isset($_SESSION['username'])){ exit; }
	if(count($args)==0){ exit; }
	
	$folderID = secureContent($folderID);
	
	/* Unfortunately we have to do this in 2 queries, not sure why MySQL does this */
	if(array_key_exists('foldername',$args))
	{
		$newFoldername = secureContent($args['foldername']);
		$query1 = "UPDATE folders SET foldername='$newFoldername' WHERE folderID='$folderID' AND ownerID='".$_SESSION['userID']."' LIMIT 1";
		$result1 = mysql_query($query1);
	}
	if(array_key_exists('perms',$args))
	{
		$perms = secureContent($args['perms']);
		$query2 = "UPDATE folders SET perms='$perms' WHERE folderID='$folderID' AND ownerID='".$_SESSION['userID']."' LIMIT 1";
		$result2 = mysql_query($query2);
	}
	
	if($result1||$result2)
	{
		echo "folder edit successful";
	}
	else
	{
		echo "folder edit failed";
	}
}

function getFolderNumContents($folderID,$scope="all",$echo=false)
{
	$folderID = secureContent($folderID);
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
	$query = "SELECT fileID FROM files WHERE parentFolder='$folderID' AND ownerID='$userID'";
	if(($scope=="public"||$scope=="private")&&$_SESSION['username']==$_SESSION['activeUser'])
	{
		$query .= " AND perms=$scope";
	}
	elseif($_SESSION['username']!=$_SESSION['activeUser'])
	{
		$query .= " AND perms='public'";
	}
	
	$result = mysql_query($query);
	
	if($result)
	{
		if(mysql_num_rows($result)==false)
		{
			if($echo){ echo "0"; }
			else{ return "0"; }
		}
		else
		{
			if($echo){ echo mysql_num_rows($result); }
			else{ return mysql_num_rows($result); }
		}
	}
}

function getFolderName($folderID)
{
	$folderID = secureContent($folderID);
	
	$query = "SELECT foldername FROM folders WHERE folderID='$folderID' LIMIT 1";
	$result = mysql_query($query);
	
	if($result)
	{
		$foldername = mysql_fetch_row($result);
		
		echo $foldername[0];
	}
}

function getFolderForm()
{
	if(!isset($_SESSION['username'])){ exit; }
	
	echo "<h4>Create a New Folder</h4>\n";
	echo "<form action=\"javascript:newFolder()\">\n
			<p>Name: <input type=\"text\" id=\"newfoldername\" name=\"newfoldername\" /></p>\n
			<p><input type=\"submit\" value=\"Create Folder\" /></p>\n
		</form>\n";
}
?>