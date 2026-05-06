<main class="scene" role="main">

  <div class="mid-grid">

    <div class="col-drawing">
      <?php if ($entry && !empty($entry['images'])): ?>
        <?php foreach ($entry['images'] as $img): ?>
          <?php
            $base   = htmlspecialchars($img['base']);
            $ext    = htmlspecialchars($img['ext']);
            $imgUrl = IMG_URL;
          ?>
          <figure class="frame">
            <picture>
              <source
                type="image/webp"
                srcset="
                  <?= $imgUrl ?>/<?= $base ?>_sm.webp  400w,
                  <?= $imgUrl ?>/<?= $base ?>_md.webp  800w,
                  <?= $imgUrl ?>/<?= $base ?>_lg.webp 1200w
                "
                sizes="(max-width: 600px) 67vw, 33vw">
              <img
                src="<?= $imgUrl ?>/<?= $base ?>_md.<?= $ext ?>"
                srcset="
                  <?= $imgUrl ?>/<?= $base ?>_sm.<?= $ext ?>  400w,
                  <?= $imgUrl ?>/<?= $base ?>_md.<?= $ext ?>  800w,
                  <?= $imgUrl ?>/<?= $base ?>_lg.<?= $ext ?> 1200w
                "
                sizes="(max-width: 600px) 67vw, 33vw"
                alt="Dessin du <?= htmlspecialchars($entry['date']) ?>"
                loading="lazy"
                decoding="async"
                class="drawing-img">
            </picture>
            <figcaption class="frame-num">no. <?= str_pad($index + 1, 3, '0', STR_PAD_LEFT) ?></figcaption>
          </figure>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="frame frame--empty">
          <p class="frame-empty-msg">Aucun dessin pour cette date.</p>
        </div>
      <?php endif; ?>
    </div>

    <aside class="col-debrief" id="col-debrief" aria-label="Historique des essais">
      <div class="debrief-label">essais</div>
    </aside>

  </div>

  <div class="bottom-zone" id="bottom-zone">
    <div class="hint-line" id="hint-line">
      <?php if ($entry): ?>
        <?= count($entry['mots']) > 1 ? count($entry['mots']) . ' mots acceptés' : '1 mot à trouver' ?>
      <?php endif; ?>
    </div>
    <div class="input-row">
      <input
        class="word-input"
        id="word-input"
        type="text"
        placeholder="votre mot…"
        autocomplete="off"
        autocorrect="off"
        autocapitalize="off"
        spellcheck="false"
        aria-label="Votre proposition">
      <button class="send-btn" id="send-btn" aria-label="Soumettre">→</button>
    </div>
    <div class="fb-row">
      <div class="fb" id="fb" role="status" aria-live="polite"></div>
      <button class="tongue-btn" id="tongue-btn">langue au chat</button>
    </div>
  </div>

  <nav class="botbar" aria-label="Navigation entre dessins">
    <button class="ghost-btn" id="prev-btn" <?= $index === 0 ? 'disabled' : '' ?>>← préc.</button>
    <div class="center-slot" id="center-slot"></div>
    <button class="next-btn" id="next-btn">suiv. →</button>
  </nav>

</main>

<script>
const ENTRY = <?= $entryJsonJs ?>;
</script>
<script src="/public/js/game.js" defer></script>