-- MySQL dump 10.13  Distrib 8.0.11, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: project
-- ------------------------------------------------------
-- Server version	8.0.11

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 SET NAMES utf8 ;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admins` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Admin','zala@gmail.com','123456');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bills`
--

DROP TABLE IF EXISTS `bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `bills` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` enum('table','parcel') NOT NULL,
  `ref_id` int(10) NOT NULL COMMENT 'table_id or parcel_number',
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','upi','credit_card') DEFAULT 'cash',
  `billed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bills`
--

LOCK TABLES `bills` WRITE;
/*!40000 ALTER TABLE `bills` DISABLE KEYS */;
INSERT INTO `bills` VALUES (1,'table',1,60.00,'upi','2026-05-23 10:09:07'),(2,'table',2,220.00,'upi','2026-05-23 10:09:36'),(3,'parcel',1,100.00,'cash','2026-05-23 10:10:23'),(4,'table',1,540.00,'upi','2026-05-23 11:21:44'),(5,'table',2,80.00,'cash','2026-05-25 05:31:13'),(6,'table',1,460.00,'cash','2026-05-27 11:17:18'),(7,'table',1,40.00,'cash','2026-05-27 11:25:04'),(8,'table',1,40.00,'cash','2026-05-29 06:38:50'),(9,'table',1,280.00,'cash','2026-05-29 07:08:41'),(10,'parcel',4,40.00,'upi','2026-05-29 07:12:34');
/*!40000 ALTER TABLE `bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `chats` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` enum('table','parcel') NOT NULL,
  `ref_id` int(10) NOT NULL,
  `role` enum('admin','waiter','cook') NOT NULL,
  `user_id` int(10) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type_ref` (`type`,`ref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chats`
--

LOCK TABLES `chats` WRITE;
/*!40000 ALTER TABLE `chats` DISABLE KEYS */;
INSERT INTO `chats` VALUES (2,'table',2,'waiter',1,'Ravi Sharma','bro  do it','2026-05-25 05:28:42'),(5,'table',2,'waiter',1,'Ravi Sharma','hello','2026-05-29 05:52:22'),(6,'parcel',6,'waiter',1,'Ravi Sharma','made spicy','2026-05-30 07:38:16'),(7,'table',1,'waiter',1,'Ravi Sharma','fast','2026-05-30 07:43:22'),(8,'parcel',6,'admin',1,'Admin','hello','2026-05-30 08:02:47');
/*!40000 ALTER TABLE `chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complete_orders`
--

DROP TABLE IF EXISTS `complete_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `complete_orders` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order_type` enum('table','parcel') NOT NULL COMMENT 'table = dine-in, parcel = takeaway',
  `ref_id` int(10) NOT NULL COMMENT 'table_id for table orders, parcel_number for parcel',
  `waiter_id` int(10) NOT NULL,
  `waiter_name` varchar(50) NOT NULL,
  `menu_id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(5) DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'price * quantity',
  `payment_method` enum('cash','upi','credit_card','pending') DEFAULT 'cash',
  `bill_total` decimal(10,2) DEFAULT '0.00' COMMENT 'total bill amount for this order group',
  `customer_name` varchar(100) DEFAULT '' COMMENT 'customer name (parcel only)',
  `customer_phone` varchar(15) DEFAULT '' COMMENT 'customer phone (parcel only)',
  `billed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when bill was printed',
  `billed_date` date GENERATED ALWAYS AS (cast(`billed_at` as date)) STORED,
  PRIMARY KEY (`id`),
  KEY `order_type` (`order_type`),
  KEY `ref_id` (`ref_id`),
  KEY `waiter_id` (`waiter_id`),
  KEY `billed_at` (`billed_at`),
  KEY `date_idx` (`billed_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Permanent record of all completed & billed orders';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complete_orders`
--

LOCK TABLES `complete_orders` WRITE;
/*!40000 ALTER TABLE `complete_orders` DISABLE KEYS */;
INSERT INTO `complete_orders` (`id`, `order_type`, `ref_id`, `waiter_id`, `waiter_name`, `menu_id`, `item_name`, `price`, `quantity`, `subtotal`, `payment_method`, `bill_total`, `customer_name`, `customer_phone`, `billed_at`) VALUES (1,'table',1,1,'Ravi Sharma',10,'Butter Naan',40.00,1,40.00,'cash',40.00,'','','2026-05-29 06:38:51'),(2,'table',1,1,'Ravi Sharma',10,'Butter Naan',40.00,1,40.00,'cash',280.00,'','','2026-05-29 07:08:41'),(3,'table',1,1,'Ravi Sharma',3,'Shahi Paneer',240.00,1,240.00,'cash',280.00,'','','2026-05-29 07:08:41'),(4,'parcel',4,1,'Ravi Sharma',10,'Butter Naan',40.00,1,40.00,'upi',40.00,'rajesh sharma','9854565254','2026-05-29 07:12:34');
/*!40000 ALTER TABLE `complete_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cook`
--

DROP TABLE IF EXISTS `cook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `cook` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cook`
--

LOCK TABLES `cook` WRITE;
/*!40000 ALTER TABLE `cook` DISABLE KEYS */;
INSERT INTO `cook` VALUES (1,'Chef Kumar','cook@gmail.com','123456');
/*!40000 ALTER TABLE `cook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_reviews`
--

DROP TABLE IF EXISTS `customer_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `customer_reviews` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `visit_count` int(5) DEFAULT '1',
  `rating` tinyint(1) DEFAULT '5',
  `review` text NOT NULL,
  `waiter_id` int(10) NOT NULL,
  `waiter_name` varchar(50) NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `waiter_id` (`waiter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_reviews`
--

LOCK TABLES `customer_reviews` WRITE;
/*!40000 ALTER TABLE `customer_reviews` DISABLE KEYS */;
INSERT INTO `customer_reviews` VALUES (1,'rajesh sharma','7012455325',3,4,'this good rsturent but i prefer salt less',1,'Ravi Sharma','2026-05-23 07:16:02');
/*!40000 ALTER TABLE `customer_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT 'General',
  `unit` varchar(20) DEFAULT 'kg',
  `quantity` decimal(10,2) DEFAULT '0.00',
  `min_quantity` decimal(10,2) DEFAULT '5.00',
  `updated_by` varchar(50) DEFAULT '',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,'Tomato','Vegetables','kg',10.00,3.00,'Admin','2026-05-23 05:42:10','2026-05-23 05:42:10'),(2,'Potato','Vegetables','kg',25.00,5.00,'Admin','2026-05-23 07:14:58','2026-05-23 07:14:58');
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_log`
--

DROP TABLE IF EXISTS `inventory_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `inventory_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `inv_id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `action` enum('add','remove') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `note` varchar(200) DEFAULT '',
  `done_by` varchar(50) NOT NULL,
  `done_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inv_id` (`inv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_log`
--

LOCK TABLES `inventory_log` WRITE;
/*!40000 ALTER TABLE `inventory_log` DISABLE KEYS */;
INSERT INTO `inventory_log` VALUES (1,2,'Potato','add',25.00,'Initial stock added','Admin','2026-05-23 07:14:59');
/*!40000 ALTER TABLE `inventory_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `menu` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT 'Main Course',
  `cooking_time` int(5) DEFAULT '15' COMMENT 'in minutes',
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1,'Paneer Butter Masala',220.00,'Main Course',20,1,'2026-05-22 10:36:15'),(2,'Dal Makhani',180.00,'Main Course',25,1,'2026-05-22 10:36:15'),(3,'Shahi Paneer',240.00,'Main Course',20,1,'2026-05-22 10:36:15'),(4,'Veg Biryani',200.00,'Rice',20,1,'2026-05-22 10:36:15'),(5,'Chicken Biryani',280.00,'Rice',25,1,'2026-05-22 10:36:15'),(6,'Chicken Tikka',280.00,'Starter',15,1,'2026-05-22 10:36:15'),(7,'Veg Manchurian',160.00,'Starter',15,1,'2026-05-22 10:36:15'),(8,'Samosa (2 pcs)',40.00,'Starter',10,1,'2026-05-22 10:36:15'),(9,'Spring Roll',80.00,'Starter',12,1,'2026-05-22 10:36:15'),(10,'Butter Naan',40.00,'Bread',10,0,'2026-05-22 10:36:15'),(11,'Garlic Naan',50.00,'Bread',10,0,'2026-05-22 10:36:15'),(12,'Roti',20.00,'Bread',5,1,'2026-05-22 10:36:15'),(13,'Paratha',35.00,'Bread',8,1,'2026-05-22 10:36:15'),(14,'Mango Lassi',80.00,'Drinks',5,1,'2026-05-22 10:36:15'),(15,'Masala Chai',30.00,'Drinks',5,1,'2026-05-22 10:36:15'),(16,'Cold Coffee',90.00,'Drinks',5,1,'2026-05-22 10:36:15'),(17,'Fresh Lime Soda',60.00,'Drinks',3,1,'2026-05-22 10:36:15'),(18,'Gulab Jamun',60.00,'Dessert',5,1,'2026-05-22 10:36:15'),(19,'Ice Cream (2 scoops)',80.00,'Dessert',3,1,'2026-05-22 10:36:15'),(20,'Rasmalai',70.00,'Dessert',5,1,'2026-05-22 10:36:15');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `notifications` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `from_type` enum('admin','waiter','cook') NOT NULL,
  `from_id` int(10) NOT NULL,
  `from_name` varchar(50) NOT NULL,
  `to_type` enum('admin','waiter','cook','all') NOT NULL,
  `message` varchar(300) NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `to_type` (`to_type`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'waiter',1,'Ravi Sharma','admin','⚠️ Need admin attention at the floor!',1,'2026-05-23 05:51:05'),(2,'waiter',1,'Ravi Sharma','cook','?️ New order placed at table, please check!',1,'2026-05-23 05:51:06'),(3,'waiter',1,'Ravi Sharma','cook','⚡ Customer is waiting for order, please hurry!',1,'2026-05-23 05:51:06'),(4,'waiter',1,'Ravi Sharma','cook','? Table order is ready to be picked up!',1,'2026-05-23 05:51:08'),(5,'waiter',1,'Ravi Sharma','cook','? Table order is ready to be picked up!',1,'2026-05-23 05:51:09'),(6,'waiter',1,'Ravi Sharma','cook','⚡ Customer is waiting for order, please hurry!',1,'2026-05-23 05:51:10'),(7,'waiter',1,'Ravi Sharma','cook','⚡ Customer is waiting for order, please hurry!',1,'2026-05-23 05:51:11'),(8,'waiter',1,'Ravi Sharma','cook','? Table order is ready to be picked up!',1,'2026-05-23 05:51:13'),(9,'waiter',1,'Ravi Sharma','cook','⚡ Customer is waiting for order, please hurry!',1,'2026-05-23 05:51:17'),(10,'cook',1,'Chef Kumar','waiter','?️ Food is ready! Please pick up from kitchen.',1,'2026-05-23 05:51:51'),(11,'cook',1,'Chef Kumar','waiter','?️ Food is ready! Please pick up from kitchen.',1,'2026-05-23 05:51:59'),(12,'waiter',1,'Ravi Sharma','cook','⚡ Customer is waiting for order, please hurry!',1,'2026-05-23 05:53:23'),(13,'waiter',1,'Ravi Sharma','cook','⚡ Reminder from Ravi Sharma (Waiter) — Masala Chai x1 — Table #1 — Order #1 — Please prepare fast!',1,'2026-05-23 06:01:00'),(14,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Masala Chai x1 — Table #1 — Order #1 — Pick up & deliver now!',1,'2026-05-23 06:02:07'),(15,'admin',1,'Admin','waiter','?️ Food is ready in kitchen, please pick up!',1,'2026-05-23 06:03:16'),(16,'admin',1,'Admin','cook','? Please start cooking for Table — customer is waiting!',1,'2026-05-23 06:51:55'),(17,'admin',1,'Admin','cook','? Please start cooking for Table — customer is waiting!',1,'2026-05-23 06:52:25'),(18,'admin',1,'Admin','cook','? Please start cooking for Table — customer is waiting!',1,'2026-05-23 06:52:55'),(19,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Masala Chai x1 — Table #1 — Order #2 — Pick up & deliver now!',1,'2026-05-23 07:12:18'),(20,'waiter',1,'Ravi Sharma','cook','⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #2 — Order #3 — Please prepare fast!',1,'2026-05-23 09:43:33'),(21,'waiter',1,'Ravi Sharma','cook','⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #2 — Order #3 — Please prepare fast!',1,'2026-05-23 09:43:49'),(22,'waiter',1,'Ravi Sharma','cook','⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #2 — Order #3 — Please prepare fast!',1,'2026-05-23 09:44:02'),(23,'waiter',1,'Ravi Sharma','cook','⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #2 — Order #3 — Please prepare fast!',1,'2026-05-23 09:45:12'),(24,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Mango Lassi x1 — Table #2 — Order #5 — Pick up & deliver now!',1,'2026-05-23 10:31:11'),(25,'cook',1,'Chef Kumar','waiter','table 1 food in problem that take more time more time then  u',1,'2026-05-23 10:55:13'),(26,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Garlic Naan x1 — Table #2 — Order #8 — Pick up & deliver now!',1,'2026-05-25 05:38:48'),(27,'admin',1,'Admin','cook','⚡ Reminder from Admin (Admin) — Garlic Naan x1 — Table #2 — Order #9 — Please prepare fast!',1,'2026-05-25 05:41:31'),(28,'waiter',1,'Ravi Sharma','cook','⚡ Reminder from Ravi Sharma (Waiter) — Garlic Naan x1 — Table #2 — Order #9 — Please prepare fast!',1,'2026-05-25 05:56:46'),(29,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Garlic Naan x1 — Table #1 — Order #12 — Pick up & deliver now!',1,'2026-05-26 06:50:57'),(30,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Garlic Naan x1 — Table #1 — Order #12 — Pick up & deliver now!',1,'2026-05-26 06:51:57'),(31,'waiter',1,'Ravi Sharma','cook','⚡ Reminder from Ravi Sharma (Waiter) — Paneer Butter Masala x1 — Table #1 — Order #13 — Please prepare fast!',1,'2026-05-26 06:53:35'),(32,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!',1,'2026-05-29 06:19:24'),(33,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!',1,'2026-05-29 06:19:33'),(34,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!',1,'2026-05-29 06:20:33'),(35,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!',1,'2026-05-29 06:21:33'),(36,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!',1,'2026-05-29 06:22:34'),(37,'cook',1,'Chef Kumar','waiter','?️ Food Ready! — Butter Naan x1 — Table #1 — Order #18 — Pick up & deliver now!',1,'2026-05-29 06:23:34');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parcel_orders`
--

DROP TABLE IF EXISTS `parcel_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `parcel_orders` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parcel_number` int(10) NOT NULL,
  `waiter_id` int(10) NOT NULL,
  `waiter_name` varchar(50) NOT NULL,
  `menu_id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(5) DEFAULT '1',
  `status` enum('waiting','cooking','complete') DEFAULT 'waiting' COMMENT 'updated by cook',
  `is_delivered` tinyint(1) DEFAULT '0',
  `payment_method` enum('cash','upi','credit_card','pending') DEFAULT 'pending',
  `payment_done` tinyint(1) DEFAULT '0',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parcel_number` (`parcel_number`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parcel_orders`
--

LOCK TABLES `parcel_orders` WRITE;
/*!40000 ALTER TABLE `parcel_orders` DISABLE KEYS */;
INSERT INTO `parcel_orders` VALUES (5,6,1,'Ravi Sharma',12,'Roti',20.00,1,'cooking',0,'pending',0,'2026-05-30 07:36:09'),(6,6,1,'Ravi Sharma',13,'Paratha',35.00,2,'complete',0,'pending',0,'2026-05-30 07:36:16');
/*!40000 ALTER TABLE `parcel_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parcels`
--

DROP TABLE IF EXISTS `parcels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `parcels` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parcel_number` int(10) NOT NULL,
  `customer_name` varchar(100) DEFAULT '',
  `customer_phone` varchar(15) DEFAULT '',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '1=active 0=closed after bill',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parcel_number` (`parcel_number`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parcels`
--

LOCK TABLES `parcels` WRITE;
/*!40000 ALTER TABLE `parcels` DISABLE KEYS */;
INSERT INTO `parcels` VALUES (1,1,'OM','9854565254',0,'2026-05-23 05:54:45'),(2,2,'','',0,'2026-05-23 05:55:03'),(3,3,'','',0,'2026-05-23 10:29:29'),(4,4,'rajesh sharma','9854565254',0,'2026-05-29 07:11:41'),(5,5,'','',0,'2026-05-30 07:35:55'),(6,6,'rajesh sharma','9854565254',1,'2026-05-30 07:36:05');
/*!40000 ALTER TABLE `parcels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `reports` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sender_type` enum('waiter','cook') NOT NULL,
  `sender_id` int(10) NOT NULL,
  `sender_name` varchar(50) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `admin_reply` text,
  `status` enum('pending','working','complete') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_type` (`sender_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,'waiter',1,'Ravi Sharma','Testing','Testing Purrpose Report','','working','2026-05-23 10:37:23');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_orders`
--

DROP TABLE IF EXISTS `table_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `table_orders` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `table_id` int(10) NOT NULL,
  `waiter_id` int(10) NOT NULL,
  `waiter_name` varchar(50) NOT NULL,
  `menu_id` int(10) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(5) DEFAULT '1',
  `status` enum('waiting','cooking','complete') DEFAULT 'waiting' COMMENT 'updated by cook',
  `is_delivered` tinyint(1) DEFAULT '0' COMMENT 'updated by waiter',
  `payment_method` enum('cash','upi','credit_card','pending') DEFAULT 'pending',
  `payment_done` tinyint(1) DEFAULT '0',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `delay_minutes` int(5) DEFAULT '0' COMMENT 'delay set by waiter in minutes',
  `show_after` datetime DEFAULT NULL COMMENT 'show to cook after this time',
  PRIMARY KEY (`id`),
  KEY `table_id` (`table_id`),
  KEY `waiter_id` (`waiter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table_orders`
--

LOCK TABLES `table_orders` WRITE;
/*!40000 ALTER TABLE `table_orders` DISABLE KEYS */;
INSERT INTO `table_orders` VALUES (21,1,1,'Ravi Sharma',1,'Paneer Butter Masala',220.00,1,'complete',0,'pending',0,'2026-05-30 05:36:57',0,NULL),(22,2,1,'Ravi Sharma',16,'Cold Coffee',90.00,1,'cooking',0,'pending',0,'2026-05-30 07:35:40',0,NULL),(23,3,1,'Ravi Sharma',14,'Mango Lassi',80.00,1,'waiting',0,'pending',0,'2026-05-30 07:35:47',0,NULL),(24,4,1,'Ravi Sharma',15,'Masala Chai',30.00,1,'waiting',0,'pending',0,'2026-05-30 07:36:43',0,NULL);
/*!40000 ALTER TABLE `table_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `tables` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `table_number` int(5) NOT NULL,
  `capacity` int(5) DEFAULT '4',
  `is_available` tinyint(1) DEFAULT '1' COMMENT '1=available 0=occupied — updated by waiter',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
INSERT INTO `tables` VALUES (1,1,4,0),(2,2,4,0),(3,3,2,0),(4,4,6,0),(5,5,4,1);
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `waiters`
--

DROP TABLE IF EXISTS `waiters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `waiters` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `phone` varchar(15) DEFAULT '',
  `is_online` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `waiters`
--

LOCK TABLES `waiters` WRITE;
/*!40000 ALTER TABLE `waiters` DISABLE KEYS */;
INSERT INTO `waiters` VALUES (1,'Ravi Sharma','ravi@gmail.com','123456','9876543210',0,'2026-05-22 10:36:14'),(2,'Priya Patel','priya@gmail.com','123456','9876543211',0,'2026-05-22 10:36:14');
/*!40000 ALTER TABLE `waiters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `waiting_list`
--

DROP TABLE IF EXISTS `waiting_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `waiting_list` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `total_people` int(5) NOT NULL,
  `note` varchar(200) DEFAULT '',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `waiting_list`
--

LOCK TABLES `waiting_list` WRITE;
/*!40000 ALTER TABLE `waiting_list` DISABLE KEYS */;
INSERT INTO `waiting_list` VALUES (1,'rajesh sharma','7012455325',5,'window seat','2026-05-30 07:37:18');
/*!40000 ALTER TABLE `waiting_list` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-30 13:52:17
