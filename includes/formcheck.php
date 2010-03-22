<?php
/*
*	formcheck.php
*	Form validation backend that requires MySQL calls
*	Code copyright Ryan "MeltingIce" LeFevre
*/

session_start();

if(!isset($_POST['username'])){ header('Location: ../index.php'); }

include('dbconnect.php');
include('security.php');

if($_POST['username']){ checkUsername($_POST['username']); }

function checkUsername($username)
{
	$username = secureContent($username);
	
	$query = "SELECT userID FROM users WHERE username='$username' LIMIT 1";
	$result = mysql_query($query);
	
	if(mysql_num_rows($result)==false||mysql_num_rows($result)==0)
	{
		echo "true";
	}
	else
	{
		echo "false";
	}
}

mysql_close();
?>