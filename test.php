<?php
echo "Chemin actuel : " . __DIR__;
echo "<br>Fichier public/index.php existe : " . (file_exists(__DIR__ . '/public/index.php') ? 'OUI' : 'NON');
?>