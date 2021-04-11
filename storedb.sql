-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 11 apr 2021 kl 13:21
-- Serverversion: 10.4.18-MariaDB
-- PHP-version: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `storedb`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `carts`
--

CREATE TABLE `carts` (
  `cart_id` bigint(20) NOT NULL COMMENT 'Cart ID',
  `cart_user_id` bigint(20) NOT NULL COMMENT 'Cart User ID FK',
  `cart_product_id` bigint(20) NOT NULL COMMENT 'Cart Product ID FK',
  `product_added` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Product Added In Cart Date/Time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Carts Table';

-- --------------------------------------------------------

--
-- Tabellstruktur `products`
--

CREATE TABLE `products` (
  `product_id` bigint(20) NOT NULL COMMENT 'Product ID',
  `title` varchar(255) NOT NULL COMMENT 'Product Title',
  `description` varchar(500) NOT NULL COMMENT 'Product Description',
  `imgUrl` varchar(1000) NOT NULL COMMENT 'Product Image Url',
  `price` int(11) NOT NULL COMMENT 'Product Price',
  `quantity` int(11) NOT NULL COMMENT 'Product Quantity',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Product Added Date/Time',
  `updated_at` datetime DEFAULT NULL COMMENT 'Product Updated Date/Time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Products Table';

--
-- Dumpning av Data i tabell `products`
--

INSERT INTO `products` (`product_id`, `title`, `description`, `imgUrl`, `price`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 'Nike Air Max 1 Lv8 Light Blue', 'Den här Nike Air Max 1 uppfyller alla dina sneakerdrömmar. Retroskon the Air Max 1 i färgsättningen obsidian har en Mini Swoosh broderad på tåhättan och är gjord i läder. En klassisk sneaker med välförtjänt lyxig finish.', 'https://www.estore.com/images/282873/product_xlarge.jpg', 1800, 70, '2021-04-11 13:07:40', NULL),
(2, 'Nike Sportswear Air Max 1 White', 'Den här stilrena versionen av the Nike Air Max 1 följer i spåren efter de senaste framgångsrika Air Max-siluetterna. Den låga retroskon i dammodell ingår i det så kallade “his and hers pack” och har en wear-away-ovandel i Summer White. Sidan pryds av en präglad Swoosh och på hälen hittar vi en broderad Swoosh i två toner. Detaljer i guld och vitt ger den här legendariska skon en förstklassig finish.', 'https://www.estore.com/images/282873/product_airmax1.jpg', 1550, 80, '2021-04-11 13:07:40', NULL),
(3, 'Nike Sportswear Air Max 1 Prm Gold', 'Först kom originalet Nike Air Max 1 “Powerwall” år 2006, sen fick vi en version i lime, följt av “Strawberry Lemonade.” Nu får vi sista delen i serien (än så länge) som fått namnet “Lemonade.”  Originalet finns med i bakgrunden i den här gula uppdaterade versionen med tryckt Air-grafik på ovandelen. Blanka inslag i läder och underlager i mesh kompletterar looken.', 'https://www.estore.com/images/279052/product_prm.jpg', 1200, 55, '2021-04-11 13:11:34', NULL),
(4, 'Nike Sportswear Air Max 90 Qs Night Silver', 'Vi fortsätter att fira the Nike Air Max 90 och dess 30-årsfirande med den här unika versionen av retroskon. The Nike Air Max 90 QS är trogen sitt ursprung med den klassiska våffelsulan, sydda topplager och klassiska TPU-detaljer. Den senaste färgsättningen på den legendariska sneakern från Nike har Air Max-stötdämpning som återskapar originalkänslan från 1990.', 'https://www.estore.com/images/279052/product_air90qs.jpg', 1500, 22, '2021-04-11 13:11:34', NULL),
(5, 'New Balance M990 Orange', 'Staple sneakern New Balance 990 är tillbaka i en uppdaterad femte version. Det som först var en löparsko, tongivande i 99X-serien, har utvecklats till en bekväm vardagssiluett, och vi ser den på allt från modeller i London till pappor i Ohio. Den senaste uppdateringen behåller den stora “N” loggan på ovandelen av svinskinn och mesh. Mellansulan har fått en mindre ändring och vi får också extra stabilitet på sidan (vilket är den största förändringen sen v4).', 'https://www.sneakersnstuff.com/images/282971/product_newbalance.jpg', 700, 200, '2021-04-11 13:15:07', NULL),
(6, 'New Balance Vision Racer White', 'Efter att New Balance i princip ägde hela 2020, är deras dundersuccé Vision Racer av Jaden Smith tillbaka i en extremt stilren vit version. Skon är inspirerad av Jadens favoriter från New Balance, the 1700 och X-Racer. Den djärva siluetten har en ovandel i läder med paneler i mesh.', 'https://www.sneakersnstuff.com/images/282971/product_newbalance22.jpg', 2200, 66, '2021-04-11 13:15:07', NULL),
(7, 'Puma Suede VTG', 'Du kanske har sett the Puma Suede flera gånger, men kom den lika nära originalversionen som den här Puma Suede VTG? Den dök upp första gången år 1968, och nu får vi en version med en nedre panel, precis som originalsiluetten från förr. The Puma Suede har en marinblå ovandel med vita topplager och avrundas med loggor i guld metallic.', 'https://www.estore.com/images/275839/product_pumaSuede.jpg', 800, 120, '2021-04-11 13:19:54', NULL),
(8, 'Puma Rs-Fast', 'Puma uppdaterar den legendariska Running System och ger den modern look. Den senaste RS-Fast har en strömlinjeformad men djärv design med element från det tidiga 2000 och futuristiska vibbar. Den andningsaktiva ovandelen har paneler i mesh och läder. Den senaste skon från Puma avrundas med retro löparteknologin som ger stötdämpning från framfot till häl.', 'https://www.estore.com/images/275839/product_pumaRsfast.jpg', 1300, 90, '2021-04-11 13:19:54', NULL);

-- --------------------------------------------------------

--
-- Tabellstruktur `sessions`
--

CREATE TABLE `sessions` (
  `session_id` bigint(20) NOT NULL COMMENT 'Session ID',
  `session_user_id` bigint(20) NOT NULL COMMENT 'User ID',
  `accesstoken` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Access Token',
  `accesstokenexpiry` datetime NOT NULL COMMENT 'Access Token Expiry Date/Time',
  `refreshtoken` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Refresh Token',
  `refreshtokenexpiry` datetime NOT NULL COMMENT 'Refresh Token Expiry Date/Time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sessions Table';

-- --------------------------------------------------------

--
-- Tabellstruktur `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL COMMENT 'Users ID',
  `fullname` varchar(255) NOT NULL COMMENT 'Users Full Name',
  `username` varchar(255) NOT NULL COMMENT 'Users Username',
  `email` varchar(255) NOT NULL COMMENT 'Users Email',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Users Password',
  `role` varchar(255) NOT NULL DEFAULT 'customer' COMMENT 'Users Role',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'User Created Date/Time',
  `useractive` enum('N','Y') NOT NULL DEFAULT 'Y' COMMENT 'Is User Active',
  `loginattempts` int(1) NOT NULL DEFAULT 0 COMMENT 'Attempts To Log In'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Users Table';

--
-- Dumpning av Data i tabell `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `email`, `password`, `role`, `created_at`, `useractive`, `loginattempts`) VALUES
(1, 'Olle Nilsson', 'olle', 'olle.nilsson@medieinstitutet.se', '$2y$10$qyK.IiDXAfAx/uLc5II42.QxVuVvcgSVMVeGgYfmUC1gsfVXQuj0G', 'admin', '2021-04-11 13:03:20', 'Y', 0);

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `cartuserid_fk` (`cart_user_id`),
  ADD KEY `cartproductid_fk` (`cart_product_id`);

--
-- Index för tabell `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Index för tabell `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `accesstoken` (`accesstoken`),
  ADD UNIQUE KEY `refreshtoken` (`refreshtoken`),
  ADD KEY `sessionuserid_fk` (`session_user_id`);

--
-- Index för tabell `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Cart ID';

--
-- AUTO_INCREMENT för tabell `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Product ID', AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT för tabell `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Session ID';

--
-- AUTO_INCREMENT för tabell `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Users ID', AUTO_INCREMENT=2;

--
-- Restriktioner för dumpade tabeller
--

--
-- Restriktioner för tabell `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `cartproductid_fk` FOREIGN KEY (`cart_product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `cartuserid_fk` FOREIGN KEY (`cart_user_id`) REFERENCES `users` (`user_id`);

--
-- Restriktioner för tabell `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessionuserid_fk` FOREIGN KEY (`session_user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
