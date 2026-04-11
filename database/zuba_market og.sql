-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2026 at 04:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zuba_market`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_type` enum('admin','customer') NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_type`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 19:53:22'),
(2, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 19:53:25'),
(3, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 19:53:30'),
(4, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 19:53:33'),
(5, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 19:53:37'),
(6, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 19:59:09'),
(7, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 19:59:21'),
(8, 'admin', 1, 'LOGIN', 'Admin logged in', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:02:24'),
(9, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:02:35'),
(10, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:04:14'),
(11, 'admin', 1, 'LOGOUT', 'Admin logged out', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:06:08'),
(12, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:07:10'),
(13, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:07:16'),
(14, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:42:35'),
(15, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:42:37'),
(16, 'admin', 1, 'LOGIN', 'Admin logged in', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:46:16'),
(17, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:46:34'),
(18, 'admin', 1, 'LOGOUT', 'Admin logged out', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:53:17'),
(19, 'admin', 1, 'LOGIN', 'Admin logged in', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:53:22'),
(20, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:55:22'),
(21, 'admin', 1, 'LOGOUT', 'Admin logged out', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:56:48'),
(22, 'admin', 1, 'LOGIN', 'Admin logged in', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:57:11'),
(23, 'admin', 1, 'LOGOUT', 'Admin logged out', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:57:35'),
(24, 'admin', 1, 'LOGIN', 'Admin logged in', '192.168.1.71', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-03-13 20:57:40'),
(25, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 20:57:44'),
(26, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:29:41'),
(27, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 15:22:41'),
(28, 'admin', 1, 'LOGOUT', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 15:24:12'),
(29, 'admin', 1, 'LOGIN', 'Admin logged in', '192.168.1.64', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-15 15:25:30'),
(30, 'admin', 1, 'LOGIN', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 15:25:49'),
(31, 'admin', 1, 'LOGOUT', 'Admin logged out', '192.168.1.64', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-15 15:30:41'),
(32, 'admin', 1, 'LOGIN', 'Admin logged in', '192.168.1.64', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-15 15:31:08'),
(33, 'admin', 1, 'LOGOUT', 'Admin logged out', '192.168.1.64', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-15 15:36:39'),
(34, 'admin', 1, 'LOGIN', 'Admin logged in', '192.168.1.64', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-15 15:36:43'),
(35, 'admin', 1, 'ADD_PRODUCT', 'Added product: HP EliteBook 850 G8 Touchscreen I5-1145G7 16GB 512GB SSD M.2 1920x1080 Class A Windows 11 Professional', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 15:44:17'),
(36, 'admin', 1, 'UPDATE_PRODUCT_STATUS', 'Product ID 1 status → inactive', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 15:49:04'),
(37, 'admin', 1, 'UPDATE_PRODUCT_STATUS', 'Product ID 1 status → active', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 15:49:13'),
(38, 'admin', 1, 'UPDATE_PRODUCT_FEATURED', 'Product ID 1 featured → 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 15:49:16'),
(39, 'admin', 1, 'UPDATE_PRODUCT_FEATURED', 'Product ID 1 featured → 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 15:49:19'),
(40, 'admin', 1, 'UPDATE_PRODUCT_FEATURED', 'Product ID 1 featured → 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 23:19:06'),
(41, 'admin', 1, 'UPDATE_PRODUCT_FEATURED', 'Product ID 1 featured → 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 23:19:08'),
(42, 'admin', 1, 'UPDATE_PRODUCT_STATUS', 'Product ID 1 status → inactive', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 23:19:10'),
(43, 'admin', 1, 'UPDATE_PRODUCT_STATUS', 'Product ID 1 status → active', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-15 23:19:12'),
(44, 'admin', 1, 'logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-16 08:53:34'),
(45, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-16 08:53:47'),
(46, 'admin', 1, 'logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-16 08:54:48'),
(47, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-16 09:05:05'),
(48, 'admin', 1, 'logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-16 09:05:54'),
(49, 'admin', 1, 'login', 'Admin logged in', '192.168.1.69', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-16 09:18:42'),
(50, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-16 09:27:48'),
(51, 'admin', 1, 'login', 'Admin logged in', '192.168.1.69', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-16 09:28:39'),
(52, 'admin', 1, 'login', 'Admin logged in', '192.168.1.69', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-16 09:50:28'),
(53, 'admin', 1, 'delete_product', 'Deleted product ID 1', '192.168.1.69', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-16 10:22:13'),
(54, 'admin', 1, 'ADD_PRODUCT', 'Added product: HP EliteBook 850 G8 Touchscreen I5-1145G7 16GB 512GB SSD M.2 1920x1080 Class A Windows 11 Professional', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-16 10:45:29'),
(55, 'admin', 1, 'ADD_PRODUCT', 'Added product: Apple iPhone 15 Pro Max, 256GB, Black Titanium - Unlocked (Renewed)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-16 10:52:39'),
(56, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:05:05'),
(57, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:06:01'),
(58, 'admin', 1, 'logout', 'Admin logged out', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:06:16'),
(59, 'admin', 1, 'add_property', 'Added new property: inzu igurishwa muri kibagabaga', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:17:32'),
(60, 'admin', 1, 'bulk_delete_properties', 'Deleted 2 properties', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:17:57'),
(61, 'admin', 1, 'add_property', 'Added new property: inzu ikodeshwa muri kibagabaga', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:21:12'),
(62, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:23:00'),
(63, 'admin', 1, 'change_property_status', 'Changed property #3 status to sold', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:27:51'),
(64, 'admin', 1, 'change_property_status', 'Changed property #3 status to active', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:27:57'),
(65, 'admin', 1, 'toggle_property_featured', 'Toggled featured for property #3', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:33:33'),
(66, 'admin', 1, 'toggle_property_featured', 'Toggled featured for property #3', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:33:41'),
(67, 'admin', 1, 'toggle_property_featured', 'Toggled featured for property #3', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:33:44'),
(68, 'admin', 1, 'ADD_PRODUCT', 'Added product: HP EliteBook 850 G8 Touchscreen I5-1145G7 16GB 512GB SSD M.2 1920x1080 Class A Windows 11 Professional', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:40:05'),
(69, 'admin', 1, 'ADD_PRODUCT', 'Added product: HP EliteBook 850 G8 Touchscreen I5-1145G7 16GB 512GB SSD M.2 1920x1080 Class A Windows 11 Professional', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:43:23'),
(70, 'admin', 1, 'delete_product', 'Deleted product ID 7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:43:33'),
(71, 'admin', 1, 'ADD_PRODUCT', 'Added product: HP EliteBook 850 G8 Touchscreen I5-1145G7 16GB 512GB SSD M.2 1920x1080 Class A Windows 11 Professional', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:44:27'),
(72, 'admin', 1, 'EDIT_PRODUCT', 'Updated product: HP EliteBook 850 G8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:45:09'),
(73, 'admin', 1, 'EDIT_PRODUCT', 'Updated product: HP EliteBook 850 G8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 19:45:35'),
(74, 'admin', 1, 'change_product_status', 'Changed product #8 status to inactive', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:55:47'),
(75, 'admin', 1, 'change_product_status', 'Changed product #8 status to out_of_stock', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:55:52'),
(76, 'admin', 1, 'change_product_status', 'Changed product #8 status to active', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 19:56:01'),
(77, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 20:21:41'),
(78, 'admin', 1, 'toggle_property_featured', 'Toggled featured for property #3', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 20:21:57'),
(79, 'admin', 1, 'logout', 'Admin logged out', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 20:36:53'),
(80, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 20:37:25'),
(81, 'admin', 1, 'add_vehicle', 'Added new vehicle: Toyota has a total of 12 car models available in India right now Toyota Fortuner', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 20:57:17'),
(82, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 20:58:03'),
(83, 'admin', 1, 'change_vehicle_status', 'Vehicle ID: 1, Status: rented', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 20:59:36'),
(84, 'admin', 1, 'change_vehicle_status', 'Vehicle ID: 1, Status: active', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-18 20:59:46'),
(85, 'admin', 1, 'delete_vehicle', 'Vehicle ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:07:16'),
(86, 'admin', 1, 'add_vehicle', 'Added new vehicle: Toyota has a total of 12 car models available in India right now Toyota Fortuner', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:09:10'),
(87, 'admin', 1, 'edit_vehicle', 'Updated vehicle: Toyota has a total of 12 car Toyota Fortuner', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:11:26'),
(88, 'admin', 1, 'edit_vehicle', 'Updated vehicle: Toyota has a total of 12 car Toyota Fortuner', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:11:53'),
(89, 'admin', 1, 'edit_vehicle', 'Updated vehicle: Toyota has a total of 12 car Toyota Fortuner', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:12:07'),
(90, 'admin', 1, 'change_vehicle_status', 'Vehicle ID: 2, Status: rented', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:12:30'),
(91, 'admin', 1, 'change_vehicle_status', 'Vehicle ID: 2, Status: maintenance', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:12:37'),
(92, 'admin', 1, 'change_vehicle_status', 'Vehicle ID: 2, Status: inactive', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:12:43'),
(93, 'admin', 1, 'change_vehicle_status', 'Vehicle ID: 2, Status: available', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-18 21:12:48'),
(94, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-19 08:20:16'),
(95, 'admin', 1, 'add_category', 'Added category: Advanced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 08:28:46'),
(96, 'admin', 1, 'delete_category', 'Deleted category #14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 08:29:32'),
(97, 'admin', 1, 'add_category', 'Added category: Advanced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 08:33:15'),
(98, 'admin', 1, 'delete_category', 'Deleted category #15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 08:35:12'),
(99, 'admin', 1, 'add_category', 'Added category: Advanced', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 08:36:03'),
(100, 'admin', 1, 'delete_category', 'Deleted category #16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 08:36:23'),
(101, 'admin', 1, 'add_payment_method', 'Added payment method: MTN Mobile Money', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 09:01:03'),
(102, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-19 09:01:29'),
(103, 'admin', 1, 'edit_admin', 'Updated admin: Super Admin', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-19 09:05:32'),
(104, 'admin', 1, 'add_admin', 'Added admin: Mucyo Clebere', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 09:06:17'),
(105, 'admin', 1, 'delete_admin', 'Deleted admin #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 09:06:26'),
(106, 'admin', 1, 'init_settings', 'Initialized site settings: 9 inserted, 8 updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 09:12:12'),
(107, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:29:42'),
(108, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:33:44'),
(109, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:37:31'),
(110, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:39:19'),
(111, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:40:18'),
(112, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:43:03'),
(113, 'admin', 1, 'update_banner', 'Updated banner ID: 6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:43:34'),
(114, 'admin', 1, 'update_banner', 'Updated banner ID: 6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:43:56'),
(115, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:45:13'),
(116, 'admin', 1, 'update_banner', 'Updated banner ID: 8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:45:33'),
(117, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:46:37'),
(118, 'admin', 1, 'update_banner', 'Updated banner ID: 10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:46:50'),
(119, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:49:41'),
(120, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:51:18'),
(121, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:52:54'),
(122, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 10:55:32'),
(123, 'admin', 1, 'logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 11:15:43'),
(124, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-19 11:17:11'),
(125, 'admin', 1, 'logout', 'Admin logged out', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-19 11:17:35'),
(126, 'admin', 1, 'login', 'Admin logged in', '192.168.1.80', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-19 11:31:38'),
(127, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 11:54:23'),
(128, 'admin', 1, 'update_settings', 'Updated site settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 11:58:11'),
(129, 'admin', 1, 'update_settings', 'Updated site settings', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-19 11:58:48'),
(130, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 09:15:45'),
(131, 'admin', 1, 'update_banner', 'Updated banner ID: 16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 09:21:04'),
(132, 'admin', 1, 'update_banner', 'Updated banner ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 09:21:24'),
(133, 'admin', 1, 'update_banner', 'Updated banner ID: 4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 09:21:46'),
(134, 'admin', 1, 'update_banner', 'Updated banner ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 09:22:39'),
(135, 'admin', 1, 'update_banner', 'Updated banner ID: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 09:23:10'),
(136, 'admin', 1, 'ADD_PRODUCT', 'Added product: iPhone 15 Pro Max 256Go Titane Bleu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:03:35'),
(137, 'admin', 1, 'ADD_PRODUCT', 'Added product: Nike Shox TL - The shoe that defined futurism', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:07:34'),
(138, 'admin', 1, 'ADD_PRODUCT', 'Added product: Galaxy S22 Ultra 5G', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:11:07'),
(139, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:11:25'),
(140, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:11:30'),
(141, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:11:34'),
(142, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:11:39'),
(143, 'admin', 1, 'ADD_PRODUCT', 'Added product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:20:56'),
(144, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 13:21:03'),
(145, 'admin', 1, 'ADD_PRODUCT', 'Added product: Medium Blue Jeans for Men', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 14:07:21'),
(146, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 14:08:49'),
(147, 'admin', 1, 'toggle_property_featured', 'Toggled featured for property #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 14:16:36'),
(148, 'admin', 1, 'toggle_vehicle_featured', 'Vehicle ID: 2, Featured: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 14:16:46'),
(149, 'admin', 1, 'add_property', 'Added new property: A Primary Residence To A Rental Property', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 14:19:06'),
(150, 'admin', 1, 'toggle_property_featured', 'Toggled featured for property #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 14:19:13'),
(151, 'admin', 1, 'add_vehicle', 'Added new vehicle: Toyota has a total of 12 car Toyota Fortuner', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-20 15:42:15'),
(152, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 09:32:50'),
(153, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 09:41:05'),
(154, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 09:41:18'),
(155, 'admin', 1, 'toggle_product_featured', 'Toggled featured for product #10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 09:41:27'),
(156, 'admin', 1, 'add_vehicle', 'Added new vehicle: BMW M30 Modified BMW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 10:37:45'),
(157, 'admin', 1, 'update_banner', 'Updated banner ID: 16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:28:39'),
(158, 'admin', 1, 'update_banner', 'Updated banner ID: 8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:29:19'),
(159, 'admin', 1, 'update_banner', 'Updated banner ID: 8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:31:37'),
(160, 'admin', 1, 'update_banner', 'Updated banner ID: 16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:32:55'),
(161, 'admin', 1, 'update_banner', 'Updated banner ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:33:24'),
(162, 'admin', 1, 'update_banner', 'Updated banner ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:34:37'),
(163, 'admin', 1, 'update_banner', 'Updated banner ID: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:35:16'),
(164, 'admin', 1, 'update_banner', 'Updated banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:51:38'),
(165, 'admin', 1, 'delete_banner', 'Deleted banner ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:51:59'),
(166, 'admin', 1, 'update_banner', 'Updated banner ID: 10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 11:53:06'),
(167, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-21 20:14:05'),
(168, 'customer', 1, 'REGISTER', 'New customer registration', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 08:25:24'),
(169, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 08:25:36'),
(170, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 08:28:16'),
(171, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 08:33:31'),
(172, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:18:45'),
(173, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:21:17'),
(174, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:27:08'),
(175, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:27:14'),
(176, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:27:26'),
(177, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 09:29:01'),
(178, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: HP EliteBook 850 G8 (ID: 8)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:29:53'),
(179, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: HP EliteBook 850 G8 (ID: 8)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:30:01'),
(180, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:30:37'),
(181, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:32:59'),
(182, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:33:03'),
(183, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:33:08'),
(184, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: A Primary Residence To A Rental Property (ID: 4)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:34:35'),
(185, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:38:36'),
(186, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:38:50'),
(187, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:40:28'),
(188, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:40:34'),
(189, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:40:38'),
(190, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:42:34'),
(191, 'customer', 1, 'ADD_TO_CART', 'Added to cart: Galaxy S22 Ultra 5G (Quantity: 1)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:42:39'),
(192, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:42:43'),
(193, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:43:19'),
(194, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: iPhone 15 Pro Max 256Go Titane Bleu (ID: 9)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:43:44'),
(195, 'customer', 1, 'ADD_TO_CART', 'Added to cart: iPhone 15 Pro Max 256Go Titane Bleu (Quantity: 1)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 09:44:42'),
(196, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 09:48:01'),
(197, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 09:58:30'),
(198, 'customer', 1, 'REMOVE_FROM_CART', 'Removed from cart: Product ID 9', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:00:38'),
(199, 'customer', 1, 'REMOVE_FROM_CART', 'Removed from cart: Product ID 11', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:00:43'),
(200, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Medium Blue Jeans for Men (ID: 13)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:00:52'),
(201, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:01:04'),
(202, 'customer', 1, 'ADD_TO_CART', 'Added to cart: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (Quantity: 1)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:01:08'),
(203, 'customer', 1, 'UPDATE_CART', 'Updated cart: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (Quantity: 2)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:01:14'),
(204, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:01:36'),
(205, 'customer', 1, 'ADD_TO_CART', 'Added to cart: Galaxy S22 Ultra 5G (Quantity: 1)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:01:44'),
(206, 'customer', 1, 'PLACE_ORDER', 'Placed order: ORD-20260322-9294B1 (Total: 1,205,000 FRw)', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:04:41'),
(207, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260322-9294B1 status to approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:30:52'),
(208, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260322-9294B1 status to processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:31:33'),
(209, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260322-9294B1 status to shipped', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:31:48'),
(210, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260322-9294B1 status to delivered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:32:04'),
(211, 'customer', 1, 'REMOVE_FROM_CART', 'Removed from cart: Product ID 12', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 10:45:14'),
(212, 'admin', 1, 'toggle_property_featured', 'Toggled featured for property #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:59:17'),
(213, 'admin', 1, 'change_property_status', 'Changed property #4 status to sold', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:59:30'),
(214, 'admin', 1, 'change_property_status', 'Changed property #4 status to active', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:59:38'),
(215, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:03:28'),
(216, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:04:16'),
(217, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:05:11'),
(218, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:05:11'),
(219, 'customer', 1, 'ADD_TO_CART', 'Added to cart: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (Quantity: 1)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:05:16'),
(220, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:05:21'),
(221, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 11:05:36'),
(222, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:06:46'),
(223, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:07:35'),
(224, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:11:58');
INSERT INTO `activity_logs` (`id`, `user_type`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(225, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:11:58'),
(226, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:16:37'),
(227, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 11:17:20'),
(228, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:18:35'),
(229, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:18:42'),
(230, 'customer', 1, 'REMOVE_FROM_CART', 'Removed from cart: Product ID 12', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:19:24'),
(231, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:19:33'),
(232, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: HP EliteBook 850 G8 (ID: 8)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:19:51'),
(233, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:20:00'),
(234, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: HP EliteBook 850 G8 (ID: 8)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:20:47'),
(235, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:20:54'),
(236, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:22:53'),
(237, 'customer', 1, 'CREATE_PROPERTY_ORDER', 'Created property order: PO-20260322-F5003D', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:30:07'),
(238, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:37:41'),
(239, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:52:23'),
(240, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:52:23'),
(241, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:53:32'),
(242, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 11:53:42'),
(243, 'admin', 1, 'UPDATE_PROPERTY_ORDER', 'Updated property order #PO-20260322-F5003D status to approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 11:57:03'),
(244, 'admin', 1, 'UPDATE_PROPERTY_ORDER', 'Updated property order #PO-20260322-F5003D status to completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 11:57:23'),
(245, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:00:19'),
(246, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: HP EliteBook 850 G8 (ID: 8)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:00:22'),
(247, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:02:44'),
(248, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:02:52'),
(249, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:02:53'),
(250, 'customer', 1, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 12:15:40'),
(251, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:46:02'),
(252, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:46:27'),
(253, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:46:55'),
(254, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:47:03'),
(255, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:47:54'),
(256, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:47:54'),
(257, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:48:38'),
(258, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 12:52:55'),
(259, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 12:55:48'),
(260, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 13:14:20'),
(261, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 13:14:25'),
(262, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '192.168.1.72', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 13:15:03'),
(263, 'customer', 1, 'VIEW_VEHICLE', 'Viewed vehicle: Toyota has a total of 12 car Toyota Fortuner (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 13:16:22'),
(264, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 13:53:34'),
(265, 'customer', 1, 'CREATE_VEHICLE_BOOKING', 'Created vehicle booking: VB-20260322-F5ADFE', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-22 15:34:07'),
(266, 'customer', 1, 'CREATE_VEHICLE_BOOKING', 'Created vehicle booking: VB-20260322-ED3350', '192.168.1.65', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 15:39:58'),
(267, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 16:13:39'),
(268, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 16:52:22'),
(269, 'customer', 1, 'LOGIN', 'Customer logged in successfully', '192.168.1.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 16:54:54'),
(270, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Nike Shox TL - The shoe that defined futurism (ID: 10)', '192.168.1.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 16:54:54'),
(271, 'customer', 1, 'ADD_TO_CART', 'Added to cart: Nike Shox TL - The shoe that defined futurism (Quantity: 1)', '192.168.1.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 16:55:00'),
(272, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Nike Shox TL - The shoe that defined futurism (ID: 10)', '192.168.1.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 16:55:19'),
(273, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Nike Shox TL - The shoe that defined futurism (ID: 10)', '192.168.1.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 16:55:22'),
(274, 'customer', 1, 'VIEW_PRODUCT', 'Viewed product: Nike Shox TL - The shoe that defined futurism (ID: 10)', '192.168.1.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 16:55:45'),
(275, 'customer', 1, 'PLACE_ORDER', 'Placed order: ORD-20260322-5A9B15 (Total: 45,000 FRw)', '192.168.1.203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-03-22 16:56:37'),
(276, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260322-5A9B15 status to approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 16:57:28'),
(277, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260322-5A9B15 status to processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 16:57:49'),
(278, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260322-5A9B15 status to shipped', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 16:58:02'),
(279, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260322-5A9B15 status to delivered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 16:58:34'),
(280, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:30:18'),
(281, 'admin', 1, 'CHANGE_ORDER_STATUS', 'Changed order #3 status to shipped', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:32:00'),
(282, 'admin', 1, 'CHANGE_ORDER_STATUS', 'Changed order #3 status to delivered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:32:07'),
(283, 'admin', 1, 'CHANGE_ORDER_STATUS', 'Changed order #3 status to shipped', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:32:12'),
(284, 'admin', 1, 'CHANGE_ORDER_STATUS', 'Changed order #3 status to delivered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:32:16'),
(285, 'admin', 1, 'CHANGE_PROPERTY_ORDER_STATUS', 'Changed property order #1 status to cancelled', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:32:23'),
(286, 'admin', 1, 'CHANGE_PROPERTY_ORDER_STATUS', 'Changed property order #1 status to completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:32:26'),
(287, 'admin', 1, 'CHANGE_BOOKING_STATUS', 'Changed booking #1 status to completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:32:33'),
(288, 'admin', 1, 'CHANGE_BOOKING_STATUS', 'Changed booking #2 status to completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 21:32:37'),
(289, 'customer', 2, 'REGISTER', 'New customer registration', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 12:54:20'),
(290, 'customer', 2, 'LOGIN', 'Customer logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 12:57:36'),
(291, 'admin', 1, 'logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 13:57:25'),
(292, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 13:58:04'),
(293, 'admin', 1, 'delete_banner', 'Deleted banner ID: 22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:04:49'),
(294, 'admin', 1, 'delete_banner', 'Deleted banner ID: 24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:04:54'),
(295, 'admin', 1, 'delete_banner', 'Deleted banner ID: 26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:04:58'),
(296, 'admin', 1, 'delete_banner', 'Deleted banner ID: 28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:02'),
(297, 'admin', 1, 'delete_banner', 'Deleted banner ID: 31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:06'),
(298, 'admin', 1, 'delete_banner', 'Deleted banner ID: 12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:10'),
(299, 'admin', 1, 'delete_banner', 'Deleted banner ID: 15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:15'),
(300, 'admin', 1, 'delete_banner', 'Deleted banner ID: 32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:19'),
(301, 'admin', 1, 'delete_banner', 'Deleted banner ID: 17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:24'),
(302, 'admin', 1, 'delete_banner', 'Deleted banner ID: 7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:29'),
(303, 'admin', 1, 'delete_banner', 'Deleted banner ID: 29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:33'),
(304, 'admin', 1, 'delete_banner', 'Deleted banner ID: 18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:37'),
(305, 'admin', 1, 'delete_banner', 'Deleted banner ID: 23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:41'),
(306, 'admin', 1, 'delete_banner', 'Deleted banner ID: 25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:44'),
(307, 'admin', 1, 'delete_banner', 'Deleted banner ID: 27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:48'),
(308, 'admin', 1, 'delete_banner', 'Deleted banner ID: 9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:52'),
(309, 'admin', 1, 'delete_banner', 'Deleted banner ID: 11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:05:57'),
(310, 'admin', 1, 'delete_banner', 'Deleted banner ID: 13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:06:01'),
(311, 'admin', 1, 'delete_banner', 'Deleted banner ID: 19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:06:05'),
(312, 'admin', 1, 'delete_banner', 'Deleted banner ID: 30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:06:09'),
(313, 'admin', 1, 'delete_banner', 'Deleted banner ID: 14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:06:12'),
(314, 'admin', 1, 'delete_banner', 'Deleted banner ID: 20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:06:15'),
(315, 'admin', 1, 'delete_banner', 'Deleted banner ID: 21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:06:20'),
(316, 'admin', 1, 'EDIT_PRODUCT', 'Updated product: HP EliteBook 850 G8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:08:19'),
(317, 'admin', 1, 'EDIT_PRODUCT', 'Updated product: iPhone 15 Pro Max 256Go Titane Bleu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:09:03'),
(318, 'admin', 1, 'edit_vehicle', 'Updated vehicle: BMW M30 Modified BMW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:09:25'),
(319, 'admin', 1, 'edit_vehicle', 'Updated vehicle: BMW M30 Modified BMW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:10:12'),
(320, 'admin', 1, 'add_vehicle', 'Added new vehicle: chevy BMW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:21:38'),
(321, 'customer', 2, 'LOGIN', 'Customer logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:22:20'),
(322, 'customer', 2, 'VIEW_VEHICLE', 'Viewed vehicle: chevy BMW (ID: 10)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:22:20'),
(323, 'customer', 2, 'VIEW_VEHICLE', 'Viewed vehicle: chevy BMW (ID: 10)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:24:44'),
(324, 'admin', 1, 'edit_vehicle', 'Updated vehicle: chevy BMW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:25:23'),
(325, 'customer', 2, 'VIEW_VEHICLE', 'Viewed vehicle: chevy BMW (ID: 10)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:25:42'),
(326, 'customer', 2, 'VIEW_PROPERTY', 'Viewed property: A Primary Residence To A Rental Property (ID: 4)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 15:13:20'),
(327, 'customer', 2, 'VIEW_VEHICLE', 'Viewed vehicle: BMW M30 Modified BMW (ID: 4)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 15:14:13'),
(328, 'customer', 2, 'CREATE_VEHICLE_BOOKING', 'Created vehicle booking: VB-20260326-78839D', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 15:15:51'),
(329, 'admin', 1, 'UPDATE_BOOKING_STATUS', 'Updated booking #VB-20260326-78839D status to approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 15:17:34'),
(330, 'admin', 1, 'UPDATE_BOOKING_STATUS', 'Updated booking #VB-20260326-78839D status to active', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 15:27:31'),
(331, 'admin', 1, 'UPDATE_BOOKING_STATUS', 'Updated booking #VB-20260326-78839D status to completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 16:08:24'),
(332, 'customer', 2, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 17:32:48'),
(333, 'customer', 2, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 17:33:34'),
(334, 'customer', 2, 'ADD_TO_CART', 'Added to cart: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (Quantity: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 17:33:43'),
(335, 'customer', 2, 'UPDATE_CART', 'Updated cart: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (Quantity: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 17:33:47'),
(336, 'customer', 2, 'PLACE_ORDER', 'Placed order: ORD-20260327-878E40 (Total: 20,000 FRw)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:22:48'),
(337, 'admin', 1, 'ADD_PRODUCT', 'Added product: olipro', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:37:32'),
(338, 'customer', 2, 'VIEW_PRODUCT', 'Viewed product: Galaxy S22 Ultra 5G (ID: 11)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:38:07'),
(339, 'customer', 2, 'ADD_TO_CART', 'Added to cart: Galaxy S22 Ultra 5G (Quantity: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:38:18'),
(340, 'customer', 2, 'PLACE_ORDER', 'Placed order: ORD-20260327-EEDE8A (Total: 1,205,000 FRw)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:40:31'),
(341, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260327-EEDE8A status to approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:41:43'),
(342, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260327-EEDE8A status to processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:42:12'),
(343, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260327-EEDE8A status to delivered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:42:40'),
(344, 'customer', 2, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:43:52'),
(345, 'customer', 2, 'VIEW_PROPERTY', 'Viewed property: inzu ikodeshwa muri kibagabaga (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:46:11'),
(346, 'admin', 1, 'create_banner', 'Created banner: grgrgg', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 07:58:12'),
(347, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:01:29'),
(348, 'admin', 1, 'delete_banner', 'Deleted banner ID: 33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:24:37'),
(349, 'customer', 3, 'REGISTER', 'New customer registration', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:26:56'),
(350, 'customer', 3, 'LOGIN', 'Customer logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:27:15'),
(351, 'customer', 3, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:27:33'),
(352, 'customer', 3, 'ADD_TO_CART', 'Added to cart: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (Quantity: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:27:49'),
(353, 'customer', 3, 'PLACE_ORDER', 'Placed order: ORD-20260327-BE75D0 (Total: 20,000 FRw)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:28:28'),
(354, 'admin', 1, 'logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:28:38'),
(355, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:28:56'),
(356, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260327-BE75D0 status to approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:29:40'),
(357, 'customer', 3, 'LOGIN', 'Customer logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:29:59'),
(358, 'customer', 3, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:30:20'),
(359, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260327-BE75D0 status to shipped', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:30:56'),
(360, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260327-BE75D0 status to delivered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:31:18'),
(361, 'admin', 1, 'ADD_PRODUCT', 'Added product: mucyo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:32:58'),
(362, 'customer', 3, 'VIEW_PRODUCT', 'Viewed product: mucyo (ID: 15)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:33:18'),
(363, 'admin', 1, 'create_banner', 'Created banner: ww', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-27 08:34:22'),
(364, 'customer', 3, 'LOGIN', 'Customer logged in successfully', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:44:12'),
(365, 'customer', 3, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:44:28'),
(366, 'customer', 3, 'VIEW_PRODUCT', 'Viewed product: Medium Blue Jeans for Men (ID: 13)', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:44:36'),
(367, 'customer', 3, 'ADD_TO_CART', 'Added to cart: Medium Blue Jeans for Men (Quantity: 1)', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:44:40'),
(368, 'customer', 3, 'PLACE_ORDER', 'Placed order: ORD-20260327-73B040 (Total: 25,000 FRw)', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:45:11'),
(369, 'admin', 1, 'login', 'Admin logged in', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:48:15'),
(370, 'admin', 1, 'UPDATE_ORDER', 'Updated order #ORD-20260327-73B040 status to delivered', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:48:53'),
(371, 'admin', 1, 'logout', 'Admin logged out', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:49:07'),
(372, 'customer', 3, 'LOGIN', 'Customer logged in successfully', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:49:44'),
(373, 'customer', 3, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:50:00'),
(374, 'customer', 3, 'VIEW_PRODUCT', 'Viewed product: TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket (ID: 12)', '192.168.18.5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '2026-03-27 08:50:57');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `permissions` text DEFAULT NULL COMMENT 'JSON format for granular permissions',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `phone`, `password`, `profile_image`, `permissions`, `status`, `last_login`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@zubamarket.com', '+250784567890', '$2y$10$Vp6z7DNQvxN0vwIFqEXkDO2slH8KKJ4thMdqi5G8F4e81lVSlnDjy', '69bbbc5c2d38d_1773911132.jpg', NULL, 'active', '2026-03-27 08:48:15', NULL, '2026-03-13 19:40:20', '2026-03-27 08:48:15');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `position` enum('hero','top','middle','bottom','sidebar') DEFAULT 'hero',
  `page` enum('home','products','properties','vehicles','all') DEFAULT 'home',
  `background_color` varchar(50) DEFAULT NULL,
  `text_color` varchar(50) DEFAULT '#ffffff',
  `overlay_opacity` decimal(3,2) DEFAULT 0.50,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `clicks` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`, `start_date`, `end_date`, `clicks`, `views`, `created_at`, `updated_at`) VALUES
(2, 'Shop the Latest Electronics', 'Up to 50% Off on Selected Items', 'Upgrade your tech with our exclusive deals on smartphones, laptops, and accessories. Limited time offer!', '69bd119469973_1773998484.jpg', 'Shop Electronics', 'products.php?category=electronics', 'hero', 'home', '#1a1a2e', '#ffffff', 0.50, 4, 'active', '2026-03-19 11:36:02', '2026-06-19 11:36:02', 0, 280, '2026-03-19 09:36:02', '2026-04-11 14:17:28'),
(3, 'Find Your Dream Home', 'Premium Properties in Prime Locations', 'Browse through our exclusive collection of residential and commercial properties. Your perfect space awaits!', '69bd11df64145_1773998559.jpg', 'Browse Properties', 'properties.php', 'hero', 'home', '#10b981', '#ffffff', 0.50, 5, 'active', '2026-03-19 11:36:02', '2026-09-19 11:36:02', 0, 280, '2026-03-19 09:36:02', '2026-04-11 14:17:28'),
(4, 'Rent Your Perfect Ride', 'Affordable Daily, Weekly & Monthly Rates', 'Choose from our wide selection of well-maintained vehicles. Book now and hit the road with confidence!', '69bd11aa784bc_1773998506.jpg', 'View Vehicles', 'vehicles.php', 'hero', 'home', '#3b82f6', '#ffffff', 0.40, 4, 'active', '2026-03-19 11:36:02', '2026-09-19 11:36:02', 0, 283, '2026-03-19 09:36:02', '2026-04-11 14:17:28'),
(5, 'Summer Sale Extravaganza', 'Massive Discounts Across All Categories', 'Save big on products, properties, and vehicle rentals. Don\'t miss out on these incredible deals!', '69bd11febfe2a_1773998590.jpg', 'Shop Sale', 'products.php?sale=1', 'hero', 'home', '#ef4444', '#ffffff', 0.50, 6, 'active', '2026-03-19 11:36:02', '2026-04-19 11:36:02', 0, 280, '2026-03-19 09:36:02', '2026-04-11 14:17:28'),
(6, 'Fashion Week Special', 'New Arrivals - Trending Styles', 'Discover the latest fashion trends and elevate your wardrobe with our exclusive collection.', '69bbd3565e534_1773917014.jpg', 'Shop Fashion', 'products.php?category=fashion', 'top', 'products', '#ec4899', '#ffffff', 0.40, 1, 'active', '2026-03-19 12:43:00', '2026-03-26 12:43:00', 0, 0, '2026-03-19 09:36:02', '2026-03-20 09:37:42'),
(8, 'Luxury Apartments Available', 'Modern Living in the Heart of Kigali', 'Explore our premium apartment listings with world-class amenities and stunning views.', '69bbd3cd270d2_1773917133.jpg', 'View Apartments', 'properties.php?type=apartment', 'hero', 'home', '#8b5cf6', '#ffffff', 0.40, 2, 'active', NULL, NULL, 0, 275, '2026-03-19 09:36:02', '2026-04-11 14:17:28'),
(10, 'Luxury Car Collection', 'Experience Premium Driving', 'Rent high-end vehicles for special occasions, business trips, or leisure travel.', '69be86a289f35_1774093986.jpg', 'View Luxury Cars', 'vehicles.php?type=luxury', 'hero', 'home', '#1e293b', '#ffffff', 0.50, 7, '', NULL, NULL, 0, 0, '2026-03-19 09:36:02', '2026-03-21 11:53:06'),
(16, 'Join Our Newsletter', 'Get 10% Off Your First Order', 'Subscribe now and receive exclusive deals, new arrivals, and special offers directly to your inbox.', '69bd1180c6b35_1773998464.jpg', 'Subscribe Now', '#newsletter', 'hero', 'home', '#f97316', '#ffffff', 0.40, 3, 'active', NULL, NULL, 0, 275, '2026-03-19 09:36:02', '2026-04-11 14:17:28'),
(34, 'ww', '22w2', 'www', '69c6410e23298_1774600462.jpg', '', '', 'hero', 'home', '#f97316', '#ffffff', 0.50, 0, 'active', NULL, NULL, 0, 15, '2026-03-27 08:34:22', '2026-04-11 14:17:28');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) UNSIGNED NOT NULL,
  `booking_number` varchar(50) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `vehicle_id` int(11) UNSIGNED NOT NULL,
  `vehicle_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `rental_days` int(11) NOT NULL,
  `rate_type` enum('daily','weekly','monthly') NOT NULL,
  `rate_amount` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `insurance_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `pickup_location` varchar(255) DEFAULT NULL,
  `dropoff_location` varchar(255) DEFAULT NULL,
  `payment_method_id` int(11) UNSIGNED DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending_payment','payment_submitted','approved','active','completed','rejected','cancelled') NOT NULL DEFAULT 'pending_payment',
  `customer_note` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_number`, `user_id`, `vehicle_id`, `vehicle_name`, `start_date`, `end_date`, `rental_days`, `rate_type`, `rate_amount`, `subtotal`, `insurance_fee`, `total_amount`, `pickup_location`, `dropoff_location`, `payment_method_id`, `payment_proof`, `status`, `customer_note`, `admin_note`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 'VB-20260322-F5ADFE', 1, 3, 'Toyota has a total of 12 car Toyota Fortuner 2026', '2026-03-23', '2026-03-25', 2, 'daily', 50000.00, 100000.00, 0.00, 100000.00, 'masaka', 'masaka', 1, 'uploads/payment_proofs/payment_1774193647_69c00bef5978e.jpg', 'completed', '', NULL, NULL, NULL, '2026-03-22 15:34:07', '2026-03-22 21:32:33'),
(2, 'VB-20260322-ED3350', 1, 4, 'BMW M30 Modified BMW 2026', '2026-03-23', '2026-03-25', 2, 'daily', 60000.00, 120000.00, 0.00, 120000.00, 'Masaka', 'Masaka', 4, 'uploads/payment_proofs/payment_1774193998_69c00d4ed23ee.jpg', 'completed', '', NULL, NULL, NULL, '2026-03-22 15:39:58', '2026-03-22 21:32:37'),
(3, 'VB-20260326-78839D', 2, 4, 'BMW M30 Modified BMW 2025', '2026-03-26', '2026-04-03', 8, 'daily', 60000.00, 480000.00, 0.00, 480000.00, 'kimicanga', 'kimicanga', 2, 'uploads/payment_proofs/payment_1774538151_69c54da787c5a.jpeg', 'completed', '', 'noted', 1, '2026-03-26 15:17:34', '2026-03-26 15:15:51', '2026-03-26 16:08:24');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `type` enum('ecommerce','realestate','carrental') NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `type`, `description`, `image`, `parent_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', 'electronics', 'ecommerce', 'Electronic devices and gadgets', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(2, 'Fashion', 'fashion', 'ecommerce', 'Clothing and accessories', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(3, 'Home & Garden', 'home-garden', 'ecommerce', 'Home improvement and garden supplies', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(4, 'Sports', 'sports', 'ecommerce', 'Sports equipment and accessories', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(5, 'Residential', 'residential', 'realestate', 'Houses and apartments', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(6, 'Commercial', 'commercial', 'realestate', 'Commercial properties', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(7, 'Land', 'land', 'realestate', 'Land for sale or lease', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(8, 'Economy Cars', 'economy-cars', 'carrental', 'Affordable and fuel-efficient vehicles', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(9, 'Luxury Cars', 'luxury-cars', 'carrental', 'Premium and luxury vehicles', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(10, 'SUVs', 'suvs', 'carrental', 'Sport utility vehicles', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(11, 'Vans', 'vans', 'carrental', 'Passenger and cargo vans', NULL, NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method_id` int(11) UNSIGNED DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending_payment','payment_submitted','approved','processing','shipped','delivered','rejected','cancelled') NOT NULL DEFAULT 'pending_payment',
  `shipping_name` varchar(100) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_country` varchar(100) NOT NULL,
  `customer_note` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `subtotal`, `shipping_fee`, `tax`, `total_amount`, `payment_method_id`, `payment_proof`, `status`, `shipping_name`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_country`, `customer_note`, `admin_note`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(3, 'ORD-20260322-9294B1', 1, 1200000.00, 5000.00, 0.00, 1205000.00, 1, 'payment_1774173881_69bfbeb92964f.jpg', 'delivered', 'Mucyo Clebere', '0791291980', 'Kicukiro, kabuga', 'Kigali', 'Rwanda', '', 'okay verified', 1, '2026-03-22 10:29:51', '2026-03-22 10:04:41', '2026-03-22 21:32:16'),
(4, 'ORD-20260322-5A9B15', 1, 40000.00, 5000.00, 0.00, 45000.00, 4, 'payment_1774198597_69c01f45aa5a3.jpg', 'delivered', 'Mucyo Clebere', '0791291980', 'Kicukiro, kabuga', 'Kigali', 'Rwanda', '', 'noted', 1, '2026-03-22 16:57:28', '2026-03-22 16:56:37', '2026-03-22 16:58:23'),
(5, 'ORD-20260327-878E40', 2, 15000.00, 5000.00, 0.00, 20000.00, 2, 'payment_1774596168_69c6304879092.jpg', 'payment_submitted', 'irakoze olivier', '+250798526218', 'karan station road', 'kigali city', 'Rwanda', '', NULL, NULL, NULL, '2026-03-27 07:22:48', '2026-03-27 07:22:48'),
(6, 'ORD-20260327-EEDE8A', 2, 1200000.00, 5000.00, 0.00, 1205000.00, 4, 'payment_1774597230_69c6346eee0d6.png', 'delivered', 'irakoze olivier', '+250798526218', 'karan station road', 'kigali city', 'Rwanda', '', 'noted', 1, '2026-03-27 07:41:43', '2026-03-27 07:40:30', '2026-03-27 07:42:39'),
(7, 'ORD-20260327-BE75D0', 3, 15000.00, 5000.00, 0.00, 20000.00, 4, 'payment_1774600107_69c63fabe768f.jpg', 'delivered', 'junior', '+250798526211', 'kk 3rd', 'kigali city', 'Rwanda', '', 'done', 1, '2026-03-27 08:29:40', '2026-03-27 08:28:27', '2026-03-27 08:31:17'),
(8, 'ORD-20260327-73B040', 3, 20000.00, 5000.00, 0.00, 25000.00, 4, 'payment_1774601111_69c643973b178.png', 'delivered', 'junior', '+250798526211', 'kk 3rd', 'kigali city', 'Rwanda', '', 'Done', NULL, NULL, '2026-03-27 08:45:11', '2026-03-27 08:48:52');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`, `created_at`) VALUES
(3, 3, 11, 'Galaxy S22 Ultra 5G', 1, 1200000.00, 1200000.00, '2026-03-22 10:04:41'),
(4, 4, 10, 'Nike Shox TL - The shoe that defined futurism', 1, 40000.00, 40000.00, '2026-03-22 16:56:37'),
(5, 5, 12, 'TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket', 1, 15000.00, 15000.00, '2026-03-27 07:22:48'),
(6, 6, 11, 'Galaxy S22 Ultra 5G', 1, 1200000.00, 1200000.00, '2026-03-27 07:40:30'),
(7, 7, 12, 'TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket', 1, 15000.00, 15000.00, '2026-03-27 08:28:28'),
(8, 8, 13, 'Medium Blue Jeans for Men', 1, 20000.00, 20000.00, '2026-03-27 08:45:11');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('mobile_money','bank','other') NOT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `type`, `account_name`, `account_number`, `instructions`, `logo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Airtel Money', 'mobile_money', 'Zuba Market', '+250788123456', 'Send payment to the number above and upload screenshot', NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(2, 'MoMo Pay Code', 'mobile_money', 'Zuba Market', '+250788654321', 'Use MoMo Pay Code and upload payment confirmation', NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(3, 'Bank Transfer', 'bank', 'Zuba Online Market Ltd', '1234567890', 'Transfer to our bank account and upload receipt', NULL, 'active', '2026-03-13 19:40:20', '2026-03-13 19:40:20'),
(4, 'MTN Mobile Money', 'mobile_money', 'Zuba Market', '07912191980', 'make sure you have sent to real account number and names', '69bbbb4f28e03_1773910863.jpg', 'active', '2026-03-19 09:01:03', '2026-03-19 09:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `compare_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sku` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `condition` enum('new','used','refurbished') DEFAULT 'new',
  `weight` decimal(8,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') NOT NULL DEFAULT 'active',
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `compare_price`, `stock`, `sku`, `brand`, `condition`, `weight`, `dimensions`, `featured`, `status`, `views`, `created_at`, `updated_at`) VALUES
(8, 1, 'HP EliteBook 850 G8', 'hp-elitebook-850-g8', 'These are post-lease laptop', 600000.00, 900000.00, 80, 'ZB-001', '', 'new', NULL, NULL, 1, '', 21, '2026-03-18 19:44:27', '2026-03-26 14:08:19'),
(9, 1, 'iPhone 15 Pro Max 256Go Titane Bleu', 'iphone-15-pro-max-256go-titane-bleu', 'iPhone 15 Pro Max - Ecran: Super Retina XDR OLED 6,7 pouces - Résolution: 2 796 x 1 290 pixels à 460 ppp - Processeur : Puce A17 Pro (CPU 6 cœurs avec 2 cœurs de performance et 4 cœurs à haute efficacité énergétique) GPU 6 cœurs, Neural Engine 16 cœurs  - Système d\'exploitation: iOS 17 - Stockage:', 1300000.00, 1500000.00, 46, 'ZB-002', 'Apple', 'new', NULL, NULL, 0, '', 10, '2026-03-20 13:03:35', '2026-03-26 14:09:03'),
(10, 2, 'Nike Shox TL - The shoe that defined futurism', 'nike-shox-tl-the-shoe-that-defined-futurism', 'The Nike Shox TL isn\'t just a sneaker, it\'s a statement. A piece that looked like a prop from a sci-fi movie at the turn of the millennium and is now back to dominate the streets. If you\'re looking for a design that combines an uncompromisi', 40000.00, 55000.00, 100, 'ZB-003', 'Nike', 'new', NULL, NULL, 0, 'active', 10, '2026-03-20 13:07:34', '2026-03-22 16:55:45'),
(11, 1, 'Galaxy S22 Ultra 5G', 'galaxy-s22-ultra-5g', 'Meet Galaxy S22 Ultra 5G, with the power of Note. Slim and bold, a polished frame surrounds the extruded shape for elegant symmetry. And the linear camera, accented by mirrored lens rings, seems to float in place.', 1200000.00, 1890000.00, 57, 'ZB-004', 'Samsung', 'new', NULL, NULL, 1, 'active', 25, '2026-03-20 13:11:07', '2026-03-27 07:38:07'),
(12, 2, 'TACVASEN Mens Bomber Jacket Lightweight Casual Spring Fall Windbreaker Zip Up Coat With Pocket', 'tacvasen-mens-bomber-jacket-lightweight-casual-spring-fall-windbreaker-zip-up-coat-with-pocket', 'Material: Polyester; Lightweight, Windbreaker, Breathable\r\nFull zipper stand collar bomber jacket, The zipper head is on the left\r\nFEATURE: Rib Knit Cuffs, Waistband, Collar for comfort, Dual pen pockets on sleeve\r\nMULTI-POCKETS: 1 zipper sleeve pocket, 2 slant hand pockets, 1 inner pocket\r\nSuitable for: Outdoor, Casual, Sportwear, Working, Daily life, Hiking, Clubwear, Sports, Spring, Fall or other outdoor activities.', 15000.00, 20000.00, 400, 'ZB-005', 'Dickie', 'new', NULL, NULL, 1, 'active', 27, '2026-03-20 13:20:56', '2026-03-27 08:50:57'),
(13, 2, 'Medium Blue Jeans for Men', 'medium-blue-jeans-for-men', 'Jeans that make your outfit stand out from the crowd. This men\'s jeans is crafted from a blend of 99% cotton and 1% spandex, forming a resilient fabric combination that not only provides comfort but is also designed to endure everyday use.', 20000.00, 30000.00, 245, 'ZB-006', 'Jeans', 'new', NULL, NULL, 0, 'active', 4, '2026-03-20 14:07:21', '2026-03-27 08:44:35'),
(14, 3, 'olipro', 'olipro', 'ykujjghdf', 0.36, 0.20, 8, '', 'Apple', 'new', NULL, NULL, 1, 'active', 0, '2026-03-27 07:37:32', '2026-03-27 07:37:32'),
(15, 1, 'mucyo', 'mucyo', 'dedee', 22222.00, 2222.00, 22, '', 'BMW M30 Modified', 'new', NULL, NULL, 1, 'active', 1, '2026-03-27 08:32:58', '2026-03-27 08:33:18');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`, `sort_order`, `created_at`) VALUES
(18, 8, '69bb009b3fa35_1773863067.jpg', 1, 0, '2026-03-18 19:44:27'),
(19, 8, '69bb009b41c04_1773863067.jpg', 0, 1, '2026-03-18 19:44:27'),
(20, 8, '69bb009b42c60_1773863067.jpg', 0, 2, '2026-03-18 19:44:27'),
(21, 9, '69bd45a7164e8_1774011815.jpg', 1, 0, '2026-03-20 13:03:35'),
(22, 9, '69bd45a717afc_1774011815.jpg', 0, 1, '2026-03-20 13:03:35'),
(23, 9, '69bd45a718b36_1774011815.jpg', 0, 2, '2026-03-20 13:03:35'),
(24, 10, '69bd4696484dc_1774012054.jpg', 1, 0, '2026-03-20 13:07:34'),
(25, 10, '69bd469649de5_1774012054.jpeg', 0, 1, '2026-03-20 13:07:34'),
(26, 10, '69bd46964c159_1774012054.jpeg', 0, 2, '2026-03-20 13:07:34'),
(27, 11, '69bd476b67543_1774012267.jpeg', 1, 0, '2026-03-20 13:11:07'),
(28, 11, '69bd476b6838b_1774012267.jpeg', 0, 1, '2026-03-20 13:11:07'),
(29, 12, '69bd49b854f61_1774012856.jpg', 1, 0, '2026-03-20 13:20:56'),
(30, 12, '69bd49b859ac9_1774012856.jpg', 0, 1, '2026-03-20 13:20:56'),
(31, 13, '69bd54999eb95_1774015641.jpeg', 1, 0, '2026-03-20 14:07:21'),
(32, 13, '69bd5499a05d6_1774015641.webp', 0, 1, '2026-03-20 14:07:21'),
(33, 14, '69c633bc1ea18_1774597052.jpeg', 1, 0, '2026-03-27 07:37:32'),
(34, 15, '69c640ba46034_1774600378.jpg', 1, 0, '2026-03-27 08:32:58');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `listing_type` enum('sale','rent') NOT NULL,
  `property_type` enum('apartment','house','villa','commercial','land','office','other') NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `rent_period` enum('daily','weekly','monthly','yearly') DEFAULT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `area_unit` enum('sqm','sqft','acres','hectares') DEFAULT 'sqm',
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `year_built` int(4) DEFAULT NULL,
  `parking_spaces` int(11) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('available','sold','rented','inactive') NOT NULL DEFAULT 'available',
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `category_id`, `title`, `slug`, `description`, `listing_type`, `property_type`, `price`, `rent_period`, `bedrooms`, `bathrooms`, `area`, `area_unit`, `address`, `city`, `state`, `country`, `zip_code`, `latitude`, `longitude`, `year_built`, `parking_spaces`, `features`, `featured`, `status`, `views`, `created_at`, `updated_at`) VALUES
(3, 6, 'inzu ikodeshwa muri kibagabaga', 'inzu-ikodeshwa-muri-kibagabaga', 'jlhlhlh', 'sale', 'house', 60000000.00, '', 6, 7, 777.00, 'sqm', 'Kicukiro, Kabuga', 'Gasabo', 'kigali', 'Rwanda', '', 0.00000000, 0.00000000, 2021, 6, '', 1, '', 30, '2026-03-18 19:21:12', '2026-03-27 07:46:11'),
(4, 5, 'A Primary Residence To A Rental Property', 'a-primary-residence-to-a-rental-property', 'Although many people choose to sell their home before buying another one, that isn’t necessarily the right choice for everyone. Converting a primary residence to a rental property takes time, but if done right, can prove to drastically improve your passive income in just a few months.', 'rent', 'house', 500000.00, '', 4, 4, 456.00, 'sqm', 'Kicukiro, Kabuga', 'Gasabo', 'kigali', 'Rwanda', '', 0.00000000, 0.00000000, 2022, 4, 'Swimming pool, Garden, Security, Gym', 0, '', 9, '2026-03-20 14:19:06', '2026-03-26 15:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) UNSIGNED NOT NULL,
  `property_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_path`, `is_primary`, `sort_order`, `created_at`) VALUES
(9, 3, '69bafb28448b2_1773861672.jpg', 1, 0, '2026-03-18 19:21:12'),
(10, 3, '69bafb2845b48_1773861672.jpg', 0, 1, '2026-03-18 19:21:12'),
(11, 3, '69bafb2846b1e_1773861672.jpg', 0, 2, '2026-03-18 19:21:12'),
(12, 4, '69bd575adc6a2_1774016346.jpg', 1, 0, '2026-03-20 14:19:06'),
(13, 4, '69bd575adfe11_1774016346.jpg', 0, 1, '2026-03-20 14:19:06'),
(14, 4, '69bd575ae1467_1774016346.jpg', 0, 2, '2026-03-20 14:19:06');

-- --------------------------------------------------------

--
-- Table structure for table `property_orders`
--

CREATE TABLE `property_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `property_id` int(11) UNSIGNED NOT NULL,
  `property_title` varchar(255) NOT NULL,
  `order_type` enum('purchase','rent') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `rent_duration` int(11) DEFAULT NULL,
  `rent_period` enum('daily','weekly','monthly','yearly') DEFAULT NULL,
  `payment_method_id` int(11) UNSIGNED DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending_payment','payment_submitted','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending_payment',
  `customer_note` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_orders`
--

INSERT INTO `property_orders` (`id`, `order_number`, `user_id`, `property_id`, `property_title`, `order_type`, `amount`, `rent_duration`, `rent_period`, `payment_method_id`, `payment_proof`, `status`, `customer_note`, `admin_note`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 'PO-20260322-F5003D', 1, 3, 'inzu ikodeshwa muri kibagabaga', 'purchase', 60000000.00, NULL, NULL, 3, 'payment_1774179007_69bfd2bf4ccad.jpg', 'completed', '', 'okay noted', 1, '2026-03-22 11:57:03', '2026-03-22 11:30:07', '2026-03-22 21:32:26');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `item_type` enum('product','property','vehicle') NOT NULL,
  `item_id` int(11) UNSIGNED NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') NOT NULL DEFAULT 'text',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `updated_at`) VALUES
(1, 'site_name', 'Zuba Online Market', 'text', '2026-03-26 11:50:18'),
(2, 'site_email', 'info@zubamarket.com', 'text', '2026-03-26 11:50:18'),
(3, 'site_phone', '+250788000000', 'text', '2026-03-26 11:50:18'),
(4, 'currency', 'RWF', 'text', '2026-03-26 11:50:18'),
(5, 'currency_symbol', 'FRw', 'text', '2026-03-26 11:50:18'),
(6, 'tax_rate', '0', 'number', '2026-03-26 11:50:18'),
(7, 'shipping_fee', '5000', 'number', '2026-03-26 11:50:18'),
(8, 'items_per_page', '12', 'number', '2026-03-26 11:50:18'),
(9, 'site_maintenance', '0', 'boolean', '2026-03-13 19:40:20'),
(10, 'site_tagline', 'Your One-Stop Marketplace', 'text', '2026-03-26 11:50:18'),
(11, 'site_address', 'Kigali, Rwanda', 'text', '2026-03-26 11:50:18'),
(12, 'enable_reviews', '1', 'boolean', '2026-03-26 11:50:18'),
(13, 'enable_wishlist', '1', 'boolean', '2026-03-26 11:50:18'),
(14, 'maintenance_mode', '0', 'boolean', '2026-03-26 11:50:18'),
(15, 'facebook_url', 'https://www.facebook.com/MucyoClebere', 'text', '2026-03-26 11:50:18'),
(16, 'twitter_url', '', 'text', '2026-03-26 11:50:18'),
(17, 'instagram_url', 'https://www.instagram.com/mucyoclebere/', 'text', '2026-03-26 11:50:18'),
(18, 'linkedin_url', '', 'text', '2026-03-26 11:50:18'),
(19, 'site_logo', '', 'text', '2026-03-26 11:50:18'),
(20, 'site_favicon', '', 'text', '2026-03-26 11:50:18'),
(21, 'enable_products', '1', 'boolean', '2026-03-26 11:50:18'),
(22, 'enable_properties', '1', 'boolean', '2026-03-26 11:50:18'),
(23, 'enable_vehicles', '1', 'boolean', '2026-03-26 11:50:18'),
(24, 'primary_color', '#f97316', 'text', '2026-03-26 11:50:18'),
(25, 'secondary_color', '#1a1a2e', 'text', '2026-03-26 11:50:18'),
(26, 'header_background', '#ffffff', 'text', '2026-03-26 11:50:18'),
(27, 'support_email', 'support@zubamarket.com', 'text', '2026-03-26 11:50:18'),
(28, 'support_phone', '+250788000000', 'text', '2026-03-26 11:50:18'),
(29, 'youtube_url', '', 'text', '2026-03-26 11:50:18'),
(30, 'whatsapp_number', '', 'text', '2026-03-26 11:50:18'),
(31, 'header_sticky', '1', 'boolean', '2026-03-26 11:50:18'),
(32, 'show_search_bar', '1', 'boolean', '2026-03-26 11:50:18'),
(33, 'show_cart_icon', '1', 'boolean', '2026-03-26 11:50:18'),
(34, 'show_wishlist_icon', '1', 'boolean', '2026-03-26 11:50:18'),
(35, 'show_user_menu', '1', 'boolean', '2026-03-26 11:50:18'),
(36, 'show_categories_menu', '1', 'boolean', '2026-03-26 11:50:18'),
(37, 'header_text_color', '#1a1a2e', 'text', '2026-03-26 11:50:18'),
(38, 'header_border_color', '#e5e7eb', 'text', '2026-03-26 11:50:18'),
(39, 'logo_max_width', '150', 'number', '2026-03-26 11:50:18'),
(40, 'logo_max_height', '50', 'number', '2026-03-26 11:50:18'),
(41, 'search_placeholder', 'Search products, properties, vehicles...', 'text', '2026-03-26 11:50:18'),
(42, 'enable_notifications', '1', 'boolean', '2026-03-26 11:50:18'),
(43, 'show_top_bar', '1', 'boolean', '2026-03-26 11:50:18'),
(44, 'top_bar_text', 'Free shipping on orders over 50,000 FRw', 'text', '2026-03-26 11:50:18'),
(45, 'top_bar_background', '#f97316', 'text', '2026-03-26 11:50:18'),
(46, 'top_bar_text_color', '#ffffff', 'text', '2026-03-26 11:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `profile_image`, `address`, `city`, `country`, `status`, `last_login`, `created_at`, `updated_at`, `reset_token`, `reset_expires`) VALUES
(1, 'Mucyo Clebere', 'mucyoclebere@gmail.com', '0791291980', '$2y$10$NPuztV5OEYpk6rSQ6/bnFeGXZHKPxn5Oh9XO2WiR.K60rpGAqrGYe', 'uploads/profiles/69c020593489f_1774198873.jpg', 'Kicukiro, kabuga', 'Kigali', 'Rwanda', 'active', '2026-03-22 16:54:54', '2026-03-22 08:25:24', '2026-03-22 17:01:13', NULL, NULL),
(2, 'irakoze olivier', 'irakozeolivier2023@gmail.com', '+250798526218', '$2y$10$7et05J8GW/LbNG18RWzL..HWf7J.JpmhmoJ9ZDmF8uIEEPkhv22Na', NULL, 'karan station road', 'kigali city', 'Rwanda', 'active', '2026-03-26 14:22:20', '2026-03-26 12:54:20', '2026-03-26 14:22:20', NULL, NULL),
(3, 'junior', 'author@news.com', '+250798526211', '$2y$10$N2G93ehl0fwb1owHS7hH5ulFRvSeSAak7i/filcvZWTMHp0iRYHG.', NULL, 'kk 3rd', 'kigali city', 'Rwanda', 'active', '2026-03-27 08:49:43', '2026-03-27 08:26:55', '2026-03-27 08:49:43', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `year` int(4) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `vehicle_type` enum('sedan','suv','truck','van','coupe','convertible','motorcycle','other') NOT NULL,
  `transmission` enum('automatic','manual') NOT NULL,
  `fuel_type` enum('petrol','diesel','electric','hybrid') NOT NULL,
  `seats` int(11) NOT NULL,
  `doors` int(11) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `plate_number` varchar(50) NOT NULL,
  `mileage` int(11) DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `weekly_rate` decimal(10,2) DEFAULT NULL,
  `monthly_rate` decimal(10,2) DEFAULT NULL,
  `insurance_included` tinyint(1) NOT NULL DEFAULT 0,
  `features` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('available','rented','maintenance','inactive') NOT NULL DEFAULT 'available',
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `category_id`, `brand`, `model`, `year`, `slug`, `description`, `vehicle_type`, `transmission`, `fuel_type`, `seats`, `doors`, `color`, `plate_number`, `mileage`, `daily_rate`, `weekly_rate`, `monthly_rate`, `insurance_included`, `features`, `location`, `featured`, `status`, `views`, `created_at`, `updated_at`) VALUES
(3, 10, 'Toyota has a total of 12 car', 'Toyota Fortuner', 2026, 'toyota-has-a-total-of-12-car-toyota-fortuner-2026', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur ac erat vitae nisl bibendum semper bibendum ut odio. Sed orci purus, rhoncus vitae libero non, convallis facilisis urna. Donec ullamcorper sit amet ipsum eu laoreet. Aliquam tempor facilisis sagittis. Praesent ex lectus, consectetur non turpis eget, faucibus euismod elit. Nulla at vestibulum elit. Morbi auctor augue libero, in laoreet nunc sagittis vel. Suspendisse posuere metus tellus.', 'suv', 'manual', '', 5, 4, 'Black', '233', 0, 50000.00, 140000.00, 450000.00, 1, 'GPS, Air Conditioning, Bluetooth, Backup Camera', 'Kigali, rusororo', 1, 'available', 9, '2026-03-20 15:42:15', '2026-03-22 13:16:21'),
(4, 9, 'BMW M30 Modified', 'BMW', 2025, 'bmw-m30-modified-bmw-2025', 'hjkfgjfhkgkf', 'sedan', 'manual', '', 5, 4, 'Silver', '455', 1000, 60000.00, 700000.00, 880700.00, 1, 'GPS, Air Conditioning, Bluetooth, Backup Camera', 'Kigali,rusororo', 1, 'available', 18, '2026-03-21 10:37:45', '2026-03-26 15:14:13'),
(10, 9, 'chevy', 'BMW', 2026, 'chevy-bmw-2026', 'ddcdcdc', 'sedan', 'manual', '', 2, 2, 'Silver', '45511', 0, 2222222.00, 0.00, 0.00, 0, '', 'Kigali, Rwanda', 0, 'available', 4, '2026-03-26 14:21:35', '2026-03-26 14:25:41');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_images`
--

CREATE TABLE `vehicle_images` (
  `id` int(11) UNSIGNED NOT NULL,
  `vehicle_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_images`
--

INSERT INTO `vehicle_images` (`id`, `vehicle_id`, `image_path`, `is_primary`, `sort_order`, `created_at`) VALUES
(7, 3, 'uploads/vehicles/69bd6ad76ca92_1774021335.jpg', 1, 0, '2026-03-20 15:42:15'),
(8, 3, 'uploads/vehicles/69bd6ad76e812_1774021335.jpg', 0, 1, '2026-03-20 15:42:15'),
(9, 3, 'uploads/vehicles/69bd6ad76f9a0_1774021335.jpg', 0, 2, '2026-03-20 15:42:15'),
(10, 4, 'uploads/vehicles/69be74f99224f_1774089465.jpg', 1, 0, '2026-03-21 10:37:45'),
(11, 4, 'uploads/vehicles/69be74f993a5b_1774089465.jpg', 0, 1, '2026-03-21 10:37:45'),
(12, 10, 'uploads/vehicles/69c541d34cdca_1774535123.jpeg', 1, 0, '2026-03-26 14:25:23');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `item_type` enum('product','property','vehicle') NOT NULL,
  `item_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `item_type`, `item_id`, `created_at`) VALUES
(14, 1, 'product', 10, '2026-03-22 16:55:40'),
(15, 2, 'product', 12, '2026-03-26 17:32:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_type_id` (`user_type`,`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `position` (`position`),
  ADD KEY `page` (`page`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_number` (`booking_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `status` (`status`),
  ADD KEY `start_date` (`start_date`),
  ADD KEY `end_date` (`end_date`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug_type` (`slug`,`type`),
  ADD KEY `type` (`type`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `approved_by` (`approved_by`);

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
  ADD KEY `status` (`status`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `status` (`status`),
  ADD KEY `featured` (`featured`),
  ADD KEY `price` (`price`);
ALTER TABLE `products` ADD FULLTEXT KEY `search` (`name`,`description`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `is_primary` (`is_primary`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `listing_type` (`listing_type`),
  ADD KEY `property_type` (`property_type`),
  ADD KEY `status` (`status`),
  ADD KEY `featured` (`featured`),
  ADD KEY `price` (`price`),
  ADD KEY `city` (`city`);
ALTER TABLE `properties` ADD FULLTEXT KEY `search` (`title`,`description`,`address`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `is_primary` (`is_primary`);

--
-- Indexes for table `property_orders`
--
ALTER TABLE `property_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `status` (`status`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_type_id` (`item_type`,`item_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `status` (`status`),
  ADD KEY `reset_token` (`reset_token`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `plate_number` (`plate_number`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `vehicle_type` (`vehicle_type`),
  ADD KEY `status` (`status`),
  ADD KEY `featured` (`featured`),
  ADD KEY `daily_rate` (`daily_rate`);
ALTER TABLE `vehicles` ADD FULLTEXT KEY `search` (`brand`,`model`,`description`);

--
-- Indexes for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `is_primary` (`is_primary`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_item` (`user_id`,`item_type`,`item_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_type_id` (`item_type`,`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `property_orders`
--
ALTER TABLE `property_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_orders`
--
ALTER TABLE `property_orders`
  ADD CONSTRAINT `property_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_orders_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_orders_ibfk_3` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `property_orders_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  ADD CONSTRAINT `vehicle_images_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
