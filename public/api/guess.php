<?php
// =============================================================
//  public/api/guess.php  —  POST : valider un essai joueur
// =============================================================
// Entrée JSON : { "date": "2025-03-15", "mot": "aigle" }
// Sortie JSON : { "correct": bool, "scores": [ {green,blue,total}, ... ] }
// =============================================================
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once ROOT . '/src/model/Galerie.php';
require_once ROOT . '/src/model/Propositions.php';

header('Content-Type: application/json; charset=utf-8');

// Uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$date  = trim($input['date'] ?? '');
$mot   = trim($input['mot']  ?? '');

if (!$date || !$mot) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

// Valider le format date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Date invalide']);
    exit;
}

$galerie = new Galerie();
$entry   = $galerie->findByDate($date);

if (!$entry) {
    http_response_code(404);
    echo json_encode(['error' => 'Entrée introuvable']);
    exit;
}

// Vérifier si correct
$correct = Galerie::checkMot($mot, $entry);

// Scores Mastermind pour chaque mot cible (sans révéler les mots)
$scores = [];
foreach ($entry['mots'] as $cible) {
    $scores[] = Galerie::mastermindScore($mot, $cible);
}

// Enregistrer la proposition (anonyme)
$props = new Propositions();
$props->add($date, $mot, $correct);

echo json_encode([
    'correct' => $correct,
    'scores'  => $scores,   // un score par mot cible, dans l'ordre
]);
