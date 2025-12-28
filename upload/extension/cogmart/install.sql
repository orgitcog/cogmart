-- Marketplace shops table
CREATE TABLE IF NOT EXISTS `oc_marketplace_shop` (
  `marketplace_shop_id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `country` varchar(64) DEFAULT NULL,
  `storefront_access_token` varchar(255) DEFAULT NULL,
  `onboarding_info_completed` tinyint(1) NOT NULL DEFAULT 0,
  `terms_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `onboarding_completed` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`marketplace_shop_id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Marketplace cart table for multi-shop cart support
CREATE TABLE IF NOT EXISTS `oc_marketplace_cart` (
  `marketplace_cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `session_id` varchar(32) NOT NULL,
  `marketplace_shop_id` int(11) NOT NULL,
  `cart_id` varchar(255) DEFAULT NULL,
  `checkout_url` text DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`marketplace_cart_id`),
  KEY `customer_id` (`customer_id`),
  KEY `session_id` (`session_id`),
  KEY `marketplace_shop_id` (`marketplace_shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
