<?php
$props = new AdminPropositions();
$stats = $props->allStats();
$total = $props->total();
$mois  = [1=>'jan',2=>'fév',3=>'mar',4=>'avr',5=>'mai',6=>'jun',
          7=>'jul',8=>'aoû',9=>'sep',10=>'oct',11=>'nov',12=>'déc'];
?>

<main class="a-main">
  <div class="a-page-header">
    <h1 class="a-title">Propositions joueurs <span class="a-count"><?= $total ?></span></h1>
  </div>

  <?php if (empty($stats)): ?>
    <p class="a-empty">Aucune proposition enregistrée pour l'instant.</p>
  <?php else: ?>
    <?php foreach ($stats as $date => $motsCounts):
      $dt  = new DateTime($date);
      $day = $dt->format('j');
      $mon = $mois[(int)$dt->format('n')];
      $yr  = $dt->format('Y');
      $totalDate = array_sum($motsCounts);
      $max = max($motsCounts);
    ?>
      <section class="a-stats-section">
        <div class="a-stats-header">
          <span class="a-date-day"><?= $day ?></span>
          <span class="a-date-mon"><?= $mon ?> <?= $yr ?></span>
          <span class="a-stats-total"><?= $totalDate ?> essai<?= $totalDate > 1 ? 's' : '' ?></span>
        </div>
        <div class="a-stats-bars">
          <?php foreach ($motsCounts as $mot => $count):
            $pct = $max > 0 ? round($count / $max * 100) : 0;
          ?>
            <div class="a-bar-row">
              <div class="a-bar-mot"><?= htmlspecialchars($mot) ?></div>
              <div class="a-bar-track">
                <div class="a-bar-fill" style="width:<?= $pct ?>%"></div>
              </div>
              <div class="a-bar-count"><?= $count ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>
</main>
