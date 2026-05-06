<?php
// =============================================================
//  admin/inc/AdminPropositions.php  —  stats des essais joueurs
// =============================================================

class AdminPropositions
{
    private array $data;

    public function __construct()
    {
        $path = ADMIN_JSON . '/propositions.json';
        $raw  = file_exists($path) ? file_get_contents($path) : '{"propositions":[]}';
        $this->data = json_decode($raw, true) ?? ['propositions' => []];
    }

    /** Stats pour toutes les dates : [date => [mot => count]] */
    public function allStats(): array
    {
        $stats = [];
        foreach ($this->data['propositions'] as $p) {
            $date = $p['date'];
            $mot  = $p['mot'];
            $stats[$date][$mot] = ($stats[$date][$mot] ?? 0) + 1;
        }
        // Trier chaque date par fréquence décroissante
        foreach ($stats as &$motsCounts) {
            arsort($motsCounts);
        }
        krsort($stats); // dates les plus récentes en premier
        return $stats;
    }

    /** Stats pour une date précise */
    public function statsForDate(string $date): array
    {
        $counts = [];
        foreach ($this->data['propositions'] as $p) {
            if ($p['date'] !== $date) continue;
            $mot = $p['mot'];
            $counts[$mot] = ($counts[$mot] ?? 0) + 1;
        }
        arsort($counts);
        return $counts;
    }

    /** Nombre total de propositions */
    public function total(): int
    {
        return count($this->data['propositions']);
    }
}
