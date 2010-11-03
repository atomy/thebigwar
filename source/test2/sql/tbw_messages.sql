-- phpMyAdmin SQL Dump
-- version 2.11.9.4
-- http://www.phpmyadmin.net
--
-- Host: db.jackinpoint.net
-- Generation Time: Nov 02, 2010 at 11:33 PM
-- Server version: 5.1.50
-- PHP Version: 5.3.3-pl1-gentoo

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `atomtest`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbw_messages`
--

DROP TABLE IF EXISTS `tbw_messages`;
CREATE TABLE IF NOT EXISTS `tbw_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `subject` text NOT NULL,
  `text` text NOT NULL,
  `time` int(11) NOT NULL,
  `toUser` int(11) NOT NULL,
  `fromUser` int(11) NOT NULL,
  `msgType` tinyint(4) NOT NULL,
  `msgRead` tinyint(1) NOT NULL,
  `isArchieved` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `tbw_messages`
--

INSERT INTO `tbw_messages` (`id`, `userid`, `subject`, `text`, `time`, `toUser`, `fromUser`, `msgType`, `msgRead`, `isArchieved`) VALUES
(23, 3, 'Re: testbetreff', 'aaiik', 1288455326, 3, 2, 5, 1, 0);
