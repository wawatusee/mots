<header class="topbar" role="banner">
  <div class="logo">Les <em>mots</em> du dessin</div>
  <div class="day-label" id="day-label">
    <?php
    if ($entry) {
        $mois = [
            1=>'janvier',2=>'février',3=>'mars',4=>'avril',
            5=>'mai',6=>'juin',7=>'juillet',8=>'août',
            9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'
        ];
        $dt = new DateTime($entry['date']);
        echo htmlspecialchars(
            $dt->format('j') . ' ' . $mois[(int)$dt->format('n')] . ' ' . $dt->format('Y')
        );
    }
    ?>
  </div>
</header>