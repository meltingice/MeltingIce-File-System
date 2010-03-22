<?php
include_once('includes/filemgmt.php');
include_once('includes/foldermgmt.php');
include_once('includes/quotafunc.php');
include_once('includes/design.php');
include_once('includes/errormsg.php');

if($_GET['user'])
{
	/* Checks to see who is visiting and sets appropriate variables */
	if(!isset($_SESSION['username'])||$_SESSION['username']!=$_GET['user']){ $scope = 'public'; $_SESSION['activeUser'] = secureContent($_GET['user']); }
	else{ $scope='all'; $_SESSION['activeUser'] = secureContent($_SESSION['username']); }
}
else
{
	if(!isset($_SESSION['username'])){ header('Location: index.php?login=needed'); exit; }
	else{ $_SESSION['activeUser'] = secureContent($_SESSION['username']); $scope = 'all'; }
}
?>