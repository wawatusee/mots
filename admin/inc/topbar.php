<header class="a-topbar">
  <div class="a-logo">Les <em>mots</em> du dessin <span class="a-badge">admin</span></div>
  <nav class="a-nav">
    <a href="/admin/" class="a-nav-link <?= (($page ?? 'liste') === 'liste') ? 'active' : '' ?>">Entrées</a>
    <a href="/admin/?page=edit" class="a-nav-link <?= (($page ?? '') === 'edit' && !isset($_GET['date'])) ? 'active' : '' ?>">+ Nouvelle</a>
    <a href="/admin/?page=stats" class="a-nav-link <?= (($page ?? '') === 'stats') ? 'active' : '' ?>">Stats</a>
    <form method="post" action="/admin/" style="display:inline;">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="a-logout">Déconnexion</button>
    </form>
  </nav>
</header>
