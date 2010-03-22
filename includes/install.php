<?php
/*
*	install.php
*	Install script for MFS2, make sure to edit dbconnect.php first!
*	Code copyright Ryan "MeltingIce" LeFevre
*/

/* Ajax event selectors */
if($_POST['dircheck']){ dirCheck(); }
if($_POST['checkmysql']){ checkMySQL(); }
if($_POST['createtable']){ createTable($_POST['createtable']); }

function dirCheck()
{
	$filechecks = array();
	$filechecks[] = file_exists("dbconnect.php");
	$filechecks[] = file_exists("../user/index.php");
	
	if($filechecks[0]==false||$filechecks[1]==false){ echo "false"; }
	else{ echo "true"; }
	
}

function checkMySQL()
{
	include('dbconnect.php');
	if($mysqlconnect && $mysqlselectdb){ echo "true"; }
	else{ echo "false"; }
}

function createTable($table)
{
	include('dbconnect.php');
	
	if($table=="admin")
	{
		$query = "CREATE TABLE IF NOT EXISTS `admin` (
  			`userID` mediumint(8) unsigned NOT NULL default '0',
  			`adminType` enum('full','semi') NOT NULL default 'full',
  			UNIQUE KEY `userID` (`userID`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		
		$result = mysql_query($query);
		
		if($result)
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	if($table=="adminoptions")
	{
		$query = "CREATE TABLE IF NOT EXISTS `adminoptions` (
		  	`optionID` smallint(5) unsigned NOT NULL auto_increment,
  			`defaultQuota` int(20) unsigned NOT NULL default '104857600',
  			PRIMARY KEY  (`optionID`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
			
		$result = mysql_query($query);
		
		if($result)
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	if($table=='files')
	{
		$query = "CREATE TABLE IF NOT EXISTS `files` (
  			`fileID` bigint(20) unsigned NOT NULL auto_increment,
  			`ownerID` mediumint(8) unsigned NOT NULL default '0',
  			`filename` varchar(255) NOT NULL default '',
  			`filesize` int(20) NOT NULL default '0',
  			`dateuploaded` int(20) NOT NULL default '0',
  			`lastmodified` int(20) NOT NULL default '0',
  			`perms` enum('public','private') NOT NULL default 'private',
  			`parentFolder` mediumint(9) NOT NULL default '-1',
  			PRIMARY KEY  (`fileID`),
  			KEY `filename` (`filename`),
  			KEY `ownerID` (`ownerID`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		
		$result = mysql_query($query);
		
		if($result)
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	if($table=='folders')
	{
		$query = "CREATE TABLE IF NOT EXISTS `folders` (
  			`folderID` mediumint(8) unsigned NOT NULL auto_increment,
  			`ownerID` mediumint(8) unsigned NOT NULL default '0',
  			`foldername` varchar(200) NOT NULL default '',
  			`datecreated` int(20) unsigned NOT NULL default '0',
  			PRIMARY KEY  (`folderID`),
  			KEY `userID` (`ownerID`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
		
		$result = mysql_query($query);
		
		if($result)
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	if($table=="quotas")
	{
		$query = "CREATE TABLE IF NOT EXISTS `quotas` (
  			`userID` mediumint(8) unsigned NOT NULL default '0',
  			`spaceUsed` mediumint(8) unsigned NOT NULL default '0',
  			`spaceAvail` mediumint(8) unsigned NOT NULL default '0',
  			UNIQUE KEY `userID` (`userID`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
			
		$result = mysql_query($query);
		
		if($result)
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	if($table=='users')
	{
		$query = "CREATE TABLE IF NOT EXISTS `users` (
 			 `userID` mediumint(8) unsigned NOT NULL auto_increment,
 			 `username` varchar(200) NOT NULL default '',
 			 `password` varchar(255) NOT NULL default '',
 			 `email` varchar(200) NOT NULL default '',
 			 `datejoined` int(20) NOT NULL default '0',
 			 `ipaddress` varchar(20) NOT NULL default '',
 			 PRIMARY KEY  (`userID`),
 			 UNIQUE KEY `username` (`username`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

		$result = mysql_query($query);
		
		if($result)
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
}
?>