-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 05, 2024 at 08:44 AM
-- Server version: 10.5.20-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `id22113914_fms`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `cropsfield_id` int(11) NOT NULL,
  `activity_type` enum('Planting','Harvest','Irrigation','Fertilizing','Pest Control','Pruning','Mulching','Weeding','Disease Control','Plowing','Transplanting','Water Management','Soil Preparation','Completion') NOT NULL,
  `activity_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`activity_id`, `cropsfield_id`, `activity_type`, `activity_date`) VALUES
(225, 92, 'Soil Preparation', '2022-07-07'),
(226, 92, 'Planting', '2022-07-19'),
(227, 92, 'Soil Preparation', '2022-07-21'),
(228, 92, 'Transplanting', '2022-07-27'),
(229, 92, 'Irrigation', '2022-08-01'),
(230, 92, 'Fertilizing', '2022-08-01'),
(231, 92, 'Weeding', '2022-08-03'),
(232, 92, 'Pest Control', '2022-08-13'),
(233, 92, 'Disease Control', '2022-08-13'),
(234, 92, 'Water Management', '2022-08-21'),
(235, 92, 'Harvest', '2022-10-01'),
(238, 92, 'Completion', '2022-10-02'),
(239, 94, 'Plowing', '2022-11-15'),
(240, 94, 'Soil Preparation', '2022-11-20'),
(241, 94, 'Planting', '2022-11-30'),
(242, 94, 'Irrigation', '2022-12-01'),
(243, 94, 'Fertilizing', '2022-12-01'),
(244, 94, 'Weeding', '2022-12-02'),
(245, 94, 'Pest Control', '2022-12-17'),
(246, 94, 'Mulching', '2022-12-27'),
(247, 94, 'Pruning', '2023-01-01'),
(248, 94, 'Disease Control', '2023-01-06'),
(249, 94, 'Harvest', '2023-01-18'),
(250, 94, 'Completion', '2023-01-19'),
(251, 95, 'Plowing', '2022-11-15'),
(252, 95, 'Soil Preparation', '2022-11-20'),
(253, 95, 'Planting', '2022-11-30'),
(254, 95, 'Irrigation', '2022-12-01'),
(255, 95, 'Fertilizing', '2022-12-01'),
(256, 95, 'Weeding', '2022-12-02'),
(257, 95, 'Pest Control', '2022-12-17'),
(258, 95, 'Mulching', '2022-12-27'),
(259, 95, 'Pruning', '2023-01-01'),
(260, 95, 'Disease Control', '2023-01-06'),
(261, 95, 'Harvest', '2023-01-25'),
(262, 95, 'Completion', '2023-01-26'),
(263, 96, 'Soil Preparation', '2023-07-07'),
(264, 96, 'Soil Preparation', '2023-07-19'),
(266, 96, 'Transplanting', '2023-07-31'),
(267, 96, 'Irrigation', '2023-08-01'),
(269, 96, 'Fertilizing', '2023-08-01'),
(270, 96, 'Weeding', '2023-08-03'),
(271, 96, 'Pest Control', '2023-08-13'),
(272, 96, 'Disease Control', '2023-08-13'),
(273, 96, 'Water Management', '2023-08-21'),
(274, 96, 'Planting', '2023-07-19'),
(275, 96, 'Harvest', '2023-09-07'),
(276, 96, 'Completion', '2023-09-09'),
(277, 97, 'Plowing', '2023-03-02'),
(278, 97, 'Soil Preparation', '2023-03-07'),
(279, 97, 'Planting', '2023-03-17'),
(280, 97, 'Fertilizing', '2023-03-18'),
(281, 97, 'Weeding', '2023-03-19'),
(282, 97, 'Irrigation', '2023-04-01'),
(283, 97, 'Mulching', '2023-04-13'),
(284, 97, 'Pruning', '2022-04-18'),
(285, 97, 'Disease Control', '2023-04-23'),
(286, 97, 'Harvest', '2023-05-03'),
(287, 97, 'Completion', '2023-05-04'),
(288, 98, 'Plowing', '2023-03-02'),
(289, 98, 'Soil Preparation', '2023-03-07'),
(290, 98, 'Planting', '2023-03-17'),
(291, 98, 'Irrigation', '2023-03-18'),
(292, 98, 'Fertilizing', '2023-04-06'),
(293, 98, 'Weeding', '2023-04-07'),
(294, 98, 'Pest Control', '2023-04-22'),
(295, 98, 'Mulching', '2023-05-02'),
(296, 98, 'Pruning', '2023-05-07'),
(297, 98, 'Disease Control', '2023-05-12'),
(298, 98, 'Harvest', '2023-05-17'),
(299, 98, 'Completion', '2023-05-18'),
(300, 99, 'Plowing', '2023-11-15'),
(301, 99, 'Soil Preparation', '2023-11-20'),
(302, 99, 'Planting', '2023-11-30'),
(303, 99, 'Irrigation', '2023-12-01'),
(304, 99, 'Fertilizing', '2023-12-01'),
(305, 99, 'Weeding', '2023-12-02'),
(306, 99, 'Pest Control', '2023-12-17'),
(307, 99, 'Mulching', '2023-12-27'),
(308, 99, 'Pruning', '2024-01-01'),
(309, 99, 'Disease Control', '2024-01-06'),
(310, 99, 'Harvest', '2024-01-18'),
(311, 99, 'Completion', '2024-01-19'),
(312, 100, 'Plowing', '2023-11-15'),
(313, 100, 'Soil Preparation', '2023-11-20'),
(314, 100, 'Planting', '2023-11-30'),
(315, 100, 'Irrigation', '2023-12-01'),
(316, 100, 'Fertilizing', '2023-12-01'),
(317, 100, 'Weeding', '2023-12-02'),
(318, 100, 'Pest Control', '2023-12-17'),
(319, 100, 'Mulching', '2023-12-27'),
(320, 100, 'Pruning', '2024-01-01'),
(321, 100, 'Disease Control', '2024-01-06'),
(322, 100, 'Harvest', '2024-01-24'),
(323, 100, 'Completion', '2024-01-26'),
(324, 101, 'Soil Preparation', '2023-04-01'),
(325, 101, 'Planting', '2023-04-15'),
(326, 101, 'Irrigation', '2023-04-15'),
(327, 101, 'Fertilizing', '2023-04-15'),
(328, 101, 'Pest Control', '2023-04-15'),
(329, 101, 'Weeding', '2023-04-15'),
(330, 101, 'Disease Control', '2023-04-15'),
(331, 101, 'Pruning', '2024-01-26'),
(332, 101, 'Harvest', '2024-04-26'),
(336, 101, 'Harvest', '2024-05-01'),
(338, 101, 'Completion', '2024-05-02'),
(354, 118, 'Planting', '2024-05-06');

-- --------------------------------------------------------

--
-- Table structure for table `crops`
--

CREATE TABLE `crops` (
  `crops_id` int(11) NOT NULL,
  `crop_name` varchar(50) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `default_sale_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crops`
--

INSERT INTO `crops` (`crops_id`, `crop_name`, `is_deleted`, `default_sale_price`) VALUES
(17, 'Watermelon', 0, 50.00),
(19, 'Melon', 0, 45.00),
(20, 'Rice', 0, 50.00),
(23, 'Sugarcane', 0, 18.00),
(33, 'corn', 0, 12.00),
(34, 'magic rice', 0, 52.00);

-- --------------------------------------------------------

--
-- Table structure for table `cropsfield`
--

CREATE TABLE `cropsfield` (
  `cropsfield_id` int(11) NOT NULL,
  `crops_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cropsfield`
--

INSERT INTO `cropsfield` (`cropsfield_id`, `crops_id`, `field_id`, `is_deleted`) VALUES
(92, 20, 28, 0),
(94, 17, 28, 0),
(95, 19, 28, 0),
(96, 20, 28, 0),
(97, 17, 28, 0),
(98, 19, 28, 0),
(99, 17, 28, 0),
(100, 19, 28, 0),
(101, 23, 30, 0),
(118, 20, 30, 0),
(120, 17, 28, 0);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `expense_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expense_id`, `activity_id`, `expense_amount`) VALUES
(154, 225, 15000.00),
(155, 226, 6000.00),
(156, 227, 8000.00),
(157, 228, 8000.00),
(158, 229, 12000.00),
(159, 230, 5000.00),
(160, 231, 12000.00),
(161, 232, 5000.00),
(162, 233, 5000.00),
(163, 234, 12000.00),
(164, 235, 15000.00),
(167, 238, 0.00),
(168, 239, 10000.00),
(169, 240, 20000.00),
(170, 241, 5000.00),
(171, 242, 13000.00),
(172, 243, 7500.00),
(173, 244, 12500.00),
(174, 245, 10000.00),
(175, 246, 5000.00),
(176, 247, 7500.00),
(177, 248, 8000.00),
(178, 249, 9000.00),
(179, 250, 1.00),
(180, 251, 12000.00),
(181, 252, 18000.00),
(182, 253, 5000.00),
(183, 254, 8500.00),
(184, 255, 6000.00),
(185, 256, 15000.00),
(186, 257, 12000.00),
(187, 258, 7000.00),
(188, 259, 10000.00),
(189, 260, 10000.00),
(190, 261, 15000.00),
(191, 262, 1.00),
(192, 263, 16000.00),
(193, 264, 10500.00),
(195, 266, 8000.00),
(196, 267, 18000.00),
(198, 269, 6000.00),
(199, 270, 13000.00),
(200, 271, 6000.00),
(201, 272, 6000.00),
(202, 273, 15000.00),
(203, 274, 6000.00),
(204, 275, 17000.00),
(205, 276, 1.00),
(206, 277, 13000.00),
(207, 278, 21000.00),
(208, 279, 5000.00),
(209, 280, 5000.00),
(210, 281, 16000.00),
(211, 282, 16000.00),
(212, 283, 7500.00),
(213, 284, 9000.00),
(214, 285, 11000.00),
(215, 286, 13000.00),
(216, 287, 1.00),
(217, 288, 15000.00),
(218, 289, 20000.00),
(219, 290, 5000.00),
(220, 291, 25000.00),
(221, 292, 8000.00),
(222, 293, 18000.00),
(223, 294, 16000.00),
(224, 295, 10000.00),
(225, 296, 9000.00),
(226, 297, 12000.00),
(227, 298, 10000.00),
(228, 299, 1.00),
(229, 300, 22000.00),
(230, 301, 27500.00),
(231, 302, 7000.00),
(232, 303, 32000.00),
(233, 304, 8000.00),
(234, 305, 25000.00),
(235, 306, 21000.00),
(236, 307, 14000.00),
(237, 308, 16000.00),
(238, 309, 18000.00),
(239, 310, 11000.00),
(240, 311, 1.00),
(241, 312, 22000.00),
(242, 313, 27500.00),
(243, 314, 7000.00),
(244, 315, 32000.00),
(245, 316, 8000.00),
(246, 317, 25000.00),
(247, 318, 21000.00),
(248, 319, 14000.00),
(249, 320, 16000.00),
(250, 321, 17000.00),
(251, 322, 13000.00),
(252, 323, 1.00),
(253, 324, 82500.00),
(254, 325, 105000.00),
(255, 326, 90000.00),
(256, 327, 75000.00),
(257, 328, 52500.00),
(258, 329, 37500.00),
(259, 330, 37500.00),
(260, 331, 30000.00),
(261, 332, 55000.00),
(265, 336, 20.00),
(267, 338, 1.00),
(285, 354, 12.00);

-- --------------------------------------------------------

--
-- Table structure for table `farm_details`
--

CREATE TABLE `farm_details` (
  `farm_id` int(11) NOT NULL,
  `farm_name` varchar(50) NOT NULL,
  `farm_location` varchar(100) NOT NULL,
  `farm_size` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farm_details`
--

INSERT INTO `farm_details` (`farm_id`, `farm_name`, `farm_location`, `farm_size`) VALUES
(1, 'JLagrisola Farm', 'Brgy. Kapito, Lian, Batangas', 7);

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `field_id` int(11) NOT NULL,
  `field_name` varchar(20) NOT NULL,
  `field_area` float NOT NULL,
  `field_status` varchar(20) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`field_id`, `field_name`, `field_area`, `field_status`, `is_deleted`) VALUES
(28, 'Left Field', 1.3, 'Active', 0),
(30, 'Right Field', 5, 'Active', 0),
(37, 'asd', 12, 'Active', 0),
(38, 'top', 5, 'Active', 0);

-- --------------------------------------------------------

--
-- Table structure for table `harvest`
--

CREATE TABLE `harvest` (
  `harvest_id` int(11) NOT NULL,
  `cropsfield_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `harvest_quantity` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `harvest`
--

INSERT INTO `harvest` (`harvest_id`, `cropsfield_id`, `activity_id`, `harvest_quantity`) VALUES
(79, 92, 235, 5850.00),
(81, 94, 249, 6000.00),
(82, 95, 261, 6000.00),
(83, 96, 275, 7500.00),
(84, 97, 286, 5400.00),
(85, 98, 298, 7000.00),
(86, 99, 310, 6000.00),
(87, 100, 322, 8000.00),
(88, 101, 332, 75000.00),
(89, 101, 336, 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `harvest_id` int(11) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `sales_quantity` decimal(10,2) DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `harvest_id`, `sale_date`, `sales_quantity`, `sale_price`) VALUES
(54, 79, '2022-10-07', 5850.00, 45.00),
(56, 81, '2023-01-20', 6000.00, 40.00),
(57, 82, '2023-01-26', 6000.00, 50.00),
(58, 83, '2023-10-17', 5500.00, 45.00),
(59, 84, '2023-05-12', 5400.00, 45.00),
(60, 85, '2023-05-19', 5000.00, 45.00),
(61, 86, '2024-01-22', 6000.00, 50.00),
(62, 87, '2024-01-28', 6000.00, 40.00),
(63, 88, '2024-04-28', 24000.00, 15.00),
(67, 88, '2024-05-02', 12000.00, 15.00),
(68, 88, '2024-05-02', 11000.00, 15.00),
(69, 88, '2024-05-02', 20000.00, 15.00),
(72, 88, '2024-04-28', 8000.00, 18.00),
(74, 83, '2023-09-29', 2000.00, 50.00),
(75, 87, '2024-01-26', 2000.00, 45.00),
(76, 85, '2023-05-19', 2000.00, 40.00),
(78, 89, '2024-05-07', 1.00, 18.00),
(79, 89, '2024-05-07', 2.00, 18.00),
(81, 89, '2024-05-08', 2.00, 18.00);

-- --------------------------------------------------------

--
-- Table structure for table `seeded_area`
--

CREATE TABLE `seeded_area` (
  `seededArea_id` int(11) NOT NULL,
  `cropsfield_id` int(11) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seeded_area`
--

INSERT INTO `seeded_area` (`seededArea_id`, `cropsfield_id`, `area`) VALUES
(51, 92, 1.30),
(53, 94, 0.50),
(54, 95, 0.50),
(56, 96, 1.30),
(57, 97, 0.50),
(58, 98, 0.50),
(59, 99, 0.50),
(60, 100, 0.55),
(61, 101, 3.00),
(71, 118, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `pass` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(20) NOT NULL,
  `role` enum('admin','user','pending','declined') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `email`, `pass`, `firstname`, `lastname`, `role`) VALUES
(24, 'admin@gmail.com', 'e3274be5c857fb42ab72d786e281b4b8', 'Admin', 'Admin', 'admin'),
(46, 'cubillajairus@gmail.com', '0192023a7bbd73250516f069df18b500', 'Jairus', 'Cubilla', 'user'),
(59, 'dsadsadas@gmail.com', 'saddsadas', 'adsad', 'dasdsa', 'admin'),
(60, 'tryngadaw@gmail.com', 'admin123', 'asdasd', 'dsadsadsa', 'admin'),
(62, 'trylast@gmail.com', '0192023a7bbd73250516f069df18b500', 'tryd', 'wewe', 'pending'),
(63, 'last@gmail.com', '0192023a7bbd73250516f069df18b500', 'Jairus', 'Justin', 'pending'),
(64, 'acantos677@gmail.com', '95193008e030a4fc6a728f68146a58c9', 'andrea', 'cantos', 'user'),
(65, 'leanmonteclaro123@gmail.com', '224495fcf50b4af427ddf8b91489b435', 'lean', 'alegre', 'user'),
(66, 'gavin@gmail.com', '33967a22d45b47270699892659388a3d', 'gavin', 'laysa', 'pending'),
(67, 'cubillajairus1@gmail.com', '0192023a7bbd73250516f069df18b500', 'fasfaf', 'safsafsa', 'pending'),
(68, 'ronnieberbie@gmail.com', 'c67c492543c0bf5e33f98a61ddfb4667', 'Ronnie', 'Berbie', 'pending'),
(69, 'Testing123@gmail.com', 'cc03e747a6afbbcbf8be7668acfebee5', 'Testing', 'Testing', 'pending'),
(70, 'sean@gmail.com', 'feed516f8ad036737189efce269d5936', 'sean', 'macalalad', 'user'),
(71, 'trytest@gmail.com', '0192023a7bbd73250516f069df18b500', 'sadasda', 'dsadsada', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `activities_ibfk_1` (`cropsfield_id`);

--
-- Indexes for table `crops`
--
ALTER TABLE `crops`
  ADD PRIMARY KEY (`crops_id`);

--
-- Indexes for table `cropsfield`
--
ALTER TABLE `cropsfield`
  ADD PRIMARY KEY (`cropsfield_id`),
  ADD KEY `fk_crops_fields` (`field_id`),
  ADD KEY `fk_cropsfield_crops` (`crops_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `farm_details`
--
ALTER TABLE `farm_details`
  ADD PRIMARY KEY (`farm_id`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`field_id`);

--
-- Indexes for table `harvest`
--
ALTER TABLE `harvest`
  ADD PRIMARY KEY (`harvest_id`),
  ADD KEY `cropsfield_id` (`cropsfield_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `harvest_id` (`harvest_id`);

--
-- Indexes for table `seeded_area`
--
ALTER TABLE `seeded_area`
  ADD PRIMARY KEY (`seededArea_id`),
  ADD KEY `cropsfield_id` (`cropsfield_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=356;

--
-- AUTO_INCREMENT for table `crops`
--
ALTER TABLE `crops`
  MODIFY `crops_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `cropsfield`
--
ALTER TABLE `cropsfield`
  MODIFY `cropsfield_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `farm_details`
--
ALTER TABLE `farm_details`
  MODIFY `farm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `field_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `harvest`
--
ALTER TABLE `harvest`
  MODIFY `harvest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `seeded_area`
--
ALTER TABLE `seeded_area`
  MODIFY `seededArea_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`cropsfield_id`) REFERENCES `cropsfield` (`cropsfield_id`) ON DELETE CASCADE;

--
-- Constraints for table `cropsfield`
--
ALTER TABLE `cropsfield`
  ADD CONSTRAINT `fk_cropsfield_crops` FOREIGN KEY (`crops_id`) REFERENCES `crops` (`crops_id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`);

--
-- Constraints for table `harvest`
--
ALTER TABLE `harvest`
  ADD CONSTRAINT `harvest_ibfk_1` FOREIGN KEY (`cropsfield_id`) REFERENCES `cropsfield` (`cropsfield_id`),
  ADD CONSTRAINT `harvest_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`harvest_id`) REFERENCES `harvest` (`harvest_id`);

--
-- Constraints for table `seeded_area`
--
ALTER TABLE `seeded_area`
  ADD CONSTRAINT `seeded_area_ibfk_1` FOREIGN KEY (`cropsfield_id`) REFERENCES `cropsfield` (`cropsfield_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
