<?php
$galerie = new AdminGalerie();
$entries = $galerie->all();
$mois = [1=>'jan',2=>'fév',3=>'mar',4=>'avr',5=>'mai',6=>'jun',
         7=>'jul',8=>'aoû',9=>'sep',10=>'oct',11=>'nov',12=>'déc'];
?>

<main class="a-main">
  <div class="a-page-header">
    <h1 class="a-title">Entrées <span class="a-count"><?= count($entries) ?></span></h1>
    <a href="/admin/?page=edit" class="a-btn a-btn--primary">+ Nouvelle entrée</a>
  </div>

  <?php if (empty($entries)): ?>
    <p class="a-empty">Aucune entrée pour l'instant.</p>
  <?php else: ?>
    <div class="a-table-wrap">
      <table class="a-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Mots</th>
            <th>Images</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($entries as $e):
            $dt  = new DateTime($e['date']);
            $day = $dt->format('j');
            $mon = $mois[(int)$dt->format('n')];
            $yr  = $dt->format('Y');
            $nbImg = count($e['images'] ?? []);
          ?>
          <tr>
            <td class="a-td-date">
              <span class="a-date-day"><?= $day ?></span>
              <span class="a-date-mon"><?= $mon ?> <?= $yr ?></span>
            </td>
            <td class="a-td-mots">
              <?php foreach ($e['mots'] as $m): ?>
                <span class="a-tag"><?= htmlspecialchars($m) ?></span>
              <?php endforeach; ?>
            </td>
            <td class="a-td-imgs">
              <?= $nbImg ?> image<?= $nbImg > 1 ? 's' : '' ?>
            </td>
            <td class="a-td-actions">
              <a href="/admin/?page=edit&date=<?= urlencode($e['date']) ?>"
                 class="a-btn a-btn--sm">Modifier</a>
              <form method="post" action="/admin/" style="display:inline;"
                    onsubmit="return confirm('Supprimer cette entrée ?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="date" value="<?= htmlspecialchars($e['date']) ?>">
                <button type="submit" class="a-btn a-btn--sm a-btn--danger">Supprimer</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
