<?php
session_start();
include_once('dbconnect.php');
include_once('security.php');

if(!isset($_SESSION['username'])||$_SESSION['username']!=$_SESSION['activeUser']){ exit; }

function getUploadMethod()
{
	$query = "SELECT uploadMethod FROM users WHERE userID='".$_SESSION['userID']."' LIMIT 1";
	$result = mysql_query($query);
	return ucfirst(reset(mysql_fetch_row($result)));
}
?>