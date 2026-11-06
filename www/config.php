<?php
// ============================================
// CONFIGURACIÓN GLOBAL
// ============================================

// Zona horaria (importante para rifas y fechas)
date_default_timezone_set('America/Havana');

// Base de datos
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'pass');
define('DB_NAME', 'acore_auth');
define('CHAR_DB_NAME', 'acore_characters');
define('WORLD_DB_NAME', 'acore_world');
define('SHOP_DB_NAME', 'acore_shop');

// Reglas de registro
define('MIN_USERNAME_LENGTH', 3);
define('MAX_USERNAME_LENGTH', 16);
define('MIN_PASSWORD_LENGTH', 6);
define('MAX_PASSWORD_LENGTH', 16);
define('EXPANSION', 2);

// Servidor
define('SERVER_IP', '127.0.0.1');
define('SERVER_PORT', 8085);
define('SERVER_TIMEOUT', 1);

// Rutas / marca
define('BG_IMAGE', 'img/fondo.jpg');
if (!defined('SERVER_NAME'))    define('SERVER_NAME',    'AzerothCore Server');
if (!defined('SERVER_TAGLINE')) define('SERVER_TAGLINE', 'Regístrate para jugar en el servidor');

// Archivos de noticias (bilingües)
if (!defined('NOTICIAS_ES_FILE')) define('NOTICIAS_ES_FILE', __DIR__ . '/noticias_es.json');
if (!defined('NOTICIAS_EN_FILE')) define('NOTICIAS_EN_FILE', __DIR__ . '/noticias_en.json');
if (!defined('NOTICIAS_FILE'))    define('NOTICIAS_FILE',    __DIR__ . '/noticias_es.json');

// Archivos de reglas (bilingües)
if (!defined('REGLAS_ES_FILE')) define('REGLAS_ES_FILE', __DIR__ . '/reglas_es.json');
if (!defined('REGLAS_EN_FILE')) define('REGLAS_EN_FILE', __DIR__ . '/reglas_en.json');

// SOAP
define('SOAP_HOST', '127.0.0.1');
define('SOAP_PORT', 7878);
define('SOAP_URI', 'urn:AC');
define('SOAP_GM_USER', 'user');
define('SOAP_GM_PASS', 'pass');

// Economía
define('COBRE_POR_ORO', 10000);
