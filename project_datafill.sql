-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2018 at 01:05 AM
-- Server version: 10.1.29-MariaDB
-- PHP Version: 7.2.0

--
-- Database: `deck_db`
--

--
-- Dumping data for table `deck_user`
--

INSERT INTO `deck_user` (`user_id`, `email`, `username`, `password`, `profile_pic`, `registration_date`) VALUES
(1, 'mitchy@mitch.com', 'MitchyBeMitchin', '3076c5841012e76899737bd0a3d251ec1bf1a97f', '82108477a6b0b35448e8510b40dbedf7ea8f45af.jpeg', '2018-04-26 13:24:43'),
(2, 'qwerty@gmail.com', 'QWERTY', 'b1b3773a05c0ed0176787a4f1574ff0075f7521e', 'd81b5d853870d9f73d2efa962618556561e5295c.png', '2018-04-26 13:27:03'),
(3, 'artlover@yahoo.com', 'art_lover96', '4f468a6824d620bf0f58640c0bc423bcb35dc48f', NULL, '2018-04-26 13:33:07'),
(4, 'pcopus@ucmo.edu', 'copus', '22ea1c649c82946aa6e479e1ffd321e4a318b1b0', 'b4ba31e8483275f785ec56c8b05a9a6334ded33a.png', '2018-04-26 14:30:03');

--
-- Dumping data for table `deck`
--

INSERT INTO `deck` (`deck_id`, `creator_id`, `file_name`, `name`, `uploaded`) VALUES
(1, 1, '40fcb837bdb489ce3a2e1aa4512f10348790dab7.xml', 'Band Geeks', '2018-04-26 13:25:56'),
(2, 1, '717f5b6ae7bed88a7bafabd2d47f1102d5e41de0.xml', 'G4M3R', '2018-04-26 13:26:13'),
(3, 2, 'ae4f86e41b7f8f92e5fcacc55db04cd806300ac9.xml', 'Magic the Gathering Players', '2018-04-26 13:31:31'),
(4, 3, '3b91cc56d51c2c312bc9327f68616e92d9bdd01d.xml', 'Artist Life', '2018-04-26 13:33:23'),
(5, 4, 'b2eae9a94f12844395989188eb3d20bb95ca93f5.xml', 'Programmer Humor', '2018-04-26 14:30:55');

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`genre_id`, `title`) VALUES
(3, 'College'),
(12, 'Family-Friendly'),
(2, 'Fine Art'),
(1, 'Funny'),
(5, 'Gaming'),
(13, 'STEM');

--
-- Dumping data for table `map_deck_to_genre`
--

INSERT INTO `map_deck_to_genre` (`deck_id`, `genre_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 5),
(3, 1),
(3, 5),
(4, 1),
(4, 2),
(4, 3),
(5, 1),
(5, 12),
(5, 13);

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`user_id`, `deck_id`, `rating`) VALUES
(1, 1, 4),
(1, 3, 2),
(2, 3, 4),
(3, 1, 4),
(3, 2, 2),
(3, 3, 5),
(4, 5, 5);