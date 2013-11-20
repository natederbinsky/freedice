-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 25, 2011 at 08:32 AM
-- Server version: 5.1.37
-- PHP Version: 5.2.10-2ubuntu6.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dice_v1`
--

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE IF NOT EXISTS `actions` (
  `action_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`action_id`),
  KEY `action_name` (`action_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`action_id`, `action_name`) VALUES
(1, 'bid'),
(2, 'challenge'),
(3, 'exact'),
(4, 'pass'),
(5, 'push'),
(8, 'accept');

-- --------------------------------------------------------

--
-- Table structure for table `action_logs`
--

CREATE TABLE IF NOT EXISTS `action_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `round_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `value` varchar(10) NOT NULL,
  `result` varchar(10) NOT NULL,
  `extra` varchar(250) NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_id` (`log_id`,`round_id`),
  KEY `round_id` (`round_id`,`result`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `action_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE IF NOT EXISTS `colors` (
  `color_id` int(11) NOT NULL AUTO_INCREMENT,
  `color_name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`color_id`),
  KEY `color_name` (`color_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`color_id`, `color_name`) VALUES
(1, 'red'),
(2, 'blue'),
(3, 'green'),
(4, 'white'),
(5, 'black'),
(6, 'orange'),
(7, 'purple'),
(8, 'clear');

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE IF NOT EXISTS `games` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_name` varchar(250) NOT NULL DEFAULT '',
  `game_pw` varchar(50) NOT NULL DEFAULT '',
  `game_status` int(11) NOT NULL,
  `dice_start` int(11) NOT NULL,
  `game_admin` int(11) NOT NULL,
  `game_emails` varchar(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `games`
--


-- --------------------------------------------------------

--
-- Table structure for table `game_players`
--

CREATE TABLE IF NOT EXISTS `game_players` (
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `dice_color` int(11) NOT NULL,
  `cup` varchar(50) NOT NULL DEFAULT '',
  `shown` varchar(50) NOT NULL,
  `play_order` int(11) NOT NULL,
  `exact_used` varchar(1) NOT NULL DEFAULT 'F',
  PRIMARY KEY (`game_id`,`player_id`),
  KEY `game_id` (`game_id`,`play_order`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `game_players`
--


-- --------------------------------------------------------

--
-- Table structure for table `game_statuses`
--

CREATE TABLE IF NOT EXISTS `game_statuses` (
  `game_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_status_name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`game_status_id`),
  KEY `game_status_name` (`game_status_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `game_statuses`
--

INSERT INTO `game_statuses` (`game_status_id`, `game_status_name`) VALUES
(1, 'Awaiting Players'),
(2, 'In Progress'),
(3, 'Finished');

-- --------------------------------------------------------

--
-- Table structure for table `msgs`
--

CREATE TABLE IF NOT EXISTS `msgs` (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `msg` varchar(250) NOT NULL,
  PRIMARY KEY (`msg_id`),
  KEY `msg_id` (`msg_id`,`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `msgs`
--


-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `player_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_name` varchar(250) NOT NULL DEFAULT '',
  `player_pw` varchar(50) NOT NULL DEFAULT '',
  `player_email` varchar(250) NOT NULL DEFAULT '',
  `auto_refresh` int(10) NOT NULL DEFAULT '10',
  `track_stats` varchar(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`player_id`),
  KEY `player_name` (`player_name`,`player_email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `players`
--


-- --------------------------------------------------------

--
-- Table structure for table `rounds`
--

CREATE TABLE IF NOT EXISTS `rounds` (
  `round_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `special_rules` varchar(10) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`round_id`),
  KEY `round_id` (`round_id`,`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `rounds`
--
