<?php
// =============================================================
//  admin/index.php  —  routeur principal de l'admin
// =============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/AdminGalerie.php';
require_once __DIR__ . '/inc/AdminPropositions.php';

$page = $_GET['page'] ?? 'liste';

// --- Actions POST sans page rendue --------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Login
    if ($action === 'login') {
        if (admin_login($_POST['user'] ?? '', $_POST['pass'] ?? '')) {
            header('Location: /admin/');
        } else {
            header('Location: /admin/?page=login&error=1');
        }
        exit;
    }

    // Logout
    if ($action === 'logout') {
        admin_logout();
        header('Location: /admin/?page=login');
        exit;
    }

    // Pages protégées
    admin_require_login();

    // Sauvegarder une entrée (ajout ou modif)
    if ($action === 'save') {
        require_once __DIR__ . '/pages/action_save.php';
        exit;
    }

    // Supprimer une entrée
    if ($action === 'delete') {
        $galerie = new AdminGalerie();
        $galerie->delete($_POST['date'] ?? '');
        header('Location: /admin/');
        exit;
    }

    // Supprimer une image
    if ($action === 'delete_image') {
        $galerie = new AdminGalerie();
        $galerie->deleteImage($_POST['date'] ?? '', $_POST['base'] ?? '');
        header('Location: /admin/?page=edit&date=' . urlencode($_POST['date']));
        exit;
    }
}

// --- Pages GET ----------------------------------------------

// Login : accessible sans auth
if ($page === 'login') {
    require __DIR__ . '/inc/head.php';
    require __DIR__ . '/pages/login.php';
    require __DIR__ . '/inc/foot.php';
    exit;
}

// Toutes les autres pages nécessitent d'être connecté
admin_require_login();

require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/topbar.php';

match ($page) {
    'edit'   => require __DIR__ . '/pages/edit.php',
    'stats'  => require __DIR__ . '/pages/stats.php',
    default  => require __DIR__ . '/pages/liste.php',
};

require __DIR__ . '/inc/foot.php';
