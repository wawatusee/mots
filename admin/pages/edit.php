<?php
$galerie = new AdminGalerie();
$date    = $_GET['date'] ?? '';
$entry   = $date ? $galerie->findByDate($date) : null;
$isNew   = !$entry;

$mots    = $entry ? implode(', ', $entry['mots']) : '';
$images  = $entry['images'] ?? [];
$mois    = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',
            7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];
echo ADMIN_JSON;
?>

<main class="a-main">
  <div class="a-page-header">
    <h1 class="a-title"><?= $isNew ? 'Nouvelle entrée' : 'Modifier — ' . htmlspecialchars($date) ?></h1>
    <a href="/admin/" class="a-btn a-btn--ghost">← Retour</a>
  </div>

  <?php if (isset($_GET['saved'])): ?>
    <div class="a-alert a-alert--ok">Entrée enregistrée.</div>
  <?php endif; ?>

  <?php if (isset($_GET['err'])): ?>
    <div class="a-alert a-alert--err">
      <?= match($_GET['err']) {
        'date'  => 'Date invalide.',
        'mots'  => 'Au moins un mot est requis.',
        'img'   => 'Erreur lors du traitement d\'une image.',
        default => 'Une erreur est survenue.'
      } ?>
    </div>
  <?php endif; ?>

  <form method="post" action="/admin/" enctype="multipart/form-data" class="a-form a-form--wide">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="date_original" value="<?= htmlspecialchars($date) ?>">

    <!-- Date -->
    <div class="a-field">
      <label class="a-label" for="date">Date</label>
      <input class="a-input" type="date" id="date" name="date"
             value="<?= htmlspecialchars($entry['date'] ?? '') ?>"
             <?= !$isNew ? 'readonly' : '' ?> required>
      <?php if (!$isNew): ?>
        <span class="a-hint">La date ne peut pas être modifiée (clé primaire).</span>
      <?php endif; ?>
    </div>

    <!-- Mots -->
    <div class="a-field">
      <label class="a-label" for="mots">Mots <span class="a-hint-inline">(séparés par des virgules)</span></label>
      <input class="a-input" type="text" id="mots" name="mots"
             value="<?= htmlspecialchars($mots) ?>"
             placeholder="aigle, rapace" required>
    </div>

    <!-- Images existantes -->
    <?php if (!empty($images)): ?>
      <div class="a-field">
        <div class="a-label">Images actuelles</div>
        <div class="a-img-grid">
          <?php foreach ($images as $img):
            $src = ADMIN_IMG_URL . '/' . $img['base'] . '_sm.' . $img['ext'];
          ?>
            <div class="a-img-card">
              <img src="<?= htmlspecialchars($src) ?>"
                   alt="<?= htmlspecialchars($img['base']) ?>"
                   class="a-img-thumb"
                   loading="lazy">
              <div class="a-img-name"><?= htmlspecialchars($img['base']) ?></div>
              <form method="post" action="/admin/"
                    onsubmit="return confirm('Supprimer cette image ?')">
                <input type="hidden" name="action" value="delete_image">
                <input type="hidden" name="date"   value="<?= htmlspecialchars($date) ?>">
                <input type="hidden" name="base"   value="<?= htmlspecialchars($img['base']) ?>">
                <button type="submit" class="a-btn a-btn--sm a-btn--danger">Supprimer</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Upload nouvelles images -->
    <div class="a-field">
      <label class="a-label" for="images">
        <?= empty($images) ? 'Images' : 'Ajouter des images' ?>
        <span class="a-hint-inline">(JPG — plusieurs fichiers possibles)</span>
      </label>
      <input class="a-input-file" type="file" id="images" name="images[]"
             accept="image/jpeg,image/jpg" multiple>
      <div class="a-preview-grid" id="preview-grid"></div>
    </div>

    <div class="a-form-actions">
      <button class="a-btn a-btn--primary" type="submit">
        <?= $isNew ? 'Créer' : 'Enregistrer' ?>
      </button>
    </div>
  </form>
</main>

<script>
// Prévisualisation locale des images avant upload
document.getElementById('images').addEventListener('change', function () {
  const grid = document.getElementById('preview-grid');
  grid.innerHTML = '';
  Array.from(this.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const card = document.createElement('div');
      card.className = 'a-img-card';
      card.innerHTML = `<img src="${e.target.result}" class="a-img-thumb" alt="">
                        <div class="a-img-name">${file.name}</div>`;
      grid.appendChild(card);
    };
    reader.readAsDataURL(file);
  });
});
</script>
