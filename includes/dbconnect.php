<?php
/*
*	dbconnect.php
*	Opens a MySQL connection, don't forget to close it!
*	Code copyright Ryan "MeltingIce" LeFevre
*/

	$mysqlhost = "localhost";
	$mysqluser = "username";
	$mysqlpass = "password";
	$mysqldb = "dbname";
	$mysqlconnect = @mysql_pconnect($mysqlhost, $mysqluser, $mysqlpass) or die('Could not connect to MySQL Server!');
	$mysqlselectdb = @mysql_select_db($mysqldb)or die('Could not select database!');
	
	/* 
	*	These filetypes should never be allowed in a public environment because they could easily allow
	*	anyone full access to your server.  One command could wipe your entire server clean.  Feel free to add to
	*	this list if you think of any I missed.  If a file with any of these extentions is uploaded, its extention
	*	will be automatically changed to .txt
	*/
	$disallow = array("php","ph3","ph4","ph5","php5","php4","htaccess","cgi","pl","rb","asp","shtml");
?>