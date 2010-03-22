<?php
session_start();
include_once('../includes/dbconnect.php');
include_once('../includes/security.php');
include_once('../includes/utilities.php');

if(!isset($_SESSION['adminID'])){ exit; }

if($_POST['userlist']){ outputUserList($_POST['page']); }
if($_POST['adminCheck']){ isUserAdmin($_POST['user'],true); }
if($_POST['banCheck']){ isUserBanned($_POST['user'],true); }

/* Global Information */
function getPublicFileStatus()
{
	$query = "SELECT publicListing FROM adminoptions LIMIT 1";
	$result = mysql_query($query);
	return ucfirst(reset(mysql_fetch_row($result)));
}

function getEmailOnRegStatus()
{
	$query = "SELECT emailOnReg FROM admin WHERE userID='".$_SESSION['adminID']."' LIMIT 1";
	$result = mysql_query($query);
	$enabled = reset(mysql_fetch_row($result));
	if($enabled=='true')
	{
		return 'Enabled';
	}
	else
	{
		return 'Disabled';
	}
}

function getUserRegStatus()
{
	$query = "SELECT userReg FROM adminoptions LIMIT 1";
	$result = mysql_query($query);
	return ucfirst(reset(mysql_fetch_row($result)));
}

function getUserLoginStatus()
{
	$query = "SELECT userLogin FROM adminoptions LIMIT 1";
	$result = mysql_query($query);
	return ucfirst(reset(mysql_fetch_row($result)));
}

function userExists($username)
{
	$username = secureContent($username);
	$query = "SELECT COUNT(*) FROM users WHERE username='$username' LIMIT 1";
	$result = mysql_query($query);
	return reset(mysql_fetch_row($result));
}

function getUserID($username)
{
	$username = secureContent($username);
	$query = "SELECT userID FROM users WHERE username='$username' LIMIT 1";
	$result = mysql_query($query);
	return reset(mysql_fetch_row($result));
}

function outputUserList($page)
{
	$users = getUserList($page,16);
	if(count($users)>0)
	{
	    $alt=false;
	    foreach($users as $user)
	    {
	    	echo "<li id=\"user_".$user['ID']."\" class=\"user";
	    	if($alt){ echo " alt"; $alt = false;}
	    	else{ $alt = true; }
	    	echo "\"><img src=\"../img/icons/user.png\" alt=\"user\" />&nbsp;<a href=\"javascript:getUserInfo('".$user['name']."')\">".$user['name']."</a></li>";
	    }
	}
	else{ echo "<li id=\"user_-1\" class=\"user\">No Users</li>"; }
}

function getNumUsers()
{
	$query = "SELECT COUNT(*) FROM users";
	$result = mysql_query($query);
	return reset(mysql_fetch_row($result));
}

function numActiveAccts()
{
	$query = "SELECT COUNT(*) FROM quotas WHERE spaceUsed!='0'";
	$result = mysql_query($query);
	return reset(mysql_fetch_row($result));
}

function getLastRegUser()
{
	$query = "SELECT username FROM users ORDER BY datejoined DESC LIMIT 1";
	$result = mysql_query($query);
	return reset(mysql_fetch_row($result));
}

function getLastUserLogin($num)
{
	$num = secureContent($num);
	$query = "SELECT username FROM users ORDER BY lastlogin DESC LIMIT $num";
	$result = mysql_query($query);
	while(list($username)=mysql_fetch_row($result))
	{
		$users[] = $username;
	}
	
	return $users;
}

function getUserMostData()
{
	$query = "SELECT userID,spaceUsed FROM quotas ORDER BY spaceUsed DESC LIMIT 1";
	$result = mysql_query($query);
	if($result)
	{
		while(list($userID,$spaceUsed)=mysql_fetch_row($result))
		{
			$query2 = "SELECT username FROM users WHERE userID='$userID'";
			$result2 = mysql_query($query2);
			$user = reset(mysql_fetch_row($result2));
			$spaceUsed = getPrettyFilesize($spaceUsed);
			return $user." ($spaceUsed)";
		} 
	}
	else
	{
		return "N/A";
	}
}

function getSpaceUsed()
{
	$query = "SELECT spaceUsed FROM quotas";
	$result = mysql_query($query);
	
	$space=0;
	while(list($spaceUsed)=mysql_fetch_row($result))
	{
		$space = $space + $spaceUsed;	
	}
	
	return getPrettyFilesize($space);
}

function getSpaceAvail()
{
	$query = "SELECT spaceAvail FROM quotas";
	$result = mysql_query($query);
	
	$space=0;
	while(list($spaceAvail)=mysql_fetch_row($result))
	{
		$space = $space + $spaceAvail;	
	}
	
	return getPrettyFilesize($space);
}

function getAvgSpace($inactive=true)
{
	$query = "SELECT spaceUsed FROM quotas";
	if(!$inactive){ $query .= " WHERE spaceUsed!=0"; }
	$result = mysql_query($query);
	
	$numUsers = mysql_num_rows($result);
	$space=0;
	while(list($spaceUsed)=mysql_fetch_row($result))
	{
		$space = $space + $spaceUsed;	
	}
	
	$avgSpace = $space/$numUsers;
	return getPrettyFilesize($avgSpace);
}

/* User List Functions */
function getUserList($page,$num)
{
	$page = secureContent($page);
	$num = secureContent($num);
	$lower = ($page-1)*$num;
	$upper = $lower+$num;
	$query = "SELECT userID,username FROM users ORDER BY username ASC LIMIT $lower,$upper";
	$result = mysql_query($query);
	
	$i=0;
	while(list($userID,$username)=mysql_fetch_row($result))
	{
		$users[$i]['ID'] = $userID;
		$users[$i]['name'] = $username;
		$i++;
	}
	
	return $users;
}

function getPagination($num)
{
	$query = "SELECT COUNT(*) FROM users";
	$result = mysql_query($query);
	return ceil(reset(mysql_fetch_row($result))/$num);
}

/* Single User Info */
function isUserAdmin($user,$echo=false)
{
	$user = secureContent($user);
	$userID = getUserID($user);
	$query = "SELECT COUNT(*) FROM admin WHERE userID='$userID'";
	$result = mysql_query($query);
	if($echo){ echo reset(mysql_fetch_row($result)); }
	else{ return reset(mysql_fetch_row($result)); }
}

function isUserBanned($user,$echo=false)
{
	$user = secureContent($user);
	$query = "SELECT COUNT(*) FROM banlist WHERE username='$user'";
	$result = mysql_query($query);
	if($echo){ echo reset(mysql_fetch_row($result)); }
	else{ return reset(mysql_fetch_row($result)); }
}

function getUserJoinedOn($user)
{
	$user = secureContent($user);
	$query = "SELECT datejoined FROM users WHERE username='$user' LIMIT 1";
	$result = mysql_query($query);
	$date = reset(mysql_fetch_row($result));
	return date('n/j/Y g:ia',$date);
}

function getUserEmailAddr($user)
{
	$user = secureContent($user);
	$query = "SELECT email FROM users WHERE username='$user' LIMIT 1";
	$result = mysql_query($query);
	return reset(mysql_fetch_row($result));
}

function getUserLastLogin($user)
{
	$user = secureContent($user);
	$query = "SELECT lastlogin FROM users WHERE username='$user' LIMIT 1";
	$result = mysql_query($query);
	$date = reset(mysql_fetch_row($result));
	if($date=='0'){ return "Never"; }
	else{ return date('n/j/Y g:ia',$date); }
}

function getUserLastIP($user)
{
	$user = secureContent($user);
	$query = "SELECT ipaddress FROM users WHERE username='$user' LIMIT 1";
	$result = mysql_query($query);
	return reset(mysql_fetch_row($result));
}

function getUserNumFiles($username,$perms='all')
{
	$username = secureContent($username);
	$perms = secureContent($perms);
	
	$userID = getUserID($username);
	
	$query2 = "SELECT COUNT(*) FROM files WHERE ownerID='$userID'";
	if($perms!='all')
	{
		$query2 .= " AND perms='$perms'";
	}
	$result2 = mysql_query($query2);
	return reset(mysql_fetch_row($result2));
}

function getUserNumFolders($username,$perms='all')
{
	$username = secureContent($username);
	$perms = secureContent($perms);
	
	$userID = getUserID($username);
	
	$query2 = "SELECT COUNT(*) FROM folders WHERE ownerID='$userID'";
	if($perms!='all')
	{
		$query2 .= " AND perms='$perms'";
	}
	$result2 = mysql_query($query2);
	return reset(mysql_fetch_row($result2));
}

function getUserSpaceUsed($username)
{
	$username = secureContent($username);
	
	$userID = getUserID($username);
	
	$query = "SELECT spaceUsed FROM quotas WHERE userID='$userID' LIMIT 1";
	$result = mysql_query($query);
	
	$space=0;
	while(list($spaceUsed)=mysql_fetch_row($result))
	{
		$space = $space + $spaceUsed;	
	}
	
	return getPrettyFilesize($space);
}

function getUserSpaceQuota($username)
{
	$username = secureContent($username);
	
	$userID = getUserID($username);
	
	$query = "SELECT spaceAvail FROM quotas WHERE userID='$userID' LIMIT 1";
	$result = mysql_query($query);
	
	$space=0;
	while(list($spaceAvail)=mysql_fetch_row($result))
	{
		$space = $space + $spaceAvail;	
	}
	
	return getPrettyFilesize($space);
}

function getLargestFileSize($username)
{
	$username = secureContent($username);
	
	$userID = getUserID($username);
	
	$query = "SELECT filesize FROM files WHERE ownerID='$userID' ORDER BY filesize DESC LIMIT 1";
	$result = mysql_query($query);
	$filesize = @reset(mysql_fetch_row($result));
	if($filesize){ return getPrettyFilesize($filesize); }
	else{ return "N/A"; }
}

function getNumberOfFileType($username,$type)
{
	include('../includes/filetypes.php');
	$username = secureContent($username);
	
	$userID = getUserID($username);
	
	$query = "SELECT filename FROM files WHERE ownerID='$userID'";
	$result = mysql_query($query);
	
	$count = 0;
	while(list($filename)=mysql_fetch_row($result))
	{
		$file = getPrettyFileExt($filename);
		if($type=='images')
		{
			if(in_array($file['ext'],$images)||in_array($file['ext'],$otherimages))
			{
				$count++;
			}
		}
		if($type=='audio')
		{
			if(in_array($file['ext'],$audio))
			{
				$count++;
			}
		}
		if($type=='video')
		{
			if(in_array($file['ext'],$video))
			{
				$count++;
			}
		}
	}
	
	return $count;
}

/* Banlist functions */
function getBannedUsers()
{
	$query = "SELECT banID,username,bandate,ipaddress FROM banlist WHERE username!='' ORDER BY username ASC";
	$result = mysql_query($query);
	
	$users = array();
	if($result&&mysql_num_rows($result)>0)
	{
		$i=0;
		while(list($banID,$username,$bandate,$ipaddress)=mysql_fetch_row($result))
		{
			$users[$i]['banID'] = $banID;
			$users[$i]['username'] = $username;
			$users[$i]['bandate'] = date('n/j/Y g:ia',$bandate);
			$users[$i]['ipaddr'] = $ipaddress;
			$i++;
		}
		
		return $users;
	}
	else
	{
		return false;
	}
}

function getBannedIPs()
{
	$query = "SELECT banID,bandate,ipaddress FROM banlist WHERE username=''";
	$result = mysql_query($query);
	
	$ips = array();
	if($result&&mysql_num_rows($result)>0)
	{
		$i=0;
		while(list($banID,$bandate,$ipaddress)=mysql_fetch_row($result))
		{
			$ips[$i]['banID'] = $banID;
			$ips[$i]['bandate'] = date('n/j/Y g:ia',$bandate);
			$ips[$i]['addr'] = $ipaddress;
			$i++;
		}
		
		return $ips;
	}
	else
	{
		return false;
	}
}
?>