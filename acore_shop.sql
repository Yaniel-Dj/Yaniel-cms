/*
SQLyog Community v13.3.0 (64 bit)
MySQL - 8.4.3 : Database - acore_shop
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`acore_shop` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `acore_shop`;

/*Table structure for table `web_account_points` */

DROP TABLE IF EXISTS `web_account_points`;

CREATE TABLE `web_account_points` (
  `account_id` int unsigned NOT NULL,
  `points` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `web_account_points` */

/*Table structure for table `web_gift_codes` */

DROP TABLE IF EXISTS `web_gift_codes`;

CREATE TABLE `web_gift_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` int NOT NULL,
  `usos_maximos` int DEFAULT '1',
  `usos_actuales` int DEFAULT '0',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_expiracion` datetime DEFAULT NULL,
  `activo` tinyint DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `web_gift_codes` */

/*Table structure for table `web_gift_redemptions` */

DROP TABLE IF EXISTS `web_gift_redemptions`;

CREATE TABLE `web_gift_redemptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo_id` int NOT NULL,
  `account_id` int NOT NULL,
  `character_guid` int NOT NULL,
  `fecha_canje` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_canje` (`codigo_id`,`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `web_gift_redemptions` */

insert  into `web_gift_redemptions`(`id`,`codigo_id`,`account_id`,`character_guid`,`fecha_canje`) values 
(1,1,3,14,'2026-11-01 18:56:20'),
(2,2,3,14,'2026-11-01 18:57:47'),
(3,3,3,11,'2026-11-01 18:59:10'),
(4,5,3,15,'2026-11-01 19:15:57'),
(5,10,3,11,'2026-11-05 06:21:47');

/*Table structure for table `web_rifas` */

DROP TABLE IF EXISTS `web_rifas`;

CREATE TABLE `web_rifas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_entry` int NOT NULL,
  `item_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_nombre_en` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_boleto` int NOT NULL,
  `fecha_inicio` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` datetime NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `ganador_guid` int DEFAULT NULL,
  `ganador_nombre` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `web_rifas` */

/*Table structure for table `web_rifas_boletos` */

DROP TABLE IF EXISTS `web_rifas_boletos`;

CREATE TABLE `web_rifas_boletos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rifa_id` int NOT NULL,
  `account_id` int NOT NULL,
  `character_guid` int NOT NULL,
  `character_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` int DEFAULT '1',
  `fecha_compra` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `web_rifas_boletos` */

/*Table structure for table `web_shop_categories` */

DROP TABLE IF EXISTS `web_shop_categories`;

CREATE TABLE `web_shop_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_en` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'fa-box',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `web_shop_categories` */

insert  into `web_shop_categories`(`id`,`nombre`,`nombre_en`,`slug`,`icono`) values 
(1,'Monturas','Mounts','monturas','fa-horse'),
(2,'Armas','Weapons','armas','fa-sword'),
(3,'Armaduras','Armor','armaduras','fa-shield'),
(4,'Consumibles','Consumables','consumibles','fa-flask');

/*Table structure for table `web_shop_items` */

DROP TABLE IF EXISTS `web_shop_items`;

CREATE TABLE `web_shop_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `item_entry` mediumint unsigned NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_en` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio` int unsigned NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `categoria_id` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_item_entry` (`item_entry`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `web_shop_items` */

insert  into `web_shop_items`(`id`,`item_entry`,`nombre`,`nombre_en`,`imagen`,`precio`,`activo`,`categoria_id`) values 
(43,50818,'Riendas de Invencible','Reins of the Invincible','item_1793810164_20586409.webp',500000000,1,1),
(44,45693,'Cabeza de Mimiron','Mimiron\'s Head','item_1793810654_b12314ea.jpg',400000000,1,1),
(45,32458,'Cenizas de Al\'ar','Ashes of Al\'ar','item_1793875538_0c546cc3.jpg',350000000,1,1),
(46,44151,'Draco del Viento del Norte','Reins of the Drake of the North Wind',NULL,250000000,1,1),
(47,43952,'Draco Azur','Reins of the Azure Drake',NULL,200000000,1,1),
(48,44707,'Protodraco Azul','Reins of the Blue Proto-Drake',NULL,180000000,1,1),
(49,44168,'Protodraco Perdido en el Tiempo','Reins of the Time-Lost Proto-Drake',NULL,300000000,1,1),
(50,44713,'Protodraco Verde','Reins of the Green Proto-Drake',NULL,150000000,1,1),
(51,44710,'Protodraco Rojo','Reins of the Red Proto-Drake',NULL,150000000,1,1),
(52,45801,'Protodraco Oxidado','Reins of the Rusted Proto-Drake',NULL,200000000,1,1),
(53,45802,'Protodraco Forjado en Hierro','Reins of the Ironbound Proto-Drake',NULL,200000000,1,1),
(54,37012,'Montura del Jinete Sin Cabeza','The Horseman\'s Reins',NULL,120000000,1,1),
(55,33225,'Tigre Espectral Veloz','Reins of the Swift Spectral Tiger',NULL,450000000,1,1),
(56,33224,'Tigre Espectral','Reins of the Spectral Tiger',NULL,350000000,1,1),
(57,49284,'Cohete X-51 X-TREME','X-51 Nether-Rocket X-TREME',NULL,250000000,1,1),
(58,49283,'Cohete X-51','X-51 Nether-Rocket',NULL,150000000,1,1),
(59,46780,'Huevo de Gallo Mágico','Magic Rooster Egg',NULL,200000000,1,1),
(60,46109,'Tortuga de Montar','Riding Turtle',NULL,180000000,1,1),
(61,54068,'Rinoceronte Blanco Lanudo','Wooly White Rhino',NULL,220000000,1,1),
(62,44086,'Gran Mamut de Hielo','Grand Ice Mammoth',NULL,400000000,1,1),
(63,49623,'Agonía de Sombras','Shadowmourne','item_1793875690_f80a2785.jpg',300000000,1,2),
(64,49919,'Val\'anyr, Martillo de los Reyes Antiguos','Val\'anyr, Hammer of Ancient Kings','item_1793875866_9866b946.jpg',250000000,1,2),
(65,17182,'Sulfuras, Mano de Ragnaros','Sulfuras, Hand of Ragnaros','item_1793876118_44f2ccc9.jpg',200000000,1,2),
(66,19019,'Furia de Trueno','Thunderfury, Blessed Blade of the Windseeker','item_1793876319_886aa7e0.jpg',200000000,1,2),
(67,32837,'Hoja de Guerra de Azzinoth (Mano Derecha)','Warglaive of Azzinoth (Main Hand)',NULL,250000000,1,2),
(68,32838,'Hoja de Guerra de Azzinoth (Mano Izquierda)','Warglaive of Azzinoth (Off Hand)',NULL,250000000,1,2),
(69,50759,'Bryntroll, el Árbitro de Huesos','Bryntroll, the Bone Arbiter',NULL,80000000,1,2),
(70,50474,'Llamada del Caos','Havoc\'s Call',NULL,70000000,1,2),
(71,50472,'Caída del Cielo','Heaven\'s Fall',NULL,70000000,1,2),
(72,50272,'Hacedor de Criptas','Cryptmaker',NULL,60000000,1,2),
(73,50730,'Glorenzelg, Hoja Alta de la Mano de Plata','Glorenzelg, High-Blade of the Silver Hand',NULL,80000000,1,2),
(74,49995,'Guardajuramentos, Carga del General de los Exploradores','Oathbinder, Charge of the Ranger-General',NULL,70000000,1,2),
(75,50731,'Archus, Gran Bastón de Antonidas','Archus, Greatstaff of Antonidas',NULL,80000000,1,2),
(76,50734,'Cetro Real de Terenas II','Royal Scepter of Terenas II',NULL,60000000,1,2),
(77,50735,'Fal\'inrush, Defensor de Quel\'Thalas','Fal\'inrush, Defender of Quel\'Thalas',NULL,50000000,1,2),
(78,50610,'Furia de Tormenta, Hoja Negra del Traidor','Stormfury, Black Blade of the Betrayer',NULL,60000000,1,2),
(79,51240,'Pecho del Guardián','Guardian\'s Chestguard',NULL,80000000,1,3),
(80,51421,'Corona de la Luz','Crown of the Light',NULL,80000000,1,3),
(81,51173,'Yelmo del Caído','Helm of the Fallen',NULL,80000000,1,3),
(82,51239,'Guanteletes del Campeón','Gauntlets of the Champion',NULL,60000000,1,3),
(83,51175,'Guardahombros del Protector','Pauldrons of the Protector',NULL,60000000,1,3),
(84,51241,'Pantalones del Conquistador','Legguards of the Conqueror',NULL,60000000,1,3),
(85,51242,'Botas del Justo','Boots of the Just',NULL,50000000,1,3),
(86,51176,'Cinturón del Sabio','Belt of the Sage',NULL,50000000,1,3),
(87,51243,'Brazales del Valiente','Bracers of the Valiant',NULL,40000000,1,3),
(88,51177,'Capa del Héroe','Cloak of the Hero',NULL,40000000,1,3),
(89,40211,'Poción de velocidad','Potion of Speed',NULL,500000,1,4),
(90,40212,'Poción de maná','Mana Potion',NULL,300000,1,4),
(91,33447,'Poción de curación rúnica','Runic Healing Potion',NULL,400000,1,4),
(92,33448,'Poción de maná rúnica','Runic Mana Potion',NULL,400000,1,4),
(93,40093,'Poción de sanación indestructible','Indestructible Potion',NULL,600000,1,4),
(94,44332,'Elixir de poderío','Elixir of Mighty Strength',NULL,800000,1,4),
(95,44331,'Elixir de pensamiento veloz','Elixir of Lightning Speed',NULL,800000,1,4),
(96,42995,'Filete de oso','Bear Steak',NULL,100000,1,4),
(97,42999,'Filete de lobo','Wolf Steak',NULL,100000,1,4),
(98,46376,'Frasco de la bruja suprema','Flask of the Supreme Power',NULL,2000000,1,4),
(99,46377,'Frasco de furia infinita','Flask of Endless Rage',NULL,2000000,1,4),
(100,46398,'Gato de la suerte','Lucky Cat',NULL,1000000,1,5),
(101,46544,'Rata de la suerte','Lucky Rat',NULL,1000000,1,5),
(102,46892,'Cucaracha','Cockroach',NULL,500000,1,5),
(103,44738,'Serpiente','Snake',NULL,500000,1,5),
(104,44737,'Rana','Frog',NULL,500000,1,5),
(105,46545,'Mascota Mecánica','Mechanical Pet',NULL,3000000,1,5),
(106,46546,'Mascota Mágica','Magical Pet',NULL,3000000,1,5),
(107,46547,'Mascota de las Sombras','Shadow Pet',NULL,4000000,1,5),
(108,45057,'Tren de juguete','Toy Train',NULL,500000,1,6),
(109,54452,'Cohete de juguete','Toy Rocket',NULL,500000,1,6),
(110,45014,'Pelota de juguete','Toy Ball',NULL,300000,1,6),
(111,45018,'Cometa de juguete','Toy Kite',NULL,300000,1,6);

/*Table structure for table `web_shop_purchases` */

DROP TABLE IF EXISTS `web_shop_purchases`;

CREATE TABLE `web_shop_purchases` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int unsigned NOT NULL,
  `character_guid` int unsigned NOT NULL,
  `item_entry` mediumint unsigned NOT NULL,
  `cantidad` int unsigned NOT NULL DEFAULT '1',
  `precio_pagado` int unsigned NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','enviado','fallido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_compra` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_account` (`account_id`),
  KEY `idx_character` (`character_guid`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `web_shop_purchases` */

insert  into `web_shop_purchases`(`id`,`account_id`,`character_guid`,`item_entry`,`cantidad`,`precio_pagado`,`fecha`,`estado`,`fecha_compra`) values 
(15,3,14,9560012,1,10000000,'2026-11-01 19:42:06','enviado','2026-11-01 19:44:42'),
(16,3,14,50818,1,150000000,'2026-11-01 19:42:47','enviado','2026-11-01 19:44:42'),
(17,3,14,50818,1,150000000,'2026-11-01 19:42:56','enviado','2026-11-01 19:44:42'),
(18,3,14,50818,1,150000000,'2026-11-01 19:45:12','enviado','2026-11-01 19:45:12'),
(19,3,14,50818,1,150000000,'2026-11-01 19:45:19','enviado','2026-11-01 19:45:19'),
(20,3,11,45014,1,300000,'2026-11-03 10:51:24','enviado','2026-11-03 10:51:24'),
(21,3,11,46377,1,2000000,'2026-11-03 10:52:54','enviado','2026-11-03 10:52:54'),
(22,3,11,40211,1,500000,'2026-11-03 10:53:32','enviado','2026-11-03 10:53:32'),
(23,3,11,40093,1,600000,'2026-11-03 10:54:15','enviado','2026-11-03 10:54:15'),
(24,3,11,40093,1,600000,'2026-11-03 10:54:20','enviado','2026-11-03 10:54:20'),
(25,3,11,49623,1,300000000,'2026-11-03 10:56:12','enviado','2026-11-03 10:56:12'),
(26,3,11,45693,1,400000000,'2026-11-03 11:32:42','enviado','2026-11-03 11:32:42'),
(27,3,11,44151,1,250000000,'2026-11-04 11:19:27','enviado','2026-11-04 11:19:27');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
