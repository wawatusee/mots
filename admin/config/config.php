<?php
// =============================================================
//  admin/config/config.php  —  configuration admin
//  INDÉPENDANT de la config publique
// =============================================================

define('ADMIN_ROOT',    dirname(__DIR__, 2));   // racine du projet
define('ADMIN_JSON',    ADMIN_ROOT . '/json');
define('ADMIN_IMG',     ADMIN_ROOT . '/public/img/content');
define('ADMIN_IMG_URL', '/public/img/content');

// --- Sécurité ------------------------------------------------
// Changer ces valeurs avant mise en production
define('ADMIN_USER',     'admin');
define('ADMIN_PASSWORD', 'motdepasse');         // en clair pour l'instant
define('ADMIN_SESSION',  'galerie_admin');

// --- Images --------------------------------------------------
// Tailles générées à l'upload (suffixe => largeur px, hauteur auto)
define('ADMIN_IMG_SIZES', [
    'sm'  => 400,
    'md'  => 800,
    'lg'  => 1200,
]);
define('ADMIN_IMG_QUALITY_JPG',  85);   // qualité JPG (0-100)
define('ADMIN_IMG_QUALITY_WEBP', 82);   // qualité WebP (0-100)

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
