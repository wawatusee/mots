<?php
// =============================================================
//  admin/pages/action_save.php  —  traitement POST save
// =============================================================

$galerie      = new AdminGalerie();
$date         = trim($_POST['date'] ?? '');
$dateOriginal = trim($_POST['date_original'] ?? '');
$motsRaw      = trim($_POST['mots'] ?? '');

// Validation date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    header('Location: /admin/?page=edit&date=' . urlencode($dateOriginal) . '&err=date');
    exit;
}

// Validation mots
$mots = array_values(array_filter(
    array_map('trim', explode(',', $motsRaw)),
    fn($m) => $m !== ''
));
if (empty($mots)) {
    header('Location: /admin/?page=edit&date=' . urlencode($date) . '&err=mots');
    exit;
}

// Récupérer l'entrée existante ou en créer une nouvelle
$entry = $galerie->findByDate($date) ?? ['date' => $date, 'mots' => [], 'images' => []];
$entry['mots'] = $mots;

// Traiter les images uploadées
if (!empty($_FILES['images']['name'][0])) {
    $nbExisting = count($entry['images']);
    $files      = $_FILES['images'];
    $count      = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $fileInfo = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];

        $num  = $nbExisting + $i + 1;
        $base = $galerie->processUpload($date, $fileInfo, $num);

        if (!$base) {
            header('Location: /admin/?page=edit&date=' . urlencode($date) . '&err=img');
            exit;
        }

        $entry['images'][] = ['base' => $base, 'ext' => 'jpg'];
    }
}
$galerie->upsert($entry);
header('Location: /admin/?page=edit&date=' . urlencode($date) . '&saved=1');
exit;
