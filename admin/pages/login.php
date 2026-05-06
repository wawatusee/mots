<?php $error = isset($_GET['error']); ?>

<div class="a-login-wrap">
  <div class="a-login-box">
    <div class="a-logo" style="margin-bottom:24px;">
      Les <em>mots</em> du dessin <span class="a-badge">admin</span>
    </div>

    <?php if ($error): ?>
      <div class="a-alert a-alert--err">Identifiants incorrects.</div>
    <?php endif; ?>

    <form method="post" action="/admin/" class="a-form">
      <input type="hidden" name="action" value="login">

      <div class="a-field">
        <label class="a-label" for="user">Identifiant</label>
        <input class="a-input" type="text" id="user" name="user"
               autocomplete="username" autofocus required>
      </div>

      <div class="a-field">
        <label class="a-label" for="pass">Mot de passe</label>
        <input class="a-input" type="password" id="pass" name="pass"
               autocomplete="current-password" required>
      </div>

      <button class="a-btn a-btn--primary" type="submit">Connexion</button>
    </form>
  </div>
</div>
