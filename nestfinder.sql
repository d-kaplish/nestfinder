-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 10:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nestfinder`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `admin_id`, `action`, `target_type`, `target_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'status_changed', 'property', 15, 'Status changed to pending', NULL, '2026-04-06 20:28:56'),
(2, 1, 'status_changed', 'property', 18, 'Status changed to pending', NULL, '2026-04-06 22:59:44'),
(3, 1, 'status_changed', 'property', 21, 'Status changed to approved', NULL, '2026-04-06 23:10:16');

-- --------------------------------------------------------

--
-- Table structure for table `contact_queries`
--

CREATE TABLE `contact_queries` (
  `query_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pictures`
--

CREATE TABLE `pictures` (
  `pic_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pictures`
--

INSERT INTO `pictures` (`pic_id`, `property_id`, `file_name`) VALUES
(32, 11, 'property11_1774337628_0.jpg'),
(33, 11, 'property11_1774337628_1.jpg'),
(34, 11, 'property11_1774337628_2.jpg'),
(35, 11, 'property11_1774337628_3.jpg'),
(36, 11, 'property11_1774337628_4.jpg'),
(37, 12, 'property12_1774337738_1.jpg'),
(38, 12, 'property12_1774337738_2.jpg'),
(39, 12, 'property12_1774337738_4.jpg'),
(40, 12, 'property12_1774337738_5.jpg'),
(41, 12, 'property12_1774337738_6.jpg'),
(50, 15, 'property15_1775023450_0.jpg'),
(51, 15, 'property15_1775023450_1.jpg'),
(52, 15, 'property15_1775023450_2.jpg'),
(53, 15, 'property15_1775023450_3.jpg'),
(54, 15, 'property15_1775023450_4.jpg'),
(55, 15, 'property15_1775023450_5.jpg'),
(56, 15, 'property15_1775023450_6.jpg'),
(57, 15, 'property15_1775023450_7.jpg'),
(66, 17, 'property17_1775513805_0.jpg'),
(67, 17, 'property17_1775513805_1.jpg'),
(68, 17, 'property17_1775513805_2.jpg'),
(69, 17, 'property17_1775513805_4.jpg'),
(70, 17, 'property17_1775513805_5.jpg'),
(71, 17, 'property17_1775513805_6.jpg'),
(72, 17, 'property17_1775513805_7.jpg'),
(74, 17, 'property17_1775513805_9.jpg'),
(75, 17, 'property17_1775513805_10.jpg'),
(76, 18, 'property18_1775514529_0.jpg'),
(77, 18, 'property18_1775514529_2.jpg'),
(78, 18, 'property18_1775514529_3.jpg'),
(79, 18, 'property18_1775514529_4.jpg'),
(80, 18, 'property18_1775514529_5.jpg'),
(81, 18, 'property18_1775514529_6.jpg'),
(82, 18, 'property18_1775514529_7.jpg'),
(83, 18, 'property18_1775514529_8.jpg'),
(84, 19, 'property19_1775514809_0.jpeg'),
(85, 19, 'property19_1775514809_1.jpg'),
(86, 19, 'property19_1775514809_2.jpeg'),
(87, 19, 'property19_1775514809_3.jpg'),
(88, 19, 'property19_1775514809_4.jpg'),
(89, 20, 'property20_1775516625_0.jpg'),
(90, 20, 'property20_1775516625_1.jpg'),
(91, 20, 'property20_1775516625_2.jpg'),
(92, 20, 'property20_1775516625_3.jpg'),
(93, 20, 'property20_1775516625_4.jpeg'),
(94, 20, 'property20_1775516625_5.jpg'),
(95, 20, 'property20_1775516625_6.jpg'),
(96, 21, 'property21_1775516984_0.webp'),
(97, 21, 'property21_1775516984_1.jpg'),
(98, 21, 'property21_1775516984_2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `price` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('Rent','Sale') NOT NULL,
  `pictures` int(11) DEFAULT NULL,
  `property_type` varchar(50) DEFAULT NULL,
  `rooms` varchar(20) DEFAULT NULL,
  `parking` varchar(30) DEFAULT NULL,
  `preferred_tenant` varchar(30) DEFAULT NULL,
  `possession_date` varchar(50) DEFAULT NULL,
  `age_of_building` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `admin_notes` text DEFAULT NULL,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqft` int(11) DEFAULT NULL,
  `furnish` enum('fully','semi','unfurnished') NOT NULL DEFAULT 'unfurnished'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `image`, `location`, `price`, `user_id`, `type`, `pictures`, `property_type`, `rooms`, `parking`, `preferred_tenant`, `possession_date`, `age_of_building`, `status`, `admin_notes`, `view_count`, `created_at`, `sqft`, `furnish`) VALUES
(11, 'property_1774337628_3843.jpg', 'Kharadi', '18000', 6, 'Rent', NULL, 'Apartment', '1RK', 'No', 'Anyone', 'Immediate', '2 years', 'approved', NULL, 3, '2026-04-02 06:35:52', 1000, 'fully'),
(12, 'property_1774337737_3698.jpg', 'Viman Nagar', '2700000', 5, 'Sale', NULL, 'Independent House', '2BHK', '2 and 4 Wheeler', 'Girls', 'From 2026-03-30', '3 years', 'approved', NULL, 20, '2026-04-02 06:35:52', 800, 'semi'),
(15, 'property_1775023449_7957.jpg', 'Koregaon Park', '6500000', 6, 'Sale', NULL, 'Independent House', '2BHK', '2 and 4 Wheeler', 'Anyone', 'Immediate', '1 year', 'approved', '', 0, '2026-04-02 06:35:52', 1200, 'unfurnished'),
(17, 'property_1775513805_5930.jpg', 'Kalyani Nagar', '5500000', 7, 'Sale', NULL, 'Independent House', '2BHK', '2 and 4 Wheeler', 'Anyone', 'Immediate', '5 years', 'approved', NULL, 7, '2026-04-06 22:16:45', 2500, 'fully'),
(18, 'property_1775514529_6268.jpg', 'Pridewell City', '10000000', 8, 'Rent', NULL, 'Independent House', '3BHK', '2 and 4 Wheeler', 'Anyone', 'From 2026-04-30', '7 Years', 'approved', '', 4, '2026-04-06 22:28:49', 1700, 'fully'),
(19, 'property_1775514809_8195.jpg', 'Viman Nagar', '4800000', 8, 'Sale', NULL, 'Apartment', '1BHK', '2 Wheeler', 'Anyone', 'Within 15 days', '3 years', 'approved', NULL, 16, '2026-04-06 22:33:29', 970, 'semi'),
(20, 'property_1775516625_2055.jpg', 'Hadapsar', '20000', 9, 'Rent', NULL, 'Apartment', '2BHK', '4 Wheeler', 'Family', 'Immediate', '2 years', 'rejected', NULL, 0, '2026-04-06 23:03:45', 1220, 'unfurnished'),
(21, 'property_1775516984_5928.jpg', 'Kharadi', '12000', 5, 'Rent', NULL, 'Apartment', '1RK', '2 Wheeler', 'Student', 'Immediate', '3 years', 'rejected', '', 11, '2026-04-06 23:09:44', 500, 'semi');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `report_reason` text NOT NULL,
  `status` enum('pending','reviewed','resolved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `property_id`, `user_id`, `report_type`, `report_reason`, `status`, `created_at`) VALUES
(1, 12, 5, 'fake_listing', 'Listing is not real it has google images.', 'reviewed', '2026-04-06 20:58:17'),
(2, 11, 5, 'already_sold', 'sold', 'pending', '2026-04-06 21:09:50'),
(3, 21, 5, 'fake_listing', 'fake listing', 'pending', '2026-04-07 06:58:37');

-- --------------------------------------------------------

--
-- Table structure for table `shortlist`
--

CREATE TABLE `shortlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shortlist`
--

INSERT INTO `shortlist` (`id`, `user_id`, `property_id`, `created_at`) VALUES
(5, 1, 12, '2026-04-02 08:06:13'),
(6, 3, 12, '2026-04-02 08:06:13'),
(7, 1, 11, '2026-04-02 08:06:13'),
(9, 5, 15, '2026-04-04 16:26:23'),
(10, 5, 12, '2026-04-04 16:26:40'),
(11, 8, 17, '2026-04-06 22:23:54'),
(12, 9, 17, '2026-04-06 23:00:58'),
(13, 9, 19, '2026-04-06 23:01:04'),
(14, 7, 21, '2026-04-06 23:10:40'),
(15, 7, 19, '2026-04-06 23:10:45'),
(16, 7, 18, '2026-04-06 23:10:52');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `updated_at`) VALUES
(1, 'site_name', 'NestFinder', 'text', '2026-04-02 06:35:52'),
(2, 'site_email', 'contact@nestfinder.com', 'text', '2026-04-02 06:35:52'),
(3, 'site_phone', '+91 9876543210', 'text', '2026-04-02 06:35:52'),
(4, 'maintenance_mode', '1', 'boolean', '2026-04-06 20:27:03'),
(5, 'property_approval_required', '1', 'boolean', '2026-04-06 20:28:40'),
(6, 'contact_address', 'Mumbai, India', 'text', '2026-04-02 06:35:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed` datetime DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `contact`, `profile_picture`, `password`, `is_admin`, `created_at`, `failed_attempts`, `last_failed`, `remember_token`) VALUES
(1, 'Daneshwari', 'daneshwari@gmail.com', '9888807488', 'admin_1_1775507867.jpg', '$2y$10$1FccAt6aDCYDoUXR1g2EKuv/k0rc22Eb2ztso8iwrUBgU9pDFb7wW', 1, '2026-02-05 08:55:06', 0, '2026-06-09 18:19:15', NULL),
(3, 'Bhoomika', 'bhoomika@gmail.com', '1234567890', NULL, '$2y$10$9/wX9f/TLI/reC6XbwVIRuB.k1TrXXxMgtCCQKTUb2nTwvFwZX/RS', 1, '2026-03-27 05:21:10', 0, NULL, '44cd084510a9dced28230f4dd755ad3ba08b58e5c4ce4a8740144250af63c34c'),
(5, 'Vedanti Deshmukh', 'vedanti@gmail.com', '9309121477', 'profile_5_1775505237.jpg', '$2y$10$CsMTkgjJZwMJP/ZHG37whuMZjN0q21yeQh4BoNS71DCiPRrPU2mF6', 0, '2026-04-06 17:29:45', 0, '2026-04-07 12:26:08', NULL),
(6, 'Saloni', 'saloni@gmail.com', '0987654321', NULL, '$2y$10$FcZhlqD8UDgiHQMFwCpZ3.5W3jnB3MTP8B697cLdfedyaS.vCjlmq', 0, '2026-04-06 21:20:38', 0, NULL, NULL),
(7, 'Prisha', 'prisha@gmail.com', '1234567890', 'profile_7_1775517063.jpg', '$2y$10$TxWjhCp1W1dMps.V4hcGRuJ1D.IfRWfiOvab/OyVsK/pc/DFvWfTK', 0, '2026-04-06 21:41:31', 0, NULL, NULL),
(8, 'Khushi', 'khushi@gmail.com', '9888807488', 'profile_8_1775514250.jpg', '$2y$10$lnLCc8quudR1I9NeAUwhOOlmipUU91b3F9HBIMud5ehspVnzpUXym', 0, '2026-04-06 22:23:37', 0, NULL, NULL),
(9, 'Prachi', 'prachi@gmail.com', '1234567890', 'profile_9_1775516755.jpg', '$2y$10$cACvBnZooWHEimuhRuPHU.zFZ.EkeyJtwK75PmdldcjNVTe8uTY.K', 0, '2026-04-06 23:00:38', 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `contact_queries`
--
ALTER TABLE `contact_queries`
  ADD PRIMARY KEY (`query_id`);

--
-- Indexes for table `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`pic_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shortlist`
--
ALTER TABLE `shortlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`property_id`),
  ADD KEY `idx_user_property` (`user_id`,`property_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_is_admin` (`is_admin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_queries`
--
ALTER TABLE `contact_queries`
  MODIFY `query_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pictures`
--
ALTER TABLE `pictures`
  MODIFY `pic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shortlist`
--
ALTER TABLE `shortlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `pictures`
--
ALTER TABLE `pictures`
  ADD CONSTRAINT `pictures_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
