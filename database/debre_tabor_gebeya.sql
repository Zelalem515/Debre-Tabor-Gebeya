-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 03:28 PM
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
-- Database: `debre_tabor_gebeya`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image_url`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
(14, 'Electronics', 'Electronic devices and gadgets', NULL, 'electronics', 1, '2025-12-18 16:09:31', '2025-12-18 16:09:31'),
(15, 'Clothing', 'Apparel and fashion items', NULL, 'clothing', 1, '2025-12-18 16:09:31', '2025-12-18 16:09:31'),
(16, 'Books', 'Books and educational materials', NULL, 'books', 1, '2025-12-18 16:09:31', '2025-12-18 16:09:31'),
(17, 'Home & Garden', 'Home and garden products', NULL, 'home-garden', 1, '2025-12-18 16:09:31', '2025-12-18 16:09:31'),
(18, 'Sports & Outdoors', 'Sports equipment and outdoor gear', NULL, 'sports-outdoors', 1, '2025-12-18 16:09:31', '2025-12-18 16:09:31'),
(19, 'Beauty & Personal Care', 'Beauty and personal care products', NULL, 'beauty-care', 1, '2025-12-18 16:09:31', '2025-12-18 16:09:31');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `product_id`, `customer_id`, `seller_id`, `last_message_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, NULL, '2025-12-19 16:48:06', '2025-12-19 16:48:06'),
(2, 6, 1, 3, '2025-12-19 17:01:07', '2025-12-19 16:55:34', '2025-12-19 17:01:07'),
(3, 6, 3, 3, NULL, '2025-12-19 17:07:32', '2025-12-19 17:07:32'),
(4, 3, 1, 3, '2025-12-19 17:08:19', '2025-12-19 17:08:07', '2025-12-19 17:08:19'),
(5, 7, 1, 3, '2025-12-19 20:40:23', '2025-12-19 17:56:18', '2025-12-19 20:40:23'),
(6, 10, 1, 3, '2025-12-19 21:06:47', '2025-12-19 21:06:27', '2025-12-19 21:06:47'),
(8, 15, 1, 15, '2025-12-20 20:03:14', '2025-12-20 20:03:09', '2025-12-20 20:03:14'),
(10, 13, 1, 3, '2025-12-23 08:30:23', '2025-12-23 08:29:46', '2025-12-23 08:30:23'),
(11, 9, 1, 3, NULL, '2025-12-23 09:55:59', '2025-12-23 09:55:59'),
(12, 19, 1, 3, '2025-12-24 17:59:26', '2025-12-24 17:59:12', '2025-12-24 17:59:26'),
(13, 20, 1, 3, '2026-01-01 12:07:12', '2026-01-01 12:07:07', '2026-01-01 12:07:12'),
(14, 21, 1, 3, '2026-01-18 17:06:02', '2026-01-18 17:05:57', '2026-01-18 17:06:02'),
(15, 22, 16, 15, NULL, '2026-03-10 07:44:21', '2026-03-10 07:44:21');

-- --------------------------------------------------------

--
-- Table structure for table `emails`
--

CREATE TABLE `emails` (
  `id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_content` longtext NOT NULL,
  `text_content` longtext DEFAULT NULL,
  `cc_emails` varchar(255) DEFAULT NULL,
  `bcc_emails` varchar(255) DEFAULT NULL,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `status` enum('queued','sent','failed') DEFAULT 'queued',
  `retry_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scheduled_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `email_id` int(11) DEFAULT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `available_stock` int(11) NOT NULL DEFAULT 0,
  `reserved_stock` int(11) NOT NULL DEFAULT 0,
  `sold_stock` int(11) NOT NULL DEFAULT 0,
  `total_stock` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_alerts`
--

CREATE TABLE `inventory_alerts` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `threshold` int(11) NOT NULL,
  `status` enum('active','resolved') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_reservations`
--

CREATE TABLE `inventory_reservations` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('reserved','released','sold') DEFAULT 'reserved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `receiver_id`, `product_id`, `message_text`, `is_read`, `read_at`, `created_at`) VALUES
(2, 1, 1, 3, 1, 'hello bro is it available', 0, NULL, '2025-12-19 16:54:36'),
(3, 1, 1, 3, 1, 'hi can i get it', 0, NULL, '2025-12-19 16:54:53'),
(4, 2, 1, 3, 6, 'hello can i get it', 1, '2025-12-19 17:02:00', '2025-12-19 16:55:49'),
(5, 2, 1, 3, 6, 'please send me', 1, '2025-12-19 17:02:00', '2025-12-19 16:56:45'),
(6, 2, 1, 3, 6, 'good see this', 1, '2025-12-19 17:02:00', '2025-12-19 17:01:07'),
(7, 4, 1, 3, 3, 'hi see this error', 0, NULL, '2025-12-19 17:08:19'),
(8, 2, 3, 1, 6, 'ok', 1, '2025-12-19 17:11:32', '2025-12-19 17:10:56'),
(9, 1, 3, 1, 1, 'cash 123 birr', 0, NULL, '2025-12-19 17:14:18'),
(10, 3, 3, 3, 6, 'hello', 0, NULL, '2025-12-19 17:14:41'),
(11, 4, 3, 1, 3, 'ok i will give', 0, NULL, '2025-12-19 17:24:34'),
(12, 5, 1, 3, 7, 'hi sorry', 0, NULL, '2025-12-19 17:56:35'),
(13, 5, 1, 3, 7, 'hello is it available', 0, NULL, '2025-12-19 18:03:27'),
(14, 5, 1, 3, 7, 'sint birr new', 0, NULL, '2025-12-19 20:40:23'),
(15, 5, 3, 1, 7, '255', 0, NULL, '2025-12-19 20:40:54'),
(16, 6, 1, 3, 10, 'yih gabi mechershaw sint naw', 0, NULL, '2025-12-19 21:06:47'),
(17, 4, 3, 1, 3, 'ok don\'t worry about the time', 0, NULL, '2025-12-20 19:33:50'),
(18, 8, 1, 15, 15, 'lakew', 0, NULL, '2025-12-20 20:03:14'),
(20, 10, 1, 3, 13, 'does it available now? in what price', 1, '2025-12-23 08:30:47', '2025-12-23 08:30:23'),
(21, 10, 3, 1, 13, 'yes of course.it is 500Birr', 0, NULL, '2025-12-23 08:31:25'),
(22, 12, 1, 3, 19, 'yih suf sint yishetal', 1, '2025-12-24 17:59:48', '2025-12-24 17:59:26'),
(23, 12, 3, 1, 19, '10000br', 0, NULL, '2025-12-24 18:00:08'),
(24, 13, 1, 3, 20, 'bezih birr tishetaleh', 0, NULL, '2026-01-01 12:07:12'),
(25, 13, 3, 1, 20, 'awe', 0, NULL, '2026-01-01 12:11:36'),
(26, 14, 1, 3, 21, 'hello', 0, NULL, '2026-01-18 17:06:02');

-- --------------------------------------------------------

--
-- Table structure for table `message_notifications`
--

CREATE TABLE `message_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `unread_count` int(11) DEFAULT 0,
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_notifications`
--

INSERT INTO `message_notifications` (`id`, `user_id`, `conversation_id`, `unread_count`, `last_checked_at`, `updated_at`) VALUES
(1, 3, 2, 0, '2025-12-19 17:15:24', '2025-12-19 17:15:24'),
(2, 3, 4, 1, NULL, '2025-12-19 17:08:19'),
(3, 3, 5, 3, NULL, '2025-12-19 20:40:23'),
(4, 3, 6, 1, NULL, '2025-12-19 21:06:47'),
(5, 15, 8, 1, NULL, '2025-12-20 20:03:14'),
(7, 3, 10, 0, '2025-12-23 08:32:58', '2025-12-23 08:32:58'),
(8, 3, 12, 0, '2025-12-24 18:30:26', '2025-12-24 18:30:26'),
(9, 3, 13, 1, NULL, '2026-01-01 12:07:12'),
(10, 3, 14, 1, NULL, '2026-01-18 17:06:02');

-- --------------------------------------------------------

--
-- Table structure for table `message_read_receipts`
--

CREATE TABLE `message_read_receipts` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `read_by` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_order_id` int(11) DEFAULT NULL,
  `related_product_id` int(11) DEFAULT NULL,
  `related_message_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_messages` tinyint(1) DEFAULT 1,
  `email_orders` tinyint(1) DEFAULT 1,
  `email_promotions` tinyint(1) DEFAULT 0,
  `sms_messages` tinyint(1) DEFAULT 0,
  `sms_orders` tinyint(1) DEFAULT 0,
  `in_app_notifications` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Paid','Shipped','Delivered','Cancelled','pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'Pending',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `seller_id`, `status`, `payment_status`, `transaction_id`, `stripe_payment_intent_id`, `total_amount`, `shipping_address`, `tracking_number`, `notes`, `created_at`, `updated_at`, `payment_method`) VALUES
(2, 1, NULL, 'Pending', 'pending', NULL, NULL, 100.00, 'Test Address', NULL, NULL, '2025-12-20 19:38:10', '2025-12-20 19:38:10', 'Telebirr'),
(3, 1, NULL, 'Pending', 'pending', NULL, NULL, 8500.00, 'Default Address', NULL, NULL, '2025-12-20 19:45:02', '2025-12-20 19:45:02', 'Telebirr'),
(4, 1, NULL, 'Pending', 'pending', NULL, NULL, 500.00, 'Default Address', NULL, NULL, '2025-12-20 20:04:05', '2025-12-20 20:04:05', 'Telebirr'),
(5, 1, NULL, 'Shipped', 'pending', NULL, NULL, 300.00, 'Default Address', NULL, NULL, '2025-12-20 20:07:47', '2026-01-01 12:11:05', 'Telebirr'),
(6, 1, NULL, 'Pending', 'pending', NULL, NULL, 56700.00, 'Default Address', NULL, NULL, '2025-12-21 12:38:12', '2025-12-21 12:38:12', 'Telebirr'),
(7, 1, NULL, 'Pending', 'pending', NULL, NULL, 20000.00, 'Pending Address', NULL, NULL, '2025-12-21 15:51:11', '2025-12-21 15:51:11', 'Pending Method'),
(8, 1, NULL, 'Pending', 'pending', NULL, NULL, 20000.00, 'Pending Address', NULL, NULL, '2025-12-23 09:52:19', '2025-12-23 09:52:19', 'Pending Method'),
(9, 1, NULL, 'Delivered', 'pending', NULL, NULL, 200.00, 'Pending Address', NULL, NULL, '2026-01-01 12:07:39', '2026-01-01 12:10:38', 'Pending Method'),
(10, 1, NULL, 'Pending', 'pending', NULL, NULL, 450.00, 'Pending Address', NULL, NULL, '2026-01-18 17:06:11', '2026-01-18 17:06:11', 'Pending Method'),
(12, 1, NULL, 'Delivered', 'pending', NULL, NULL, 65000.00, 'Pending Address', NULL, NULL, '2026-01-22 16:41:05', '2026-03-09 04:41:18', 'Pending Method'),
(13, 1, NULL, 'Pending', 'pending', NULL, NULL, 1232.00, 'Pending Address', NULL, NULL, '2026-03-04 16:50:14', '2026-03-04 16:50:14', 'Pending Method');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `seller_id`, `quantity`, `unit_price`, `price`, `subtotal`, `created_at`) VALUES
(4, 3, 3, 3, 1, 8500.00, 0.00, 8500.00, '2025-12-20 19:45:02'),
(5, 4, 11, 3, 1, 500.00, 0.00, 500.00, '2025-12-20 20:04:05'),
(6, 5, 12, 3, 1, 300.00, 0.00, 300.00, '2025-12-20 20:07:47'),
(7, 6, 2, 3, 1, 56700.00, 0.00, 56700.00, '2025-12-21 12:38:12'),
(8, 7, 1, 3, 1, 20000.00, 0.00, 20000.00, '2025-12-21 15:51:11'),
(9, 8, 1, 3, 1, 20000.00, 0.00, 20000.00, '2025-12-23 09:52:19'),
(10, 9, 20, 3, 2, 100.00, 0.00, 200.00, '2026-01-01 12:07:39'),
(11, 10, 21, 3, 1, 450.00, 0.00, 450.00, '2026-01-18 17:06:11'),
(13, 12, 22, 15, 1, 65000.00, 0.00, 65000.00, '2026-01-22 16:41:05'),
(14, 13, 15, 15, 1, 1232.00, 0.00, 1232.00, '2026-03-04 16:50:14');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('pending','succeeded','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `transaction_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `stripe_payment_intent_id`, `amount`, `currency`, `status`, `payment_method`, `error_message`, `created_at`, `updated_at`, `transaction_id`) VALUES
(2, 3, NULL, 8500.00, 'USD', 'failed', 'Telebirr', NULL, '2025-12-20 19:46:04', '2025-12-20 19:46:04', 'TEL-1766259964-2320');

-- --------------------------------------------------------

--
-- Table structure for table `payment_errors`
--

CREATE TABLE `payment_errors` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `error_type` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `retry_count` int(11) DEFAULT 0,
  `is_retryable` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_webhooks`
--

CREATE TABLE `payment_webhooks` (
  `id` int(11) NOT NULL,
  `stripe_event_id` varchar(255) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `processed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `image_url`, `rating`, `review_count`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, 14, 'Smart phone Samsung', 'Brand:New\r\nRam:4GB\r\nstorage:128Gb\r\nbattery:6000mAh\r\nColor:silver\r\n', 20000.00, 3, NULL, 0.00, 0, 1, '2025-12-18 16:19:28', '2025-12-23 10:20:45'),
(2, 3, 14, 'Hp Pavilon laptop', 'brand:new\r\nRam 16Gb\r\nstorage:512GB ssd\r\ncore i7 12 gneration', 56700.00, 3, NULL, 0.00, 0, 1, '2025-12-18 16:21:31', '2025-12-23 10:17:07'),
(3, 3, 15, 'Suit', 'new brand japan suit for man', 8500.00, 2, NULL, 0.00, 0, 1, '2025-12-18 16:37:37', '2025-12-20 19:45:02'),
(6, 3, 15, ' የሃበሻ ቀሚስ', 'ምርጥ የሃበሻ ቀሚስ ለአዋቂወች እና ለህጻናት \r\nበፈለጉት ቀለም እና ዲዛይን አለን ።', 2350.00, 5, NULL, 0.00, 0, 1, '2025-12-18 17:39:38', '2025-12-23 10:09:22'),
(7, 3, 14, 'iphone 15 pro max', 'Brand: new\r\nRAM:8GB\r\nStorage:256GB\r\nBattery :6000mAh🔋 \r\nCamera:300px resolution\r\nwith different color ', 120000.00, 3, NULL, 0.00, 0, 1, '2025-12-18 17:50:22', '2025-12-23 10:05:24'),
(8, 3, 15, 'Gabi', 'የሀበሻ ምርጥ ጋቢ ለወንዶች ጥለት በመረጡት ይሆናል።', 1250.00, 4, NULL, 0.00, 0, 1, '2025-12-18 19:07:39', '2025-12-23 10:02:08'),
(9, 3, 15, 'Habesha Dress ', 'habesha female dress\r\nfor buity and cultures ', 12000.00, 3, NULL, 0.00, 0, 1, '2025-12-19 20:59:24', '2025-12-19 20:59:24'),
(10, 3, 15, 'Gabi', 'Ye Ethiopian smart Gabi le bird and le wubet hulu mehon yrmichil', 10000.00, 4, NULL, 0.00, 0, 1, '2025-12-19 21:00:49', '2025-12-19 21:00:49'),
(11, 3, 15, 'Cap', 'Cap for buity and sun care ', 500.00, 5, NULL, 0.00, 0, 1, '2025-12-19 21:02:13', '2025-12-23 08:27:09'),
(12, 3, 16, 'Ethio History ', 'Hidtory of ethiopia Books \r\n\r\nauthor:Blaten geta hiruy\r\n', 300.00, 2, NULL, 0.00, 0, 1, '2025-12-19 21:04:09', '2025-12-23 08:23:14'),
(13, 3, 15, 'የፖርት ትጥቅ ', 'orignal new Arsenal kit ', 500.00, 2, NULL, 0.00, 0, 1, '2025-12-19 21:05:30', '2025-12-23 10:11:13'),
(15, 15, 17, 'mobile alech', 'smart mobile new gizu ahununu', 1232.00, 2, NULL, 0.00, 0, 1, '2025-12-20 20:02:45', '2026-03-04 16:50:14'),
(16, 3, 15, 'Shoes ', 'New Fashion Addidas Shoes with different color', 2300.00, 4, NULL, 0.00, 0, 1, '2025-12-23 10:23:55', '2025-12-23 10:23:55'),
(17, 3, 15, 'T-shirt', 'እጅግዬ ጉርድ ምርጥ ምርጥ ቲሽርት አለ', 450.00, 10, NULL, 0.00, 0, 1, '2025-12-23 10:26:38', '2025-12-23 10:26:38'),
(18, 3, 15, 'Short', 'ለስፖርት የሚሆኑ ቁምጣዎች', 250.00, 3, NULL, 0.00, 0, 1, '2025-12-23 10:28:35', '2025-12-23 10:28:35'),
(19, 3, 15, 'Suit', 'fashion suit for wedding \r\nWe have discounts if you come in group', 5700.00, 5, NULL, 0.00, 0, 1, '2025-12-23 10:31:17', '2025-12-23 10:31:17'),
(20, 3, 15, 'Socks', 'socks for kid and adult\'s ', 100.00, 13, NULL, 0.00, 0, 1, '2025-12-23 10:32:18', '2026-01-01 12:07:39'),
(21, 3, 16, 'programming book', 'learn all programming language in one ', 450.00, 5, NULL, 0.00, 0, 1, '2025-12-23 10:34:02', '2026-01-18 17:06:11'),
(22, 15, 14, 'Hp Pavilon laptop', 'Brand:New\r\nprocessor:Intel core i5 11 Generation\r\nGraphics:2GB NVIDIA MX450 Dedicated Graphics card \r\nRam:16Gb\r\nstorage:512SSD\r\n5h+ Battery life\r\nAlmunium body\r\n', 65000.00, 2, NULL, 0.00, 0, 1, '2025-12-23 10:41:50', '2026-01-22 16:41:05');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `image_order`, `is_primary`, `created_at`) VALUES
(7, 3, 'images/uploads/products/cap.png', 0, 1, '2025-12-19 12:23:04'),
(8, 3, 'images/uploads/products/cap1.png', 1, 0, '2025-12-19 12:23:04'),
(9, 3, 'images/uploads/products/dres.png', 2, 0, '2025-12-19 12:23:04'),
(25, 9, 'images/uploads/product_9_1.png', 0, 1, '2025-12-19 20:59:24'),
(26, 9, 'images/uploads/product_9_2.png', 1, 0, '2025-12-19 20:59:24'),
(27, 10, 'images/uploads/product_10_1.png', 0, 1, '2025-12-19 21:00:49'),
(28, 10, 'images/uploads/product_10_2.png', 1, 0, '2025-12-19 21:00:49'),
(29, 10, 'images/uploads/product_10_3.png', 2, 0, '2025-12-19 21:00:49'),
(33, 12, 'images/uploads/product_12_1.jpg', 0, 1, '2025-12-19 21:04:09'),
(34, 12, 'images/uploads/product_12_2.jpg', 1, 0, '2025-12-19 21:04:09'),
(35, 13, 'images/uploads/product_13_1.png', 0, 1, '2025-12-19 21:05:30'),
(36, 13, 'images/uploads/product_13_2.png', 1, 0, '2025-12-19 21:05:30'),
(37, 13, 'images/uploads/product_13_3.png', 2, 0, '2025-12-19 21:05:30'),
(38, 15, 'images/uploads/product_15_1.png', 0, 1, '2025-12-20 20:02:45'),
(39, 15, 'images/uploads/product_15_2.png', 1, 0, '2025-12-20 20:02:45'),
(40, 11, 'images/uploads/product_11_1.png', 0, 1, '2025-12-23 08:27:09'),
(41, 11, 'images/uploads/product_11_2.png', 1, 0, '2025-12-23 08:27:09'),
(42, 11, 'images/uploads/product_11_3.png', 2, 0, '2025-12-23 08:27:09'),
(43, 8, 'images/uploads/product_8_1.png', 0, 1, '2025-12-23 10:02:08'),
(44, 8, 'images/uploads/product_8_2.png', 1, 0, '2025-12-23 10:02:08'),
(45, 8, 'images/uploads/product_8_3.png', 2, 0, '2025-12-23 10:02:08'),
(46, 7, 'images/uploads/product_7_1.png', 0, 1, '2025-12-23 10:05:24'),
(47, 7, 'images/uploads/product_7_2.png', 1, 0, '2025-12-23 10:05:24'),
(48, 6, 'images/uploads/product_6_1.png', 0, 1, '2025-12-23 10:09:22'),
(49, 6, 'images/uploads/product_6_2.png', 1, 0, '2025-12-23 10:09:22'),
(50, 6, 'images/uploads/product_6_3.png', 2, 0, '2025-12-23 10:09:22'),
(51, 2, 'images/uploads/product_2_1.png', 0, 1, '2025-12-23 10:17:07'),
(52, 2, 'images/uploads/product_2_2.png', 1, 0, '2025-12-23 10:17:07'),
(53, 2, 'images/uploads/product_2_3.png', 2, 0, '2025-12-23 10:17:07'),
(54, 1, 'images/uploads/product_1_1.png', 0, 1, '2025-12-23 10:20:45'),
(55, 1, 'images/uploads/product_1_2.png', 1, 0, '2025-12-23 10:20:45'),
(56, 1, 'images/uploads/product_1_3.png', 2, 0, '2025-12-23 10:20:45'),
(57, 16, 'images/uploads/product_16_1.png', 0, 1, '2025-12-23 10:23:55'),
(58, 16, 'images/uploads/product_16_2.png', 1, 0, '2025-12-23 10:23:55'),
(59, 16, 'images/uploads/product_16_3.png', 2, 0, '2025-12-23 10:23:55'),
(60, 17, 'images/uploads/product_17_1.png', 0, 1, '2025-12-23 10:26:38'),
(61, 17, 'images/uploads/product_17_2.webp', 1, 0, '2025-12-23 10:26:38'),
(62, 17, 'images/uploads/product_17_3.webp', 2, 0, '2025-12-23 10:26:38'),
(63, 18, 'images/uploads/product_18_1.png', 0, 1, '2025-12-23 10:28:35'),
(64, 18, 'images/uploads/product_18_2.png', 1, 0, '2025-12-23 10:28:35'),
(65, 19, 'images/uploads/product_19_1.png', 0, 1, '2025-12-23 10:31:17'),
(66, 19, 'images/uploads/product_19_2.png', 1, 0, '2025-12-23 10:31:17'),
(67, 19, 'images/uploads/product_19_3.png', 2, 0, '2025-12-23 10:31:17'),
(68, 20, 'images/uploads/product_20_1.jpg', 0, 1, '2025-12-23 10:32:18'),
(69, 21, 'images/uploads/product_21_1.webp', 0, 1, '2025-12-23 10:34:02'),
(70, 21, 'images/uploads/product_21_2.webp', 1, 0, '2025-12-23 10:34:02'),
(71, 22, 'images/uploads/product_22_1.png', 0, 1, '2025-12-23 10:41:50'),
(72, 22, 'images/uploads/product_22_2.png', 1, 0, '2025-12-23 10:41:50'),
(73, 22, 'images/uploads/product_22_3.png', 2, 0, '2025-12-23 10:41:50');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','seller','admin') DEFAULT 'customer',
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `phone`, `role`, `profile_picture`, `bio`, `address`, `city`, `country`, `postal_code`, `is_verified`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'zed1234@gmail.com', '$2y$10$p6pUzoJ2LSzIS.MES23cFe2SGW2EKejU8asgNDRdBvM0yUWnJ/you', 'Zelalem Birhan', NULL, 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-12-18 13:28:39', '2025-12-18 13:28:39'),
(3, 'muluken@lbl.com', '$2y$10$/2l3nhbLydAH6tR5TtvjU..yNo7U7qFl1Rcrp1STt0eugYNyxd8h2', 'Muluken Ayanew', '0923619113', 'seller', NULL, 'ነጋዴ', 'ደብረታቦር', 'ከተማ', 'ኢትዮጵያ', NULL, 0, 1, '2025-12-18 13:36:21', '2025-12-23 10:45:30'),
(4, 'admin@lalibela.com', '$2y$10$p1K8ebMGs/HJfoOMP4RxK.kk7Kk.euR8pF0pCCiT/gTyPdDkzJtOC', 'Admin User', NULL, 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-12-18 16:09:31', '2025-12-18 16:17:32'),
(15, 'mululasta@lbs.com', '$2y$10$I40SomZ.iNgMC8UCw1k2peSm9jjPXIjeamnR6snJjGFiaIqgHGDMK', 'Mullugeta Abey', NULL, 'seller', 'images/uploads/profile_1766260833_69470061207fa.jpg', NULL, NULL, NULL, NULL, NULL, 0, 1, '2025-12-20 20:00:33', '2025-12-20 20:00:33'),
(16, 'kasu@dtu.com', '$2y$10$2OsNSDiP8nq54lXSn7yaJO.9/bkaS0JRZUKyiivPfYJ400XaPpWg2', 'Kasech Biru', NULL, 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2026-03-10 07:14:51', '2026-03-10 07:14:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation` (`product_id`,`customer_id`,`seller_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_seller_id` (`seller_id`),
  ADD KEY `idx_last_message_at` (`last_message_at`);

--
-- Indexes for table `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_recipient_email` (`recipient_email`),
  ADD KEY `idx_template_name` (`template_name`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_id` (`email_id`),
  ADD KEY `idx_recipient_email` (`recipient_email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`),
  ADD KEY `idx_available_stock` (`available_stock`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_reason` (`reason`);

--
-- Indexes for table `inventory_reservations`
--
ALTER TABLE `inventory_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation_id` (`conversation_id`),
  ADD KEY `idx_sender_id` (`sender_id`),
  ADD KEY `idx_receiver_id` (`receiver_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `message_notifications`
--
ALTER TABLE `message_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_notification` (`user_id`,`conversation_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_conversation_id` (`conversation_id`);

--
-- Indexes for table `message_read_receipts`
--
ALTER TABLE `message_read_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_read` (`message_id`,`read_by`),
  ADD KEY `read_by` (`read_by`),
  ADD KEY `idx_message_id` (`message_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `related_order_id` (`related_order_id`),
  ADD KEY `related_product_id` (`related_product_id`),
  ADD KEY `related_message_id` (`related_message_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_payment_intent_id` (`stripe_payment_intent_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_seller_id` (`seller_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_stripe_payment_intent_id` (`stripe_payment_intent_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `fk_order_items_seller` (`seller_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_payment_intent_id` (`stripe_payment_intent_id`),
  ADD UNIQUE KEY `stripe_payment_intent_id_2` (`stripe_payment_intent_id`),
  ADD KEY `idx_stripe_payment_intent_id` (`stripe_payment_intent_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `payment_errors`
--
ALTER TABLE `payment_errors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_error_type` (`error_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `payment_webhooks`
--
ALTER TABLE `payment_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_event_id` (`stripe_event_id`),
  ADD KEY `idx_stripe_event_id` (`stripe_event_id`),
  ADD KEY `idx_processed` (`processed`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_event_type` (`event_type`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_seller_id` (`seller_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_primary` (`is_primary`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `emails`
--
ALTER TABLE `emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_reservations`
--
ALTER TABLE `inventory_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `message_notifications`
--
ALTER TABLE `message_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `message_read_receipts`
--
ALTER TABLE `message_read_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_errors`
--
ALTER TABLE `payment_errors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_webhooks`
--
ALTER TABLE `payment_webhooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`email_id`) REFERENCES `emails` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD CONSTRAINT `inventory_alerts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_logs_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_reservations`
--
ALTER TABLE `inventory_reservations`
  ADD CONSTRAINT `inventory_reservations_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_reservations_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_4` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_notifications`
--
ALTER TABLE `message_notifications`
  ADD CONSTRAINT `message_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_notifications_ibfk_2` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_read_receipts`
--
ALTER TABLE `message_read_receipts`
  ADD CONSTRAINT `message_read_receipts_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_read_receipts_ibfk_2` FOREIGN KEY (`read_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`related_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`related_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_4` FOREIGN KEY (`related_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_errors`
--
ALTER TABLE `payment_errors`
  ADD CONSTRAINT `payment_errors_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
