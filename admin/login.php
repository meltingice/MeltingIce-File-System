<?php
/*
*	login.php
*	Logs a user into MFS2 Admin Panel and redirects to admin.php
*	Code copyright Ryan "MeltingIce" LeFevre
*/

session_start();

if(!isset($_POST['username'])){ header('Location: index.php'); }

include("../includes/dbconnect.php");
include("../includes/security.php");

$username = secureContent($_POST['username']);
$_password = hash('sha256',secureContent($_POST['password']));

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
    		/* Ok so this user exists and has entered their password... but are they an admin? */
    		$adminQuery = "SELECT userID FROM admin WHERE userID='$userID' LIMIT 1";
    		$adminResult = mysql_query($adminQuery);
    		if($adminResult&&mysql_num_rows($adminResult)>0)
    		{
    			$_SESSION['adminID'] = reset(mysql_fetch_row($adminResult));
    			$_SESSION['adminName'] = $username;
    			$_SESSION['adminIP'] = $ipaddress;
    			
    			header('Location: admin.php');
    		}
    		else
    		{
    			header('Location: index.php?login=notadmin');
    		}
    	}
    	else
    	{
    		/* Username found, password incorrect */
    		header('Location: index.php?login=passfail');
    	}
    }
    if($index==0)
    {
    	header('Location: index.php?login=userfail');
    }
}
else
{
    /* Username not found */
    header('Location: ../index.php?login=userfail');
}

mysql_close();
?>