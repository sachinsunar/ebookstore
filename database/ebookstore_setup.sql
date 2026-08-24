-- ============================================================
--  Unicorn Book Shop - COMPLETE DATABASE SETUP (single file)
--  Schema + Nepali catalog data. No customer accounts, orders,
--  carts or messages are included (fresh store).
--
--  Included in schema: eSewa payment columns on `invoice`
--  (transaction_uuid, payment_method, payment_status,
--   transaction_code, paid_at) and `admin.password`.
--
--  Bootstrap admin created:
--      email:    nena123maharjan@gmail.com
--      password: admin123
--
--  Import (XAMPP):
--      /opt/lampp/bin/mysql -u root < ebookstore_setup.sql
--  or via phpMyAdmin: create nothing manually - this file
--  creates the `ebookstore` database itself.
--
--  Safe to re-import (tables use DROP TABLE IF EXISTS).
-- ============================================================
CREATE DATABASE IF NOT EXISTS `ebookstore` DEFAULT CHARACTER
SET
  utf8mb3;

USE `ebookstore`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

/*!40101 SET NAMES utf8mb4 */;

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;

/*!40103 SET TIME_ZONE='+00:00' */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `admin`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `admin` (
    `fname` varchar(45) DEFAULT NULL,
    `lname` varchar(45) DEFAULT NULL,
    `email` varchar(100) NOT NULL,
    `vcode` varchar(20) DEFAULT NULL,
    `password` varchar(100) NOT NULL DEFAULT '',
    PRIMARY KEY (`email`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admin` WRITE;

/*!40000 ALTER TABLE `admin` DISABLE KEYS */;

INSERT INTO
  `admin`
VALUES
  (
    'Nena',
    'Maharjan',
    'nena123maharjan@gmail.com',
    '674c0a2aa8c5e',
    'admin123'
  );

/*!40000 ALTER TABLE `admin` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `author`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `author` (
    `author_id` int (11) NOT NULL AUTO_INCREMENT,
    `author_name` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`author_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 12 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `author` WRITE;

/*!40000 ALTER TABLE `author` DISABLE KEYS */;

INSERT INTO
  `author`
VALUES
  (1, 'Laxmi Prasad Devkota'),
  (2, 'Krishna Prasad Bhattarai'),
  (3, 'Bhawani Bhikshu'),
  (8, 'Lil Bahadur Chhetri'),
  (9, 'Parijat'),
  (10, 'B. P. Koirala'),
  (11, 'Diamond Shumsher Rana');

/*!40000 ALTER TABLE `author` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `author_has_publisher`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `author_has_publisher` (
    `author_author_id` int (11) NOT NULL,
    `publisher_publisher_id` int (11) NOT NULL,
    `id` int (11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (`id`),
    KEY `fk_model_has_brand_brand1_idx` (`publisher_publisher_id`),
    KEY `fk_model_has_brand_model1_idx` (`author_author_id`),
    CONSTRAINT `fk_model_has_brand_brand1` FOREIGN KEY (`publisher_publisher_id`) REFERENCES `publisher` (`publisher_id`),
    CONSTRAINT `fk_model_has_brand_model1` FOREIGN KEY (`author_author_id`) REFERENCES `author` (`author_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 15 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `author_has_publisher` WRITE;

/*!40000 ALTER TABLE `author_has_publisher` DISABLE KEYS */;

INSERT INTO
  `author_has_publisher`
VALUES
  (1, 1, 1),
  (2, 2, 2),
  (2, 3, 8),
  (3, 2, 9),
  (8, 1, 10),
  (9, 8, 11),
  (10, 9, 12),
  (11, 2, 13),
  (2, 1, 14);

/*!40000 ALTER TABLE `author_has_publisher` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `cart`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `cart` (
    `cart_id` int (11) NOT NULL AUTO_INCREMENT,
    `qty` int (11) DEFAULT NULL,
    `user_email` varchar(100) NOT NULL,
    `product_id` int (11) NOT NULL,
    PRIMARY KEY (`cart_id`),
    KEY `fk_cart_user1_idx` (`user_email`),
    KEY `fk_cart_product1_idx` (`product_id`),
    CONSTRAINT `fk_cart_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
    CONSTRAINT `fk_cart_user1` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 17 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cart` WRITE;

/*!40000 ALTER TABLE `cart` DISABLE KEYS */;

/*!40000 ALTER TABLE `cart` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `category`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `category` (
    `cat_id` int (11) NOT NULL AUTO_INCREMENT,
    `cat_name` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`cat_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 9 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `category` WRITE;

/*!40000 ALTER TABLE `category` DISABLE KEYS */;

INSERT INTO
  `category`
VALUES
  (1, 'Novels'),
  (2, 'Short Stories'),
  (3, 'Educational'),
  (4, 'Language'),
  (5, 'Religion'),
  (7, 'Translations');

/*!40000 ALTER TABLE `category` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `category_has_publisher`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `category_has_publisher` (
    `category_cat_id` int (11) NOT NULL,
    `publisher_publisher_id` int (11) NOT NULL,
    KEY `fk_category_has_brand_brand1_idx` (`publisher_publisher_id`),
    KEY `fk_category_has_brand_category1_idx` (`category_cat_id`),
    CONSTRAINT `fk_category_has_brand_brand1` FOREIGN KEY (`publisher_publisher_id`) REFERENCES `publisher` (`publisher_id`),
    CONSTRAINT `fk_category_has_brand_category1` FOREIGN KEY (`category_cat_id`) REFERENCES `category` (`cat_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `category_has_publisher` WRITE;

/*!40000 ALTER TABLE `category_has_publisher` DISABLE KEYS */;

/*!40000 ALTER TABLE `category_has_publisher` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `chat`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `chat` (
    `chat_id` int (11) NOT NULL AUTO_INCREMENT,
    `content` text DEFAULT NULL,
    `date_time` datetime DEFAULT NULL,
    `status` int (11) DEFAULT NULL,
    `from` varchar(100) NOT NULL,
    `to` varchar(100) NOT NULL,
    PRIMARY KEY (`chat_id`),
    KEY `fk_chat_user1_idx` (`from`),
    KEY `fk_chat_admin1_idx` (`to`),
    CONSTRAINT `fk_chat_admin1` FOREIGN KEY (`to`) REFERENCES `admin` (`email`),
    CONSTRAINT `fk_chat_user1` FOREIGN KEY (`from`) REFERENCES `user` (`email`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 19 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chat` WRITE;

/*!40000 ALTER TABLE `chat` DISABLE KEYS */;

/*!40000 ALTER TABLE `chat` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `city`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `city` (
    `city_id` int (11) NOT NULL AUTO_INCREMENT,
    `city_name` varchar(45) DEFAULT NULL,
    `district_district_id` int (11) NOT NULL,
    PRIMARY KEY (`city_id`),
    KEY `fk_city_district1_idx` (`district_district_id`),
    CONSTRAINT `fk_city_district1` FOREIGN KEY (`district_district_id`) REFERENCES `district` (`district_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 9 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `city` WRITE;

/*!40000 ALTER TABLE `city` DISABLE KEYS */;

INSERT INTO
  `city`
VALUES
  (1, 'Pokhara', 1),
  (2, 'Lekhnath', 1),
  (3, 'Kathmandu', 4),
  (4, 'Baneshwor', 4),
  (5, 'Boudha', 4),
  (6, 'Damauli', 2),
  (7, 'Lalitpur', 5),
  (8, 'Birendranagar', 3);

/*!40000 ALTER TABLE `city` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `district`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `district` (
    `district_id` int (11) NOT NULL AUTO_INCREMENT,
    `district_name` varchar(45) DEFAULT NULL,
    `province_province_id` int (11) NOT NULL,
    PRIMARY KEY (`district_id`),
    KEY `fk_district_province1_idx` (`province_province_id`),
    CONSTRAINT `fk_district_province1` FOREIGN KEY (`province_province_id`) REFERENCES `province` (`province_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 8 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `district` WRITE;

/*!40000 ALTER TABLE `district` DISABLE KEYS */;

INSERT INTO
  `district`
VALUES
  (1, 'Kaski', 2),
  (2, 'Tanahun', 2),
  (3, 'Surkhet', 4),
  (4, 'Kathmandu', 1),
  (5, 'Lalitpur', 1);

/*!40000 ALTER TABLE `district` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `feedback`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `feedback` (
    `feed_id` int (11) NOT NULL AUTO_INCREMENT,
    `type` int (11) DEFAULT NULL,
    `date` datetime DEFAULT NULL,
    `feed` varchar(250) DEFAULT NULL,
    `product_id` int (11) NOT NULL,
    `user_email` varchar(100) NOT NULL,
    PRIMARY KEY (`feed_id`),
    KEY `fk_feedback_product1_idx` (`product_id`),
    KEY `fk_feedback_user1_idx` (`user_email`),
    CONSTRAINT `fk_feedback_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
    CONSTRAINT `fk_feedback_user1` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `feedback` WRITE;

/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;

/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `gender`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `gender` (
    `gender_id` int (11) NOT NULL AUTO_INCREMENT,
    `gender_name` varchar(10) DEFAULT NULL,
    PRIMARY KEY (`gender_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `gender` WRITE;

/*!40000 ALTER TABLE `gender` DISABLE KEYS */;

INSERT INTO
  `gender`
VALUES
  (1, 'Male'),
  (2, 'Female');

/*!40000 ALTER TABLE `gender` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `invoice`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `invoice` (
    `invoice_id` int (11) NOT NULL AUTO_INCREMENT,
    `order_id` varchar(20) DEFAULT NULL,
    `transaction_uuid` varchar(64) DEFAULT NULL,
    `date` datetime DEFAULT NULL,
    `total` double DEFAULT NULL,
    `payment_method` varchar(30) NOT NULL DEFAULT 'esewa',
    `payment_status` varchar(15) NOT NULL DEFAULT 'PENDING',
    `transaction_code` varchar(50) DEFAULT NULL,
    `paid_at` datetime DEFAULT NULL,
    `qty` int (11) DEFAULT NULL,
    `product_id` int (11) NOT NULL,
    `user_email` varchar(100) NOT NULL,
    `order_status_status_id` int (11) NOT NULL,
    PRIMARY KEY (`invoice_id`),
    UNIQUE KEY `uq_invoice_transaction_uuid` (`transaction_uuid`),
    KEY `fk_invoice_product1_idx` (`product_id`),
    KEY `fk_invoice_user1_idx` (`user_email`),
    KEY `fk_invoice_order_status1_idx` (`order_status_status_id`),
    KEY `idx_invoice_payment_status` (`user_email`, `product_id`, `payment_status`),
    CONSTRAINT `fk_invoice_order_status1` FOREIGN KEY (`order_status_status_id`) REFERENCES `order_status` (`status_id`),
    CONSTRAINT `fk_invoice_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
    CONSTRAINT `fk_invoice_user1` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 17 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invoice` WRITE;

/*!40000 ALTER TABLE `invoice` DISABLE KEYS */;

/*!40000 ALTER TABLE `invoice` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `order_status`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `order_status` (
    `status_id` int (11) NOT NULL,
    `status` varchar(45) NOT NULL,
    PRIMARY KEY (`status_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `order_status` WRITE;

/*!40000 ALTER TABLE `order_status` DISABLE KEYS */;

INSERT INTO
  `order_status`
VALUES
  (1, 'Waiting for accept'),
  (2, 'Order Placed'),
  (3, 'Delivered');

/*!40000 ALTER TABLE `order_status` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `product`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `product` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `price` double DEFAULT NULL,
    `qty` int (11) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `title` varchar(100) DEFAULT NULL,
    `datetime_added` datetime DEFAULT NULL,
    `delivery_fee_colombo` double DEFAULT NULL,
    `delivery_fee_other` double DEFAULT NULL,
    `category_cat_id` int (11) NOT NULL,
    `author_has_publisher_id` int (11) NOT NULL,
    `status_status_id` int (11) NOT NULL,
    `admin_email` varchar(100) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `fk_product_category1_idx` (`category_cat_id`),
    KEY `fk_product_model_has_brand1_idx` (`author_has_publisher_id`),
    KEY `fk_product_status1_idx` (`status_status_id`),
    KEY `fk_product_admin1_idx` (`admin_email`),
    CONSTRAINT `fk_product_admin1` FOREIGN KEY (`admin_email`) REFERENCES `admin` (`email`),
    CONSTRAINT `fk_product_category1` FOREIGN KEY (`category_cat_id`) REFERENCES `category` (`cat_id`),
    CONSTRAINT `fk_product_model_has_brand1` FOREIGN KEY (`author_has_publisher_id`) REFERENCES `author_has_publisher` (`id`),
    CONSTRAINT `fk_product_status1` FOREIGN KEY (`status_status_id`) REFERENCES `status` (`status_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 19 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product` WRITE;

/*!40000 ALTER TABLE `product` DISABLE KEYS */;

INSERT INTO
  `product`
VALUES
  (
    1,
    560,
    8,
    'Muna Madan is an epic love poem written by Laxmi Prasad Devkota, considered one of the most celebrated works in Nepali literature.',
    'Muna Madan',
    '2024-03-28 17:59:44',
    200,
    350,
    1,
    1,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    8,
    450,
    30,
    'A collection of short stories translated into Nepali, exploring everyday life and rural struggles.',
    'Feriyeko Aankha',
    '2024-04-04 22:57:27',
    250,
    300,
    1,
    8,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    9,
    500,
    0,
    'Physics Past Paper Review 2020 for SEE and +2 level students.',
    'Physics Paper Review 2020',
    '2024-04-05 11:49:55',
    250,
    300,
    3,
    9,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    10,
    550,
    32,
    'A gripping mystery novel in the style of classic detective fiction, translated for Nepali readers.',
    'Rahasyamaya Chinha',
    '2024-04-10 14:56:55',
    250,
    300,
    1,
    1,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    11,
    600,
    40,
    'Karnali Blues follows a father-son relationship set against the backdrop of rural Karnali, Nepal.',
    'Karnali Blues',
    '2024-04-10 14:58:16',
    250,
    300,
    1,
    1,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    12,
    650,
    23,
    'Mechanical Science textbook covering fundamentals for engineering diploma students.',
    'Mechanical Science',
    '2024-06-10 13:50:02',
    250,
    350,
    3,
    9,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    13,
    750,
    9,
    'Shirishko Phool is a novel written by Nepali writer Parijat and first published in 1965. It is considered a landmark work of modern Nepali fiction, exploring themes of existentialism and post-war disillusionment.',
    'Shirishko Phool',
    '2024-06-26 21:44:12',
    350,
    400,
    1,
    10,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    14,
    660,
    10,
    'Basain is a novel written by Nepali writer Lil Bahadur Chhetri and first published in 1957, depicting the hardships of a poor hill farming family.',
    'Basain',
    '2024-06-26 21:46:14',
    350,
    400,
    1,
    10,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    15,
    700,
    12,
    'Diamond Shumsher Rana, popularly known for his historical and social novels, wrote this acclaimed work portraying Rana-era Nepal.',
    'Seto Bagh',
    '2024-06-26 21:50:29',
    350,
    400,
    1,
    11,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    16,
    300,
    5,
    'Sumnima is a short novel by B. P. Koirala exploring the philosophical contrast between civilization and nature.',
    'Sumnima',
    '2024-06-26 21:57:44',
    300,
    350,
    2,
    12,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    17,
    1755,
    12,
    'ISBN 	9789993343217',
    'Palpasa Café',
    '2024-06-26 22:02:05',
    350,
    400,
    7,
    13,
    1,
    'nena123maharjan@gmail.com'
  ),
  (
    18,
    450,
    6,
    'A collection of folk tales and proverbs from the hills of Nepal.',
    'Lok Katha Sangraha',
    '2024-06-28 00:49:23',
    350,
    400,
    1,
    14,
    1,
    'nena123maharjan@gmail.com'
  );

/*!40000 ALTER TABLE `product` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `product_img`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `product_img` (
    `img_path` varchar(100) NOT NULL,
    `product_id` int (11) NOT NULL,
    PRIMARY KEY (`img_path`),
    KEY `fk_product_img_product1_idx` (`product_id`),
    CONSTRAINT `fk_product_img_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_img` WRITE;

/*!40000 ALTER TABLE `product_img` DISABLE KEYS */;

INSERT INTO
  `product_img`
VALUES
  (
    'resource//product_img//Muna Madan_0_6a8c66be0f88d.png',
    1
  ),
  (
    'resource//product_img//Feriyeko Aankha_0_6666b47ed451c.jpeg',
    8
  ),
  (
    'resource//product_img//Feriyeko Aankha_1_6666b47edaf3c.jpeg',
    8
  ),
  (
    'resource//product_img//Feriyeko Aankha_2_6666b47ee00aa.jpeg',
    8
  ),
  (
    'resource//product_img//Physics Paper Review 2020_0_660f980b08d7a.jpeg',
    9
  ),
  (
    'resource//product_img//Physics Paper Review 2020_1_660f980b0c9a0.jpeg',
    9
  ),
  (
    'resource//product_img//Physics Paper Review 2020_2_660f980b0da55.jpeg',
    9
  ),
  (
    'resource//product_img//Rahasyamaya Chinha_0_66165b5f94464.jpeg',
    10
  ),
  (
    'resource//product_img//Rahasyamaya Chinha_1_66165b5f97d1d.jpeg',
    10
  ),
  (
    'resource//product_img//Rahasyamaya Chinha_2_66165b5f9bad2.jpeg',
    10
  ),
  (
    'resource//product_img//Karnali Blues_0_66165bb03ae09.jpeg',
    11
  ),
  (
    'resource//product_img//Karnali Blues_1_66165bb04025a.jpeg',
    11
  ),
  (
    'resource//product_img//Karnali Blues_2_66165bb044036.jpeg',
    11
  ),
  (
    'resource//product_img//Mechanical Science_0_6666b732a75ea.jpeg',
    12
  ),
  (
    'resource//product_img//Mechanical Science_1_6666b732abc38.jpeg',
    12
  ),
  (
    'resource//product_img//Mechanical Science_2_6666b732af910.jpeg',
    12
  ),
  (
    'resource//product_img//Shirishko Phool_0_667c3e5472806.jpeg',
    13
  ),
  (
    'resource//product_img//Shirishko Phool_1_667c3e5473b6f.jpeg',
    13
  ),
  (
    'resource//product_img//Shirishko Phool_2_667c3e547475b.jpeg',
    13
  ),
  (
    'resource//product_img//Basain_0_667c3ecea03d4.jpeg',
    14
  ),
  (
    'resource//product_img//Basain_1_667c3ecea1547.jpeg',
    14
  ),
  (
    'resource//product_img//Basain_2_667c3ecea7c80.jpeg',
    14
  ),
  (
    'resource//product_img//Seto Bagh_0_667c3fcd0ab8c.jpeg',
    15
  ),
  (
    'resource//product_img//Seto Bagh_1_667c3fcd0e8a7.jpeg',
    15
  ),
  (
    'resource//product_img//Seto Bagh_2_667c3fcd12728.jpeg',
    15
  ),
  (
    'resource//product_img//Sumnima_0_667c418017519.jpeg',
    16
  ),
  (
    'resource//product_img//Sumnima_1_667c41801b451.jpeg',
    16
  ),
  (
    'resource//product_img//Sumnima_2_667c41801f081.jpeg',
    16
  ),
  (
    'resource//product_img//Palpasa Cafe_0_667c4285e38e8.jpeg',
    17
  ),
  (
    'resource//product_img//Palpasa Cafe_1_667c4285e4b65.jpeg',
    17
  ),
  (
    'resource//product_img//Palpasa Cafe_2_667c4285eb06f.jpeg',
    17
  ),
  (
    'resource//product_img//Lok Katha Sangraha_0_667dbb3baf4fd.jpeg',
    18
  ),
  (
    'resource//product_img//Lok Katha Sangraha_1_667dbb3bb07a2.jpeg',
    18
  ),
  (
    'resource//product_img//Lok Katha Sangraha_2_667dbb3bb18da.jpeg',
    18
  );

/*!40000 ALTER TABLE `product_img` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `profile_img`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `profile_img` (
    `path` varchar(100) NOT NULL,
    `user_email` varchar(100) NOT NULL,
    PRIMARY KEY (`path`),
    KEY `fk_profile_img_user1_idx` (`user_email`),
    CONSTRAINT `fk_profile_img_user1` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `profile_img` WRITE;

/*!40000 ALTER TABLE `profile_img` DISABLE KEYS */;

/*!40000 ALTER TABLE `profile_img` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `province`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `province` (
    `province_id` int (11) NOT NULL AUTO_INCREMENT,
    `province_name` varchar(45) DEFAULT NULL,
    PRIMARY KEY (`province_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `province` WRITE;

/*!40000 ALTER TABLE `province` DISABLE KEYS */;

INSERT INTO
  `province`
VALUES
  (1, 'Bagmati'),
  (2, 'Gandaki'),
  (3, 'Karnali'),
  (4, 'Koshi'),
  (5, 'Lumbini');

/*!40000 ALTER TABLE `province` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `publisher`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `publisher` (
    `publisher_id` int (11) NOT NULL AUTO_INCREMENT,
    `publisher_name` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`publisher_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 10 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `publisher` WRITE;

/*!40000 ALTER TABLE `publisher` DISABLE KEYS */;

INSERT INTO
  `publisher`
VALUES
  (1, 'Sajha Prakashan'),
  (2, 'Ratna Pustak Bhandar'),
  (3, 'Vidyarthi Pustak Bhandar'),
  (7, 'Nepal Academy'),
  (8, 'Pairavi Prakashan'),
  (9, 'Madan Puraskar Pustakalaya');

/*!40000 ALTER TABLE `publisher` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `recent`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `recent` (
    `r_id` int (11) NOT NULL AUTO_INCREMENT,
    `product_id` int (11) NOT NULL,
    `user_email` varchar(100) NOT NULL,
    PRIMARY KEY (`r_id`),
    KEY `fk_recent_product1_idx` (`product_id`),
    KEY `fk_recent_user1_idx` (`user_email`),
    CONSTRAINT `fk_recent_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
    CONSTRAINT `fk_recent_user1` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 7 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `recent` WRITE;

/*!40000 ALTER TABLE `recent` DISABLE KEYS */;

/*!40000 ALTER TABLE `recent` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `status`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `status` (
    `status_id` int (11) NOT NULL AUTO_INCREMENT,
    `status` varchar(45) DEFAULT NULL,
    PRIMARY KEY (`status_id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `status` WRITE;

/*!40000 ALTER TABLE `status` DISABLE KEYS */;

INSERT INTO
  `status`
VALUES
  (1, 'Active'),
  (2, 'Inactive');

/*!40000 ALTER TABLE `status` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `user`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `user` (
    `fname` varchar(50) NOT NULL,
    `lname` varchar(45) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(20) NOT NULL,
    `mobile` varchar(10) NOT NULL,
    `joined_date` datetime NOT NULL,
    `verification_code` varchar(20) DEFAULT NULL,
    `gender_gender_id` int (11) NOT NULL,
    `status_status_id` int (11) NOT NULL,
    PRIMARY KEY (`email`),
    KEY `fk_user_gender_idx` (`gender_gender_id`),
    KEY `fk_user_status1_idx` (`status_status_id`),
    CONSTRAINT `fk_user_gender` FOREIGN KEY (`gender_gender_id`) REFERENCES `gender` (`gender_id`),
    CONSTRAINT `fk_user_status1` FOREIGN KEY (`status_status_id`) REFERENCES `status` (`status_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user` WRITE;

/*!40000 ALTER TABLE `user` DISABLE KEYS */;

/*!40000 ALTER TABLE `user` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `user_has_address`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `user_has_address` (
    `user_email` varchar(100) NOT NULL,
    `city_city_id` int (11) NOT NULL,
    `address_id` int (11) NOT NULL AUTO_INCREMENT,
    `line1` text DEFAULT NULL,
    `line2` text DEFAULT NULL,
    `postal_code` varchar(10) DEFAULT NULL,
    PRIMARY KEY (`address_id`),
    KEY `fk_user_has_city_city1_idx` (`city_city_id`),
    KEY `fk_user_has_city_user1_idx` (`user_email`),
    CONSTRAINT `fk_user_has_city_city1` FOREIGN KEY (`city_city_id`) REFERENCES `city` (`city_id`),
    CONSTRAINT `fk_user_has_city_user1` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 8 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_has_address` WRITE;

/*!40000 ALTER TABLE `user_has_address` DISABLE KEYS */;

/*!40000 ALTER TABLE `user_has_address` ENABLE KEYS */;

UNLOCK TABLES;

DROP TABLE IF EXISTS `watchlist`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
  `watchlist` (
    `w_id` int (11) NOT NULL AUTO_INCREMENT,
    `user_email` varchar(100) NOT NULL,
    `product_id` int (11) NOT NULL,
    PRIMARY KEY (`w_id`) USING BTREE,
    KEY `fk_watchlist_user1_idx` (`user_email`),
    KEY `fk_watchlist_product1_idx` (`product_id`),
    CONSTRAINT `fk_watchlist_product1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
    CONSTRAINT `fk_watchlist_user1` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 42 DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `watchlist` WRITE;

/*!40000 ALTER TABLE `watchlist` DISABLE KEYS */;

/*!40000 ALTER TABLE `watchlist` ENABLE KEYS */;

UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;

/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;