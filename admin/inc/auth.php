<?php
// =============================================================
//  admin/inc/auth.php  —  gestion de la session admin
// =============================================================

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(ADMIN_SESSION);
        session_start();
    }
}

function admin_is_logged(): bool
{
    admin_session_start();
    return !empty($_SESSION['admin_logged']);
}

function admin_require_login(): void
{
    if (!admin_is_logged()) {
        header('Location: /admin/?page=login');
        exit;
    }
}

function admin_login(string $user, string $pass): bool
{
    admin_session_start();
    if ($user === ADMIN_USER && $pass === ADMIN_PASSWORD) {
        $_SESSION['admin_logged'] = true;
        return true;
    }
    return false;
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];
    session_destroy();
}
