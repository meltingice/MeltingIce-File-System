<?php
session_start();
include_once('../dbconnect.php'); //connects to database
include_once('../paths.php');
include_once('../osimo.php');
$osimo = new Osimo(); //makes magic happen

if($_GET['q']){ userSearch($_GET['q']); }

function userSearch($userQuery)
{
	$userQuery = secureContent($userQuery);
	
	$query = "SELECT username FROM users WHERE username LIKE '%$userQuery%'";
	$result = mysql_query($query);
	
	while(list($username)=mysql_fetch_row($result))
	{
		echo $username."\n";
	}
}
?>