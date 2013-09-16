DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `creator_id` INT(11) UNSIGNED NOT NULL,
  `number` VARCHAR(40) NOT NULL,
  `currency` VARCHAR(3) NOT NULL,
  `monetary_source` TEXT,
  `is_frozen` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_deleted` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  PRIMARY KEY  (`id`),
  KEY `fk_user_id` (`creator_id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `store_purchases`;
CREATE TABLE `store_purchases` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `number` VARCHAR(40) NOT NULL,
  `store_id` INT(10) UNSIGNED NULL,
  `purchase_id` INT(10) UNSIGNED NULL,
  `is_deleted` INT(1) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `store_refunds`;
CREATE TABLE `store_refunds` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_purchase_id` INT(10) UNSIGNED NULL,
  `created_at` DATETIME,
  `raw_response` TEXT,
  `reason` TEXT,
  `is_deleted` INT(1) UNSIGNED NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `store_refund_items`;
CREATE TABLE `store_refund_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_refund_id` INT(10) UNSIGNED NULL,
  `purchase_item_id` INT(10) UNSIGNED NULL,
  `amount` DECIMAL(10,2) NULL,
  `is_deleted` INT(1) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_id` INT(10) UNSIGNED NULL,
  `payment_id` VARCHAR(255) NOT NULL,
  `model` VARCHAR(20) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `raw_response` TEXT,
  `is_deleted` INT(1) UNSIGNED NOT NULL,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `purchase_items`;
CREATE TABLE `purchase_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_purchase_id` INT(10) UNSIGNED NULL,
  `reference_id` INT(10) UNSIGNED NULL,
  `reference_model` VARCHAR(40) NULL,
  `price` DECIMAL(10,2) NULL,
  `quantity` INT(11) NULL,
  `type` VARCHAR(255) NULL,
  `is_payable` INT(1) UNSIGNED NOT NULL,
  `is_discount` INT(1) UNSIGNED NOT NULL,
  `is_deleted` INT(1) UNSIGNED NOT NULL,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(254) NOT NULL,
  `username` VARCHAR(32) NOT NULL DEFAULT '',
  `password` VARCHAR(64) NOT NULL,
  `logins` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_login` INT(10) UNSIGNED,
  `facebook_uid` VARCHAR(100),
  `twitter_uid` VARCHAR(100),
  `last_login_ip` VARCHAR(40),
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `stores`;
CREATE TABLE `stores` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(254) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(254) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL,
  `store_id` INT(10) UNSIGNED NULL,
  `shipping_id` INT(10) UNSIGNED NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `variations`;
CREATE TABLE `variations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(254) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `product_id` INT(10) UNSIGNED NULL,
  PRIMARY KEY  (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `short_name` varchar(10) NOT NULL,
  `type` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `locations_branches`;
CREATE TABLE `locations_branches` (
  `ansestor_id` int(11) UNSIGNED NOT NULL,
  `descendant_id` int(11) UNSIGNED NOT NULL,
  `depth` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `shippings`;
CREATE TABLE `shippings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `processing_time` varchar(100) NOT NULL,
  `ships_from_id` int(11) UNSIGNED NOT NULL,
  `store_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `shipping_groups`;
CREATE TABLE `shipping_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `price` DECIMAL(10,2) NOT NULL,
  `additional_item_price` DECIMAL(10,2) NULL,
  `discount_threshold` DECIMAL(10,2) NULL,
  `delivery_time` varchar(100) NOT NULL,
  `shipping_id` int(11) UNSIGNED NOT NULL,
  `method_id` int(11) UNSIGNED NOT NULL,
  `location_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `shipping_methods`;
CREATE TABLE `shipping_methods` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `shipping_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `store_purchase_shippings`;
CREATE TABLE `store_purchase_shippings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_purchase_shipping_id` int(11) UNSIGNED NOT NULL,
  `store_purchase_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `shipping_items`;
CREATE TABLE `shipping_items` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_item_id` int(11) UNSIGNED NOT NULL,
  `shipping_group_id` int(11) UNSIGNED NOT NULL,
  `store_purchase_shipping_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `locations` (`id`, `name`, `short_name`, `type`)
VALUES
  (1,'Everywhere', NULL, 'region'),
  (2,'Europe', NULL, 'region'),
  (3,'France', 'FR', 'country'),
  (4,'Turkey', 'TR', 'country'),
  (5,'Germany', 'GR', 'country'),
  (6,'Australia', 'AU', 'country'),
  (7,'United Kingdom', 'UK', 'country'),
  (8,'Russia', 'RU', 'country');

INSERT INTO `locations_branches` (`ansestor_id`, `descendant_id`, `depth`)
VALUES
  (1,1,0),
  (2,2,0),
  (3,3,0),
  (4,4,0),
  (5,5,0),
  (6,6,0),
  (7,7,0),
  (8,8,0),
  (1,2,1),
  (1,4,1),
  (1,5,1),
  (1,6,1),
  (1,7,1),
  (1,3,2),
  (2,3,1),
  (2,5,1),
  (2,7,1);

# Dump of table payments
# ------------------------------------------------------------

INSERT INTO `payments` (`id`, `purchase_id`, `payment_id`, `model`, `status`, `raw_response`, `is_deleted`)
VALUES
  (1,1,'11111','payment_emp','paid','{"order_id":"5580812","order_total":"400.00","order_datetime":"2013-08-13 15:04:37","order_status":"Paid","cart":{"item":[{"id":"5657022","code":"1","name":"Chair","description":{},"qty":"1","digital":"0","discount":"0","predefined":"0","unit_price":"200.00"},{"id":"5657032","code":2,"name":"Rug","description":{},"qty":"1","digital":"0","discount":"0","predefined":"0","unit_price":"200.00"}]},"transaction":{"type":"sale","response":"A","response_code":"0","response_text":"approved","trans_id":"1078663342","account_id":"635172"}}',0);

# Dump of table products
# ------------------------------------------------------------

INSERT INTO `products` (`id`, `name`, `price`, `currency`, `store_id`, `shipping_id`)
VALUES
  (1,'Chair',290.40,'GBP',1, 1),
  (2,'Rug',30.00,'GBP',1, 1),
  (3,'Matrass',130.99,'EUR',1, 2),
  (4,'Bed',200.00,'EUR',1, 3);

# Dump of table purchases
# ------------------------------------------------------------

INSERT INTO `purchases` (`id`, `creator_id`, `number`, `currency`, `monetary_source`, `is_frozen`, `is_deleted`)
VALUES
  (1,1,'CNV7IC','EUR','C:33:"OpenBuildings\\Monetary\\Source_ECB":775:{a:33:{s:3:"USD";s:6:"1.3355";s:3:"JPY";s:6:"132.35";s:3:"BGN";s:6:"1.9558";s:3:"CZK";s:6:"25.665";s:3:"DKK";s:6:"7.4588";s:3:"GBP";s:7:"0.85910";s:3:"HUF";s:6:"298.98";s:3:"LTL";s:6:"3.4528";s:3:"LVL";s:6:"0.7027";s:3:"PLN";s:6:"4.2323";s:3:"RON";s:6:"4.4423";s:3:"SEK";s:6:"8.7140";s:3:"CHF";s:6:"1.2358";s:3:"NOK";s:6:"8.0940";s:3:"HRK";s:6:"7.5520";s:3:"RUB";s:7:"44.1375";s:3:"TRY";s:6:"2.6640";s:3:"AUD";s:6:"1.4879";s:3:"BRL";s:6:"3.2059";s:3:"CAD";s:6:"1.4114";s:3:"CNY";s:6:"8.1759";s:3:"HKD";s:7:"10.3579";s:3:"IDR";s:8:"14723.80";s:3:"ILS";s:6:"4.8086";s:3:"INR";s:7:"85.5050";s:3:"KRW";s:7:"1489.41";s:3:"MXN";s:7:"17.4804";s:3:"MYR";s:6:"4.4085";s:3:"NZD";s:6:"1.7185";s:3:"PHP";s:6:"59.013";s:3:"SGD";s:6:"1.7120";s:3:"THB";s:6:"42.749";s:3:"ZAR";s:7:"13.6968";}}',1,0),
  (2,1,'AAV7IC','GBP','',0,0);

# Dump of table store_purchases
# ------------------------------------------------------------

INSERT INTO `store_purchases` (`id`, `number`, `store_id`, `purchase_id`, `is_deleted`)
VALUES
  (1,'3S2GJG',1,1,0),
  (2,'AA2GJG',1,2,0);

# Dump of table purchase_items
# ------------------------------------------------------------

INSERT INTO `purchase_items` (`id`, `store_purchase_id`, `reference_id`, `reference_model`, `price`, `quantity`, `type`, `is_payable`, `is_discount`, `is_deleted`)
VALUES
  (1,1,1,'product',200.00,1,'product',1,0,0),
  (2,1,1,'variation',200.00,1,'product',1,0,0),
  (3,1,2,'variation',100.00,1,'product',1,0,0),
  (4,1,1,'shipping',10,1,'store_purchase_shipping',1,0,0),
  (5,2,1,'product',NULL,1,'product',1,0,0);

# Dump of table stores
# ------------------------------------------------------------

INSERT INTO `stores` (`id`, `name`)
VALUES
  (1,'Example Store'),
  (2,'Empty Store');

# Dump of table users
# ------------------------------------------------------------

INSERT INTO `users` (`id`, `email`, `username`, `password`, `logins`, `last_login`, `facebook_uid`, `twitter_uid`, `last_login_ip`)
VALUES
  (1,'admin@example.com','admin','f02c9f1f724ebcf9db6784175cb6bd82663380a5f8bd78c57ad20d5dfd953f15',5,1374320224,'facebook-test','','10.20.10.1');


# Dump of table variations
# ------------------------------------------------------------
INSERT INTO `variations` (`id`, `name`, `price`, `product_id`)
VALUES
  (1,'Red',295.40,1),
  (2,'Green',298.90,1);


INSERT INTO `shipping_methods` (`id`, `name`, `shipping_id`)
VALUES
  (1,'Post', 0),
  (2,'Courier', 0),
  (3,'Custom', 1);


INSERT INTO `shippings` (`id`, `name`, `currency`, `processing_time`, `ships_from_id`, `store_id`)
VALUES
  (1,'Normal', 'GBP', '2|3', 3, 1),
  (2,'Custom', 'GBP', '1|5', 4, 1),
  (3,'Normal', 'GBP', '1|5', 4, 2);


INSERT INTO `shipping_groups` (`id`, `price`, `delivery_time`, `shipping_id`, `method_id`, `location_id`)
VALUES
  (1, '10.00', '2|4', 1, 1, 1),
  (2, '20.00', '1|2', 1, 2, 2),
  (3, '15.00', '2|4', 1, 3, 4),
  (4, '15.00', '2|4', 1, 2, 3),
  (5, '12.00', '2|4', 1, 1, 3),
  (6, '5.00', '2|3', 2, 1, 3);

INSERT INTO `store_purchase_shippings` (`id`, `store_purchase_id`)
VALUES
  (1, 1);

INSERT INTO `shipping_items` (`id`, `store_purchase_shipping_id`, `purchase_item_id`, `shipping_group_id`)
VALUES
  (1, 1, 1, 1),
  (2, 1, 2, 2);


