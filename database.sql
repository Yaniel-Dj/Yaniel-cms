-- ============================================================
-- Yaniel CMS - Base de datos
-- Base de datos: acore_shop
-- Compatible con AzerothCore 3.3.5a
-- Crea las tablas personalizadas para la tienda, códigos y rifas
-- ============================================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `acore_shop`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `acore_shop`;

-- ============================================================
-- TABLA: Categorías de la tienda
-- ============================================================
CREATE TABLE IF NOT EXISTS `web_shop_categories` (
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `nombre`    VARCHAR(100) NOT NULL,
    `nombre_en` VARCHAR(100) NULL,
    `slug`      VARCHAR(100) NOT NULL UNIQUE,
    `icono`     VARCHAR(50) DEFAULT 'fa-box'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: Items de la tienda
-- ============================================================
CREATE TABLE IF NOT EXISTS `web_shop_items` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `item_entry`   INT NOT NULL,
    `nombre`       VARCHAR(120) NOT NULL,
    `nombre_en`    VARCHAR(120) NULL,
    `precio`       INT NOT NULL COMMENT 'Precio en cobre',
    `activo`       TINYINT DEFAULT 1,
    `categoria_id` INT DEFAULT 1,
    INDEX `idx_categoria` (`categoria_id`),
    INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: Compras de la tienda
-- ============================================================
CREATE TABLE IF NOT EXISTS `web_shop_purchases` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `account_id`     INT NOT NULL,
    `character_guid` INT NOT NULL,
    `item_entry`     INT NOT NULL,
    `cantidad`       INT DEFAULT 1,
    `precio_pagado`  INT NOT NULL COMMENT 'Precio en cobre',
    `estado`         VARCHAR(20) DEFAULT 'pendiente'
                     COMMENT 'pendiente, enviado, fallido',
    `fecha_compra`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_account` (`account_id`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: Códigos de regalo
-- ============================================================
CREATE TABLE IF NOT EXISTS `web_gift_codes` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `codigo`            VARCHAR(50) NOT NULL UNIQUE,
    `tipo`              VARCHAR(20) NOT NULL COMMENT 'oro o item',
    `valor`             INT NOT NULL COMMENT 'Cantidad de oro o ID del item',
    `usos_maximos`      INT DEFAULT 1,
    `usos_actuales`     INT DEFAULT 0,
    `fecha_creacion`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_expiracion`  DATETIME NULL,
    `activo`            TINYINT DEFAULT 1,
    INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: Registro de canjes de códigos
-- ============================================================
CREATE TABLE IF NOT EXISTS `web_gift_redemptions` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_id`      INT NOT NULL,
    `account_id`     INT NOT NULL,
    `character_guid` INT NOT NULL,
    `fecha_canje`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unico_canje` (`codigo_id`, `account_id`),
    INDEX `idx_account` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: Rifas
-- ============================================================
CREATE TABLE IF NOT EXISTS `web_rifas` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `item_entry`     INT NOT NULL,
    `item_nombre`    VARCHAR(100) NOT NULL,
    `item_nombre_en` VARCHAR(100) NULL,
    `precio_boleto`  INT NOT NULL COMMENT 'Precio en oro',
    `fecha_inicio`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_fin`      DATETIME NOT NULL,
    `estado`         VARCHAR(20) DEFAULT 'activa'
                     COMMENT 'activa, finalizada',
    `ganador_guid`   INT DEFAULT NULL,
    `ganador_nombre` VARCHAR(50) DEFAULT NULL,
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: Boletos de rifas
-- ============================================================
CREATE TABLE IF NOT EXISTS `web_rifas_boletos` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `rifa_id`        INT NOT NULL,
    `account_id`     INT NOT NULL,
    `character_guid` INT NOT NULL,
    `character_name` VARCHAR(50) NOT NULL,
    `cantidad`       INT DEFAULT 1,
    `fecha_compra`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rifa` (`rifa_id`),
    INDEX `idx_account` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS DE EJEMPLO: Categorías
-- ============================================================
INSERT IGNORE INTO `web_shop_categories` (`nombre`, `nombre_en`, `slug`, `icono`) VALUES
('Monturas',   'Mounts',      'monturas',   'fa-horse'),
('Armas',      'Weapons',     'armas',      'fa-sword'),
('Armaduras',  'Armor',       'armaduras',  'fa-shield'),
('Consumibles','Consumables', 'consumibles','fa-flask'),
('Mascotas',   'Pets',        'mascotas',   'fa-paw'),
('Juguetes',   'Toys',        'juguetes',   'fa-gamepad');

-- ============================================================
-- DATOS DE EJEMPLO: Items
-- ============================================================
INSERT IGNORE INTO `web_shop_items`
    (`item_entry`, `nombre`, `nombre_en`, `precio`, `activo`, `categoria_id`) VALUES
(50818, 'Riendas de Invencible',       'Reins of the Invincible',       50000000, 1, 1),
(45693, 'Mimiron\'s Head',             'Mimiron\'s Head',               30000000, 1, 1),
(51955, 'Riendas del Draco de Hielo',  'Reins of the Icebound Drake',   20000000, 1, 1),
(49623, 'Sulfuras, Mano de Ragnaros',  'Sulfuras, Hand of Ragnaros',    40000000, 1, 2),
(32837, 'Hoja del Infierno',           'Hellfire Blade',                15000000, 1, 2),
(50759, 'Espada de los Reyes Caídos',  'Sword of the Fallen Kings',     10000000, 1, 2),
(51421, 'Corona de la Luz',            'Crown of the Light',            25000000, 1, 3),
(51240, 'Pecho del Guardián',          'Guardian\'s Chestguard',        12000000, 1, 3),
(40211, 'Poción de velocidad',         'Potion of Speed',               50000,    1, 4),
(40212, 'Poción de maná',              'Mana Potion',                   30000,    1, 4),
(46398, 'Gato de la suerte',           'Lucky Cat',                     1000000,  1, 5),
(46544, 'Rata de la suerte',           'Lucky Rat',                     1000000,  1, 5),
(45057, 'Tren de juguete',             'Toy Train',                     500000,   1, 6),
(54452, 'Cohete de juguete',           'Toy Rocket',                    500000,   1, 6);

-- ============================================================
-- DATOS DE EJEMPLO: Códigos de regalo
-- ============================================================
INSERT IGNORE INTO `web_gift_codes`
    (`codigo`, `tipo`, `valor`, `usos_maximos`, `fecha_expiracion`, `activo`) VALUES
('BIENVENIDO2026',  'oro',  1000,  100, DATE_ADD(NOW(), INTERVAL 30 DAY), 1),
('REGALO500',       'oro',  500,   50,  DATE_ADD(NOW(), INTERVAL 15 DAY), 1),
('MONTURAESPECIAL', 'item', 50818, 5,   DATE_ADD(NOW(), INTERVAL 7 DAY),  1),
('POCIONESGRATIS',  'item', 40211, 200, NULL,                             1);

-- ============================================================
-- DATOS DE EJEMPLO: Rifas
-- ============================================================
INSERT IGNORE INTO `web_rifas`
    (`item_entry`, `item_nombre`, `item_nombre_en`, `precio_boleto`, `fecha_fin`, `estado`) VALUES
(50818, 'Riendas de Invencible',      'Reins of the Invincible',     1000, DATE_ADD(NOW(), INTERVAL 10 DAY), 'activa'),
(45693, 'Mimiron\'s Head',            'Mimiron\'s Head',             500,  DATE_ADD(NOW(), INTERVAL 15 DAY), 'activa'),
(51955, 'Riendas del Draco de Hielo', 'Reins of the Icebound Drake', 750,  DATE_ADD(NOW(), INTERVAL 20 DAY), 'activa');

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
