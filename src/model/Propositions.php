<?php
// =============================================================
//  src/model/Propositions.php  —  stockage JSON des essais
// =============================================================

class Propositions
{
    private string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = JSON_DIR . '/propositions.json';
    }

    /** Enregistrer un essai joueur */
    public function add(string $date, string $mot, bool $correct): void
    {
        $data = $this->load();
        $data['propositions'][] = [
            'date' => $date,
            'mot' => mb_strtolower(trim($mot), 'UTF-8'),
            'correct' => $correct,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
        $this->save($data);
    }

    /** Toutes les propositions pour une date donnée */
    public function forDate(string $date): array
    {
        $data = $this->load();
        return array_values(array_filter(
            $data['propositions'],
            fn($p) => $p['date'] === $date
        ));
    }

    /** Statistiques pour une date : [mot => count] triés par fréquence */
    public function statsForDate(string $date): array
    {
        $props = $this->forDate($date);
        $counts = [];
        foreach ($props as $p) {
            $m = $p['mot'];
            $counts[$m] = ($counts[$m] ?? 0) + 1;
        }
        arsort($counts);
        return $counts;
    }

    private function load(): array
    {
        if (!file_exists($this->jsonPath))
            return ['propositions' => []];
        $raw = file_get_contents($this->jsonPath);
        return json_decode($raw, true) ?? ['propositions' => []];
    }

    private function save(array $data): void
    {
        // Verrou fichier pour éviter les écritures simultanées
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->jsonPath, $json, LOCK_EX);
    }
}
