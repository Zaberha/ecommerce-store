-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 12, 2025 at 09:08 AM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

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
(32, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '', '+971561599855', 71);

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
(1, 'Advanced Promedia1', '+971552969432', 'info@advancedpromedia.com', 'Internet City1', 'Dubai', 'United Arab Emirates', 'admin', '$2y$10$DQlojWqI4TYHANuYF5Kp8O00oyjFLFS1FsBPJMjcGfA0aZsaIhcvK', 'uploads/logo.png', '#6e6e6d', '#fad0c9', '#d66edd', '#8b0a94', '\'Georgia\', serif', 'English', 'AED', 1.00, '5%', 'https://www.facebook.com/advancedpromedia', 'https://www.instagram.com/advancedpromedia/', 'Twitter Link', 'https://www.tiktok.com/@advancedpromedia', 'snapchat link', 'https://www.linkedin.com/company/advanced-promedia/', 'https://g.co/kgs/RkqBKxE', 'https://www.youtube.com/channel/UCU22Ik1e8BGU0BB2ls5JhUQ', 1, 10.00, 45, 1, 5000, 1.00, 5.00, 90);

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
(27, 36, 'United Arab Emirates', 'Dubai', 'Merdef', 'Majali', '8', '12', '2', '+971561599855');

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
(6, 'Sharjah', 40.00);

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
(28, 'Gift', 'Gift1', 5.00, '2025-04-11 18:45:25', 0, '2025-04-30 12:00:00', 1, 'gift', 'specific_user', 36, NULL);

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
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
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
  `payment_status` enum('received','pending','on_delivery') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `discount`, `payment_method`, `order_status`, `created_at`, `delivery_charges`, `tax_amount`, `discount_by_code`, `discount_code`, `discount_code_id`, `grand_total`, `method`, `picked_at`, `delivered_at`, `actual_delivery`, `refunded_at`, `cancelled_at`, `processed_at`, `payment_status`) VALUES
(71, 36, 368.00, 0.00, 'cash on delivery', 'completed', '2025-04-08 18:49:37', 40.00, 18.40, 18.40, 'Gift1', 28, 408.00, '7X', '2025-04-10', '2025-04-11', '2025-04-11', NULL, NULL, '2025-04-09 19:23:14', 'received');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(86, 71, 63, 1, 368.00);

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
(9, 'Pay Pal', 'uploads/payment_methods/67e98f29b229d.png', 'Easy Transfer with Pay Pal', 'paypal', '', '', 1, '2025-03-27 22:12:35');

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
  `is_new` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `created_at`, `category_id`, `is_offer`, `discount_percentage`, `product_code`, `active`, `minimum_order`, `max_order`, `stock_limit`, `min_stock`, `cost`, `free_shipping`, `return_allowed`, `affiliate`, `affiliate_link`, `main_image`, `image2`, `image3`, `image4`, `volume`, `weight`, `international_code`, `brand_id`, `supplier_id`, `origin_country`, `delivery_rate`, `delivery_duration`, `overview`, `sort_order`, `is_new`) VALUES
(53, 'Aghla', 'Top Notes: Bergamot, Lemon, Saffran.\r\nMiddle Notes: Orange Blossom Abs, Patchouli, Toffee.\r\nBase Notes: Amber, Vanilla, Ambroxan.', 660.00, '2025-03-11 14:23:27', 7, 0, NULL, 'SKU: 302074', 1, 1, 10, 0, 0, 380.00, 0, 0, 0, '', 'touch-of-oud-aghla-80ml-01.jpg', 'touch-of-oud-aghla-80ml-02.jpg', 'touch-of-oud-aghla-80ml-04.jpg', 'touch-of-oud-aghla-intense-edp-80ml-013.jpg', '20x12x5 cm', 0.20, '', 13, 5, 'UAE', 20, 4, 'Top Notes: Bergamot, Lemon, Saffran. Middle Notes: Orange Blossom Abs, Patchouli, Toffee. Base Notes: Amber, Vanilla, Ambroxan.', 0, 0),
(54, 'Lemar', 'Top Notes: Litchi, Bergamot.\r\nMiddle Notes: Rose, Vanilla.\r\nBase Notes: Amber.', 660.00, '2025-03-11 14:39:19', 7, 0, NULL, 'SKU: 301858', 1, 1, 10, 11, 0, 380.00, 0, 0, 0, '', 'touch-of-oud-lemar-edp-80ml-0111.jpg', 'touch-of-oud-lemar-edp-80ml-012.jpg', 'touch-of-oud-lemar-edp-80ml-014.jpg', 'touch-of-oud-lemar-edp-80ml-013.jpg', '20x12x5 cm', 0.20, '', 13, 5, 'UAE', 20, 4, '', 0, 0),
(55, 'Trat', 'Top Notes: Agarwood, Musk, Oudhy.\r\nMiddle Notes: Leather, Oud, Sandalwood.\r\nBase Notes: Amber, Musk, Oudhy.', 980.00, '2025-03-11 15:25:07', 7, 1, 10.00, 'SKU: 301764', 1, 1, 5, 8, 0, 700.00, 0, 0, 0, '', 'touch-of-oud-trat-edp-80ml-011.jpg', 'touch-of-oud-trat-edp-80ml-012.jpg', 'touch-of-oud-trat-edp-80ml-014.jpg', 'touch-of-oud-trat-edp-80ml-013.jpg', '18x12x5 cm', 0.20, '', 13, 5, 'UAE', 20, 4, 'Top Notes: Agarwood, Musk, Oudhy. Middle Notes: Leather, Oud, Sandalwood. Base Notes: Amber, Musk, Oudhy.', 0, 0),
(56, 'Saden', 'Top Notes: Bergamot, Coriander.\r\nMiddle Notes: Rose, Iris, Agrawood.\r\nBase Notes: Leather, Dry Wood, Oud.', 660.00, '2025-03-11 15:28:52', 7, 1, 5.00, 'SKU: 301553', 1, 1, 3, 10, 0, 390.00, 0, 0, 0, '', 'touch-of-oud-saden-edp-80ml-011.jpg', 'touch-of-oud-saden-edp-80ml-014.jpg', 'touch-of-oud-saden-edp-80ml-012.jpg', 'touch-of-oud-trat-edp-80ml-013.jpg', '20x12x5 cm', 0.20, '', 13, 5, 'UAE', 20, 4, '', 0, 0),
(57, 'Oud Royale 8Pcs Gift Set', 'The Oud Royale Collection features an exquisite 8-piece gift set including a luxurious crystal burner, high-quality oud wood, a Dukhoon Aghla incense blend, Rajwan, Rose Paradise spray, and premium accessories for an elegant oud experience.', 1850.00, '2025-03-11 16:32:16', 12, 0, NULL, 'SKU: 302265', 1, 1, 10, 48, 0, 1200.00, 0, 0, 0, '', 'oud-royale-8pcs-gift-set-01.jpg', 'oud-royale-8pcs-gift-set-02.jpg', 'oud-royale-8pcs-gift-set-03.jpg', 'oud-royale-8pcs-gift-set-04.jpg', '40x18x15 cm', 0.40, '', 13, 5, 'UAE', 30, 4, '', 0, 0),
(58, 'Luxury Perfumes Gift Set', 'This amazing set comes with:\r\nSalwan Perfume\r\nSaden Perfume\r\nTayma Perfume\r\nJelood Perfume\r\nMoroki with Oud Dukhoon\r\nD\'oud Hindi Suifi\r\nAgarwood Hindi Assam Double Supper 01\r\nAgarwood Hindi Assam Double Supper 02', 5200.00, '2025-03-11 16:40:35', 12, 0, NULL, 'SKU: 301903', 1, 1, 5, 8, 0, 4000.00, 1, 0, 0, '', 'llll.jpg', 'touch-of-oud-8-pcs-perfumes-set-02.jpg', 'touch-of-oud-set-box.jpg', 'lkl.jpg', '40x40x15 cm', 0.80, '', 13, 5, 'UAE', 0, 4, '', 0, 0),
(59, 'Aghla All Over Body Spray', 'Top Notes: Iris - Cedarwood Ambery - Sandal Wood\r\nMiddle Notes: Orange Blossom Abs Patchouli - Toffee\r\nBase Notes: Amber - Vanilla Ambroxan', 295.00, '2025-03-11 18:51:09', 10, 0, NULL, 'SKU: 301757', 1, 1, 5, 19, 0, 176.00, 0, 0, 0, '', 'Aghla-bottle.jpg', 'Aghla-With-box.jpg', 'Aghla-bottle - Copy.jpg', 'Aghla-With-box - Copy.jpg', '10x3x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0),
(60, 'Moroki With Oud', 'Top Notes: Floral, Rose.\r\nMiddle Notes: Saffron, Pink Pepper.\r\nBase Notes: Amber, Oud, Musk.', 620.00, '2025-03-11 18:54:45', 9, 0, NULL, 'SKU: 301766', 1, 1, 4, 11, 0, 400.00, 0, 0, 0, '', 'touch-of-oud-moroki-with-oud-24gm-01.jpg', 'touch-of-oud-moroki-with-oud-24gm-01 - Copy.jpg', 'touch-of-oud-moroki-with-oud-24gm-02.jpg', 'kj.jpg', '12x12x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0),
(61, 'Powder Musk All Over Body Spray', 'Top Notes: Violet, Hawthorn, Lily.\r\nMiddle Notes: Heliotrope, White Flowers, Incense.\r\nBase Notes: Amber, Musk, Vanilla.', 295.00, '2025-03-11 21:05:27', 10, 0, NULL, 'SKU: 301767', 1, 1, 4, 25, 0, 180.00, 0, 0, 0, '', 'touch-of-oud-powder-musk-all-over-body-spray-125ml-01 - Copy.jpg', 'touch-of-oud-powder-musk-all-over-body-spray-125ml-02 - Copy.jpg', 'touch-of-oud-powder-musk-all-over-body-spray-125ml-01.jpg', 'touch-of-oud-powder-musk-all-over-body-spray-125ml-02.jpg', '10x3x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0),
(62, 'Manthoor All Over Body Spray', 'Top Notes: Iris - Galbanum Pink Peppercorn Saffron\r\nMiddle Notes: Sage - Apricot - Amber Buffalo Grass - Rose\r\nBase Notes: Tonka Beans - Birch Wood Musky - Leather Patchouli', 295.00, '2025-03-11 21:08:23', 10, 0, NULL, 'SKU: 301754', 1, 1, 4, 20, 0, 180.00, 0, 0, 0, '', 'aa - Copy.jpg', 'bb - Copy.jpg', 'aa.jpg', 'bb.jpg', '10x3x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0),
(63, 'Oud Mubakhar Aseel', 'Top Notes: Rose, White Flower, Grapefruit.\r\nMiddle Notes: Jasmine, Iris, Cyclamen, Hawthorn.\r\nBase Notes: Tonak Bean, Musk, Sandalwood, Vanilla.', 368.00, '2025-03-11 21:17:59', 9, 0, NULL, 'SKU: 300915', 1, 1, 5, 28, 0, 240.00, 0, 0, 0, '', '1111.jpg', '222222 - Copy.jpg', '1111 - Copy.jpg', '222222.jpg', '15x10x10 cm', 0.20, '', 13, 5, 'UAE', 20, 4, '', 0, 0),
(64, 'Jala Hair Mist', 'A Floral vortex edges the hair and sneaks slowly to the head spreading a garden of flowers starting with Jasmine through Violet and Aniseed, ending with sweet warm Vanilla aroma blended in irresistible Cashmere wood scent.', 260.00, '2025-03-11 21:23:38', 11, 0, NULL, 'SKU: 301519', 1, 1, 3, 10, 0, 200.00, 0, 0, 0, '', 'aa.jpg', 'bb.jpg', 'aa - Copy.jpg', 'bb - Copy.jpg', '12x7x5 cm', 0.10, '', 13, 5, 'UAE', 20, 4, '', 0, 0),
(65, 'Ramadan Luxurious 2Pcs Gift Set', 'A luxurious Ramadan gift set featuring Moroki with Oud and Aghla by Touch of Oud perfumes, perfect for adding elegance to the seas.', 1200.00, '2025-03-11 21:27:43', 12, 0, NULL, 'SKU: 302256', 1, 1, 3, 12, 0, 900.00, 0, 0, 0, '', 'luxurious-2pc-gift-set-01.jpg', 'luxurious-2pc-gift-set-02.jpg', '2pc-gift-set-03.jpg', '2pc-gift-set-04.jpg', '40x30x15 cm', 0.30, '', 13, 5, 'UAE', 30, 4, '', 0, 0);

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
(36, 'Malek', 'Malek', 'Malek', NULL, '+971561599855', 'lebanonssp@gmail.com');

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
(4, 'Eid Mubarak', 'Special 10 % discount on selected items', '2025-04-01 21:50:00', '2025-04-30 20:50:00', 10.00, 1, '2025-04-02 20:51:55', '2025-04-07 19:15:51', 0, NULL),
(7, 'Spring Promotion', '5% discount on selected items', '2025-04-01 22:15:00', '2025-04-15 21:15:00', 5.00, 1, '2025-04-02 21:15:46', '2025-04-02 21:16:10', 0, NULL);

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
(6, 4, 55, 1, '2025-04-02 20:52:18'),
(8, 7, 56, 1, '2025-04-02 21:16:20');

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
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `group_id`, `username`, `password`, `email`, `phone`, `created_at`, `points`) VALUES
(36, NULL, 'Malek', '$2y$10$LMyqmFcLe586qziII/5WF.8P5jECQC2EN3BfZ2JHwJRLDwPA3Z/ka', 'lebanonssp@gmail.com', '+971561599855', '2025-04-11 15:49:20', 368);

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
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `fk_brand` (`brand_id`),
  ADD KEY `fk_supplier` (`supplier_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=560;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `delivery_area`
--
ALTER TABLE `delivery_area`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `delivery_options`
--
ALTER TABLE `delivery_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `discount_codes`
--
ALTER TABLE `discount_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `promotion_items`
--
ALTER TABLE `promotion_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actual_addresses`
--
ALTER TABLE `actual_addresses`
  ADD CONSTRAINT `actual_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

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
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  ADD CONSTRAINT `fk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

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
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_group` FOREIGN KEY (`group_id`) REFERENCES `customer_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
