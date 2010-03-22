<?php
/*
*	filemgmt.php
*	Functions relating to managing a users files (the meat and potatoes of MFS2)
*	Code copyright Ryan "MeltingIce" LeFevre
*/

// Work-around for setting up a session for SWFUpload
if (isset($_POST["PHPSESSID"])) {
    session_id($_POST["PHPSESSID"]);
}
session_start();

include_once('dbconnect.php');
include_once('security.php');
include_once('quotafunc.php');
include_once('fileinfo.php');
include_once('utilities.php');

/* Ajax decision makers */
if($_POST['scope']&&!$_POST['sort']&&!$_POST['showperms']&&!$_POST['refresh']){ 
	if($_POST['folder']){ getUserFiles($_SESSION['activeUser'],$_POST['scope'],'filename','ASC',false,$_POST['folder']); }
	else
	{
		getUserFiles($_SESSION['activeUser'], $_POST['scope']);
	}
}
if($_POST['getUploadForm']){ getUploadForm(); }
if($_POST['sort']){ getUserFiles($_POST['user'],$_POST['scope'],$_POST['sort'],$_POST['order'],false,$_POST['folder']); }
if($_POST['delfile']){ deleteFile($_POST['delfile']); }
if($_POST['changeperms']){ changeFilePerms($_POST['changeperms']); }
if($_POST['showperms']){ getUserFiles($_SESSION['activeUser'], $_POST['scope'], 'filename', 'ASC', true, $_POST['folder']); }
if($_POST['refresh']){ getUserFiles($_SESSION['activeUser'],$_POST['scope']); }
if($_POST['renamefile']){ renameFile($_POST['renamefile'],$_POST['newfilename']); }
if($_POST['refreshFile']){ refreshFile($_POST['refreshFile']); }
if($_POST['movefile']){ moveFile($_POST['fileID'],$_POST['folderID']); }

/* lolwut? You never know... */
if(!is_uploaded_file($_FILES['Filedata']['tmp_name'])&&!is_uploaded_file($_FILES['Filedata']['tmp_name'][0])&&$_POST['fileupload']=='true'){ header('Location: ../home.php'); exit; }

/* Handles file uploads via SWFUpload. Sorry if this looks messy, I tried to make it as clean as possible */
if(is_uploaded_file($_FILES['Filedata']['tmp_name'])&&$_POST['fileupload']=='true'&&$_POST['swfupload']=='true'&&isset($_SESSION['activeUser']))
{	
    /* Sets the upload directory */
    $userdir = getUserDir();
    
    /* Check file extention for disallowed types and change if necessary, then move uploaded file to user directory */
    if(!fileSecurityCheck($_FILES['Filedata']['name'])){ $filename = cleanseFilename(changeFileExtention($_FILES['Filedata']['name'],'txt')); }
    else{ $filename = $_FILES['Filedata']['name']; }
    $filename = str_replace("/","",$filename); //prevents directory change hack
    
    if(!file_exists($userdir."/".$filename)){ $moveResult = move_uploaded_file($_FILES['Filedata']['tmp_name'], $userdir."/".$filename); }
    else // If filename already exists, add a number to the end of it to make it unique
    {
    	$filename = setFilename($filename);
    	$moveResult = move_uploaded_file($_FILES['Filedata']['tmp_name'], $userdir."/".$filename);
    }
   	
   	if($moveResult!=false)
   	{
   	    /* Lets get some useful values from each uploaded file */
   	    $uploadedFiles['name'] = secureContent($filename);
   	    $uploadedFiles['filesize'] = $_FILES['Filedata']['size'];
   	    $uploadedFiles['date'] = time();
        if($_POST['fileperms']=='private'||$_POST['fileperms']=='public'){ $uploadedFiles['perms'] = $_POST['fileperms']; }
		if(isset($_POST['folder'])){ $folder = secureContent($_POST['folder']); }
		else{ $folder = -1; }
   	}
   	else
   	{
   	    echo "file move fail"; exit;
   	}
    
    /* Set up MySQL query */
    $query = "INSERT INTO files (`ownerID`, `filename`, `filesize`, `dateuploaded`, `lastmodified`, `perms`, `parentFolder`) VALUES ";
    $query .= "(".$_SESSION['userID'].", '".$uploadedFiles['name']."', ".$uploadedFiles['filesize'].", ".$uploadedFiles['date'].", ".$uploadedFiles['date'].", '".$uploadedFiles['perms']."', '$folder')";
    	    
    $result = mysql_query($query);
    
    if($result)
    {
    	refreshSpaceUsed(); // Update the quota table now that the file has been uploaded
    	echo "upload finished";
    }
    else
    {
    	/* Sorry to delete your files so soon, but we need to keep MySQL in sync with the files */
		unlink($userdir."/".$uploadedFiles['name']);    	
    }
}

/* Handles file uploads via simple uploader for those without Flash installed */
if(is_uploaded_file($_FILES['Filedata']['tmp_name'][0])&&$_POST['fileupload']=='true'&&$_POST['swfupload']=='false'&&isset($_SESSION['username']))
{
	if(!fileSecurityCheck($_FILES['Filedata']['name'])){ $changeExt = true; } else { $changeExt = false; }
	
	/* Sets the upload directory */
	$userdir = getUserDir();
		
    for($i=0;$i<count($_FILES['Filedata']['tmp_name']);$i++)
    {	
    	/* Check file extention for disallowed types and change if necessary, then move uploaded file to user directory */
    	if(!fileSecurityCheck($_FILES['Filedata']['name'][$i])){ $filename = cleanseFilename(changeFileExtention($_FILES['Filedata']['name'][$i],'txt')); }
   		else{ $filename = $_FILES['Filedata']['name'][$i]; }
   		$filename = str_replace("/","",$filename); //prevents directory change hack
   		
   		if(file_exists($userdir."/".$filename))
   		{
   			$filename = setFilename($filename);
   		}
   		
   		$moveResult = move_uploaded_file($_FILES['Filedata']['tmp_name'][$i], $userdir."/".$filename);
   		
   		if($moveResult!=false)
   		{
   			/* Lets get some useful values from each uploaded file */
   			$uploadedFiles[$i]['name'] = secureContent($filename);
   			$uploadedFiles[$i]['filesize'] = $_FILES['Filedata']['size'][$i];
   			$uploadedFiles[$i]['date'] = time();
    		if($_POST['fileperms']=='private'||$_POST['fileperms']=='public'){ $uploadedFiles[$i]['perms'] = $_POST['fileperms']; }
   		}
   		else
   		{
   			header('Location: ../home.php?upload=filefail'); exit;
   		}
    }
    
    /* Build the MySQL query so we can do this all in one go and not many queries */
    $query = "INSERT INTO files (`ownerID`, `filename`, `filesize`, `dateuploaded`, `lastmodified`, `perms`) VALUES ";
    for($j=0;$j<count($uploadedFiles);$j++)
    {
    	$query .= "(".$_SESSION['userID'].", '".$uploadedFiles[$j]['name']."', ".$uploadedFiles[$j]['filesize'].", ".$uploadedFiles[$j]['date'].", ".$uploadedFiles[$j]['date'].", '".$uploadedFiles[$j]['perms']."')";
    	
    	if(count($uploadedFiles)>1&&$j!=count($uploadedFiles)-1){ $query.=","; }

    }
    
    $result = mysql_query($query);
    if($result)
    {
    	refreshSpaceUsed(); // Update quota table since new files have been uploaded
    	header('Location: ../home.php?upload=finished'); exit;
    }
    else
    {
    	/* Sorry to delete your files so soon, but we need to keep MySQL in sync with the files */
    	for($i=0;$i<count($uploadedFiles);$i++)
    	{
    		unlink($userdir."/".$uploadedFiles[$i]['name']);
    	}
    	
    	header('Location: ../home.php?upload=dbfail'); exit;
    }
}

/*	Retrieves a users filelist from MySQL. 
*	Although it accepts a 'scope' parameter, it will not show private files if they are not yours
*/
function getUserFiles($user, $scope='all', $sortItem='filename', $sortOrder="ASC", $showPerms=false, $folderID=-1)
{
	$user = secureContent($user);
	$sortItem = secureContent($sortItem);
	$sortOrder = secureContent($sortOrder);
	$folderID = secureContent($folderID);

	/* If viewer is the owner of the files... */
	if($user==$_SESSION['username'])
	{
		/* Lets build this query step-by-step */
		$query = "SELECT fileID, filename, perms FROM files WHERE ownerID='".$_SESSION['userID']."' AND parentFolder='$folderID'";
		
		/* If scope is specified */
		if($scope=='public'||$scope=='private')
		{
			$query.=" AND perms='$scope'";
		}

		$query .= " ORDER BY $sortItem $sortOrder";
		
		$result = mysql_query($query); //Fetch the file infos from mysql
		$userexists = true;
	}
	else // If viewer is not owner of the files, aka public file listing
	{
		/* Find users ID and make sure they exist */
		$query1 = "SELECT userID FROM users WHERE username='$user' LIMIT 1";
		$result1 = mysql_query($query1);
		if(mysql_num_rows($result1)!=0) //if user exists...
		{
			$userID = mysql_fetch_row($result1);
			$query2 = "SELECT fileID, filename FROM files WHERE ownerID='".$userID[0]."' AND perms='public' AND parentFolder='$folderID'"; //public files only!
			$query2 .= " ORDER BY $sortItem $sortOrder";
			$result = mysql_query($query2); //Fetch the file infos from mysql
			$userexists = true;
		}
		else
		{
			$userexists = false;
		}
		
	}
	
	/* Time to output some stuff to the UI */
	if($userexists)
	{
		$rows = mysql_num_rows($result);
	
		/* If no files are retrieved from MySQL */
		if($rows==false||$rows==0)
		{
			if($scope=='all'&&$user==$_SESSION['username'])
			{
				echo "<li>You have no files uploaded in this folder.</li>\n";
			}
			elseif($scope=='private')
			{
				echo "<li>You have no private files.</li>\n";
			}
			elseif($scope=='public'&&$user!=$_SESSION['username'])
			{
				echo "<li>$user has no public files.</li>\n";
			}
			elseif($scope=='public'&&$user==$_SESSION['username'])
			{
				echo "<li>You have no public files.</li>\n";
			}
			else
			{
				echo "<li>$user has no public files in this folder.</li>\n";
			}
		}
		else // Time to output some files!
		{
			$alt=false;
			
			while(list($fileID,$filename,$perms)=mysql_fetch_row($result))
			{
				$file = getPrettyFileExt($filename);
				
				echo "<li id=\"file_$fileID\" class=\"file";
				if($alt){ echo " alt"; $alt=false; }
				else{ $alt=true; }
				echo "\">";
				
				/* Get the filetype icon */
				echo "<span class=\"filetypeicon\"><img src=\"img/icons/".getFileIcon($file['ext'])."\" alt=\"filetype\" /></span>";
				
				/* Output the filename */
				echo " <span class=\"filelink\"><a href=\"javascript:";
				if($showPerms){ echo "void(0)"; }
				else{ echo "getFileInfo($fileID)"; }
				echo "\"><span class=\"filename\">".$file['base']."</span>";
				
				if($showPerms){ echo " <span id=\"perm_$fileID\" class=\"fileperm\">($perms)</span>"; }
				echo "</a></span></li>\n";
			}
			
			/* Time for some jQuery stuff, mmm... tasty */
			if($_SESSION['username']==$_SESSION['activeUser'])
			{
				echo "<script>
						$('.file').draggable({
							cursor: 'pointer',
							opacity: 0.5,
							revert: 'invalid',
							delay: 150
						});
						$('.file').click(function(event){
							if(event.shiftKey)
							{
								if(!$(this).hasClass('multi-select'))
								{
									$(this).addClass('multi-select');
								}
								else
								{
									$(this).removeClass('multi-select');
								}
							}
						});							
					</script>
				";
			}
		}
	}
	else
	{
		echo "<li>This user does not exist.</li>"; // sadface :(
	}
}

/*
*	Deletes a file from MySQL and the physical disk
*	If an image thumbnail was generated for the file, it will be deleted too
*/
function deleteFile($fileID)
{
	if(!isset($_SESSION['userID'])){ echo "log in foo"; exit; }
	
	$delresult = false;
	$fileID = secureContent($fileID);
	
	/* Since MFS2 supports bulk file deletion, lets build this into a single MySQL query */
	$query1 = "SELECT filename FROM files WHERE";
	$selector = "";
	for($i=0;$i<count($fileID);$i++)
	{
		if($i!=0){ $selector.= " OR"; }
		$selector.= " fileID='".$fileID[$i]."'";
	}
	$query1 .= $selector." AND ownerID='".$_SESSION['userID']."'";
	
	//echo "The SELECT query: ".$query1."\n";

	$result1 = mysql_query($query1);

	/* If you don't own this file or if the file doesn't exist, this will fail */
	if(mysql_num_rows($result1)>0)
	{
		/* We can reuse the WHERE statement from the previous query */
		$query2 = "DELETE FROM files WHERE";
		$query2 .= $selector." AND ownerID='".$_SESSION['userID']."'";
		//echo "The DELETE query: ".$query2."\n";
		$result2 = mysql_query($query2);
		if($result2)
		{
			echo "db removal success\n";
			
			/* Sets the user directory */
			$userdir = getUserDir();
			
			/* Time to delete the files from the disk */
			while(list($filename)=mysql_fetch_row($result1))
			{
				$delresult = unlink($userdir.'/'.$filename);
	
				if($delresult){ echo "physical file deleted: ".$filename."\n"; }
				else{ echo "physical file delete fail: ".$filename."\n"; }
				
				/* If there is a thumbnail for the image being deleted, delete it too */
				if(file_exists("$userdir/thumbs/$filename"))
				{
					$delresult = unlink($userdir.'/thumbs/'.$filename);
					
					if($delresult){ echo "physical thumbnail deleted: ".$filename."\n"; }
					else{ echo "physical thumbnail delete fail: ".$filename."\n"; }
				}
			}
			
			refreshSpaceUsed();
		}
		else
		{
			echo "db removal fail\n"; 
		}
	}
	else
	{
		echo "either the file doesn't exist or you fail\n"; //hmm... hacker or curious user?
	}
	
	return $delresult;
}

/*
*	This will toggle the file permissions for a specified file
*	Makes use of a MySQL subquery so that everything can be done in 1 query
*/
function changeFilePerms($fileID)
{
	$fileID = secureContent($fileID);
	
	$query = "UPDATE files SET perms=(SELECT CASE perms WHEN 'private' THEN 'public' ELSE 'private' END) WHERE fileID='$fileID' AND ownerID='".$_SESSION['userID']."'";
	$result = mysql_query($query);
	
	if($result)
	{
		echo "db query successful";
	}
	else
	{
		echo "database update fail";
	}
}

/* Does what you probably guessed, renames a file specified by its ID */
function renameFile($fileID, $newFilename)
{
	if(!isset($_SESSION['userID'])&&$_SESSION['username']!=$_SESSION['activeUser']){ exit; }
	
	$fileID = secureContent($fileID);
	$newFilename = secureContent($newFilename);
	
	if(!fileSecurityCheck($newFilename)){ $newFilename = cleanseFilename($newFilename); }
	$newFilename = str_replace("/","",$newFilename); //prevents directory change hack
	
	/* First the MySQL side */
	$query1 = "SELECT filename FROM files WHERE fileID='$fileID' LIMIT 1";
	$result1 = mysql_query($query1);
	
	$oldFilename = reset(mysql_fetch_row($result1));
	$fileExt = getPrettyFileExt($oldFilename,false);
	
	/* Gotta check to make sure the filename doesn't exist already */
	$userdir = getUserDir();
	if(file_exists($userdir."/".$newFilename.".".$fileExt['ext'])){ echo "filename already exists\n"; exit; }
	
	$query = "UPDATE files SET filename='".$newFilename.".".$fileExt['ext']."' WHERE fileID='$fileID' AND ownerID='".$_SESSION['userID']."' LIMIT 1";
	$result = mysql_query($query);
	
	if($result)
	{	
		$rename = rename($userdir."/".$oldFilename, $userdir."/".$newFilename.".".$fileExt['ext']);
		if($rename){ echo "filename changed to ".$newFilename.".".$fileExt['ext']."\n"; }
		else{ echo "physical file rename failed\n"; }
		
		if(file_exists($userdir."/thumbs/".$oldFilename))
		{
			$rename2 = rename($userdir."/thumbs/".$oldFilename, $userdir."/thumbs/".$newFilename.".".$fileExt['ext']);
			if($rename2){ echo "thumbnail changed to ".$newFilename.".".$fileExt['ext']; }
			else{ echo "thumbnail rename failed"; }
		}
	}
	else
	{
		echo "db failure";
	}
}

/* Used mainly for refreshing a single file after its renamed */
function refreshFile($fileID)
{
	$fileID = secureContent($fileID);
	
	$query = "SELECT filename FROM files WHERE fileID='$fileID' AND ownerID='".$_SESSION['userID']."' LIMIT 1";
	$result = mysql_query($query);
	
	if($result)
	{
		while(list($filename)=mysql_fetch_row($result))
		{
			$file = getPrettyFileExt($filename);
				
			/* Get the filetype icon */
			echo "<span class=\"filetypeicon\"><img src=\"img/icons/".getFileIcon($file['ext'])."\" alt=\"filetype\" /></span>";
			
			/* Output the filename */
			echo " <span class=\"filelink\"><a href=\"javascript:getFileInfo($fileID)\"><span class=\"filename\">".$file['base']."</span>";
			
			echo "</a></span>\n";
		}
	}
	else
	{
		echo "db failure";
	}
}

/* 
*	Moves a file from one folder to another
*	No physical file moving going on here
*/
function moveFile($fileIDs,$folderID)
{
	$fileIDs = secureContent($fileIDs);
	$folderID = secureContent($folderID);
	foreach($fileIDs as $fileID)
	{
		$query = "UPDATE files SET parentFolder='$folderID' WHERE fileID='$fileID' LIMIT 1";
		$result = mysql_query($query);
		
		if($result)
		{
			echo "File #$fileID moved\n";
		}
		else
		{
			echo "File move failed\n";
		}
	}
}

function getUploadForm()
{
	echo "
		<h4>UPLOAD FILES</h4>\n
		<form action=\"includes/filemgmt.php\" method=\"post\" enctype=\"multipart/form-data\" id=\"fileuploadform\">\n
			<p><input type=\"file\" class=\"fileinput\" name=\"Filedata[]\" /></p>\n
			<p id=\"addanotherinput\"><a href=\"#\" onclick=\"addUploadInput()\">ADD ANOTHER FILE INPUT</a></p>\n
			<p><select name=\"fileperms\">\n
				<option name=\"private\" value=\"private\">Private</option>\n
				<option name=\"public\" value=\"public\">Public</option>\n
			</select></p>\n
			<input type=\"hidden\" name=\"fileupload\" value=\"true\" />\n
			<input type=\"hidden\" name=\"swfupload\" value=\"false\" />\n
			<p><input type=\"submit\" name=\"submitbutton\" value=\"UPLOAD\" /></p>\n
		</form>\n
	";
}
?>