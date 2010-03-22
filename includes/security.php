<?php
/*
*	security.php
*	Security-based functions to stop hackers and secure the server/MySQL database
*	Code copyright Ryan "MeltingIce" LeFevre
*/

/* Escape strings/arrays for insertion to MySQL */
function secureContent($content)
{
	if(is_array($content))
	{
	    for($i=0;$i<count($content);$i++)
	    {
	    	$content[$i] = mysql_real_escape_string(html_entity_decode($content[$i]));
	    }
	    
	    return $content;
	}
	else
	{
	    return mysql_real_escape_string(html_entity_decode($content));
	}
}

/* Checks uploaded file extentions for security purposes */
function fileSecurityCheck($filename)
{
	GLOBAL $disallow;
		
	/* Lets break this filename down */
	$boom = explode('.',$filename);
	//$fileExt = $boom[count($boom)-1];
	    	
	for($i=0;$i<count($disallow);$i++)
	{
		for($j=0;$j<count($boom);$j++)
		{
			if($disallow[$i]==$boom[$j])
	   		{
	    		return false;
	    	}
		}
	}
	
	return true;
}

/* Splits a filename at each period and checks every one but the first for disallowed strings */
function cleanseFilename($filename)
{
	GLOBAL $disallow;
	
	if(strpos($filename,'.')==0){ $filename = substr($filename,1); }

	$boom = explode('.',$filename);
	
	if($boom[0]=='htaccess'){ return "htaccess.txt"; }
	
	for($i=1;$i<count($boom);$i++)
	{
		$ext .= $boom[$i];
	}
	
	return $boom[0].".".str_replace($disallow,'',$ext);
}

/* Changes file extention of a file to a specified type */
function changeFileExtention($filename, $newExt)
{
	$boom = explode('.',$filename);
	$fileExt = $boom[count($boom)-1];
	
	$keep = explode($fileExt,$filename);
	return $keep[0].$newExt;
}
?>