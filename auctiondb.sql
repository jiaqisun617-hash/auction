-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-12-04 00:17:34
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

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
(4, 4, 50.00, 10.00, '2025-11-29 01:29:52', '2025-12-16 19:29:00', 'Sched'),
(5, 5, 55.00, 11.00, '2025-11-29 01:37:35', '2025-12-17 01:37:00', 'Sched'),
(6, 6, 15.00, 3.00, '2025-11-29 18:45:04', '2025-12-24 18:47:00', 'Sched'),
(8, 8, 500.00, 100.00, '2025-12-01 18:15:32', '2025-12-04 18:14:00', 'Sched'),
(9, 9, 5.00, 1.00, '2025-12-01 18:24:17', '2025-12-03 18:22:00', 'Sched'),
(10, 10, 20.00, 4.00, '2025-12-01 18:28:55', '2025-12-04 00:27:00', 'Sched'),
(11, 11, 200.00, 40.00, '2025-12-01 18:30:35', '2025-12-02 22:29:00', 'Sched'),
(12, 12, 3000.00, 600.00, '2025-12-01 18:41:22', '2025-12-03 19:40:00', 'Sched'),
(13, 13, 1000.00, 200.00, '2025-12-01 18:51:53', '2025-12-02 21:51:00', 'Sched'),
(16, 16, 20.00, 4.00, '2025-12-03 18:43:59', '2025-12-03 19:55:00', 'Sched'),
(17, 17, 50.00, 10.00, '2025-12-03 18:59:40', '2026-01-03 18:59:00', 'Sched');

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
(9, 16, 13, 21.00, '2025-12-03 18:46:46'),
(10, 16, 9, 22.00, '2025-12-03 18:48:46'),
(11, 16, 13, 25.00, '2025-12-03 18:50:40'),
(12, 16, 9, 26.00, '2025-12-03 18:52:09'),
(13, 16, 13, 28.00, '2025-12-03 18:53:10'),
(14, 8, 13, 540.00, '2025-12-03 18:57:47');

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
(8, 'Instruments');

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
(4, 6, 'uploads/item_6_1764441904.jpg', 1),
(7, 8, 'uploads/item_8_1764612932_0.jpg', 1),
(8, 8, 'uploads/item_8_1764612932_1.jpg', 2),
(9, 8, 'uploads/item_8_1764612932_2.jpg', 3),
(10, 9, 'uploads/item_9_1764613457_0.jpg', 1),
(11, 10, 'uploads/item_10_1764613735_0.jpg', 1),
(12, 11, 'uploads/item_11_1764613835_0.jpg', 1),
(13, 12, 'uploads/item_12_1764614482_0.jpg', 1),
(14, 12, 'uploads/item_12_1764614482_1.jpg', 2),
(15, 12, 'uploads/item_12_1764614482_2.jpg', 3),
(16, 12, 'uploads/item_12_1764614482_3.jpg', 4),
(17, 12, 'uploads/item_12_1764614482_4.jpg', 5),
(18, 13, 'uploads/item_13_1764615113_0.jpg', 1),
(19, 13, 'uploads/item_13_1764615113_1.jpg', 2),
(20, 13, 'uploads/item_13_1764615113_2.jpg', 3),
(21, 13, 'uploads/item_13_1764615113_3.jpg', 4),
(30, 16, 'uploads/item_16_1764787439_0.jpg', 1),
(31, 16, 'uploads/item_16_1764787439_1.jpg', 2),
(32, 16, 'uploads/item_16_1764787439_2.jpg', 3),
(33, 16, 'uploads/item_16_1764787439_3.jpg', 4),
(34, 17, 'uploads/item_17_1764788380_0.jpg', 1),
(35, 17, 'uploads/item_17_1764788380_1.jpg', 2),
(36, 17, 'uploads/item_17_1764788380_2.jpg', 3);

-- --------------------------------------------------------

--
-- 表的结构 `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `condition` varchar(50) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `item`
--

INSERT INTO `item` (`item_id`, `seller_id`, `category_id`, `title`, `description`, `condition`, `date_created`) VALUES
(4, 3, 3, 'Chair', 'Wodden Chair', 'Used - Like New', '2025-11-29 01:29:52'),
(5, 3, 3, 'Chair', 'office chair', 'Used - Good', '2025-11-29 01:37:35'),
(6, 4, 3, 'Chair', 'White chair', 'Used - Acceptable', '2025-11-29 18:45:04'),
(8, 3, 2, 'Canada Goose down jacket', 'CANADA GOOSE Canada Goose Expedition Men\'s Parka Classic Upgrade 2051M', 'New', '2025-12-01 18:15:32'),
(9, 3, 4, 'Book \'ALL THE LIGHT WE CAN NOT SEE\'', 'Written by Anthony Doerr.\r\nIncludes an except from cloud cuckoo land.', 'Used - Good', '2025-12-01 18:24:17'),
(10, 3, 6, 'Basketball', 'Wilson Official NBA Collaborative Trendy Tie-Dye Gradient Basketball for College Students, Indoor and Outdoor Use, Size 7', 'Used - Good', '2025-12-01 18:28:55'),
(11, 3, 7, 'Pink Gemstone Necklace ', 'A striking pear-cut pink gemstone centerpiece, surrounded by a brilliant diamond halo and set on an elegant double-chain design. The necklace features intricate diamond-accented curves, adding sparkle and sophistication.', 'Used - Good', '2025-12-01 18:30:35'),
(12, 3, 8, 'Scott Walker Special Custom left-handed electric guitar, made in USA; Body: Mahogany with textured', 'Body: Mahogany with textured oxidised copper finish; Neck: three piece maple and walnut; Fretboard: birds eye maple, light surface blemish to second fret; Frets: good; Electrics: working; Hardware: good; Case: original fitted hard case; Weight: 4.12kg', 'Used - Like New', '2025-12-01 18:41:22'),
(13, 3, 1, 'Iphone 17 Pro', 'iPhone 17 pro 512GB', 'New', '2025-12-01 18:51:53'),
(16, 15, 5, 'popmart', 'this is a toy', 'Used - Like New', '2025-12-03 18:43:59'),
(17, 15, 2, 'Nike Shoes', 'Nike Team Hustle D 11 Kids\' Winter Sports Shoes for Boys and Girls', 'New', '2025-12-03 18:59:40');

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
  `created_at` datetime DEFAULT current_timestamp(),
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password_hash`, `account_type`, `created_at`, `balance`) VALUES
(2, 'TestBuyer', 'TestBuyer@gmail.com', '$2y$10$ia4nS7kT8Tpw2YGUgqc0su74B9D3edUAooHU0eBqLW671Hzvjj/wm', 'buyer', '2025-11-29 00:51:34', 0.00),
(3, 'TestSeller', 'TestSeller@gmail.com', '$2y$10$15tgS.9pAT7FtuZ7.M60S.CPWyO2MFsILIILx1.fvVfs9Z.Tbmf6i', 'seller', '2025-11-29 01:24:00', 0.00),
(4, 'U1', 'wu029385@gmail.com', '$2y$10$g2DDjQbYXph7/Isor4W6XuFgbXSxkVW1pSO/KZJXmYM5e0g6tq55a', 'buyer', '2025-11-29 15:18:44', 0.00),
(9, 'yah', 'yanhan@22.com', '$2y$10$MTZ4Iky5ZxjfPMC9OkeK2emcUNNJCqIBMHXUh/1j47BLQo35Ac21m', 'buyer', '2025-12-03 17:52:01', 50.00),
(13, 'princess', 'jiaqisun617@gmail.com', '$2y$10$/0azSy00DCH9DKS0Yn50qenNzLtbXyljoJ2nHABzAlJAqid8NglFS', 'buyer', '2025-12-03 18:23:50', 96.00),
(14, 'seller2', 'princess@gmail.com', '$2y$10$fu6eWVv/rHjNY2Cp4lRwbu2ukhU2H1/Jtw77vAwOJ1Nq3KrYzCsrW', 'buyer', '2025-12-03 18:27:40', 0.00),
(15, 'seller3', 'seller@gmail.com', '$2y$10$AoYRqXfk/jztABAOJMD3meHE7gcVeBbRjOU4F3lk3PsDvEwMBd45C', 'seller', '2025-12-03 18:29:12', 0.00);

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
(2, 1),
(3, 2),
(4, 1),
(4, 2),
(9, 1),
(13, 1),
(14, 1),
(15, 2);

-- --------------------------------------------------------

--
-- 表的结构 `watchlist`
--

CREATE TABLE `watchlist` (
  `user_id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `watchlist`
--

INSERT INTO `watchlist` (`user_id`, `auction_id`, `created_at`) VALUES
(13, 4, '2025-12-03 18:57:09');

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
-- 表的索引 `item`
--
ALTER TABLE `item`
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
  MODIFY `auction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `bid`
--
ALTER TABLE `bid`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用表AUTO_INCREMENT `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `image`
--
ALTER TABLE `image`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- 使用表AUTO_INCREMENT `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  ADD CONSTRAINT `fk_image_item` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE;

--
-- 限制表 `item`
--
ALTER TABLE `item`
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
