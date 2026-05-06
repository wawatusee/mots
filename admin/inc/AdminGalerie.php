<?php
// =============================================================
//  admin/inc/AdminGalerie.php  —  CRUD sur mots.json
// =============================================================

class AdminGalerie
{
    private string $jsonPath;
    private array  $data;

    public function __construct()
    {
        $this->jsonPath = ADMIN_JSON . '/mots.json';
        $this->load();
    }

    // --- Lecture --------------------------------------------

    public function all(): array
    {
        $entries = $this->data['entries'];
        usort($entries, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $entries;
    }

    public function findByDate(string $date): ?array
    {
        foreach ($this->data['entries'] as $entry) {
            if ($entry['date'] === $date) return $entry;
        }
        return null;
    }

    // --- Écriture -------------------------------------------

    /** Ajouter ou mettre à jour une entrée */
    public function upsert(array $entry): void
    {
        $date = $entry['date'];
        foreach ($this->data['entries'] as &$e) {
            if ($e['date'] === $date) {
                $e = $entry;
                $this->save();
                return;
            }
        }
        $this->data['entries'][] = $entry;
        $this->save();
    }

    /** Supprimer une entrée par date (et ses images) */
    public function delete(string $date): void
    {
        $entry = $this->findByDate($date);
        if ($entry) {
            // Supprimer les fichiers images associés
            foreach ($entry['images'] ?? [] as $img) {
                $this->deleteImageFiles($img['base'], $img['ext']);
            }
        }
        $this->data['entries'] = array_values(array_filter(
            $this->data['entries'],
            fn($e) => $e['date'] !== $date
        ));
        $this->save();
    }

    /** Supprimer une image d'une entrée */
    public function deleteImage(string $date, string $base): void
    {
        foreach ($this->data['entries'] as &$entry) {
            if ($entry['date'] !== $date) continue;
            $entry['images'] = array_values(array_filter(
                $entry['images'] ?? [],
                fn($img) => $img['base'] !== $base
            ));
            $this->deleteImageFiles($base, 'jpg');
            break;
        }
        $this->save();
    }

    // --- Images ---------------------------------------------

    /**
     * Traiter un fichier uploadé :
     * - générer _sm, _md, _lg en JPG et WebP
     * - retourner le nom de base généré
     */
    public function processUpload(string $date, array $file, int $num): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $base = $date . '_' . str_pad($num, 3, '0', STR_PAD_LEFT);

        // Créer le dossier de destination si nécessaire
        if (!is_dir(ADMIN_IMG)) {
            mkdir(ADMIN_IMG, 0755, true);
        }

        // Charger l'image source
        $src = $this->loadImage($file['tmp_name'], $file['type']);
        if (!$src) return null;

        foreach (ADMIN_IMG_SIZES as $suffix => $width) {
            $resized = $this->resize($src, $width);

            // JPG
            $pathJpg = ADMIN_IMG . '/' . $base . '_' . $suffix . '.jpg';
            imagejpeg($resized, $pathJpg, ADMIN_IMG_QUALITY_JPG);

            // WebP (si disponible)
            if (function_exists('imagewebp')) {
                $pathWebp = ADMIN_IMG . '/' . $base . '_' . $suffix . '.webp';
                imagewebp($resized, $pathWebp, ADMIN_IMG_QUALITY_WEBP);
            }

            imagedestroy($resized);
        }
        imagedestroy($src);

        return $base;
    }

    // --- Privé ----------------------------------------------

    private function load(): void
    {
        $raw = file_exists($this->jsonPath)
            ? file_get_contents($this->jsonPath)
            : '{"entries":[]}';
        $this->data = json_decode($raw, true) ?? ['entries' => []];
    }

private function save(): void
{
$json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($this->jsonPath, $json, LOCK_EX);
}

    private function deleteImageFiles(string $base, string $ext): void
    {
        $suffixes = ['sm', 'md', 'lg'];
        foreach ($suffixes as $s) {
            foreach (['jpg', 'webp'] as $fmt) {
                $path = ADMIN_IMG . '/' . $base . '_' . $s . '.' . $fmt;
                if (file_exists($path)) unlink($path);
            }
        }
    }

    private function loadImage(string $path, string $mime): \GdImage|false
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png'               => imagecreatefrompng($path),
            'image/webp'              => imagecreatefromwebp($path),
            default                   => false,
        };
    }

    private function resize(\GdImage $src, int $targetWidth): \GdImage
    {
        $srcW = imagesx($src);
        $srcH = imagesy($src);

        // Ne pas agrandir
        if ($srcW <= $targetWidth) {
            $targetWidth  = $srcW;
            $targetHeight = $srcH;
        } else {
            $targetHeight = (int)round($srcH * $targetWidth / $srcW);
        }

        $dst = imagecreatetruecolor($targetWidth, $targetHeight);

        // Préserver la transparence si PNG
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        imagecopyresampled($dst, $src, 0, 0, 0, 0,
            $targetWidth, $targetHeight, $srcW, $srcH);

        return $dst;
    }
}
