<?php
// =============================================================
//  src/model/Galerie.php  —  accès aux données mots.json
// =============================================================

class Galerie
{
    private string $jsonPath;
    private array  $data;

    public function __construct()
    {
        $this->jsonPath = JSON_DIR . '/mots.json';
        $raw = file_get_contents($this->jsonPath);
        $this->data = json_decode($raw, true) ?? ['entries' => []];
    }

    /** Toutes les entrées triées par date croissante */
    public function all(): array
    {
        $entries = $this->data['entries'];
        usort($entries, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $entries;
    }

    /** Entrée du jour (date Y-m-d) */
    public function today(): ?array
    {
        $today = date('Y-m-d');
        return $this->findByDate($today);
    }

    /** Entrée par date */
    public function findByDate(string $date): ?array
    {
        foreach ($this->data['entries'] as $entry) {
            if ($entry['date'] === $date) return $entry;
        }
        return null;
    }

    /** Entrée par index (0-based, trié par date) */
    public function findByIndex(int $index): ?array
    {
        $all = $this->all();
        return $all[$index] ?? null;
    }

    /** Index d'une date dans la liste triée */
    public function indexOfDate(string $date): int
    {
        foreach ($this->all() as $i => $entry) {
            if ($entry['date'] === $date) return $i;
        }
        return 0;
    }

    /** Nombre total d'entrées */
    public function count(): int
    {
        return count($this->data['entries']);
    }

    /**
     * Vérifier si un mot proposé correspond à une entrée.
     * Insensible à la casse et aux accents.
     */
    public static function checkMot(string $guess, array $entry): bool
    {
        $g = self::normalize($guess);
        foreach ($entry['mots'] as $mot) {
            if (self::normalize($mot) === $g) return true;
        }
        return false;
    }

    /** Normalisation : minuscules + suppression accents (sans extension intl) */
    public static function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s), 'UTF-8');
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        $s = preg_replace('/[^a-z0-9]/u', '', $s);
        return $s;
    }

    /**
     * Score Mastermind pour un essai vs un mot cible.
     * Retourne ['green'=>int, 'blue'=>int, 'total'=>int]
     */
    public static function mastermindScore(string $guess, string $target): array
    {
        $g = mb_str_split(self::normalize($guess));
        $t = mb_str_split(self::normalize($target));
        $green = 0;
        $blue  = 0;
        $usedT = array_fill(0, count($t), false);
        $usedG = array_fill(0, count($g), false);

        // Pass 1 : verts (bonne lettre, bonne position)
        $len = min(count($g), count($t));
        for ($i = 0; $i < $len; $i++) {
            if ($g[$i] === $t[$i]) {
                $green++;
                $usedT[$i] = true;
                $usedG[$i] = true;
            }
        }
        // Pass 2 : bleus (bonne lettre, mauvaise position)
        for ($i = 0; $i < count($g); $i++) {
            if ($usedG[$i]) continue;
            for ($j = 0; $j < count($t); $j++) {
                if (!$usedT[$j] && $g[$i] === $t[$j]) {
                    $blue++;
                    $usedT[$j] = true;
                    $usedG[$i] = true;
                    break;
                }
            }
        }
        return ['green' => $green, 'blue' => $blue, 'total' => $green + $blue];
    }
}
