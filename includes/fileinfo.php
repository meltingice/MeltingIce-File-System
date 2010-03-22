<?php
/*
*	fileinfo.php
*	Retrieves information about a file from MySQL
*	Code copyright Ryan "MeltingIce" LeFevre
*/

session_start();
include_once('dbconnect.php');
include_once('security.php');
include_once('filemgmt.php');
include_once('utilities.php');
include_once('getid3/getid3.php');
include_once('filetypes.php');
$getID3 = new getID3;


if($_POST['IDtoName']){ fileIDtoName($_POST['IDtoName']); }

if($_POST['fileID']&&!$_POST['movefile']&&!$_POST['modalbox'])
{	
	$fileID = secureContent($_POST['fileID']);
	$query = "SELECT ownerID,filename,filesize,dateuploaded,lastmodified,perms FROM files WHERE fileID=$fileID LIMIT 1";
	$result = mysql_query($query);
	
	if($result)
	{
		while(list($ownerID,$filename,$filesize,$dateuploaded,$lastmodified,$perms)=mysql_fetch_row($result))
		{
			if($ownerID!=$_SESSION['userID']&&$perms=='private')
			{
				echo "<p>You are not authorized to view this.</p>"; exit;
			}
			else
			{
				$file = getPrettyFileExt($filename);
				if(in_array($file['ext'],$images)) // create a thumbnail if it doesn't exist, and then display it
				{
					if(!file_exists('../user/'.$_SESSION['activeUser'].'/thumbs/'.$filename))
					{
						createThumb('../user/'.$_SESSION['activeUser'].'/'.$filename,'../user/'.$_SESSION['activeUser'].'/thumbs/'.$filename,200,200);
					}
					
					echo "<div id=\"info_thumbnail\">\n";
						echo "<img src=\"user/".$_SESSION['activeUser']."/thumbs/$filename\" alt=\"thumbnail\" />\n";
					echo "</div>\n";
				}
				elseif($file['ext']=='MP3') //load the mp3 player
				{
					echo "<div id=\"info_thumbnail\">\n";
						echo "<embed type=\"application/x-shockwave-flash\" src=\"includes/player.swf\" id=\"audioplayer1\" name=\"audioplayer1\" bgcolor=\"#f8f8f8\" quality=\"high\" menu=\"false\" flashvars=\"soundFile=user/".$_SESSION['activeUser']."/$filename&autostart=no\" height=\"48\" width=\"220\">\n";
					echo "</div>\n";
				}
				echo "<div id=\"info_filename\"><p>".$file['base']."</p></div>\n";

				if(in_array($file['ext'],$images)){ echo "<div id=\"info_moreinfo\" style=\"padding-top: 1px;\">"; }
				else{ echo "<div id=\"info_moreinfo\">\n"; }
					echo "<p><span class=\"info_leftcontent\">Kind</span><span class=\"info_rightcontent\">".$file['ext']."</span></p>\n";
					echo "<p><span class=\"info_leftcontent\">Size</span><span class=\"info_rightcontent\">".getPrettyFilesize($filesize)."</span></p>\n";
					echo "<p><span class=\"info_leftcontent\">Uploaded</span><span class=\"info_rightcontent\">".date('n/j/Y g:ia',$dateuploaded)."</span></p>\n";
					if(in_array($file['ext'],$images))
					{
						$imageinfo = getimagesize('../user/'.$_SESSION['activeUser'].'/'.$filename);
						if($imageinfo)
						{
							echo "<p><span class=\"info_leftcontent\">Resolution</span><span class=\"info_rightcontent\">".$imageinfo[0]."x".$imageinfo[1]."</span></p>\n";
						}
					}
				echo "</div>\n";
				echo "<div id=\"info_evenmoreinfo\">\n";
					getEvenMoreInfo($filename);
				echo "</div>\n";
				echo "<div id=\"info_links\">\n";
					echo "<a href=\"user/".$_SESSION['activeUser']."/$filename\" target=\"_blank\"><img src=\"img/download.png\" alt=\"download\" /></a>\n";
					echo "&nbsp;<a href=\"javascript:deleteConfirm($fileID)\"><img src=\"img/delete.png\" alt=\"delete\" /></a>\n";
				echo "</div>\n";
			}
		}
	}
}

function fileIDtoName($fileID,$echo=true)
{
	if($_SESSION['username']!=$_SESSION['activeUser']){ exit; }
	$fileID = secureContent($fileID);
	
	$query = "SELECT filename FROM files WHERE fileID='$fileID' AND ownerID='".$_SESSION['userID']."' LIMIT 1";
	$result = mysql_query($query);
	$filename = reset(mysql_fetch_row($result));
	
	if($echo){ echo $filename; }
	else{ return $filename; }
}

function getFileIcon($filetype)
{
	global $images,$audio,$video,$otherimages,$data;
	/* Image formats */
	//$images = array("JPG","JPEG","PNG","GIF","BMP","PSD");
	
	/* Document formats */
	$docs = array("DOC","DOCX","TXT","RTF","ODT","HTML","HTM");
	
	/* Audio formats */
	//$audio = array("MP3","M4A","M4P","AAC","OGG","WAV");
	
	/* Video formats */
	//$video = array("MKV","WMV","MOV","MP4","AVI");
	
	if(in_array($filetype,$images)||in_array($filetype,$otherimages))
	{
		return "image.png";
	}
	elseif(in_array($filetype,$docs))
	{
		return "page.png";
	}
	elseif(in_array($filetype,$audio))
	{
		return "sound.png";
	}
	elseif(in_array($filetype,$video))
	{
		return "film.png";
	}
	else
	{
		return "page_white.png";
	}
}

/* Creates image thumbnail */
/* Code Beautified and Gif support added by Jeff :P */
function createThumb($name, $filename, $new_w, $new_h)
{
	$ext = pathinfo($name,PATHINFO_EXTENSION);
	
	if($file['ext']=='jpg'||$file['ext']=='jpeg')
	{
		$src_img = @imagecreatefromjpeg($name);
	}
	else if($file['ext']=='png')
	{
		$src_img = @imagecreatefrompng($name);
	}
	else if($file['ext']=='gif')
	{
		$src_img = @imagecreatefromgif($name);
	}
	$old_x = @imageSX($src_img);
	$old_y = @imageSY($src_img);
	if ($old_x > $old_y)
	{
		$thumb_w = $new_w;
		$thumb_h = $old_y * ($new_h / $old_x);
	}
	else if($old_x < $old_y)
	{
		$thumb_w = $old_x * ($new_w / $old_y);
		$thumb_h = $new_h;
	}
	else if ($old_x == $old_y)
	{
		$thumb_w = $new_w;
		$thumb_h = $new_h;
	}
	
	$dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);
	@imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
	
	if($file['ext']=='jpg'||$file['ext']=='jpeg')
	{
		@imagejpeg($dst_img, $filename);
	}
	else if($file['ext']=='png')
	{
		@imagepng($dst_img, $filename);
	}
	else if($file['ext']=='gif')
	{
		@imagegif($dst_img, $filename);
	}
	
	imagedestroy($dst_img);
	@imagedestroy($src_img);
}

function getEvenMoreInfo($filename)
{
	global $getID3,$audio,$video,$images,$otherimages,$data;

	$file = getPrettyFileExt($filename);
	$fileinfo = $getID3->analyze('../user/'.$_SESSION['activeUser'].'/'.$filename);
	
	if(in_array($file['ext'],$audio)) // If file contains audio data
	{
		/* Ugh... getID3 returns quite a messy array */
	    if(array_key_exists('quicktime',$fileinfo['tags_html'])){ $ident = 'quicktime'; }
	    elseif(array_key_exists('id3v2',$fileinfo['tags_html'])){ $ident = 'id3v2'; }
	    elseif(array_key_exists('vorbiscomment',$fileinfo['tags_html'])){ $ident = 'vorbiscomment'; }
	    
	    echo "<h4>More File Data</h4>\n";
	    echo "<p><span class=\"info_moretitle\">Artist:</span> ".$fileinfo['tags_html'][$ident]['artist'][0]."</p>";
	    echo "<p><span class=\"info_moretitle\">Title:</span> ".$fileinfo['tags_html'][$ident]['title'][0]."</p>";
	    echo "<p><span class=\"info_moretitle\">Album:</span> ".$fileinfo['tags_html'][$ident]['album'][0]."</p>";
	    echo "<p><span class=\"info_moretitle\">Duration:</span> ".$fileinfo['playtime_string']."</p>";
	    echo "<p><span class=\"info_moretitle\">Bitrate:</span> ".round(($fileinfo['bitrate']/1000),2)."kbps</p>";
	    echo "<p><span class=\"info_moretitle\">Channels:</span> ".$fileinfo['audio']['channels']."</p>";
	}
	if(in_array($file['ext'],$video)) //If file contains video data
	{
	    echo "<h4>More File Data</h4>\n";
	    echo "<p><span class=\"info_moretitle\">Duration:</span> ".$fileinfo['playtime_string']."</p>";
	    echo "<p><span class=\"info_moretitle\">Resolution:</span> ".$fileinfo['video']['resolution_x']."x".$fileinfo['video']['resolution_y']."</p>";
	    echo "<p><span class=\"info_moretitle\">Bitrate:</span> ".round(($fileinfo['bitrate']/1000),2)."kbps</p>";
	    echo "<p><span class=\"info_moretitle\">File Format:</span> ".$fileinfo['fileformat']."</p>";
	    echo "<p><span class=\"info_moretitle\">Codec:</span> ".$fileinfo['video']['codec']."</p>";
	}
	if(in_array($file['ext'],$images)) //If file is an image
	{
	    echo "<h4>EXIF Data (if available)</h4>\n";
	    echo "<p><span class=\"info_moretitle\">Date Taken:</span> ".$fileinfo[strtolower($file['ext'])]['exif']['EXIF']['DateTimeOriginal']."</p>";
	    echo "<p><span class=\"info_moretitle\">F-Stop:</span> ".$fileinfo[strtolower($file['ext'])]['exif']['COMPUTED']['ApertureFNumber']."</p>";
	    echo "<p><span class=\"info_moretitle\">Exposure:</span> ".$fileinfo[strtolower($file['ext'])]['exif']['EXIF']['ExposureTime']."</p>";
	    echo "<p><span class=\"info_moretitle\">ISO:</span> ".$fileinfo[strtolower($file['ext'])]['exif']['EXIF']['ISOSpeedRatings']."</p>";
	    echo "<p><span class=\"info_moretitle\">Flash:</span> ";
	    if($fileinfo[strtolower($file['ext'])]['exif']['EXIF']['Flash']){ echo "On</p>"; }
	    else{ echo "Off</p>"; }
	}
}
?>