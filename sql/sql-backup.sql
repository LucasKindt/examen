-- --------------------------------------------------------
-- Host:                         localhost
-- Server versie:                10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Versie:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumpen data van tabel app.doctrine_migration_versions: ~11 rows (ongeveer)
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
	('DoctrineMigrations\\Version20241112173349', '2024-11-12 17:33:57', 181),
	('DoctrineMigrations\\Version20241112192847', '2024-11-12 19:28:54', 77),
	('DoctrineMigrations\\Version20241112193052', '2024-11-12 19:30:58', 29),
	('DoctrineMigrations\\Version20241112193515', '2024-11-12 19:35:20', 226),
	('DoctrineMigrations\\Version20241113130135', '2024-11-13 13:01:42', 19),
	('DoctrineMigrations\\Version20241113131154', '2024-11-13 13:11:57', 289),
	('DoctrineMigrations\\Version20241113152738', '2024-11-13 15:27:41', 32),
	('DoctrineMigrations\\Version20241114132105', '2024-11-14 13:21:09', 117),
	('DoctrineMigrations\\Version20241115134851', '2024-11-15 13:48:58', 58),
	('DoctrineMigrations\\Version20241118092557', '2024-11-18 09:26:22', 18),
	('DoctrineMigrations\\Version20241118162519', '2024-11-18 16:25:22', 14);

-- Dumpen data van tabel app.messenger_messages: ~0 rows (ongeveer)

-- Dumpen data van tabel app.order: ~6 rows (ongeveer)
INSERT INTO `order` (`id`, `name`, `email`, `phone`, `date`, `status`, `number`, `is_ready_email_send`) VALUES
	(18, 'Jantje Neels', 'jantjeneels@gmail.com', '0619029124', '2024-11-18 10:40:00', 'paid', '56956444', NULL),
	(19, 'Lucas kindt', 'lucaskindt77@gmail.com', '0637319019', '2024-10-18 10:41:00', 'paid', '35491846', NULL),
	(20, 'Heinrich Bierman', 'lucaskindt77@gmail.com', '0612312312', '2024-11-18 10:46:00', 'ready', '18667818', 1),
	(21, 'Lucas kindt', 'lucaskindt77@gmail.com', '0637319019', '2024-11-18 10:47:00', 'ready', '20941915', 1),
	(22, 'Lucas kindt', 'lucaskindt77@gmail.com', '0637319019', '2024-11-18 16:12:00', 'ready', '61180347', 1),
	(23, 'Lucas kindt', 'lucaskindt77@gmail.com', '0637319019', '2024-11-18 18:07:00', 'ready', '55850580', 1);

-- Dumpen data van tabel app.order_product: ~6 rows (ongeveer)
INSERT INTO `order_product` (`id`, `product_id`, `torder_id`, `amount`, `stock_updated`) VALUES
	(29, 3, 18, 6, 1),
	(30, 4, 19, 12, 1),
	(31, 4, 20, 1, 1),
	(32, 4, 21, 1, 1),
	(33, 4, 22, 1, 1),
	(34, 5, 23, 21, 1);

-- Dumpen data van tabel app.product: ~5 rows (ongeveer)
INSERT INTO `product` (`id`, `name`, `price`, `description`, `stock`, `image`) VALUES
	(3, 'Test Product 1', 975.00, '<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ornare leo vitae orci molestie tristique. In maximus ultricies ligula. Duis porttitor nibh in urna dignissim congue. Ut eros tortor, tempor vitae malesuada sed, sollicitudin eu ante. Duis n', 0, '58b7654d41621ef1280a02836638c8fd1f6d6520.jpg'),
	(4, 'Test Product 2', 1850.00, '<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sollicitudin non lacus at tristique. Nulla ultrices ultrices placerat. Praesent quis libero sodales risus bibendum scelerisque. Phasellus semper, felis a placerat pretium, massa urna vene', 162, '3bb168bf3bb546b629df26ab95f44eda11d7d3c5.jpg'),
	(5, 'Test Product 3', 595.00, '<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sollicitudin non lacus at tristique. Nulla ultrices ultrices placerat. Praesent quis libero sodales risus bibendum scelerisque. Phasellus semper, felis a placerat pretium, massa urna vene', 0, '3cf9fdc11bed0fa630a950cd133f806d4273e72a.jpg'),
	(6, 'Test Product 4', 5450.00, '<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sollicitudin non lacus at tristique. Nulla ultrices ultrices placerat. Praesent quis libero sodales risus bibendum scelerisque. Phasellus semper, felis a placerat pretium, massa urna vene', 180, 'bad3b4bb417c730907f9af545feeafce46393cec.jpg'),
	(7, 'Test Product 5', 1499.00, '<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sollicitudin non lacus at tristique. Nulla ultrices ultrices placerat. Praesent quis libero sodales risus bibendum scelerisque. Phasellus semper, felis a placerat pretium, massa urna vene', 188, '019239acf8f92a90f00a910f99a2445a9c585dd2.jpg');

-- Dumpen data van tabel app.product_category: ~1 rows (ongeveer)
INSERT INTO `product_category` (`id`, `name`) VALUES
	(2, 'Haargel');

-- Dumpen data van tabel app.product_category_product: ~5 rows (ongeveer)
INSERT INTO `product_category_product` (`product_category_id`, `product_id`) VALUES
	(2, 3),
	(2, 4),
	(2, 5),
	(2, 6),
	(2, 7);

-- Dumpen data van tabel app.treatment: ~2 rows (ongeveer)
INSERT INTO `treatment` (`id`, `name`, `description`, `price`, `image`) VALUES
	(3, 'Knippen', 'Lekker knippen hierzo', '1999', 'd299d4ceefe7d72e8a2241aea4b6b6372c8dbc3d.webp'),
	(4, 'Wassen', 'Lekker wassen hierzo', '1999', '6d1b0fdeaf4c3927be2f34b27a35ccee218137f1.webp');

-- Dumpen data van tabel app.user: ~2 rows (ongeveer)
INSERT INTO `user` (`id`, `email`, `name`, `roles`, `password`, `is_verified`) VALUES
	(2, 'admin@jehaarzitgoed.nl', 'admin', '["ROLE_USER","ROLE_ADMIN","ROLE_EMPLOYEE"]', '$2y$13$L8YmopBG9r2KyHR5mgFpeuaz.JFathUwdWxEcdDsD/bVY427fnrva', 0),
	(3, 'medewerker@jehaarzitgoed.nl', 'Medewerker', '["ROLE_USER","ROLE_EMPLOYEE"]', '$2y$13$5MAjUAan3ZFgdA0zDZUTEe7Um1sVbWYkZhWCunCu9cGlGonCKhKtm', 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
