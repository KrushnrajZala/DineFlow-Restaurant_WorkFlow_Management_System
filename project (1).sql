-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2026 at 06:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `DataFlow`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`) VALUES
(1, 'Admin', 'zala@gmail.com', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(10) NOT NULL,
  `type` enum('table','parcel') NOT NULL,
  `ref_id` int(10) NOT NULL COMMENT 'table_id or parcel_number',
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','upi','credit_card') DEFAULT 'cash',
  `billed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `type`, `ref_id`, `total_amount`, `payment_method`, `billed_at`) VALUES
(1, 'table', 1, 60.00, 'upi', '2026-05-23 10:09:07'),
(2, 'table', 2, 220.00, 'upi', '2026-05-23 10:09:36'),
(3, 'parcel', 1, 100.00, 'cash', '2026-05-23 10:10:23'),
(4, 'table', 1, 540.00, 'upi', '2026-05-23 11:21:44'),
(5, 'table', 2, 80.00, 'cash', '2026-05-25 05:31:13'),
(6, 'table', 1, 460.00, 'cash', '2026-05-27 11:17:18'),
(7, 'table', 1, 40.00, 'cash', '2026-05-27 11:25:04'),
(8, 'table', 1, 40.00, 'cash', '2026-05-29 06:38:50'),
(9, 'table', 1, 280.00, 'cash', '2026-05-29 07:08:41'),
(10, 'parcel', 4, 40.00, 'upi', '2026-05-29 07:12:34');

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(10) NOT NULL,
  `type` enum('table','parcel') NOT NULL,
  `ref_id` int(10) NOT NULL,
  `role` enum('admin','waiter','cook') NOT NULL,
  `user_id` int(10) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `type`, `ref_id`, `role`, `user_id`, `user_name`, `message`, `created_at`) VALUES
(2, 'table', 2, 'waiter', 1, 'Ravi Sharma', 'bro  do it', '2026-05-25 05:28:42'),
(5, 'table', 2, 'waiter', 1, 'Ravi Sharma', 'hello', '2026-05-29 05:52:22'),
(6, 'parcel', 6, 'waiter', 1, 'Ravi Sharma', 'made spicy', '2026-05-30 07:38:16'),
(7, 'table', 1, 'waiter', 1, 'Ravi Sharma', 'fast', '2026-05-30 07:43:22'),
(8, 'parcel', 6, 'admin', 1, 'Admin', 'hello', '2026-05-30 08:02:47');

-- --------------------------------------------------------

--
-- Table structure for table `complete_orders`
--

CREATE TABLE `complete_orders` (
  `id` int(10) NOT NULL,
  `order_type` enum('table','parcel') NOT NULL COMMENT 'table = dine-in, parcel = takeaway',
  `ref_id` int(10) NOT NULL COMMENT 'table_id for table orders, parcel_number for parcel',
  `waiter_id` int(10) NOT NULL,
  `waiter_name` varchar(50) NOT NULL,
  `menu_id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(5) DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL COMMENT 'price * quantity',
  `payment_method` enum('cash','upi','credit_card','pending') DEFAULT 'cash',
  `bill_total` decimal(10,2) DEFAULT 0.00 COMMENT 'total bill amount for this order group',
  `customer_name` varchar(100) DEFAULT '' COMMENT 'customer name (parcel only)',
  `customer_phone` varchar(15) DEFAULT '' COMMENT 'customer phone (parcel only)',
  `billed_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'when bill was printed',
  `billed_date` date GENERATED ALWAYS AS (cast(`billed_at` as date)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Permanent record of all completed & billed orders';

--
-- Dumping data for table `complete_orders`
--

INSERT INTO `complete_orders` (`id`, `order_type`, `ref_id`, `waiter_id`, `waiter_name`, `menu_id`, `item_name`, `price`, `quantity`, `subtotal`, `payment_method`, `bill_total`, `customer_name`, `customer_phone`, `billed_at`) VALUES
(1, 'table', 1, 1, 'Ravi Sharma', 10, 'Butter Naan', 40.00, 1, 40.00, 'cash', 40.00, '', '', '2026-09-08 06:38:51'),
(2, 'table', 1, 1, 'Ravi Sharma', 10, 'Butter Naan', 40.00, 1, 40.00, 'cash', 280.00, '', '', '2026-09-08 07:08:41'),
(3, 'table', 1, 1, 'Ravi Sharma', 3, 'Shahi Paneer', 240.00, 1, 240.00, 'cash', 280.00, '', '', '2026-09-08 07:08:41'),
(4, 'parcel', 4, 1, 'Ravi Sharma', 10, 'Butter Naan', 40.00, 1, 40.00, 'upi', 40.00, 'rajesh sharma', '9854565254', '2026-05-29 07:12:34');

-- --------------------------------------------------------

--
-- Table structure for table `cook`
--

CREATE TABLE `cook` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cook`
--

INSERT INTO `cook` (`id`, `name`, `email`, `password`) VALUES
(1, 'Chef Kumar', 'cook@gmail.com', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `customer_reviews`
--

CREATE TABLE `customer_reviews` (
  `id` int(10) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `visit_count` int(5) DEFAULT 1,
  `rating` tinyint(1) DEFAULT 5,
  `review` text NOT NULL,
  `waiter_id` int(10) NOT NULL,
  `waiter_name` varchar(50) NOT NULL,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_reviews`
--

INSERT INTO `customer_reviews` (`id`, `customer_name`, `mobile`, `visit_count`, `rating`, `review`, `waiter_id`, `waiter_name`, `added_at`) VALUES
(1, 'rajesh sharma', '7012455325', 3, 4, 'this good rsturent but i prefer salt less', 1, 'Ravi Sharma', '2026-05-23 07:16:02');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT 'General',
  `unit` varchar(20) DEFAULT 'kg',
  `quantity` decimal(10,2) DEFAULT 0.00,
  `min_quantity` decimal(10,2) DEFAULT 5.00,
  `updated_by` varchar(50) DEFAULT '',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `category`, `unit`, `quantity`, `min_quantity`, `updated_by`, `updated_at`, `created_at`) VALUES
(1, 'Tomato', 'Vegetables', 'kg', 10.00, 3.00, 'Admin', '2026-05-23 05:42:10', '2026-05-23 05:42:10'),
(2, 'Potato', 'Vegetables', 'kg', 25.00, 5.00, 'Admin', '2026-05-23 07:14:58', '2026-05-23 07:14:58');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(10) NOT NULL,
  `inv_id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `action` enum('add','remove') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `note` varchar(200) DEFAULT '',
  `done_by` varchar(50) NOT NULL,
  `done_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_log`
--

INSERT INTO `inventory_log` (`id`, `inv_id`, `item_name`, `action`, `quantity`, `note`, `done_by`, `done_at`) VALUES
(1, 2, 'Potato', 'add', 25.00, 'Initial stock added', 'Admin', '2026-05-23 07:14:59');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT 'Main Course',
  `cooking_time` int(5) DEFAULT 15 COMMENT 'in minutes',
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `price`, `category`, `cooking_time`, `is_available`, `created_at`) VALUES
(1, 'Paneer Butter Masala', 220.00, 'Main Course', 20, 1, '2026-05-22 10:36:15'),
(2, 'Dal Makhani', 180.00, 'Main Course', 25, 1, '2026-05-22 10:36:15'),
(3, 'Shahi Paneer', 240.00, 'Main Course', 20, 1, '2026-05-22 10:36:15'),
(4, 'Veg Biryani', 200.00, 'Rice', 20, 1, '2026-05-22 10:36:15'),
(5, 'Chicken Biryani', 280.00, 'Rice', 25, 1, '2026-05-22 10:36:15'),
(6, 'Chicken Tikka', 280.00, 'Starter', 15, 1, '2026-05-22 10:36:15'),
(7, 'Veg Manchurian', 160.00, 'Starter', 15, 1, '2026-05-22 10:36:15'),
(8, 'Samosa (2 pcs)', 40.00, 'Starter', 10, 1, '2026-05-22 10:36:15'),
(9, 'Spring Roll', 80.00, 'Starter', 12, 1, '2026-05-22 10:36:15'),
(10, 'Butter Naan', 40.00, 'Bread', 10, 0, '2026-05-22 10:36:15'),
(11, 'Garlic Naan', 50.00, 'Bread', 10, 0, '2026-05-22 10:36:15'),
(12, 'Roti', 20.00, 'Bread', 5, 1, '2026-05-22 10:36:15'),
(13, 'Paratha', 35.00, 'Bread', 8, 1, '2026-05-22 10:36:15'),
(14, 'Mango Lassi', 80.00, 'Drinks', 5, 1, '2026-05-22 10:36:15'),
(15, 'Masala Chai', 30.00, 'Drinks', 5, 1, '2026-05-22 10:36:15'),
(16, 'Cold Coffee', 90.00, 'Drinks', 5, 1, '2026-05-22 10:36:15'),
(17, 'Fresh Lime Soda', 60.00, 'Drinks', 3, 1, '2026-05-22 10:36:15'),
(18, 'Gulab Jamun', 60.00, 'Dessert', 5, 1, '2026-05-22 10:36:15'),
(19, 'Ice Cream (2 scoops)', 80.00, 'Dessert', 3, 1, '2026-05-22 10:36:15'),
(20, 'Rasmalai', 70.00, 'Dessert', 5, 1, '2026-05-22 10:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) NOT NULL,
  `from_type` enum('admin','waiter','cook') NOT NULL,
  `from_id` int(10) NOT NULL,
  `from_name` varchar(50) NOT NULL,
  `to_type` enum('admin','waiter','cook','all') NOT NULL,
  `message` varchar(300) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `from_type`, `from_id`, `from_name`, `to_type`, `message`, `is_read`, `created_at`) VALUES
(1, 'waiter', 1, 'Ravi Sharma', 'admin', '⚠️ Need admin attention at the floor!', 1, '2026-05-23 05:51:05'),
(2, 'waiter', 1, 'Ravi Sharma', 'cook', '?️ New order placed at table, please check!', 1, '2026-05-23 05:51:06'),
(3, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Customer is waiting for order, please hurry!', 1, '2026-05-23 05:51:06'),
(4, 'waiter', 1, 'Ravi Sharma', 'cook', '? Table order is ready to be picked up!', 1, '2026-05-23 05:51:08'),
(5, 'waiter', 1, 'Ravi Sharma', 'cook', '? Table order is ready to be picked up!', 1, '2026-05-23 05:51:09'),
(6, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Customer is waiting for order, please hurry!', 1, '2026-05-23 05:51:10'),
(7, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Customer is waiting for order, please hurry!', 1, '2026-05-23 05:51:11'),
(8, 'waiter', 1, 'Ravi Sharma', 'cook', '? Table order is ready to be picked up!', 1, '2026-05-23 05:51:13'),
(9, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Customer is waiting for order, please hurry!', 1, '2026-05-23 05:51:17'),
(10, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food is ready! Please pick up from kitchen.', 1, '2026-05-23 05:51:51'),
(11, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food is ready! Please pick up from kitchen.', 1, '2026-05-23 05:51:59'),
(12, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Customer is waiting for order, please hurry!', 1, '2026-05-23 05:53:23'),
(13, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Reminder from Ravi Sharma (Waiter) — Masala Chai x1 — Table #1 — Order #1 — Please prepare fast!', 1, '2026-05-23 06:01:00'),
(14, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Masala Chai x1 — Table #1 — Order #1 — Pick up & deliver now!', 1, '2026-05-23 06:02:07'),
(15, 'admin', 1, 'Admin', 'waiter', '?️ Food is ready in kitchen, please pick up!', 1, '2026-05-23 06:03:16'),
(16, 'admin', 1, 'Admin', 'cook', '? Please start cooking for Table — customer is waiting!', 1, '2026-05-23 06:51:55'),
(17, 'admin', 1, 'Admin', 'cook', '? Please start cooking for Table — customer is waiting!', 1, '2026-05-23 06:52:25'),
(18, 'admin', 1, 'Admin', 'cook', '? Please start cooking for Table — customer is waiting!', 1, '2026-05-23 06:52:55'),
(19, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Masala Chai x1 — Table #1 — Order #2 — Pick up & deliver now!', 1, '2026-05-23 07:12:18'),
(20, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #2 — Order #3 — Please prepare fast!', 1, '2026-05-23 09:43:33'),
(21, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #2 — Order #3 — Please prepare fast!', 1, '2026-05-23 09:43:49'),
(22, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #2 — Order #3 — Please prepare fast!', 1, '2026-05-23 09:44:02'),
(23, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #2 — Order #3 — Please prepare fast!', 1, '2026-05-23 09:45:12'),
(24, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Mango Lassi x1 — Table #2 — Order #5 — Pick up & deliver now!', 1, '2026-05-23 10:31:11'),
(25, 'cook', 1, 'Chef Kumar', 'waiter', 'table 1 food in problem that take more time more time then  u', 1, '2026-05-23 10:55:13'),
(26, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Garlic Naan x1 — Table #2 — Order #8 — Pick up & deliver now!', 1, '2026-05-25 05:38:48'),
(27, 'admin', 1, 'Admin', 'cook', '⚡ Reminder from Admin (Admin) — Garlic Naan x1 — Table #2 — Order #9 — Please prepare fast!', 1, '2026-05-25 05:41:31'),
(28, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Reminder from Ravi Sharma (Waiter) — Garlic Naan x1 — Table #2 — Order #9 — Please prepare fast!', 1, '2026-05-25 05:56:46'),
(29, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Garlic Naan x1 — Table #1 — Order #12 — Pick up & deliver now!', 1, '2026-05-26 06:50:57'),
(30, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Garlic Naan x1 — Table #1 — Order #12 — Pick up & deliver now!', 1, '2026-05-26 06:51:57'),
(31, 'waiter', 1, 'Ravi Sharma', 'cook', '⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #1 — Order #13 — Please prepare fast!', 1, '2026-05-26 06:53:35'),
(32, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!', 1, '2026-05-29 06:19:24'),
(33, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!', 1, '2026-05-29 06:19:33'),
(34, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!', 1, '2026-05-29 06:20:33'),
(35, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!', 1, '2026-05-29 06:21:33'),
(36, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!', 1, '2026-05-29 06:22:34'),
(37, 'cook', 1, 'Chef Kumar', 'waiter', '?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!', 1, '2026-05-29 06:23:34');

-- --------------------------------------------------------

--
-- Table structure for table `parcels`
--

CREATE TABLE `parcels` (
  `id` int(10) NOT NULL,
  `parcel_number` int(10) NOT NULL,
  `customer_name` varchar(100) DEFAULT '',
  `customer_phone` varchar(15) DEFAULT '',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=active 0=closed after bill',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parcels`
--

INSERT INTO `parcels` (`id`, `parcel_number`, `customer_name`, `customer_phone`, `is_active`, `created_at`) VALUES
(1, 1, 'OM', '9854565254', 0, '2026-05-23 05:54:45'),
(2, 2, '', '', 0, '2026-05-23 05:55:03'),
(3, 3, '', '', 0, '2026-05-23 10:29:29'),
(4, 4, 'rajesh sharma', '9854565254', 0, '2026-05-29 07:11:41'),
(5, 5, '', '', 0, '2026-05-30 07:35:55'),
(6, 6, 'rajesh sharma', '9854565254', 1, '2026-05-30 07:36:05');

-- --------------------------------------------------------

--
-- Table structure for table `parcel_orders`
--

CREATE TABLE `parcel_orders` (
  `id` int(10) NOT NULL,
  `parcel_number` int(10) NOT NULL,
  `waiter_id` int(10) NOT NULL,
  `waiter_name` varchar(50) NOT NULL,
  `menu_id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(5) DEFAULT 1,
  `status` enum('waiting','cooking','complete') DEFAULT 'waiting' COMMENT 'updated by cook',
  `is_delivered` tinyint(1) DEFAULT 0,
  `payment_method` enum('cash','upi','credit_card','pending') DEFAULT 'pending',
  `payment_done` tinyint(1) DEFAULT 0,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parcel_orders`
--

INSERT INTO `parcel_orders` (`id`, `parcel_number`, `waiter_id`, `waiter_name`, `menu_id`, `item_name`, `price`, `quantity`, `status`, `is_delivered`, `payment_method`, `payment_done`, `added_at`) VALUES
(5, 6, 1, 'Ravi Sharma', 12, 'Roti', 20.00, 1, 'cooking', 0, 'pending', 0, '2026-05-30 07:36:09'),
(6, 6, 1, 'Ravi Sharma', 13, 'Paratha', 35.00, 2, 'complete', 0, 'pending', 0, '2026-05-30 07:36:16');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(10) NOT NULL,
  `sender_type` enum('waiter','cook') NOT NULL,
  `sender_id` int(10) NOT NULL,
  `sender_name` varchar(50) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` enum('pending','working','complete') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `sender_type`, `sender_id`, `sender_name`, `subject`, `message`, `admin_reply`, `status`, `created_at`) VALUES
(1, 'waiter', 1, 'Ravi Sharma', 'Testing', 'Testing Purrpose Report', '', 'working', '2026-05-23 10:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(10) NOT NULL,
  `table_number` int(5) NOT NULL,
  `capacity` int(5) DEFAULT 4,
  `is_available` tinyint(1) DEFAULT 1 COMMENT '1=available 0=occupied — updated by waiter'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `table_number`, `capacity`, `is_available`) VALUES
(1, 1, 4, 0),
(2, 2, 4, 0),
(3, 3, 2, 1),
(4, 4, 6, 1),
(5, 5, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `table_orders`
--

CREATE TABLE `table_orders` (
  `id` int(10) NOT NULL,
  `table_id` int(10) NOT NULL,
  `waiter_id` int(10) NOT NULL,
  `waiter_name` varchar(50) NOT NULL,
  `menu_id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(5) DEFAULT 1,
  `status` enum('waiting','cooking','complete') DEFAULT 'waiting' COMMENT 'updated by cook',
  `is_delivered` tinyint(1) DEFAULT 0 COMMENT 'updated by waiter',
  `payment_method` enum('cash','upi','credit_card','pending') DEFAULT 'pending',
  `payment_done` tinyint(1) DEFAULT 0,
  `added_at` timestamp NULL DEFAULT current_timestamp(),
  `delay_minutes` int(5) DEFAULT 0 COMMENT 'delay set by waiter in minutes',
  `show_after` datetime DEFAULT NULL COMMENT 'show to cook after this time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_orders`
--

INSERT INTO `table_orders` (`id`, `table_id`, `waiter_id`, `waiter_name`, `menu_id`, `item_name`, `price`, `quantity`, `status`, `is_delivered`, `payment_method`, `payment_done`, `added_at`, `delay_minutes`, `show_after`) VALUES
(21, 1, 1, 'Ravi Sharma', 1, 'Paneer Butter Masala', 220.00, 1, 'complete', 1, 'pending', 0, '2026-05-30 05:36:57', 0, NULL),
(25, 2, 1, 'Ravi Sharma', 13, 'Paratha', 35.00, 1, 'waiting', 0, 'pending', 0, '2026-07-19 13:00:42', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `waiters`
--

CREATE TABLE `waiters` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `phone` varchar(15) DEFAULT '',
  `is_online` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waiters`
--

INSERT INTO `waiters` (`id`, `name`, `email`, `password`, `phone`, `is_online`, `created_at`) VALUES
(1, 'Ravi Sharma', 'ravi@gmail.com', '123456', '9876543210', 0, '2026-05-22 10:36:14'),
(2, 'Priya Patel', 'priya@gmail.com', '123456', '9876543211', 0, '2026-05-22 10:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `waiting_list`
--

CREATE TABLE `waiting_list` (
  `id` int(10) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `total_people` int(5) NOT NULL,
  `note` varchar(200) DEFAULT '',
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waiting_list`
--

INSERT INTO `waiting_list` (`id`, `customer_name`, `phone`, `total_people`, `note`, `added_at`) VALUES
(1, 'rajesh sharma', '7012455325', 5, 'window seat', '2026-05-30 07:37:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_ref` (`type`,`ref_id`);

--
-- Indexes for table `complete_orders`
--
ALTER TABLE `complete_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_type` (`order_type`),
  ADD KEY `ref_id` (`ref_id`),
  ADD KEY `waiter_id` (`waiter_id`),
  ADD KEY `billed_at` (`billed_at`),
  ADD KEY `date_idx` (`billed_date`);

--
-- Indexes for table `cook`
--
ALTER TABLE `cook`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customer_reviews`
--
ALTER TABLE `customer_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `waiter_id` (`waiter_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inv_id` (`inv_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `to_type` (`to_type`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `parcels`
--
ALTER TABLE `parcels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parcel_number` (`parcel_number`);

--
-- Indexes for table `parcel_orders`
--
ALTER TABLE `parcel_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parcel_number` (`parcel_number`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_type` (`sender_type`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `table_orders`
--
ALTER TABLE `table_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `waiter_id` (`waiter_id`);

--
-- Indexes for table `waiters`
--
ALTER TABLE `waiters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `complete_orders`
--
ALTER TABLE `complete_orders`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cook`
--
ALTER TABLE `cook`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer_reviews`
--
ALTER TABLE `customer_reviews`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `parcels`
--
ALTER TABLE `parcels`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `parcel_orders`
--
ALTER TABLE `parcel_orders`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `table_orders`
--
ALTER TABLE `table_orders`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `waiters`
--
ALTER TABLE `waiters`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `waiting_list`
--
ALTER TABLE `waiting_list`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
