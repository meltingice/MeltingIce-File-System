-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 17, 2008 at 04:37 PM
-- Server version: 4.1.22
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `meltingi_mfs2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `userID` mediumint(8) unsigned NOT NULL default '0',
  `adminType` enum('full','semi') NOT NULL default 'full',
  `emailOnReg` enum('true','false') NOT NULL default 'true',
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adminoptions`
--

CREATE TABLE IF NOT EXISTS `adminoptions` (
  `optionID` smallint(5) unsigned NOT NULL auto_increment,
  `defaultQuota` int(20) unsigned NOT NULL default '104857600',
  `publicListing` enum('enabled','disabled') NOT NULL default 'enabled',
  `userReg` enum('enabled','disabled') NOT NULL default 'enabled',
  `userLogin` enum('enabled','disabled') NOT NULL default 'enabled',
  PRIMARY KEY  (`optionID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `banlist`
--

CREATE TABLE IF NOT EXISTS `banlist` (
  `banID` mediumint(8) unsigned NOT NULL auto_increment,
  `userID` mediumint(8) unsigned NOT NULL default '0',
  `username` varchar(120) NOT NULL default '',
  `bandate` int(20) unsigned NOT NULL default '0',
  `ipaddress` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`banID`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE IF NOT EXISTS `folders` (
  `folderID` mediumint(8) unsigned NOT NULL auto_increment,
  `ownerID` mediumint(8) unsigned NOT NULL default '0',
  `foldername` varchar(200) NOT NULL default '',
  `datecreated` int(20) unsigned NOT NULL default '0',
  `perms` enum('public','private') NOT NULL default 'private',
  PRIMARY KEY  (`folderID`),
  KEY `userID` (`ownerID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quotas`
--

CREATE TABLE IF NOT EXISTS `quotas` (
  `userID` mediumint(8) unsigned NOT NULL default '0',
  `spaceUsed` int(20) unsigned NOT NULL default '0',
  `spaceAvail` int(20) unsigned NOT NULL default '0',
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userID` mediumint(8) unsigned NOT NULL auto_increment,
  `username` varchar(200) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `email` varchar(200) NOT NULL default '',
  `datejoined` int(20) NOT NULL default '0',
  `ipaddress` varchar(20) NOT NULL default '',
  `lastlogin` int(20) unsigned NOT NULL default '0',
  `uploadMethod` enum('flash','http') NOT NULL default 'flash',
  PRIMARY KEY  (`userID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
