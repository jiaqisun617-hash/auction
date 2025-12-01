-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-12-01 18:21:50
-- 服务器版本： 10.4.28-MariaDB
-- PHP 版本： 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `auctiondb`
--

-- --------------------------------------------------------

--
-- 表的结构 `auction`
--

CREATE TABLE `auction` (
  `auction_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `start_price` decimal(10,2) NOT NULL CHECK (`start_price` > 0),
  `reserve_price` decimal(10,2) NOT NULL CHECK (`reserve_price` >= 0),
  `start_time` datetime DEFAULT current_timestamp(),
  `end_time` datetime NOT NULL,
  `status` enum('Sched','Live','Closed','Unsold') DEFAULT 'Sched'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `auction`
--

INSERT INTO `auction` (`auction_id`, `item_id`, `start_price`, `reserve_price`, `start_time`, `end_time`, `status`) VALUES
(1, 1, 1000.00, 200.00, '2025-11-12 19:08:18', '2025-11-20 19:08:00', 'Sched'),
(2, 2, 100202.00, 20040.40, '2025-11-13 10:42:58', '2025-12-13 20:00:00', 'Sched'),
(4, 4, 50.00, 10.00, '2025-11-29 01:29:52', '2025-12-16 19:29:00', 'Sched'),
(5, 5, 55.00, 11.00, '2025-11-29 01:37:35', '2025-12-17 01:37:00', 'Sched'),
(6, 6, 15.00, 3.00, '2025-11-29 18:45:04', '2025-12-24 18:47:00', 'Sched');

-- --------------------------------------------------------

--
-- 表的结构 `bid`
--

CREATE TABLE `bid` (
  `bid_id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `bidder_id` int(11) NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL CHECK (`bid_amount` > 0),
  `bid_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `bid`
--

INSERT INTO `bid` (`bid_id`, `auction_id`, `bidder_id`, `bid_amount`, `bid_time`) VALUES
(1, 5, 2, 56.00, '2025-11-29 14:55:33'),
(2, 4, 2, 51.00, '2025-11-30 23:56:26'),
(3, 4, 4, 52.00, '2025-12-01 00:05:40'),
(4, 4, 4, 58.00, '2025-12-01 16:00:04'),
(5, 4, 4, 59.00, '2025-12-01 16:00:23'),
(6, 4, 4, 60.00, '2025-12-01 16:00:38'),
(7, 2, 4, 100203.00, '2025-12-01 16:00:50');

-- --------------------------------------------------------

--
-- 表的结构 `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Electronics'),
(2, 'Fashion'),
(3, 'Home & Kitchen'),
(4, 'Books'),
(5, 'Toys'),
(6, 'Sports'),
(7, 'Collectibles'),
(8, 'Beauty'),
(9, 'Grocery'),
(10, 'Pet Supplies');

-- --------------------------------------------------------

--
-- 表的结构 `image`
--

CREATE TABLE `image` (
  `image_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `image`
--

INSERT INTO `image` (`image_id`, `item_id`, `path`, `sort_order`) VALUES
(2, 4, 'uploads/item_4_1764379792.jpg', 1),
(3, 5, 'uploads/item_5_1764380255.jpg', 1),
(4, 6, 'uploads/item_6_1764441904.jpg', 1);

-- --------------------------------------------------------

--
-- 表的结构 `Item`
--

CREATE TABLE `Item` (
  `item_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `condition` varchar(50) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `Item`
--

INSERT INTO `Item` (`item_id`, `seller_id`, `category_id`, `title`, `description`, `condition`, `date_created`) VALUES
(1, 1, 1, 'iPhone17 pro', 'smartphone', 'New', '2025-11-12 19:08:18'),
(2, 1, 1, 'iPhone17 pro max', 'iphone', '', '2025-11-13 10:42:58'),
(4, 3, 3, 'Chair', 'Wodden Chair', 'Used - Like New', '2025-11-29 01:29:52'),
(5, 3, 3, 'Chair', 'office chair', 'Used - Good', '2025-11-29 01:37:35'),
(6, 4, 3, 'Chair', 'White chair', 'Used - Acceptable', '2025-11-29 18:45:04');

-- --------------------------------------------------------

--
-- 表的结构 `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `role`
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
(3, 'admin'),
(1, 'buyer'),
(2, 'seller');

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `account_type` enum('buyer','seller','admin') DEFAULT 'buyer',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password_hash`, `account_type`, `created_at`) VALUES
(1, '', 'jiaqisun617@gmail.com', '$2y$10$YAvdeWYnjh8mQyWOPvjg1uB9a/y4RtppqdkUK5GWbQLGTMZkSiHwO', 'buyer', '2025-11-12 19:07:10'),
(2, 'TestBuyer', 'TestBuyer@gmail.com', '$2y$10$ia4nS7kT8Tpw2YGUgqc0su74B9D3edUAooHU0eBqLW671Hzvjj/wm', 'buyer', '2025-11-29 00:51:34'),
(3, 'TestSeller', 'TestSeller@gmail.com', '$2y$10$15tgS.9pAT7FtuZ7.M60S.CPWyO2MFsILIILx1.fvVfs9Z.Tbmf6i', 'seller', '2025-11-29 01:24:00'),
(4, 'JYW', 'wu029385@gmail.com', '$2y$10$g2DDjQbYXph7/Isor4W6XuFgbXSxkVW1pSO/KZJXmYM5e0g6tq55a', 'buyer', '2025-11-29 15:18:44');

-- --------------------------------------------------------

--
-- 表的结构 `userrole`
--

CREATE TABLE `userrole` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `userrole`
--

INSERT INTO `userrole` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 3),
(2, 1),
(3, 2),
(4, 1),
(4, 2);

-- --------------------------------------------------------

--
-- 表的结构 `watchlist`
--

CREATE TABLE `watchlist` (
  `user_id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `notify_email` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `auction`
--
ALTER TABLE `auction`
  ADD PRIMARY KEY (`auction_id`),
  ADD KEY `item_id` (`item_id`);

--
-- 表的索引 `bid`
--
ALTER TABLE `bid`
  ADD PRIMARY KEY (`bid_id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `bidder_id` (`bidder_id`);

--
-- 表的索引 `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- 表的索引 `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `fk_image_item` (`item_id`);

--
-- 表的索引 `Item`
--
ALTER TABLE `Item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);

--
-- 表的索引 `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 表的索引 `userrole`
--
ALTER TABLE `userrole`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- 表的索引 `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`user_id`,`auction_id`),
  ADD KEY `auction_id` (`auction_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `auction`
--
ALTER TABLE `auction`
  MODIFY `auction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `bid`
--
ALTER TABLE `bid`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `image`
--
ALTER TABLE `image`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `Item`
--
ALTER TABLE `Item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 限制导出的表
--

--
-- 限制表 `auction`
--
ALTER TABLE `auction`
  ADD CONSTRAINT `auction_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE;

--
-- 限制表 `bid`
--
ALTER TABLE `bid`
  ADD CONSTRAINT `bid_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bid_ibfk_2` FOREIGN KEY (`bidder_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- 限制表 `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `fk_image_item` FOREIGN KEY (`item_id`) REFERENCES `Item` (`item_id`) ON DELETE CASCADE;

--
-- 限制表 `Item`
--
ALTER TABLE `Item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL;

--
-- 限制表 `userrole`
--
ALTER TABLE `userrole`
  ADD CONSTRAINT `userrole_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `userrole_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE;

--
-- 限制表 `watchlist`
--
ALTER TABLE `watchlist`
  ADD CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `watchlist_ibfk_2` FOREIGN KEY (`auction_id`) REFERENCES `auction` (`auction_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
