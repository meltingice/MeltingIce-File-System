<?php
/*
*	login.php
*	Logs a user into MFS2 and redirects to home.php
*	Code copyright Ryan "MeltingIce" LeFevre
*/

session_start();

if(!isset($_POST['username'])){ header('Location: ../index.php'); }

include("dbconnect.php");
include("security.php");

$username = secureContent($_POST['username']);
$_password = hash('sha256',secureContent($_POST['password']));

/* Check to make sure user/IP isn't banned first */
$ip = $_SERVER['REMOTE_ADDR'];
$banQuery1 = "SELECT COUNT(*) FROM banlist WHERE username='$username'";
$banResult1 = mysql_query($banQuery1);
$banCheck1 = reset(mysql_fetch_row($banResult1));
if(!$banCheck1) // ok the username isn't banned, check for the IP
{
	$banQuery2 = "SELECT COUNT(*) FROM banlist WHERE username='' AND ipaddress='$ip'";
	$banResult2 = mysql_query($banQuery2);
	if(reset(mysql_fetch_row($banResult2)))
	{
		header('Location: ../index.php?login=ipbanned'); exit;
	}
}
else
{
	header('Location: ../index.php?login=userbanned'); exit;
}

/* Made it through the ban checks, continue with login */
$query = "SELECT `userID`, `password`, `ipaddress` FROM users WHERE username='$username' LIMIT 1";
$result = mysql_query($query);

if($result)
{
	$index=0;
    while(list($userID,$password,$ipaddress)=mysql_fetch_row($result))
    {
    	$index++;
    	if($_password==$password)
    	{
    		/* Welcome to MFS2, time to set some useful session variables */
    		$_SESSION['userID'] = $userID;
    		$_SESSION['username'] = $username;
    		$_SESSION['ipaddress'] = $ipaddress;
    		$lastlogin = time();
    		
    		/* Update users table with current time */
    		$query = "UPDATE users SET lastlogin='$lastlogin',ipaddress='".$_SERVER['REMOTE_ADDR']."' WHERE userID='$userID' LIMIT 1";
    		$result = mysql_query($query);
    		
    		header('Location: ../home.php');
    	}
    	else
    	{
    		/* Username found, password incorrect */
    		header('Location: ../index.php?login=passfail');
    	}
    }
    if($index==0)
    {
    	header('Location: ../index.php?login=userfail');
    }
}
else
{
    /* Username not found */
    header('Location: ../index.php?login=userfail');
}

mysql_close();
?>