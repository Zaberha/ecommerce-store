-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 02:44 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u547298449_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `abandoned_carts`
--

CREATE TABLE `abandoned_carts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cart_data` text NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reminded` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `actual_addresses`
--

CREATE TABLE `actual_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `country` varchar(100) DEFAULT 'your Country',
  `city` varchar(100) DEFAULT 'your City',
  `street` varchar(100) DEFAULT 'your Street',
  `building_name` varchar(100) DEFAULT 'your building Name',
  `building_number` varchar(100) DEFAULT 'your Building No.',
  `floor_number` varchar(100) DEFAULT 'Alternative Phone No.',
  `flat_number` varchar(15) NOT NULL,
  `alternative_phone` varchar(100) DEFAULT NULL,
  `order_id` tinyint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `actual_addresses`
--

INSERT INTO `actual_addresses` (`id`, `user_id`, `country`, `city`, `street`, `building_name`, `building_number`, `floor_number`, `flat_number`, `alternative_phone`, `order_id`) VALUES
(32, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '2', '+971561599855', 71),
(35, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '', '+971561599855', 74),
(36, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '', '+971561599855', 75),
(37, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '2', '+971561599855', 76),
(38, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '2', '+971561599855', 77),
(39, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '2', '+971561599855', 78),
(40, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '2', '+971561599855', 79);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `store_name` varchar(255) NOT NULL,
  `store_phone` varchar(20) NOT NULL,
  `store_email` varchar(255) NOT NULL,
  `store_address` varchar(255) NOT NULL,
  `store_city` varchar(100) NOT NULL,
  `store_country` varchar(100) NOT NULL,
  `admin_username` varchar(100) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `business_logo` varchar(255) DEFAULT NULL,
  `main_color` varchar(20) DEFAULT '#28a745',
  `second_color` varchar(20) DEFAULT '#007bff',
  `third_color` varchar(20) DEFAULT '#dc3545',
  `forth_color` varchar(20) NOT NULL,
  `font_family` varchar(100) DEFAULT 'Arial, sans-serif',
  `default_language` enum('English','Arabic') DEFAULT 'English',
  `default_currency` enum('AED','$','SP') DEFAULT 'AED',
  `currency_rate` decimal(10,2) DEFAULT 1.00,
  `tax_rate` enum('0%','5%','10%','11%','12%','15%','18%','20%') DEFAULT '0%',
  `facebook_link` varchar(255) DEFAULT NULL,
  `instagram_link` varchar(255) DEFAULT NULL,
  `x_link` varchar(255) DEFAULT NULL,
  `tiktok_link` varchar(255) DEFAULT NULL,
  `snapchat_link` varchar(255) DEFAULT NULL,
  `linkedin_link` varchar(255) DEFAULT NULL,
  `google_business_link` varchar(255) DEFAULT NULL,
  `youtube_channel_link` varchar(255) DEFAULT NULL,
  `auto_registration_coupon` tinyint(1) DEFAULT 0,
  `registration_coupon_percentage` decimal(5,2) DEFAULT 10.00,
  `registration_coupon_expiry_days` int(11) DEFAULT 7,
  `loyalty_program_enabled` tinyint(1) DEFAULT 0,
  `COLLOYALTY_coupon_threshold` int(11) DEFAULT 100,
  `loyalty_points_rate` decimal(5,2) DEFAULT 1.00,
  `loyalty_coupon_percentage` decimal(5,2) DEFAULT 15.00,
  `loyalty_coupon_expiry_days` int(11) DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `store_name`, `store_phone`, `store_email`, `store_address`, `store_city`, `store_country`, `admin_username`, `admin_password`, `business_logo`, `main_color`, `second_color`, `third_color`, `forth_color`, `font_family`, `default_language`, `default_currency`, `currency_rate`, `tax_rate`, `facebook_link`, `instagram_link`, `x_link`, `tiktok_link`, `snapchat_link`, `linkedin_link`, `google_business_link`, `youtube_channel_link`, `auto_registration_coupon`, `registration_coupon_percentage`, `registration_coupon_expiry_days`, `loyalty_program_enabled`, `COLLOYALTY_coupon_threshold`, `loyalty_points_rate`, `loyalty_coupon_percentage`, `loyalty_coupon_expiry_days`) VALUES
(1, 'AdvancedPromedia1', '+971552969432', 'info@advancedpromedia.com', 'Internet City1', 'Dubai', 'UAE', 'admin', '$2y$10$DQlojWqI4TYHANuYF5Kp8O00oyjFLFS1FsBPJMjcGfA0aZsaIhcvK', 'uploads/logo.png', '#6e6e6d', '#fad0c9', '#d66edd', '#8b0a94', '\'Georgia\', serif', 'English', 'AED', 1.00, '5%', 'https://www.facebook.com/advancedpromedia', 'https://www.instagram.com/advancedpromedia/', 'Twitter Link', 'https://www.tiktok.com/@advancedpromedia', 'snapchat link', 'https://www.linkedin.com/company/advanced-promedia/', 'https://g.co/kgs/RkqBKxE', 'https://www.youtube.com/channel/UCU22Ik1e8BGU0BB2ls5JhUQ', 1, 7.00, 45, 1, 5000, 1.00, 5.00, 90);

-- --------------------------------------------------------

--
-- Table structure for table `alert_logs`
--

CREATE TABLE `alert_logs` (
  `id` int(11) NOT NULL,
  `alert_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `performed_by` int(11) NOT NULL,
  `old_status` varchar(20) NOT NULL,
  `new_status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_settings`
--

CREATE TABLE `alert_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alert_settings`
--

INSERT INTO `alert_settings` (`id`, `setting_key`, `setting_value`) VALUES
(21, 'notify_email', '1'),
(22, 'notify_dashboard', '1'),
(23, 'email_recipients', 'albara.bitar@gmail.com'),
(24, 'sms_recipients', ''),
(25, 'slack_webhook', ''),
(26, 'webhook_url', ''),
(27, 'low_stock_threshold', '20'),
(28, 'over_stock_threshold', '150'),
(29, 'alert_frequency', '48'),
(30, 'enable_alerts', '1');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `content` longtext NOT NULL,
  `type` enum('technology','business','health','lifestyle','education') NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `date`, `title`, `description`, `content`, `type`, `image`, `created_at`, `updated_at`) VALUES
(1, '2025-04-01', 'Glam and Elegant Events', 'some text but the text is very well and you should now do it and call me when your are happiest in the farm with itsy botsy spider no way, this the cala og my happen reviews.', 'some text but the text is very well and you should now do it and call me when your are happiest in the farm with itsy botsy spider no way, this the cala og my happen reviews.\r\n\r\nsome text but the text is very well and you should now do it and call me when your are happiest in the farm with itsy botsy spider no way, this the cala og my happen reviews.\r\n\r\nsome text but the text is very well and you should now do it and call me when your are happiest in the farm with itsy botsy spider no way, this the cala og my happen reviews.', 'lifestyle', '67fe98630f316__3438ce0c-d744-4b07-8b8d-e67604db25b8.jpg', '2025-04-15 17:33:23', '2025-04-15 17:33:23'),
(2, '2025-04-04', 'What is Lorem Ipsum?', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s,', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. \r\n\r\nIt was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 'health', '67fe9ceaaaaef_Profile-04.png', '2025-04-15 17:52:42', '2025-04-15 17:52:42'),
(3, '2025-04-14', 'Why do we use it?', 'some text description', 'some text but the text is very well and you should now do it and call me when your are happiest in the farm with itsy botsy spider no way, this the cala og my happen reviews.\r\n\r\nsome text but the text is very well and you should now do it and call me when your are happiest in the farm with itsy botsy spider no way, this the cala og my happen reviews.\r\n\r\nsome text but the text is very well and you should now do it and call me when your are happiest in the farm with itsy botsy spider no way, this the cala og my happen reviews.', 'lifestyle', '67ff24e4116f7_1 (13).jpg', '2025-04-16 03:32:52', '2025-04-16 03:32:52');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `active`) VALUES
(13, 'Advanced Promedia', 1);

-- --------------------------------------------------------

--
-- Table structure for table `campaign_emails`
--

CREATE TABLE `campaign_emails` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sent_at` datetime NOT NULL,
  `status` enum('sent','failed') DEFAULT 'sent',
  `open_count` int(11) DEFAULT 0,
  `last_opened_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card_details`
--

CREATE TABLE `card_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_holder_name` varchar(255) NOT NULL,
  `expiry_year` int(4) NOT NULL,
  `expiry_month` int(2) NOT NULL,
  `cvv` varchar(3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `product_id`, `quantity`, `user_id`, `created_at`) VALUES
(742, 60, 1, 40, '2025-04-22 18:36:44'),
(765, 63, 1, 36, '2025-04-27 18:40:34');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `active`) VALUES
(7, 'Perfume', 1),
(9, 'Oud Mubakhar', 1),
(10, 'Body Spray', 1),
(11, 'Hair Mist', 1),
(12, 'Gift Boxes', 1);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `delivery_charges` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `country_code`, `country_name`, `delivery_charges`, `created_at`, `updated_at`) VALUES
(1, 'AE', 'United Arab Emirates', 15.00, '2025-04-07 10:08:31', '2025-04-07 10:08:31'),
(2, 'SA', 'Saudi Arabia', 14.00, '2025-04-07 10:08:31', '2025-04-07 10:08:31'),
(3, 'QA', 'Qatar', 16.00, '2025-04-07 10:08:31', '2025-04-07 10:08:31'),
(4, 'KW', 'Kuwait', 15.50, '2025-04-07 10:08:31', '2025-04-07 10:08:31'),
(5, 'OM', 'Oman', 17.00, '2025-04-07 10:08:31', '2025-04-07 10:08:31'),
(6, 'BH', 'Bahrain', 40.00, '2025-04-07 10:08:31', '2025-04-07 20:04:58'),
(7, 'IQ', 'Iraq', 20.00, '2025-04-07 10:08:31', '2025-04-07 10:08:31'),
(10, 'JO', 'Jordan', 13.00, '2025-04-07 10:08:31', '2025-04-07 10:08:31'),
(11, 'LB', 'Lebanon', 14.00, '2025-04-07 10:08:31', '2025-04-07 10:08:31'),
(13, 'SY', 'Syria', 19.00, '2025-04-07 10:08:31', '2025-04-07 10:08:31');

-- --------------------------------------------------------

--
-- Table structure for table `customer_groups`
--

CREATE TABLE `customer_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_groups`
--

INSERT INTO `customer_groups` (`id`, `name`, `description`, `discount`, `created_at`) VALUES
(5, 'Students', 'Student in Abu Dhabi', 0.00, '2025-04-22 11:33:36');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_addresses`
--

CREATE TABLE `delivery_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `building_name` varchar(100) DEFAULT NULL,
  `building_number` varchar(100) DEFAULT NULL,
  `floor_number` varchar(100) DEFAULT NULL,
  `flat_number` varchar(10) NOT NULL,
  `alternative_phone` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_addresses`
--

INSERT INTO `delivery_addresses` (`id`, `user_id`, `country`, `city`, `street`, `building_name`, `building_number`, `floor_number`, `flat_number`, `alternative_phone`) VALUES
(27, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '2', '+971561599855'),
(31, 40, 'United Arab Emirates', 'Dubai', NULL, NULL, NULL, NULL, '', '+971556644532');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_area`
--

CREATE TABLE `delivery_area` (
  `id` int(11) NOT NULL,
  `area_name` varchar(255) NOT NULL,
  `area_freight_rate` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_area`
--

INSERT INTO `delivery_area` (`id`, `area_name`, `area_freight_rate`) VALUES
(1, 'Abu Dhabi', 50.00),
(2, 'Dubai', 30.00),
(6, 'Sharjah', 40.00),
(7, 'Ras Al Khayma', 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_options`
--

CREATE TABLE `delivery_options` (
  `id` int(11) NOT NULL,
  `flat_rate` decimal(10,2) DEFAULT 0.00,
  `is_rate_by_product` tinyint(1) DEFAULT 0,
  `is_delivery_by_area` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_options`
--

INSERT INTO `delivery_options` (`id`, `flat_rate`, `is_rate_by_product`, `is_delivery_by_area`) VALUES
(1, 40.00, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `discount_codes`
--

CREATE TABLE `discount_codes` (
  `id` int(11) NOT NULL,
  `code_name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active_flag` tinyint(1) DEFAULT 1,
  `expiry_date` timestamp NOT NULL DEFAULT (current_timestamp() + interval 10 day),
  `usage_limit` int(11) DEFAULT 1,
  `type` enum('loyalty','gift','registration') NOT NULL,
  `assigned_to` enum('all','specific_user','user_group') DEFAULT 'all',
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discount_codes`
--

INSERT INTO `discount_codes` (`id`, `code_name`, `code`, `discount_percentage`, `created_at`, `active_flag`, `expiry_date`, `usage_limit`, `type`, `assigned_to`, `user_id`, `group_id`) VALUES
(32, 'Gift', 'PU12VW5U', 7.00, '2025-04-22 13:30:50', 1, '2025-04-24 09:00:00', 1, 'gift', 'all', 36, NULL),
(33, 'Welcome Coupon', 'XIAE2DU6', 10.00, '2025-04-22 18:27:25', 1, '2025-06-06 17:27:25', 1, 'registration', 'specific_user', 40, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_campaigns`
--

CREATE TABLE `email_campaigns` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target` enum('all_users','abandoned_carts','both') NOT NULL,
  `sent_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL COMMENT 'Admin user ID who created the campaign'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','manager') NOT NULL DEFAULT 'manager',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `username`, `password`, `email`, `full_name`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Admin User', 'admin', 1, '2025-04-16 21:26:13', '2025-04-16 21:26:13'),
(2, 'Marco', '$2y$10$SSEINnTNCdK9K9UsY6e1BuZ2qYIZnqM0Iwv5S/BLURfFcLKp4dSR6', 'marco@marco.com', 'Marco', 'manager', 1, '2025-04-16 21:41:35', '2025-04-16 21:41:35');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_alerts`
--

CREATE TABLE `inventory_alerts` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `current_quantity` int(11) NOT NULL,
  `threshold_quantity` int(11) NOT NULL,
  `alert_type` enum('low_stock','out_of_stock','over_stock') NOT NULL,
  `status` enum('pending','sent','acknowledged') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL COMMENT 'Positive for addition, negative for deduction',
  `movement_type` enum('purchase','sale','adjustment','transfer','return','damage','other') NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'Related order_id, purchase_id etc',
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `product_id`, `variant_id`, `warehouse_id`, `quantity`, `movement_type`, `reference_id`, `notes`, `user_id`, `created_at`) VALUES
(1, 63, NULL, 4, 10, 'purchase', 8, NULL, 1, '2025-04-26 22:39:40'),
(2, 67, NULL, 4, 25, 'purchase', 9, NULL, 1, '2025-04-27 10:08:32'),
(3, 63, NULL, 4, -1, 'sale', 79, NULL, 1, '2025-04-27 19:58:40'),
(4, 63, NULL, 4, 5, 'purchase', 10, NULL, 1, '2025-04-27 20:07:26'),
(5, 63, NULL, 4, 2, 'purchase', 11, NULL, 1, '2025-04-27 20:15:53'),
(6, 63, NULL, 4, 2, 'adjustment', NULL, 'Gift', 1, '2025-04-28 09:09:49'),
(7, 63, NULL, 4, -2, 'transfer', 4, 'Transfer out', 1, '2025-04-28 09:29:19'),
(8, 63, NULL, 5, 2, 'transfer', 4, 'Transfer in', 1, '2025-04-28 09:29:19'),
(9, 67, NULL, 4, -25, 'transfer', 5, 'Transfer out', 1, '2025-04-29 09:31:25'),
(10, 67, NULL, 5, 25, 'transfer', 5, 'Transfer in', 1, '2025-04-29 09:31:25');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transfers`
--

CREATE TABLE `inventory_transfers` (
  `id` int(11) NOT NULL,
  `from_warehouse_id` int(11) NOT NULL,
  `to_warehouse_id` int(11) NOT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_transfers`
--

INSERT INTO `inventory_transfers` (`id`, `from_warehouse_id`, `to_warehouse_id`, `status`, `notes`, `created_by`, `created_at`, `completed_by`, `completed_at`) VALUES
(4, 4, 5, 'completed', 'Gifts always will be sent to Abu Dhabi Warehouse', 1, '2025-04-28 10:10:23', 1, '2025-04-28 10:29:19'),
(5, 4, 5, 'completed', 'Transfer products', 1, '2025-04-29 10:30:43', 1, '2025-04-29 10:31:25');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transfer_items`
--

CREATE TABLE `inventory_transfer_items` (
  `id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_transfer_items`
--

INSERT INTO `inventory_transfer_items` (`id`, `transfer_id`, `product_id`, `variant_id`, `quantity`) VALUES
(3, 4, 63, NULL, 2),
(4, 5, 67, NULL, 25);

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `header` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `link_caption` varchar(100) DEFAULT NULL,
  `type` enum('new release','promotion','event','news') NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `date`, `header`, `description`, `link`, `link_caption`, `type`, `image`, `created_at`, `updated_at`) VALUES
(2, '2025-04-01', 'We have won the award of something for the best in silly issue', 'privileges\' to ', 'https://www.google.com', 'Learn more', 'news', '67fe905fb38de_Designer (1).jpeg', '2025-04-15 16:59:11', '2025-04-15 17:01:19');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash on delivery','visa card') NOT NULL,
  `order_status` enum('pending','completed','cancelled','processing','shipped','refunded') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_charges` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `discount_by_code` decimal(10,2) NOT NULL,
  `discount_code` varchar(50) DEFAULT NULL,
  `discount_code_id` int(11) DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `method` text NOT NULL,
  `picked_at` varchar(15) NOT NULL,
  `delivered_at` varchar(15) NOT NULL,
  `actual_delivery` varchar(20) NOT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `payment_status` enum('received','pending','on_delivery','cancelled','returned') DEFAULT 'pending',
  `rolled_flag` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `warehouse_id`, `total_amount`, `discount`, `payment_method`, `order_status`, `created_at`, `delivery_charges`, `tax_amount`, `discount_by_code`, `discount_code`, `discount_code_id`, `grand_total`, `method`, `picked_at`, `delivered_at`, `actual_delivery`, `refunded_at`, `cancelled_at`, `processed_at`, `payment_status`, `rolled_flag`) VALUES
(71, 36, NULL, 368.00, 0.00, 'cash on delivery', 'refunded', '2025-04-08 18:49:37', 40.00, 18.40, 18.40, 'Gift1', 28, 408.00, '7X', '2025-04-10', '2025-04-11', '2025-04-11', '2025-04-25 18:20:27', NULL, '2025-04-09 19:23:14', 'returned', 0),
(74, 36, NULL, 260.00, 0.00, 'cash on delivery', 'pending', '2025-04-15 10:44:08', 40.00, 13.00, 0.00, NULL, NULL, 313.00, '', '', '', '', NULL, NULL, NULL, 'pending', 0),
(75, 36, NULL, 620.00, 0.00, 'cash on delivery', 'pending', '2025-04-15 11:04:45', 40.00, 31.00, 0.00, NULL, NULL, 691.00, '', '', '', '', NULL, NULL, NULL, 'on_delivery', 0),
(76, 36, NULL, 295.00, 0.00, 'cash on delivery', 'refunded', '2025-04-15 12:25:26', 40.00, 14.75, 0.00, NULL, NULL, 349.75, 'Wasla', '2025-04-17', '2025-04-21', '2025-04-22', '2025-04-25 17:45:52', '2025-04-25 17:45:32', NULL, 'returned', 1),
(77, 36, NULL, 620.00, 0.00, 'cash on delivery', 'cancelled', '2025-04-22 19:08:58', 15.00, 31.00, 0.00, NULL, NULL, 666.00, '7X', '2025-04-22', '2025-04-24', '2025-04-25', NULL, '2025-04-25 17:25:18', NULL, 'cancelled', 1),
(78, 36, NULL, 980.00, 137.20, 'cash on delivery', 'pending', '2025-04-27 17:20:44', 15.00, 42.14, 0.00, NULL, NULL, 899.94, '', '', '', '', NULL, NULL, NULL, 'on_delivery', 0),
(79, 36, 4, 368.00, 0.00, 'cash on delivery', 'shipped', '2025-04-27 18:40:45', 15.00, 18.40, 0.00, NULL, NULL, 401.40, '7X', '2025-04-27', '2025-04-27', '', NULL, NULL, NULL, 'on_delivery', 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `quantity`, `price`) VALUES
(93, 78, 55, NULL, 1, 980.00),
(94, 79, 63, NULL, 1, 368.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `value` varchar(50) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `token_key` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `image`, `description`, `value`, `link`, `token_key`, `active`, `created_at`) VALUES
(5, 'Cash on Delivery (COD)', 'uploads/payment_methods/67e98ede55299.png', 'Pay in cash when you receive your order', 'cash on delivery', 'No Link Required', 'No Key', 1, '2025-03-27 21:27:43'),
(6, 'Credit/Debit Card', 'uploads/payment_methods/67e992a5415aa.png', 'Pay securely with Visa, Mastercard, etc.', 'visa card', '', 'STRIPE_API_KEY', 1, '2025-03-27 21:27:43'),
(7, 'Tabby', 'uploads/payment_methods/67e98f4dc8c30.png', 'Split your payment into 4 interest-free installments', 'tabby', '', '', 1, '2025-03-27 21:27:43'),
(8, 'Tamara', 'uploads/payment_methods/67e98f427bef5.png', 'Pay in 3 installments with 0% interest', 'tamara', '', '', 1, '2025-03-27 21:27:43'),
(9, 'Pay Pal', 'uploads/payment_methods/67e98f29b229d.png', 'Easy Transfer with Pay Pal', 'paypal', '', '', 1, '2025-03-27 22:12:35'),
(12, 'Sham Cash', 'uploads/payment_methods/680cd31ed0bad.jpg', 'Syria Payment', 'sham', '', '', 1, '2025-04-26 12:35:42');

-- --------------------------------------------------------

--
-- Table structure for table `privileges`
--

CREATE TABLE `privileges` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `page_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `can_access` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `privileges`
--

INSERT INTO `privileges` (`id`, `employee_id`, `page_name`, `created_at`, `updated_at`, `can_access`) VALUES
(85, 2, 'products', '2025-04-17 17:53:45', '2025-04-17 17:53:45', 1),
(86, 2, 'categories', '2025-04-17 17:53:45', '2025-04-17 17:53:45', 1),
(87, 2, 'brands', '2025-04-17 17:53:45', '2025-04-17 17:53:45', 1),
(88, 2, 'suppliers', '2025-04-17 17:53:45', '2025-04-17 17:53:45', 1),
(89, 2, 'orders', '2025-04-17 17:53:45', '2025-04-17 17:53:45', 1),
(90, 2, 'invoices', '2025-04-17 17:53:45', '2025-04-17 17:53:45', 1),
(91, 2, 'shipments', '2025-04-17 17:53:45', '2025-04-17 17:53:45', 1),
(92, 2, 'customers', '2025-04-17 17:53:45', '2025-04-17 17:53:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `is_offer` tinyint(1) DEFAULT 0,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `product_code` varchar(50) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `minimum_order` int(10) UNSIGNED DEFAULT 1,
  `max_order` int(10) UNSIGNED DEFAULT NULL,
  `stock_limit` int(10) UNSIGNED DEFAULT NULL,
  `min_stock` int(11) NOT NULL,
  `cost` decimal(10,2) UNSIGNED DEFAULT NULL,
  `free_shipping` tinyint(1) DEFAULT 0,
  `return_allowed` tinyint(1) DEFAULT 0,
  `affiliate` tinyint(1) DEFAULT 0,
  `affiliate_link` varchar(255) DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `image4` varchar(255) DEFAULT NULL,
  `volume` varchar(100) DEFAULT NULL,
  `weight` decimal(10,2) UNSIGNED DEFAULT NULL,
  `international_code` varchar(50) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `origin_country` varchar(50) NOT NULL DEFAULT 'Local',
  `delivery_rate` int(11) NOT NULL,
  `delivery_duration` int(11) NOT NULL,
  `overview` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `keywords` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `created_at`, `category_id`, `is_offer`, `discount_percentage`, `product_code`, `active`, `minimum_order`, `max_order`, `stock_limit`, `min_stock`, `cost`, `free_shipping`, `return_allowed`, `affiliate`, `affiliate_link`, `main_image`, `image2`, `image3`, `image4`, `volume`, `weight`, `international_code`, `brand_id`, `supplier_id`, `origin_country`, `delivery_rate`, `delivery_duration`, `overview`, `sort_order`, `is_new`, `keywords`) VALUES
(53, 'Aghla', 'Top Notes: Bergamot, Lemon, Saffran.\r\nMiddle Notes: Orange Blossom Abs, Patchouli, Toffee.\r\nBase Notes: Amber, Vanilla, Ambroxan.', 660.00, '2025-03-11 14:23:27', 7, 0, NULL, 'SKU: 302074', 1, 1, 10, 0, 0, 380.00, 0, 0, 0, '', 'touch-of-oud-aghla-80ml-01.jpg', 'touch-of-oud-aghla-80ml-02.jpg', 'touch-of-oud-aghla-80ml-04.jpg', 'touch-of-oud-aghla-intense-edp-80ml-013.jpg', '20x12x5 cm', 0.20, '', 13, 5, 'UAE', 20, 4, 'Top Notes: Bergamot, Lemon, Saffran. Middle Notes: Orange Blossom Abs, Patchouli, Toffee. Base Notes: Amber, Vanilla, Ambroxan.', 0, 1, ''),
(54, 'Lemar', 'Top Notes: Litchi, Bergamot.\r\nMiddle Notes: Rose, Vanilla.\r\nBase Notes: Amber.', 660.00, '2025-03-11 14:39:19', 7, 1, 14.00, 'SKU: 301858', 1, 1, 10, 9, 0, 380.00, 0, 0, 0, '', 'touch-of-oud-lemar-edp-80ml-0111.jpg', 'touch-of-oud-lemar-edp-80ml-012.jpg', 'touch-of-oud-lemar-edp-80ml-014.jpg', 'touch-of-oud-lemar-edp-80ml-013.jpg', '20x12x5 cm', 0.20, '', 13, 5, 'UAE', 20, 4, '', 0, 0, ''),
(55, 'Trat', 'Top Notes: Agarwood, Musk, Oudhy.\r\nMiddle Notes: Leather, Oud, Sandalwood.\r\nBase Notes: Amber, Musk, Oudhy.', 980.00, '2025-03-11 15:25:07', 7, 1, 14.00, 'SKU: 301764', 1, 1, 5, 7, 0, 700.00, 0, 0, 0, '', 'touch-of-oud-trat-edp-80ml-011.jpg', 'touch-of-oud-trat-edp-80ml-012.jpg', 'touch-of-oud-trat-edp-80ml-014.jpg', 'touch-of-oud-trat-edp-80ml-013.jpg', '18x12x5 cm', 0.20, '', 13, 5, 'UAE', 20, 4, 'Top Notes: Agarwood, Musk, Oudhy. Middle Notes: Leather, Oud, Sandalwood. Base Notes: Amber, Musk, Oudhy.', 0, 1, ''),
(56, 'Saden', 'Top Notes: Bergamot, Coriander.\r\nMiddle Notes: Rose, Iris, Agrawood.\r\nBase Notes: Leather, Dry Wood, Oud.', 660.00, '2025-03-11 15:28:52', 7, 1, 14.00, 'SKU: 301553', 1, 1, 3, 10, 0, 390.00, 0, 0, 0, '', 'touch-of-oud-saden-edp-80ml-011.jpg', 'touch-of-oud-saden-edp-80ml-014.jpg', 'touch-of-oud-saden-edp-80ml-012.jpg', 'touch-of-oud-trat-edp-80ml-013.jpg', '20x12x5 cm', 0.20, '', 13, 5, 'UAE', 20, 4, '', 0, 0, ''),
(57, 'Oud Royale 8Pcs Gift Set', 'The Oud Royale Collection features an exquisite 8-piece gift set including a luxurious crystal burner, high-quality oud wood, a Dukhoon Aghla incense blend, Rajwan, Rose Paradise spray, and premium accessories for an elegant oud experience.', 1850.00, '2025-03-11 16:32:16', 12, 0, NULL, 'SKU: 302265', 1, 1, 10, 48, 0, 1200.00, 0, 0, 0, '', 'oud-royale-8pcs-gift-set-01.jpg', 'oud-royale-8pcs-gift-set-02.jpg', 'oud-royale-8pcs-gift-set-03.jpg', 'oud-royale-8pcs-gift-set-04.jpg', '40x18x15 cm', 0.40, '', 13, 5, 'UAE', 30, 4, '', 0, 1, ''),
(58, 'Luxury Perfumes Gift Set', 'This amazing set comes with:\r\nSalwan Perfume\r\nSaden Perfume\r\nTayma Perfume\r\nJelood Perfume\r\nMoroki with Oud Dukhoon\r\nD\'oud Hindi Suifi\r\nAgarwood Hindi Assam Double Supper 01\r\nAgarwood Hindi Assam Double Supper 02', 5200.00, '2025-03-11 16:40:35', 12, 0, NULL, 'SKU: 301903', 1, 1, 5, 8, 0, 4000.00, 1, 0, 0, '', 'llll.jpg', 'touch-of-oud-8-pcs-perfumes-set-02.jpg', 'touch-of-oud-set-box.jpg', 'lkl.jpg', '40x40x15 cm', 0.80, '', 13, 5, 'UAE', 0, 4, '', 0, 0, ''),
(59, 'Aghla All Over Body Spray', 'Top Notes: Iris - Cedarwood Ambery - Sandal Wood\r\nMiddle Notes: Orange Blossom Abs Patchouli - Toffee\r\nBase Notes: Amber - Vanilla Ambroxan', 295.00, '2025-03-11 18:51:09', 10, 0, NULL, 'SKU: 301757', 1, 1, 5, 18, 0, 176.00, 0, 0, 0, '', 'Aghla-bottle.jpg', 'Aghla-With-box.jpg', 'Aghla-bottle - Copy.jpg', 'Aghla-With-box - Copy.jpg', '10x3x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 1, ''),
(60, 'Moroki With Oud', 'Top Notes: Floral, Rose.\r\nMiddle Notes: Saffron, Pink Pepper.\r\nBase Notes: Amber, Oud, Musk.', 620.00, '2025-03-11 18:54:45', 9, 0, NULL, 'SKU: 301766', 1, 1, 4, 10, 0, 400.00, 0, 0, 0, '', 'touch-of-oud-moroki-with-oud-24gm-01.jpg', 'touch-of-oud-moroki-with-oud-24gm-01 - Copy.jpg', 'touch-of-oud-moroki-with-oud-24gm-02.jpg', 'kj.jpg', '12x12x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0, ''),
(61, 'Powder Musk All Over Body Spray', 'Top Notes: Violet, Hawthorn, Lily.\r\nMiddle Notes: Heliotrope, White Flowers, Incense.\r\nBase Notes: Amber, Musk, Vanilla.', 295.00, '2025-03-11 21:05:27', 10, 0, NULL, 'SKU: 301767', 1, 1, 4, 25, 0, 180.00, 0, 0, 0, '', 'touch-of-oud-powder-musk-all-over-body-spray-125ml-01 - Copy.jpg', 'touch-of-oud-powder-musk-all-over-body-spray-125ml-02 - Copy.jpg', 'touch-of-oud-powder-musk-all-over-body-spray-125ml-01.jpg', 'touch-of-oud-powder-musk-all-over-body-spray-125ml-02.jpg', '10x3x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0, ''),
(62, 'Manthoor All Over Body Spray', 'Top Notes: Iris - Galbanum Pink Peppercorn Saffron\r\nMiddle Notes: Sage - Apricot - Amber Buffalo Grass - Rose\r\nBase Notes: Tonka Beans - Birch Wood Musky - Leather Patchouli', 295.00, '2025-03-11 21:08:23', 10, 0, NULL, 'SKU: 301754', 0, 1, 4, 20, 0, 180.00, 0, 0, 0, '', 'aa - Copy.jpg', 'bb - Copy.jpg', 'aa.jpg', 'bb.jpg', '10x3x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0, ''),
(63, 'Oud Mubakhar Aseel', 'Top Notes: Rose, White Flower, Grapefruit.\r\nMiddle Notes: Jasmine, Iris, Cyclamen, Hawthorn.\r\nBase Notes: Tonak Bean, Musk, Sandalwood, Vanilla.', 368.00, '2025-03-11 21:17:59', 9, 0, NULL, 'SKU: 300915', 1, 1, 5, 27, 0, 240.00, 0, 0, 0, '', '1111.jpg', '222222 - Copy.jpg', '1111 - Copy.jpg', '222222.jpg', '15x10x10 cm', 0.20, '', 13, 5, 'UAE', 20, 4, '', 0, 0, ''),
(64, 'Jala Hair Mist', 'A Floral vortex edges the hair and sneaks slowly to the head spreading a garden of flowers starting with Jasmine through Violet and Aniseed, ending with sweet warm Vanilla aroma blended in irresistible Cashmere wood scent.', 260.00, '2025-03-11 21:23:38', 11, 0, NULL, 'SKU: 301519', 1, 1, 3, 9, 0, 200.00, 0, 0, 0, '', 'aa.jpg', 'bb.jpg', 'aa - Copy.jpg', 'bb - Copy.jpg', '12x7x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0, ''),
(65, 'Ramadan Luxurious 2Pcs Gift Set', 'A luxurious Ramadan gift set featuring Moroki with Oud and Aghla by Touch of Oud perfumes, perfect for adding elegance to the seas.', 1200.00, '2025-03-11 21:27:43', 12, 0, NULL, 'SKU: 302256', 1, 1, 3, 12, 0, 900.00, 0, 0, 0, '', 'luxurious-2pc-gift-set-01.jpg', 'luxurious-2pc-gift-set-02.jpg', '2pc-gift-set-03.jpg', '2pc-gift-set-04.jpg', '40x30x15 cm', 0.30, '', 13, 5, 'UAE', 30, 4, '', 0, 0, ''),
(67, 'Sala ', 'asdasdas', 230.00, '2025-04-27 09:52:56', 7, 1, 0.00, 'SKU: 301000', 1, 1, 5, 0, 5, 0.00, 0, 0, 0, '', 'image_dYD6aTvE_1745142563948_raw.jpg', 'image_tYGwnbRI_1745141936634_raw.jpg', '5-6-7-8- (1).jpg', '5-6-7-8- (2).jpg', '40x40x15 cm', 0.20, '', 13, 5, 'UAE', 20, 3, 'asdasdad', 20, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `product_barcodes`
--

CREATE TABLE `product_barcodes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `barcode_type` enum('EAN-13','UPC-A','CODE-128','QR') NOT NULL DEFAULT 'EAN-13',
  `barcode_value` varchar(50) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `option_name` varchar(50) NOT NULL COMMENT 'Size, Color etc',
  `option_value` varchar(100) NOT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_views`
--

CREATE TABLE `product_views` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_views`
--

INSERT INTO `product_views` (`id`, `product_id`, `session_id`, `viewed_at`) VALUES
(94, 56, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:35:20'),
(95, 54, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:35:51'),
(96, 57, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:35:58'),
(97, 63, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:36:08'),
(98, 63, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:41:42'),
(99, 63, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:42:52'),
(100, 61, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:43:13'),
(101, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:43:33'),
(102, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 07:44:54'),
(103, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:02:00'),
(104, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:09:36'),
(105, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:10:54'),
(106, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:11:37'),
(107, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:16:50'),
(108, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:17:19'),
(109, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:18:56'),
(110, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:21:52'),
(111, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:22:25'),
(112, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:22:48'),
(113, 65, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:24:04'),
(114, 59, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:28:10'),
(115, 59, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:28:31'),
(116, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:28:37'),
(117, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:29:58'),
(118, 61, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:31:52'),
(119, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:33:52'),
(120, 56, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:34:38'),
(121, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 08:48:10'),
(122, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:41:03'),
(123, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:41:12'),
(124, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:42:51'),
(125, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:43:56'),
(126, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:45:11'),
(127, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:46:41'),
(128, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:47:26'),
(129, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:47:49'),
(130, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:48:42'),
(131, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:49:43'),
(132, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:51:42'),
(133, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:52:09'),
(134, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:52:34'),
(135, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 09:53:31'),
(136, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:16:17'),
(137, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:16:40'),
(138, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:18:25'),
(139, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:19:11'),
(140, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:20:42'),
(141, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:21:04'),
(142, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:24:09'),
(143, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:24:53'),
(144, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:26:55'),
(145, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:30:11'),
(146, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:30:42'),
(147, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:31:11'),
(148, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:32:09'),
(149, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:32:51'),
(150, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:33:20'),
(151, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:33:51'),
(152, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:35:26'),
(153, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:44:52'),
(154, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:45:55'),
(155, 55, 'c1099b9b5fbcd50f780cd0b730d5baf3', '2025-04-18 10:45:56'),
(156, 55, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:47:23'),
(157, 55, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:48:20'),
(158, 55, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:51:02'),
(159, 57, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:51:32'),
(160, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:52:09'),
(161, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:53:23'),
(162, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:54:54'),
(163, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:55:39'),
(164, 64, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:56:03'),
(165, 58, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:56:15'),
(166, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:56:43'),
(167, 65, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:57:40'),
(168, 65, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 10:59:49'),
(169, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:03:26'),
(170, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:06:45'),
(171, 57, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:06:54'),
(172, 57, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:08:48'),
(173, 57, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:10:19'),
(174, 58, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:10:28'),
(175, 58, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:10:47'),
(176, 64, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:10:56'),
(177, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:10:59'),
(178, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:11:57'),
(179, 59, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:12:21'),
(180, 64, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:12:24'),
(181, 61, 'd55d0e8d0937f7c43a04d609e763380a', '2025-04-18 11:13:04'),
(182, 62, '60f72141ed55c0cca5385a61904c4370', '2025-04-18 19:53:13'),
(183, 62, '60f72141ed55c0cca5385a61904c4370', '2025-04-18 19:54:23'),
(184, 62, '60f72141ed55c0cca5385a61904c4370', '2025-04-19 20:10:27'),
(185, 62, '60f72141ed55c0cca5385a61904c4370', '2025-04-19 20:18:27'),
(186, 55, '3b17e2055bf73b801d3be99cd46b0c9c', '2025-04-19 21:07:40'),
(187, 55, '9cca7b2ccad4994dc2e34bb36f656077', '2025-04-19 22:20:59'),
(188, 59, 'b55c2f2d86e13e409bc55598ec0562e0', '2025-04-22 18:17:14'),
(189, 64, 'b55c2f2d86e13e409bc55598ec0562e0', '2025-04-22 18:19:33'),
(190, 58, 'b55c2f2d86e13e409bc55598ec0562e0', '2025-04-22 18:19:42'),
(191, 53, 'b55c2f2d86e13e409bc55598ec0562e0', '2025-04-22 18:19:48'),
(192, 56, 'b55c2f2d86e13e409bc55598ec0562e0', '2025-04-22 18:20:33'),
(193, 60, '10ab423d610a6c8053ccfb032743e4a8', '2025-04-22 18:29:45'),
(194, 57, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 19:07:24'),
(195, 54, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:46:22'),
(196, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:46:26'),
(197, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:50:53'),
(198, 60, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:51:05'),
(199, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:51:17'),
(200, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:51:42'),
(201, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:54:27'),
(202, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:54:28'),
(203, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:57:03'),
(204, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:57:28'),
(205, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:58:28'),
(206, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:58:30'),
(207, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:58:30'),
(208, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 21:58:31'),
(209, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:01:18'),
(210, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:04:14'),
(211, 56, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:05:26'),
(212, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:05:52'),
(213, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:14:34'),
(214, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:16:08'),
(215, 57, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:19:39'),
(216, 53, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:20:11'),
(217, 54, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:21:21'),
(218, 54, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:24:13'),
(219, 55, '8d97eb7db74c806ad724ef2cd1048995', '2025-04-22 22:27:32'),
(220, 56, '28d72eb4f7086b60072bf3275bb367f0', '2025-04-24 11:20:41'),
(221, 54, '28d72eb4f7086b60072bf3275bb367f0', '2025-04-24 11:41:50'),
(222, 55, '28d72eb4f7086b60072bf3275bb367f0', '2025-04-24 11:42:04'),
(223, 54, '28d72eb4f7086b60072bf3275bb367f0', '2025-04-24 11:42:36'),
(224, 54, '28d72eb4f7086b60072bf3275bb367f0', '2025-04-24 11:43:41'),
(225, 54, '28d72eb4f7086b60072bf3275bb367f0', '2025-04-24 11:44:08'),
(226, 54, '1cea694c6f56244fd55630cbf1dd1525', '2025-04-24 11:44:33'),
(227, 54, '1cea694c6f56244fd55630cbf1dd1525', '2025-04-24 11:45:26'),
(228, 55, '1cea694c6f56244fd55630cbf1dd1525', '2025-04-24 11:45:49'),
(229, 56, '516352a3195cc3c1224d8037a82561cb', '2025-04-24 21:25:54'),
(230, 57, '52b65502c8f618133a240804733f4cce', '2025-04-25 10:47:27'),
(231, 57, '52b65502c8f618133a240804733f4cce', '2025-04-25 10:49:20'),
(232, 57, '52b65502c8f618133a240804733f4cce', '2025-04-25 10:52:36'),
(233, 53, '52b65502c8f618133a240804733f4cce', '2025-04-25 10:56:39'),
(234, 53, '52b65502c8f618133a240804733f4cce', '2025-04-25 10:57:55'),
(235, 61, '52b65502c8f618133a240804733f4cce', '2025-04-25 11:00:00'),
(236, 53, '44ec899ba87a9f171bb12cd5ee614814', '2025-04-26 12:15:04'),
(237, 54, '44ec899ba87a9f171bb12cd5ee614814', '2025-04-26 12:16:06'),
(238, 54, '44ec899ba87a9f171bb12cd5ee614814', '2025-04-26 12:19:15'),
(239, 67, '3e3b39116c84fcb9b6185ebd2ce2ab23', '2025-04-27 09:56:09'),
(240, 67, '3e3b39116c84fcb9b6185ebd2ce2ab23', '2025-04-27 10:15:56');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`user_id`, `username`, `first_name`, `last_name`, `address`, `phone`, `email`) VALUES
(36, 'Malek', 'Malek', 'Malek', NULL, '+971561599855', 'lebanonssp@gmail.com'),
(40, 'Samer', 'Samer', 'Samer', NULL, '+971556644532', 'samer@samer.com');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `expiry_date` datetime NOT NULL,
  `all_discount_percentage` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_sent` tinyint(1) DEFAULT 0,
  `email_sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `name`, `description`, `start_date`, `expiry_date`, `all_discount_percentage`, `is_active`, `created_at`, `updated_at`, `email_sent`, `email_sent_at`) VALUES
(9, 'New', '14%', '2025-04-21 15:43:00', '2025-04-30 14:43:00', 14.00, 1, '2025-04-24 14:43:39', '2025-04-25 08:19:33', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `promotion_items`
--

CREATE TABLE `promotion_items` (
  `id` int(11) NOT NULL,
  `promotion_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `use_general_discount` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotion_items`
--

INSERT INTO `promotion_items` (`id`, `promotion_id`, `product_id`, `use_general_discount`, `created_at`) VALUES
(11, 9, 55, 1, '2025-04-24 14:49:00'),
(12, 9, 54, 1, '2025-04-25 08:19:12'),
(13, 9, 56, 1, '2025-04-25 08:19:12');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` enum('draft','ordered','received','partial','cancelled') NOT NULL DEFAULT 'draft',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `supplier_id`, `warehouse_id`, `order_number`, `order_date`, `expected_delivery_date`, `status`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(7, 5, 4, 'PO-20250427-135FEB', '2025-04-27', '2025-04-28', 'draft', 3150.00, 0.00, 0.00, 3150.00, 'Maxo', 1, '2025-04-26 22:29:19', '2025-04-26 22:36:43'),
(8, 5, 4, 'PO-20250427-23EEE3', '2025-04-27', '2025-04-29', 'received', 1260.00, 0.00, 0.00, 1260.00, 'yar', 1, '2025-04-26 22:38:10', '2025-04-26 22:39:40'),
(9, 5, 4, 'PO-20250427-6D6EA6', '2025-04-27', '2025-04-28', 'received', 4200.00, 0.00, 0.00, 4200.00, 'First ', 1, '2025-04-27 10:08:02', '2025-04-27 10:08:32'),
(10, 5, 4, 'PO-20250427-E7301C', '2025-04-27', '2025-04-27', 'received', 630.00, 0.00, 0.00, 630.00, 'second', 1, '2025-04-27 20:07:07', '2025-04-27 20:07:26'),
(11, 5, 4, 'PO-20250427-592379', '2025-04-27', '0000-00-00', 'received', 420.00, 0.00, 0.00, 420.00, 'third', 1, '2025-04-27 20:15:47', '2025-04-27 20:15:53');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `received_quantity` int(11) NOT NULL DEFAULT 0,
  `cost_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `product_id`, `variant_id`, `quantity`, `received_quantity`, `cost_price`, `tax_rate`, `total_price`, `notes`) VALUES
(4, 7, 56, NULL, 10, 0, 300.00, 5.00, 3150.00, NULL),
(6, 8, 63, NULL, 10, 10, 120.00, 5.00, 1260.00, NULL),
(7, 9, 67, NULL, 25, 25, 160.00, 5.00, 4200.00, NULL),
(8, 10, 63, NULL, 5, 5, 120.00, 5.00, 630.00, NULL),
(9, 11, 63, NULL, 2, 2, 200.00, 5.00, 420.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `review_text` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `stars` int(11) NOT NULL CHECK (`stars` between 1 and 5),
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `review_text`, `user_id`, `stars`, `product_id`, `created_at`, `active`) VALUES
(11, 'Amazing perfume', 36, 5, 54, '2025-04-11 19:02:06', 1),
(12, 'Fantastic item, the best ever tried', 36, 5, 61, '2025-04-11 19:02:51', 1);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `area_of_delivery` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_methods`
--

INSERT INTO `shipping_methods` (`id`, `name`, `phone`, `email`, `address`, `country`, `area_of_delivery`) VALUES
(1, 'Wasla', '+971551111111', 'info@wasleh.ae', 'Dubai, Deira, Abu Hail Center', 'UAE', 'UAE'),
(2, '7X', '+9714343434', 'john.doe@example.com', 'Bani Yas road, Lana Tower, Fl.5', '', 'Dubai Only');

-- --------------------------------------------------------

--
-- Table structure for table `social_media_accounts`
--

CREATE TABLE `social_media_accounts` (
  `id` int(11) NOT NULL,
  `platform` enum('facebook','instagram') NOT NULL,
  `account_id` varchar(255) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `access_token` text NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_media_orders`
--

CREATE TABLE `social_media_orders` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `platform` enum('facebook','instagram') NOT NULL,
  `platform_order_id` varchar(255) NOT NULL,
  `synced_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_media_product_mapping`
--

CREATE TABLE `social_media_product_mapping` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `platform` enum('facebook','instagram') NOT NULL,
  `platform_product_id` varchar(255) NOT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_media_settings`
--

CREATE TABLE `social_media_settings` (
  `id` int(11) NOT NULL,
  `platform` enum('facebook','instagram') NOT NULL,
  `auto_post_products` tinyint(1) NOT NULL DEFAULT 0,
  `auto_sync_products` tinyint(1) NOT NULL DEFAULT 0,
  `auto_sync_orders` tinyint(1) NOT NULL DEFAULT 0,
  `last_sync` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_media_settings`
--

INSERT INTO `social_media_settings` (`id`, `platform`, `auto_post_products`, `auto_sync_products`, `auto_sync_orders`, `last_sync`, `created_at`) VALUES
(5, 'facebook', 1, 1, 0, NULL, '2025-04-28 14:10:35'),
(6, 'instagram', 1, 1, 0, NULL, '2025-04-28 14:10:35');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `email`, `address`) VALUES
(5, 'Advanced Promedia', '+971559988773', 'info@advancedpromedia.com', 'Dubai');

-- --------------------------------------------------------

--
-- Table structure for table `unsubscribed_emails`
--

CREATE TABLE `unsubscribed_emails` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `unsubscribed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reason` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unsubscribed_emails`
--

INSERT INTO `unsubscribed_emails` (`id`, `email`, `unsubscribed_at`, `reason`, `ip_address`, `user_agent`) VALUES
(1, 'lebanonssp@gmail.com', '2025-04-11 18:41:19', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `points` int(11) DEFAULT 0,
  `birth_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `group_id`, `username`, `password`, `email`, `phone`, `created_at`, `points`, `birth_date`) VALUES
(36, NULL, 'Malek', '$2y$10$LMyqmFcLe586qziII/5WF.8P5jECQC2EN3BfZ2JHwJRLDwPA3Z/ka', 'lebanonssp@gmail.com', '+971561599855', '2025-04-11 15:49:20', 2358, NULL),
(40, NULL, 'Samer', '$2y$10$1nbJ07cSxwbwImi8j2bf8ugJO23sAUVuQ19rBiWuxXuCKOKBSMSUu', 'samer@samer.com', '+971556644532', '2025-04-22 18:27:25', 0, '1970-10-15');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `location`, `contact_person`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES
(4, 'Main Warehouse', 'Dubai', 'Mazen Hamara', '+971559988772', 'mazen_hamra@ssproviders.com', 1, '2025-04-26 16:39:38', '2025-04-26 16:42:01'),
(5, 'Abu Dhabi', 'Abu dhabi', 'Salem', '+971553344221', '', 1, '2025-04-27 13:53:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_stock`
--

CREATE TABLE `warehouse_stock` (
  `id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouse_stock`
--

INSERT INTO `warehouse_stock` (`id`, `warehouse_id`, `product_id`, `variant_id`, `quantity`, `last_updated`) VALUES
(2, 4, 63, NULL, 7, '2025-04-28 10:29:19'),
(3, 4, 67, NULL, 0, '2025-04-29 10:31:25'),
(4, 4, 63, NULL, 3, '2025-04-28 10:29:19'),
(5, 4, 63, NULL, 0, '2025-04-28 10:29:19'),
(7, 4, 63, NULL, 0, '2025-04-28 10:29:19'),
(8, 5, 63, NULL, 2, '2025-04-28 10:29:19'),
(9, 5, 67, NULL, 25, '2025-04-29 10:31:25');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abandoned_carts`
--
ALTER TABLE `abandoned_carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `actual_addresses`
--
ALTER TABLE `actual_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alert_logs`
--
ALTER TABLE `alert_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alert_id` (`alert_id`);

--
-- Indexes for table `alert_settings`
--
ALTER TABLE `alert_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `blogs` ADD FULLTEXT KEY `search_index` (`title`,`description`,`content`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `campaign_emails`
--
ALTER TABLE `campaign_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `card_details`
--
ALTER TABLE `card_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD UNIQUE KEY `user_id_2` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_country_code` (`country_code`);

--
-- Indexes for table `customer_groups`
--
ALTER TABLE `customer_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `delivery_area`
--
ALTER TABLE `delivery_area`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_options`
--
ALTER TABLE `delivery_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discount_codes`
--
ALTER TABLE `discount_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sent_at` (`sent_at`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_warehouse_id` (`from_warehouse_id`),
  ADD KEY `to_warehouse_id` (`to_warehouse_id`);

--
-- Indexes for table `inventory_transfer_items`
--
ALTER TABLE `inventory_transfer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfer_id` (`transfer_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `news` ADD FULLTEXT KEY `search_index` (`header`,`description`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `value` (`value`);

--
-- Indexes for table `privileges`
--
ALTER TABLE `privileges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_page` (`employee_id`,`page_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `fk_brand` (`brand_id`),
  ADD KEY `fk_supplier` (`supplier_id`),
  ADD KEY `idx_product_name` (`name`);
ALTER TABLE `products` ADD FULLTEXT KEY `ft_search` (`name`,`description`,`keywords`);

--
-- Indexes for table `product_barcodes`
--
ALTER TABLE `product_barcodes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode_value` (`barcode_value`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_views`
--
ALTER TABLE `product_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `viewed_at` (`viewed_at`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promotion_items`
--
ALTER TABLE `promotion_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_promotion_product` (`promotion_id`,`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_media_accounts`
--
ALTER TABLE `social_media_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_media_orders`
--
ALTER TABLE `social_media_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `social_media_product_mapping`
--
ALTER TABLE `social_media_product_mapping`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `social_media_settings`
--
ALTER TABLE `social_media_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unsubscribed_emails`
--
ALTER TABLE `unsubscribed_emails`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `unsubscribed_at` (`unsubscribed_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_group` (`group_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouse_product_variant` (`warehouse_id`,`product_id`,`variant_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abandoned_carts`
--
ALTER TABLE `abandoned_carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `actual_addresses`
--
ALTER TABLE `actual_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `alert_logs`
--
ALTER TABLE `alert_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alert_settings`
--
ALTER TABLE `alert_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `campaign_emails`
--
ALTER TABLE `campaign_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `card_details`
--
ALTER TABLE `card_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=766;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `customer_groups`
--
ALTER TABLE `customer_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `delivery_area`
--
ALTER TABLE `delivery_area`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `delivery_options`
--
ALTER TABLE `delivery_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `discount_codes`
--
ALTER TABLE `discount_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_transfer_items`
--
ALTER TABLE `inventory_transfer_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `privileges`
--
ALTER TABLE `privileges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `product_barcodes`
--
ALTER TABLE `product_barcodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_views`
--
ALTER TABLE `product_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `promotion_items`
--
ALTER TABLE `promotion_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `social_media_accounts`
--
ALTER TABLE `social_media_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_media_orders`
--
ALTER TABLE `social_media_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_media_product_mapping`
--
ALTER TABLE `social_media_product_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_media_settings`
--
ALTER TABLE `social_media_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `unsubscribed_emails`
--
ALTER TABLE `unsubscribed_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actual_addresses`
--
ALTER TABLE `actual_addresses`
  ADD CONSTRAINT `actual_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `alert_logs`
--
ALTER TABLE `alert_logs`
  ADD CONSTRAINT `alert_logs_ibfk_1` FOREIGN KEY (`alert_id`) REFERENCES `inventory_alerts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `campaign_emails`
--
ALTER TABLE `campaign_emails`
  ADD CONSTRAINT `campaign_emails_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `card_details`
--
ALTER TABLE `card_details`
  ADD CONSTRAINT `card_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD CONSTRAINT `delivery_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD CONSTRAINT `inventory_alerts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_alerts_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_alerts_ibfk_3` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_ibfk_3` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_transfers`
--
ALTER TABLE `inventory_transfers`
  ADD CONSTRAINT `inventory_transfers_ibfk_1` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `inventory_transfers_ibfk_2` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `inventory_transfer_items`
--
ALTER TABLE `inventory_transfer_items`
  ADD CONSTRAINT `fk_transfer_item_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_transfer_items_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `inventory_transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transfer_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_transfer_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `privileges`
--
ALTER TABLE `privileges`
  ADD CONSTRAINT `privileges_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  ADD CONSTRAINT `fk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_barcodes`
--
ALTER TABLE `product_barcodes`
  ADD CONSTRAINT `product_barcodes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_barcodes_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `promotion_items`
--
ALTER TABLE `promotion_items`
  ADD CONSTRAINT `promotion_items_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `social_media_orders`
--
ALTER TABLE `social_media_orders`
  ADD CONSTRAINT `social_media_orders_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `social_media_product_mapping`
--
ALTER TABLE `social_media_product_mapping`
  ADD CONSTRAINT `social_media_product_mapping_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_group` FOREIGN KEY (`group_id`) REFERENCES `customer_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  ADD CONSTRAINT `warehouse_stock_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_stock_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_stock_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
