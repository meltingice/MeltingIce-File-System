<?php
function getErrorMsg()
{
	if($_GET['login']=='passfail')
	{
		return 'The password entered was incorrect.';
	}
	if($_GET['login']=='userfail')
	{
		return 'This username does not exist.';
	}
	if($_GET['login']=='needed')
	{
		return 'You must log in to view this page.';
	}
	if($_GET['login']=='notadmin')
	{
		return 'This user does not have admin privileges.';
	}
	if($_GET['login']=='userbanned')
	{
		return 'This username has been banned.';
	}
	if($_GET['login']=='ipbanned')
	{
		return 'This IP address has been banned.';
	}
	if($_GET['logout']=='true')
	{
		return 'You have been logged out.';
	}
	if($_GET['register']=='email')
	{
		return 'The email addresses entered are either invalid or do not match.';
	}
	if($_GET['register']=='username')
	{
		return 'The username entered is invalid, please try something different.';
	}
	if($_GET['register']=='true')
	{
		return 'You have successfully registered, log in below.';
	}
	if($_GET['register']=='false')
	{
		return 'Registration failed, please try again.';
	}
	if($_GET['upload']=='finished')
	{
		return 'The file(s) have been uploaded.';
	}
	if($_GET['upload']=='filefail'||$_GET['upload']=='dbfail')
	{
		return 'There was an error uploading your file(s).';
	}
}
?>