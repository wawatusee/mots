# mots
When you see adrawing guess what word is represent
# Les mots du dessin

Galerie-jeu en PHP où le joueur doit deviner le mot associé au dessin du jour. Le débrief des essais fonctionne sur le principe du Mastermind : points verts pour les lettres bien placées, points bleus pour les lettres présentes mais mal placées.

---

## Arborescence

```
site/
├── config/
│   └── config.php              Configuration publique (chemins, constantes)
├── inc/
│   ├── head.php                Balises HTML <head>
│   ├── header.php              Barre du haut avec date
│   ├── main.php                Scène du jeu (dessin + débrief + saisie)
│   └── footer.php              Fermeture HTML
├── json/
│   ├── mots.json               Données du jeu (dates, mots, images)
│   └── propositions.json       Historique anonyme des essais joueurs
├── public/
│   ├── index.php               Contrôleur principal public
│   ├── api/
│   │   └── guess.php           API POST : validation d'un essai
│   ├── css/
│   │   ├── style.css           Styles globaux
│   │   └── portrait.css        Styles spécifiques au format portrait
│   ├── js/
│   │   └── game.js             Logique du jeu côté client
│   └── img/
│       └── content/            Images des dessins (générées par l'admin)
├── src/
│   └── model/
│       ├── Galerie.php         Lecture JSON, validation mots, score Mastermind
│       └── Propositions.php    Enregistrement des essais joueurs
├── admin/
│   ├── index.php               Routeur admin
│   ├── config/
│   │   └── config.php          Configuration admin (mot de passe, chemins)
│   ├── inc/
│   │   ├── auth.php            Gestion de session (login/logout)
│   │   ├── AdminGalerie.php    CRUD JSON + traitement images
│   │   ├── AdminPropositions.php Lecture stats joueurs
│   │   ├── head.php            HTML <head> admin
│   │   ├── foot.php            Fermeture HTML admin
│   │   └── topbar.php          Navigation admin
│   ├── pages/
│   │   ├── login.php           Formulaire de connexion
│   │   ├── liste.php           Liste de toutes les entrées
│   │   ├── edit.php            Formulaire ajout/modification
│   │   ├── action_save.php     Traitement POST (écriture JSON + images)
│   │   └── stats.php           Statistiques des propositions joueurs
│   └── css/
│       └── admin.css           Styles de l'interface admin
└── index.php                   Redirection vers public/
```

---

## Installation

### Prérequis

- PHP 8.1 ou supérieur
- Extension GD activée (pour le redimensionnement des images)
- Serveur web Apache, Nginx ou serveur de développement PHP intégré

### Extensions PHP requises

- `gd` — redimensionnement et conversion des images
- `mbstring` — gestion des chaînes UTF-8
- `iconv` — normalisation des accents

### Déploiement

**À la racine du serveur :**

Aucune configuration supplémentaire. Les chemins dans `config/config.php` et `inc/head.php` utilisent `/public/...` directement.

**Dans un sous-dossier (ex: `/mots`) :**

Dans `config/config.php`, définir :

```php
define('BASE_URL', '/mots');
```

Puis dans `inc/head.php` :

```php
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/portrait.css">
```

Dans `inc/main.php`, avant les balises `<script>` :

```php
<script>
const BASE_URL = '<?= BASE_URL ?>';
const ENTRY = <?= $entryJsonJs ?>;
</script>
```

Dans `public/js/game.js`, remplacer l'URL de l'API :

```javascript
const res = await fetch(BASE_URL + '/public/api/guess.php', {
```

### Permissions

Les dossiers suivants doivent être accessibles en écriture par le serveur web :

```
json/
public/img/content/
```

---

## Configuration

### Publique — `config/config.php`

```php
define('BASE_URL', '');                    // Sous-dossier si nécessaire (ex: '/mots')
define('ROOT',     dirname(__DIR__));      // Racine du projet
define('JSON_DIR', ROOT . '/json');        // Dossier des fichiers JSON
define('IMG_DIR',  ROOT . '/public/img/content'); // Dossier des images
define('IMG_URL',  BASE_URL . '/public/img/content'); // URL publique des images
```

### Admin — `admin/config/config.php`

```php
define('ADMIN_USER',     'admin');         // Identifiant
define('ADMIN_PASSWORD', 'motdepasse');    // Mot de passe — À CHANGER
define('ADMIN_IMG_SIZES', [               // Tailles générées à l'upload
    'sm'  => 400,
    'md'  => 800,
    'lg'  => 1200,
]);
define('ADMIN_IMG_QUALITY_JPG',  85);     // Qualité JPG (0-100)
define('ADMIN_IMG_QUALITY_WEBP', 82);     // Qualité WebP (0-100)
```

---

## Format des données

### `json/mots.json`

```json
{
  "entries": [
    {
      "date": "2026-01-01",
      "mots": ["lapin", "lièvre"],
      "images": [
        { "base": "2026-01-01_001", "ext": "jpg" },
        { "base": "2026-01-01_002", "ext": "jpg" }
      ]
    }
  ]
}
```

- `date` — format `YYYY-MM-DD`, clé primaire unique
- `mots` — tableau de mots acceptés (un seul suffit pour gagner)
- `images` — tableau d'images ; le `base` correspond au nom sans suffixe ni extension

### `json/propositions.json`

```json
{
  "propositions": [
    {
      "date": "2026-01-01",
      "mot": "canard",
      "correct": false,
      "timestamp": "2026-01-01 14:23:11"
    }
  ]
}
```

Les propositions sont anonymes — aucune donnée personnelle n'est collectée.

---

## Images

### Convention de nommage

Pour chaque image uploadée via l'admin, trois tailles sont générées automatiquement en JPG et WebP :

```
2026-01-01_001_sm.jpg    →  400px de large
2026-01-01_001_md.jpg    →  800px de large
2026-01-01_001_lg.jpg    → 1200px de large
2026-01-01_001_sm.webp
2026-01-01_001_md.webp
2026-01-01_001_lg.webp
```

Format : `{date}_{numéro}_{taille}.{extension}`

### Lazy-loading et formats adaptatifs

Les images sont servies via des balises `<picture>` avec `srcset`. Le navigateur choisit automatiquement le format (WebP si supporté, JPG sinon) et la taille adaptée à l'écran.

---

## Règles du jeu

1. Un dessin est affiché — le joueur doit deviner le mot associé
2. La saisie est **insensible à la casse et aux accents** (`Éléphant` = `elephant`)
3. Si le dessin a plusieurs mots acceptés, **un seul suffit pour gagner**
4. Après chaque essai raté, un débrief Mastermind s'affiche :
   - **Chiffre** = nombre total de lettres en commun
   - **Point vert** = bonne lettre, bonne position
   - **Point bleu** = bonne lettre, mauvaise position
   - Si plusieurs mots cibles, une ligne de débrief par mot (anonyme)
5. **"Langue au chat"** = abandon ; affiche "nouveau mot" dans le débrief et déverrouille la navigation
6. Le bouton **"suiv. →"** n'apparaît qu'après une victoire ou un abandon
7. L'état de chaque dessin (essais, victoire, abandon) est conservé en `sessionStorage` le temps de la session

---

## Interface admin

Accessible via `/admin/` (ou `/mots/admin/` en sous-dossier).

**Identifiants par défaut :**
- Utilisateur : `admin`
- Mot de passe : `motdepasse`

⚠️ Changer ces valeurs dans `admin/config/config.php` avant toute mise en production.

### Pages

| Page | URL | Description |
|------|-----|-------------|
| Connexion | `/admin/?page=login` | Formulaire de connexion |
| Liste | `/admin/` | Toutes les entrées triées par date |
| Nouvelle entrée | `/admin/?page=edit` | Créer une entrée avec date, mots et images |
| Modifier | `/admin/?page=edit&date=YYYY-MM-DD` | Modifier une entrée existante |
| Statistiques | `/admin/?page=stats` | Propositions joueurs par date |

### Upload d'images

- Format accepté : **JPG uniquement**
- Plusieurs images possibles par entrée
- Les variantes `_sm`, `_md`, `_lg` en JPG et WebP sont générées automatiquement
- La conversion WebP nécessite que la fonction `imagewebp()` soit disponible (GD)

---

## API

### `POST /public/api/guess.php`

Valide un essai et enregistre la proposition.

**Corps de la requête (JSON) :**
```json
{ "date": "2026-01-01", "mot": "lapin" }
```

**Réponse (JSON) :**
```json
{
  "correct": false,
  "scores": [
    { "green": 1, "blue": 2, "total": 3 },
    { "green": 0, "blue": 1, "total": 1 }
  ]
}
```

- `correct` — `true` si le mot est dans la liste des mots acceptés
- `scores` — un objet par mot cible dans l'ordre du JSON (les mots cibles ne sont jamais exposés)

---

## Sécurité

- Les mots associés aux dessins ne sont **jamais envoyés au navigateur** — la validation se fait entièrement côté serveur
- L'interface admin est protégée par session PHP
- Les propositions joueurs sont anonymes (pas d'IP, pas d'identifiant)
- Les fichiers JSON sont protégés contre les écritures simultanées via `LOCK_EX`

---

## Évolutions prévues

- Calendrier de l'avent comme mode de navigation alternatif
- Système de points selon le nombre d'essais
- Page publique d'affichage des propositions anonymes
- Navigation par date
