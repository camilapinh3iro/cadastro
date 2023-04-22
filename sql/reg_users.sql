-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 31, 2012 at 08:08 PM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

-- Table structure for table `reg_users`
CREATE TABLE IF NOT EXISTS `reg_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ra` varchar(150) NOT NULL,
  `user_name` varchar(150) NOT NULL,
  `mac_address` varchar(25) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `course` varchar(45) NOT NULL,
  `registration_date` date NOT NULL,
  `expiration_date` date NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
