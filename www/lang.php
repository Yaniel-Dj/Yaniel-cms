<?php
// ============================================
// SISTEMA DE IDIOMAS · ES / EN
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $__lang_nuevo = ($_GET['lang'] === 'en') ? 'en' : 'es';
    $_SESSION['lang'] = $__lang_nuevo;
}

$idioma = $_SESSION['lang'] ?? 'es';
if (!in_array($idioma, ['es', 'en'], true)) {
    $idioma = 'es';
}

// Cargar el archivo de traducción correspondiente
if ($idioma === 'en') {
    $__trad = require __DIR__ . '/lang_en.php';
} else {
    $__trad = require __DIR__ . '/lang_es.php';
}

function t($key) {
    global $__trad;
    return $__trad[$key] ?? $key;
}

// ============================================
// CONSTANTES Y HELPERS COMPARTIDOS
// ============================================
if (!defined('SERVER_NAME')) {
    define('SERVER_NAME', 'AzerothCore Server');
}
if (!defined('NOTICIAS_FILE')) {
    define('NOTICIAS_FILE', __DIR__ . '/noticias_es.json');
}

if (!function_exists('fechaBonita')) {
    function fechaBonita($fecha) {
        global $idioma;
        if (!$fecha) return '';
        $ts = strtotime($fecha);
        if (!$ts) return htmlspecialchars($fecha);
        if ($idioma === 'en') {
            return date('F j, Y', $ts);
        }
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        return date('j', $ts) . ' de ' . $meses[(int)date('n', $ts) - 1] . ' de ' . date('Y', $ts);
    }
}