-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2026 at 12:38 PM
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
-- Database: `tradecircle`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Electronics', 'Phones, laptops, accessories, and practical tech.'),
(2, 'Fashion', 'Clothing, shoes, bags, and accessories.'),
(3, 'Home', 'Furniture, appliances, kitchen, and home essentials.'),
(4, 'Books', 'Textbooks, novels, study material, and stationery.'),
(5, 'Sports', 'Fitness gear, bicycles, boots, and sports equipment.'),
(6, 'Beauty', 'Personal care, grooming, and cosmetics.'),
(7, 'Kids', 'Toys, school items, clothes, and baby goods.'),
(8, 'Services', 'Local consumer services offered by individuals.');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(140) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `item_condition` enum('new','like_new','good','fair','service') NOT NULL DEFAULT 'good',
  `genre` varchar(80) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('available','pending','sold','cancelled','hidden') NOT NULL DEFAULT 'available',
  `image_path` varchar(255) DEFAULT NULL,
  `location` varchar(160) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `seller_id`, `category_id`, `title`, `description`, `price`, `item_condition`, `genre`, `quantity`, `status`, `image_path`, `location`) VALUES
(1, 6, 1, 'Portable Power Bank (20000mAh)', 'Essential for load-shedding with dual USB ports for simultaneous charging.', 349.00, 'new', 'Accessories', 15, 'available', 'uploads/listings/listing_6_62db44736634462c.png', 'Highveld'),
(2, 6, 2, 'Vintage Denim Jacket', 'Pre-loved, thrifted jacket in excellent condition, perfect for winter.', 150.00, 'fair', 'Streetwear', 1, 'available', 'uploads/listings/listing_6_4616b304d634d426.webp', 'Highveld'),
(3, 6, 3, 'Solar LED Lantern', 'Rechargeable solar light ideal for keeping the lights on during power outages.', 120.00, 'new', 'Appliances', 25, 'available', 'uploads/listings/listing_6_a335a67b7301f345.jpg', 'Highveld'),
(4, 6, 3, 'Homemade Biltong Spice', 'Secret family recipe spice blend for the perfect homemade braai biltong.', 45.00, 'new', 'Kitchen', 50, 'available', 'uploads/listings/listing_6_baa56fbdc8c4f356.webp', 'Highveld'),
(5, 6, 2, 'Shweshwe Tote Bag', 'Locally handcrafted, vibrant traditional print tote bag.', 180.00, 'new', 'Accessories', 5, 'available', 'uploads/listings/listing_6_e8e7c31447a22431.png', 'Highveld'),
(6, 7, 4, 'Used Science Textbook', 'First-year biological science textbook, slight highlighting but very readable.', 250.00, 'fair', 'Textbooks', 12, 'available', 'uploads/listings/listing_7_de8897d3b73fc1fa.jpg', 'Sandton'),
(7, 7, 1, 'Refurbished iPhone 17', 'Fully tested, affordable iPhone with minor scratches on the back casing.', 17500.00, 'fair', 'Phones', 1, 'available', 'uploads/listings/listing_7_cedeb01f851fbb3e.jpg', 'Sandton'),
(8, 7, 1, 'Adjustable Laptop Stand', 'Portable ergonomic stand, perfect for remote working or studying setups.', 199.00, 'new', 'Accessories', 7, 'available', 'uploads/listings/listing_7_ec6ebfb4795a0b83.jpg', 'Sandton');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `buyer_name_snapshot` varchar(120) DEFAULT NULL,
  `buyer_email_snapshot` varchar(160) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('payment_pending','paid','cancelled','payment_failed') NOT NULL DEFAULT 'payment_pending',
  `delivery_method` enum('pickup','local_delivery') NOT NULL DEFAULT 'pickup',
  `delivery_address` varchar(255) DEFAULT NULL,
  `buyer_notes` text DEFAULT NULL,
  `payment_reference` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `buyer_name_snapshot`, `buyer_email_snapshot`, `total_amount`, `status`, `delivery_method`, `delivery_address`, `buyer_notes`, `payment_reference`) VALUES
(1, 8, 'Hlulani Mbendani', 'bhluli@gmail.com', 199.00, 'paid', 'pickup', 'Hatfield', '', 'LOCAL-SIM-1'),
(2, 1, 'Milani Kubuli', 'milanikubuli@superadmin.com', 199.00, 'cancelled', 'local_delivery', 'Centurion', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `listing_title_snapshot` varchar(140) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `seller_name_snapshot` varchar(120) DEFAULT NULL,
  `seller_email_snapshot` varchar(160) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `item_status` enum('payment_pending','paid','seller_confirmed','completed','cancelled') NOT NULL DEFAULT 'payment_pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `listing_id`, `listing_title_snapshot`, `seller_id`, `seller_name_snapshot`, `seller_email_snapshot`, `quantity`, `unit_price`, `item_status`) VALUES
(1, 1, 8, 'Adjustable Laptop Stand', 7, 'Milani Kubuli', 's2milani@gmail.com', 1, 199.00, 'paid'),
(2, 2, 8, 'Adjustable Laptop Stand', 7, 'Milani Kubuli', 's2milani@gmail.com', 1, 199.00, 'payment_pending');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `provider` varchar(80) NOT NULL,
  `payment_reference` varchar(120) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','complete','failed') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `provider`, `payment_reference`, `amount`, `status`) VALUES
(1, 1, 'PayFast Sandbox', 'LOCAL-SIM-1', 199.00, 'complete');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `reason` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('open','reviewing','resolved','dismissed') NOT NULL DEFAULT 'open',
  `admin_id` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `listing_id`, `reporter_id`, `reason`, `details`, `status`, `admin_id`, `admin_notes`) VALUES
(2, 7, 8, 'Incorrect price or misleading description', 'For a used item, the price is a bit high', 'open', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `seller_requests`
--

CREATE TABLE `seller_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(140) NOT NULL,
  `motivation` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seller_requests`
--

INSERT INTO `seller_requests` (`id`, `user_id`, `business_name`, `motivation`, `status`) VALUES
(2, 8, 'Hlulani', 'Id like to sell my clothes that are no longer in use', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password` varchar(120) NOT NULL,
  `role` enum('buyer','seller','admin','superadmin') NOT NULL DEFAULT 'buyer',
  `phone` varchar(40) DEFAULT NULL,
  `id_number` varchar(13) DEFAULT NULL,
  `location` varchar(160) DEFAULT NULL,
  `address_line1` varchar(160) DEFAULT NULL,
  `address_line2` varchar(160) DEFAULT NULL,
  `suburb` varchar(120) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `province` varchar(120) DEFAULT NULL,
  `zip_code` varchar(12) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `verification_status` enum('unverified','pending','approved','rejected') NOT NULL DEFAULT 'unverified',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `phone`, `id_number`, `location`, `address_line1`, `address_line2`, `suburb`, `city`, `province`, `zip_code`, `bio`, `verification_status`, `status`) VALUES
(1, 'Milani Kubuli', 'milanikubuli@superadmin.com', 'password123', 'superadmin', '0622619125', NULL, 'Centurion', '1 Admin Street', '', 'Highveld', 'Centurion', 'Gauteng', '0157', 'Platform owner account with full RBAC permissions.', 'approved', 'active'),
(6, 'Milani Kubuli', 'smilani@gmail.com', 'password123', 'seller', '1234567891', '9001015009087', 'Highveld', '12 Market Road', '', 'Highveld', 'Centurion', 'Gauteng', '0157', NULL, 'approved', 'active'),
(7, 'Milani Kubuli', 's2milani@gmail.com', 'password123', 'seller', '1234567892', '9001015009088', 'Sandton', '45 Seller Avenue', '', 'Sandton', 'Johannesburg', 'Gauteng', '2196', '', 'approved', 'active'),
(8, 'Hlulani Mbendani', 'bhluli@gmail.com', 'password123', 'buyer', '1234567893', '9001015009089', 'Hatfield', '8 Student Lane', '', 'Hatfield', 'Pretoria', 'Gauteng', '0083', NULL, 'pending', 'active'),
(9, 'Mihle Kubuli', 'bmihle@gmail.com', 'password123', 'buyer', '1234567894', '9001015009090', 'Highveld', '22 Buyer Close', '', 'Highveld', 'Centurion', 'Gauteng', '0157', NULL, 'pending', 'active'),
(10, 'Milani Kubuli', 'milanikubuli@admin.com', 'password123', 'admin', '0622619125', NULL, 'Centurion', '2 Admin Street', '', 'Highveld', 'Centurion', 'Gauteng', '0157', NULL, 'approved', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_listing` (`user_id`,`listing_id`),
  ADD KEY `fk_cart_listing` (`listing_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_listings_seller` (`seller_id`),
  ADD KEY `fk_listings_category` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_buyer` (`buyer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`order_id`),
  ADD KEY `fk_order_items_listing` (`listing_id`),
  ADD KEY `fk_order_items_seller` (`seller_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_order` (`order_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reports_listing` (`listing_id`),
  ADD KEY `fk_reports_reporter` (`reporter_id`),
  ADD KEY `fk_reports_admin` (`admin_id`);

--
-- Indexes for table `seller_requests`
--
ALTER TABLE `seller_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_seller_requests_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_id_number` (`id_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `seller_requests`
--
ALTER TABLE `seller_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `fk_listings_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_listings_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reports_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `seller_requests`
--
ALTER TABLE `seller_requests`
  ADD CONSTRAINT `fk_seller_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `id_number` varchar(13) DEFAULT NULL AFTER `phone`,
  ADD COLUMN IF NOT EXISTS `address_line1` varchar(160) DEFAULT NULL AFTER `location`,
  ADD COLUMN IF NOT EXISTS `address_line2` varchar(160) DEFAULT NULL AFTER `address_line1`,
  ADD COLUMN IF NOT EXISTS `suburb` varchar(120) DEFAULT NULL AFTER `address_line2`,
  ADD COLUMN IF NOT EXISTS `city` varchar(120) DEFAULT NULL AFTER `suburb`,
  ADD COLUMN IF NOT EXISTS `province` varchar(120) DEFAULT NULL AFTER `city`,
  ADD COLUMN IF NOT EXISTS `zip_code` varchar(12) DEFAULT NULL AFTER `province`,
  ADD COLUMN IF NOT EXISTS `verification_status` enum('unverified','pending','approved','rejected') NOT NULL DEFAULT 'unverified' AFTER `bio`;

UPDATE `users` u
JOIN `users` keeper
  ON keeper.id_number = u.id_number
 AND keeper.id < u.id
SET u.id_number = NULL,
    u.verification_status = 'unverified'
WHERE u.id_number IS NOT NULL
  AND u.id_number <> '';

UPDATE `users`
SET `verification_status` = 'approved'
WHERE `role` IN ('seller', 'admin', 'superadmin')
  AND `verification_status` = 'unverified';
  
  ALTER TABLE `orders`
  DROP FOREIGN KEY `fk_orders_buyer`;

ALTER TABLE `order_items`
  DROP FOREIGN KEY `fk_order_items_listing`,
  DROP FOREIGN KEY `fk_order_items_seller`;

ALTER TABLE `orders`
  MODIFY `buyer_id` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `buyer_name_snapshot` varchar(120) DEFAULT NULL AFTER `buyer_id`,
  ADD COLUMN IF NOT EXISTS `buyer_email_snapshot` varchar(160) DEFAULT NULL AFTER `buyer_name_snapshot`;

ALTER TABLE `order_items`
  MODIFY `listing_id` int(11) DEFAULT NULL,
  MODIFY `seller_id` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `listing_title_snapshot` varchar(140) DEFAULT NULL AFTER `listing_id`,
  ADD COLUMN IF NOT EXISTS `seller_name_snapshot` varchar(120) DEFAULT NULL AFTER `seller_id`,
  ADD COLUMN IF NOT EXISTS `seller_email_snapshot` varchar(160) DEFAULT NULL AFTER `seller_name_snapshot`;

UPDATE `orders` o
JOIN `users` u ON u.id = o.buyer_id
SET o.buyer_name_snapshot = COALESCE(o.buyer_name_snapshot, u.full_name),
    o.buyer_email_snapshot = COALESCE(o.buyer_email_snapshot, u.email);

UPDATE `order_items` oi
JOIN `listings` l ON l.id = oi.listing_id
JOIN `users` u ON u.id = oi.seller_id
SET oi.listing_title_snapshot = COALESCE(oi.listing_title_snapshot, l.title),
    oi.seller_name_snapshot = COALESCE(oi.seller_name_snapshot, u.full_name),
    oi.seller_email_snapshot = COALESCE(oi.seller_email_snapshot, u.email);

ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_items_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
  
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
