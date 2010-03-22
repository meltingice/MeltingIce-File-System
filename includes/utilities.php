<?php
/*
*	utilities.php
*	Mostly random, but useful, functions that needed their own home
*	Code copyright Ryan "MeltingIce" LeFevre
*/

function getUserDir($user=null)
{
	$dummypath = realpath('../user/index.php');
    $boom = explode('index.php',$dummypath);
    if($user==null)
    {
    	return $boom[0].$_SESSION['activeUser'];
    }
    else
    {
    	return $boom[0].$user;
    }
    
}

function getPrettyFileExt($filename,$upper=true)
{
	$boom = explode('.',$filename);
	$fileExt = $boom[count($boom)-1];
	
	for($i=0;$i<count($boom)-1;$i++)
	{
		$basename.=$boom[$i];
	}
	
	$file['base'] = $basename;
	if($upper){ $file['ext'] = strtoupper($fileExt); }
	else{ $file['ext'] = $fileExt; }
	
	return $file;
}

function getPrettyFilesize($size)
{
	if($size<1024)
	{
		return $size.'B'; //return bytes
	}
	if($size>=1024&&$size<1048576)
	{
		return round($size/1024,1).'KB'; //return kilobytes
	}
	if($size>=1048576&&$size<1073741824)
	{
		return round($size/1024/1024,1)."MB"; //return megabytes
	}
	if($size>=1073741824)
	{
		return round($size/1024/1024/1024,1)."GB"; //return gigabytes (just in case, you never know!)
	}
}

function getLastUploadedFiles($num)
{
	$num = secureContent($num);
	if($_SESSION['username']!=$_SESSION['activeUser'])
	{
		$IDquery = "SELECT userID FROM users WHERE username='".$_SESSION['activeUser']."' LIMIT 1";
		$IDresult = mysql_query($IDquery);
		$userID = reset(mysql_fetch_row($IDresult));
	}
	else
	{
		$userID = $_SESSION['userID'];
	}
	$query = "SELECT filename FROM files WHERE ownerID='$userID'";
	if($_SESSION['username']!=$_SESSION['activeUser'])
	{
		$query .= " AND perms='public'";
	}
	$query .= " ORDER BY dateuploaded DESC LIMIT $num";
	$result = mysql_query($query);
	
	if($result&&mysql_num_rows($result)>0)
	{
		while(list($filename)=mysql_fetch_row($result))
		{
			echo "<li>$filename</li>";
		}
	}
	else
	{
		echo "<li>No recent files have been uploaded.</li>";
	}
}

function setFilename($origname)
{	
	$userdir = getUserDir()."/";
	$file = getPrettyFileExt($origname,false);
	$count = 0;
	$findName = true;
	while($findName)
	{
		if(is_file($userdir.$file['base'].$count.".".$file['ext']))
		{
			$count++;
		}
		else
		{
			$findName = false;
		}
	}
	
	return $file['base'].$count.".".$file['ext'];
}

function advancedRmdir($path) {
    $origipath = $path;
    $handler = opendir($path);
    while (true) {
        $item = readdir($handler);
        if ($item == "." or $item == "..") {
            continue;
        } elseif (gettype($item) == "boolean") {
            closedir($handler);
            if (!@rmdir($path)) {
                return false;
            }
            if ($path == $origipath) {
                break;
            }
            $path = substr($path, 0, strrpos($path, "/"));
            $handler = opendir($path);
        } elseif (is_dir($path."/".$item)) {
            closedir($handler);
            $path = $path."/".$item;
            $handler = opendir($path);
        } else {
            unlink($path."/".$item);
        }
    }
    return true;
}
?>