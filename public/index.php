<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT . '/src/model/Galerie.php';
require_once ROOT . '/src/model/Propositions.php';

$galerie = new Galerie();
$all     = $galerie->all();
$total   = count($all);

// Dernière entrée dont la date <= aujourd'hui
$todayDate  = date('Y-m-d');
$todayIndex = 0;
foreach ($all as $i => $e) {
    if ($e['date'] <= $todayDate) $todayIndex = $i;
}

$index = isset($_GET['index']) ? (int)$_GET['index'] : $todayIndex;
$index = max(0, min($index, $total - 1));

$entry = $all[$index];

$entryJsonJs = json_encode([
    'date'   => $entry['date'],
    'index'  => $index,
    'total'  => $total,
    'nbMots' => count($entry['mots']),
    'images' => $entry['images'],
], JSON_UNESCAPED_UNICODE);

require_once ROOT . '/inc/head.php';
require_once ROOT . '/inc/header.php';
require_once ROOT . '/inc/main.php';
require_once ROOT . '/inc/footer.php';