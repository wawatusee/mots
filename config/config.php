<?php
// =============================================================
//  config/config.php  —  configuration publique
// =============================================================

define('ROOT',        dirname(__DIR__));
define('JSON_DIR',    ROOT . '/json');
define('IMG_DIR',     ROOT . '/public/img/content');
define('IMG_URL',     '/public/img/content');

// Tailles d'images disponibles pour le srcset (suffixe => largeur px)
define('IMG_SIZES', [
    'sm'  => 400,
    'md'  => 800,
    'lg'  => 1200,
]);

// Formats d'image modernes supportés
define('IMG_FORMATS', ['webp', 'jpg']);

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
