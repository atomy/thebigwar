-- phpMyAdmin SQL Dump
-- version 2.11.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 16. Juli 2010 um 02:42
-- Server Version: 5.0.90
-- PHP-Version: 5.2.13-pl0-gentoo

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `tbwtest`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ticketmessages`
--

CREATE TABLE IF NOT EXISTS `ticketmessages` (
  `id` int(11) NOT NULL auto_increment,
  `ticketid` int(11) NOT NULL,
  `message` varchar(2048) NOT NULL,
  `username` varchar(32) NOT NULL,
  `gameoperator` tinyint(1) NOT NULL,
  `time_created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `ticketmessages`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL auto_increment,
  `reporter` varchar(32) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `subject` varchar(128) NOT NULL,
  `time_created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `tickets`
--

