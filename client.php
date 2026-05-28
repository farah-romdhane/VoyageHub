<?php
// --------- INITIALISATION SESSION ET AUTHENTIFICATION ----------
// session_save_path(__DIR__ . "/sessions"); // stockage sessions diro
session_start();  

// PROTECTION D’ACCÈS
if (!isset($_SESSION["email"], $_SESSION["token"])) {
  header("Location: connexion.php");
  exit;
}

// ----------- Connexion BD ----------
// Création de l'objet PDO pour se connecter à MySQL
// ATTR_ERRMODE => permet d'afficher les erreurs SQL sous forme d'exception

$bdd = new PDO(
  "mysql:host=localhost;dbname=demo4_users",
  "root",
  "root",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
// On récupère les infos stockées en session (si elles existent)
$email = $_SESSION["email"] ?? null;
$token = $_SESSION["token"] ?? null;

$user = null; // Contiendra les infos utilisateur si trouvé
$user_id = null; // ID utilisateur

// Si email ET token existent -> on vérifie en base de données
if ($email && $token) {
  $req = $bdd->prepare(
    "SELECT id, pseudo, email FROM users WHERE email = :email AND token = :token"
  );
  $req->execute([
    "email" => $email,
    "token" => $token
  ]);
  // Soit on récupère l'utilisateur, soit false
  $user = $req->fetch(); 
  if ($user) {
    $user_id = (int)$user["id"];
  }
}

// Si pas d'utilisateur trouvé -> on bloque l'accès
if (!$user) {
  // On affiche une page d'accès refusé
  // puis on arrête totalement l'exécution du script
  ?>
  <!DOCTYPE html>
  <html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    
  </head>
  <body>
    <p>Accès refusé</p>
    <a href="connexion.php">Connexion</a>
  </body>
  </html>
  <?php
  exit;
}

// --------- Recherche ----------
$search = trim($_GET["q"] ?? "");

// --------- Filtre ----------
$categorie = trim($_GET["categorie"] ?? ""); // ex: "Culture" ou "" (toutes)

// --------- Pagination ----------
/*
  on récupère le numéro de page dans l'URL (?page=2), ou 1 par défaut
  si rien n'est fourni , on prend 1 par défaut
  (max(1, qlqchose)) permet de forcer à au moins 1 (pas de page 0 ou négative)
*/
$page = max(1, (int)($_GET["page"] ?? 1));

$limit = 9; // nb maximum de voyages par page

/*
  calcul du point de départ (offset)
  exemple : si on a 23 voyages et qu'on affiche 9 par page, on aura besoin de 3 pages pour tout afficher
  23 / 9 = 2.55 => ceil(2.55) = 3
  page 1 , offset = (1-1)*9 = 0  => affiche les voyages 1 à 9
  page 2 , offset = (2-1)*9 = 9  => affiche les voyages 10 à 18
  page 3 , offset = (3-1)*9 = 18 => affiche les voyages 19 à 23
*/
$offset = ($page - 1) * $limit;

/*
  comptage total des voyages <<<POUR CET UTILISATEUR ET POUR LES FILTRES ACTUELS>>>
  
*/
$countSql = "SELECT COUNT(*) FROM voyages WHERE user_id = :uid";

// Si recherche texte active, on compte seulement ceux qui matchent
if ($search !== "") {
  $countSql .= " AND (
    titre LIKE :q
    OR description LIKE :q
    OR categorie LIKE :q
  )";
}

// Si filtre catégorie actif, on compte seulement cette catégorie
if ($categorie !== "") {
  $countSql .= " AND categorie = :cat";
}

$countStmt = $bdd->prepare($countSql);

// binder user (int)
$countStmt->bindValue(":uid", (int)$user_id, PDO::PARAM_INT);

// binder la recherche si elle est utilisée
if ($search !== "") {
  $countStmt->bindValue(":q", "%".$search."%", PDO::PARAM_STR);
}

// binder la catégorie si elle est utilisée
if ($categorie !== "") {
  $countStmt->bindValue(":cat", $categorie, PDO::PARAM_STR);
}

$countStmt->execute();

// récupération du total sous forme de nombre entier
$totalVoyages = (int)$countStmt->fetchColumn();

// calcul du nombre total de pages nécessaires (au moins 1 page)
$totalPages = max(1, (int)ceil($totalVoyages / $limit));


// --------- Sécurisation de la pagination ----------
/*
  Si l’utilisateur se retrouve sur une page invalide
  (ex : suppression d’éléments qui réduit le nombre total de pages),
  on le redirige automatiquement vers la dernière page valide.
  Note: on conserve aussi q et categorie dans l'URL, sinon il perd ses filtres.
*/
if ($page > $totalPages) {
  $redirect = "?page=".$totalPages;
  if ($search !== "")    $redirect .= "&q=".urlencode($search);
  if ($categorie !== "") $redirect .= "&categorie=".urlencode($categorie);
  header("Location: ".$redirect);
  exit;
}


// --------- Charger les voyages ----------
// On construit la requête étape par étape (user + recherche + filtre + pagination)
$sql = "
  SELECT id, titre, date_depart, date_retour, categorie, description, updated_at
  FROM voyages
  WHERE user_id = :uid
";

// Pagination: on garde LIMIT/OFFSET à la fin, mais on ajoute des filtres avant
// Paramètres obligatoires au départ: uniquement l'utilisateur

if ($search !== "") {
  // Recherche texte: on cherche dans titre/description/categorie
  // Exemple: q="paris" => titre LIKE "%paris%"
  $sql .= " AND (
    titre LIKE :q
    OR description LIKE :q
    OR categorie LIKE :q
  )";
}

if ($categorie !== "") {
  // Filtre catégorie EXACT:
  // Exemple: categorieFilter="Culture" => categorie = "Culture"
  $sql .= " AND categorie = :cat";
}

// Tri + pagination
$sql .= " ORDER BY date_depart ASC LIMIT :limit OFFSET :offset";

// Préparation
$stmt = $bdd->prepare($sql);

// Bind des paramètres => sécurisé + typé

// binder l'utilisateur (int)
$stmt->bindValue(":uid", (int)$user_id, PDO::PARAM_INT);

// Binder la recherche si elle est utilisée
if ($search !== "") {
  $stmt->bindValue(":q", "%".$search."%", PDO::PARAM_STR);
}

// Binder la catégorie si le filtre est utilisé
if ($categorie !== "") {
  $stmt->bindValue(":cat", $categorie, PDO::PARAM_STR);
}

// Binder pagination (int)
$stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);

// Exécution
$stmt->execute();

// Résultat: tableau associatif
$voyages = $stmt->fetchAll();


// Petite fonction utilitaire: afficher date proprement
function fmt_date($d) {
  if (!$d) return "";
  // $d est "YYYY-MM-DD"
  $parts = explode("-", $d);
  if (count($parts) !== 3) return $d;
  return $parts[2]."/".$parts[1]."/".$parts[0];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VoyageHub</title>
  <link rel="stylesheet" href="style.css?v=999">
</head>
<body>

<header class="topbar">
  <div class="brand">
    <h1 class="logo">VoyageHub</h1>
    <p class="welcome">
      Bienvenue, <span class="user"><?= htmlspecialchars($user["pseudo"]) ?></span>
    </p>
  </div>

  <div class="actions">
    <!-- btns: Nouveau voyage et Deconnexion -->
    <button type="button" class="btn primary" id="btn-add-trip">+ Nouveau voyage</button>
    <a class="btn ghost" href="deconnexion.php">Déconnexion</a>
  </div>
</header>

<main class="container">

  <!-- ===== Barre recherche / filtres ===== -->
  <section class="searchbar">
    <form method="get" action="client.php" class="searchform">
      <input type="hidden" name="page" value="1">
      <input
        id="search-q"
        type="text"
        name="q"
        placeholder="Rechercher par titre, description ou catégorie..."
        value="<?= htmlspecialchars($search) ?>"
        class="searchinput"
      >

      <!-- FILTRE PAR CATÉGORIE -->
      <select name="categorie" class="btn primary" id="select-categorie">
        <option value="">Filtrer par catégorie</option>
        <option value="culture" <?= $categorie === "culture" ? "selected" : "" ?>>Culture</option>
        <option value="aventure" <?= $categorie === "aventure" ? "selected" : "" ?>>Aventure</option>
        <option value="urbain" <?= $categorie === "urbain" ? "selected" : "" ?>>Urbain</option>
        <option value="detente" <?= $categorie === "detente" ? "selected" : "" ?>>Détente</option>
      </select>

    </form>
  </section>

  <!-- ===== Nouveau Voyage ===== -->

   <!-- 
  Cette fenêtre modale est cachée par défaut (class="hidden").
  Elle s’ouvre via JavaScript quand on clique sur "+ Nouveau voyage".-->

<div id="trip-modal" class="modal hidden" aria-hidden="true">
  <div class="modal-overlay" data-close="1"></div>

  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-header">
      <h2 id="modal-title" class="section-title">Nouveau voyage</h2>
      <button type="button" class="modal-close" data-close="1" aria-label="Fermer">✕</button>
    </div>

    <form id="form-new-trip" class="modal-form">
      <div class="row">
        <label>
          Titre *
          <input type="text" name="titre" required placeholder="Ex: Découverte de Paris">
        </label>

        <label>
          Catégorie
          <div class="select-wrapper">
          <select name="categorie">
            <option value="">Choisir une catégorie</option>
            <option value="culture">Culture</option>
            <option value="urbain">Urbain</option>
            <option value="aventure">Aventure</option>
            <option value="detente">Détente</option>
          </select>
          </div>
        </label>

      </div>

      <div class="row">
        <label>
          Date départ *
          <input type="date" name="date_depart" required>
        </label>

        <label>
          Date retour
          <input type="date" name="date_retour">
        </label>
      </div>

      <label>
        Description
        <textarea name="description" rows="3" placeholder="Ex: Tour Eiffel, Louvre, Montmartre..."></textarea>
      </label>

      <div class="row actions">
        <button type="submit" class="btn primary" id="submit-btn">Créer</button>
        <button class="btn ghost" type="reset">Effacer</button>
        <span id="newtrip-msg" class="msg"></span>
      </div>
    </form>
  </div>
</div>

<!-- MODAL CONFIRM DELETE -->
<div id="confirm-modal" class="confirm hidden">
  <div class="confirm-overlay"></div>

  <div class="confirm-box">
    <h3>Confirmer la suppression</h3>
    <p id="confirm-text"></p>

    <div class="confirm-actions">
      <button class="btn ghost" id="btn-cancel">Annuler</button>
      <button class="btn danger" id="btn-confirm">Supprimer</button>
    </div>
  </div>
</div>

  <!-------- Grille des tuiles -------->
  <section id="grid-voyages" class="<?= count($voyages) ? 'grid' : 'empty-host' ?>">
    <?php  
    // On vérifie s’il existe des voyages à afficher : si le tableau $voyages est vide (count($voyages) === 0), 
    // la page affiche un empty state contenant un message informatif, une icône et un bouton permettant de créer un premier voyage,
    // afin de guider l’utilisateur lorsqu’il n’a encore aucun contenu ; 
    // sinon (else), la grille des tuiles sera affichée avec les cartes correspondant aux voyages récupérés depuis la base de données.
    if (count($voyages) === 0): ?>

    <div class="empty-wrapper">
      <div id="empty-state" class="empty-state">
        <div class="empty-icon">✈️</div>
        <h3>Aucun voyage pour le moment</h3>
        <p>Commencez à documenter vos aventures !</p>

        <button class="btn primary btn-open-form">
          + Créer mon premier voyage
        </button>
      </div>
    </div>

    <?php else: ?>

      <?php foreach ($voyages as $v): ?>
        <article class="card"
        data-date-depart="<?= htmlspecialchars($v['date_depart']) ?>"
        data-date-retour="<?= htmlspecialchars($v['date_retour']) ?>">


          <!-- Zone image (placeholder) -->
          <div class="card-media">
              <?php
                $cat = strtolower($v["categorie"] ?? "");
                $image = "SC.jpg";

                if ($cat === "culture") $image = "culture.png";
                elseif ($cat === "urbain") $image = "urbaine.png";
                elseif ($cat === "aventure") $image = "aventure.png";
                elseif ($cat === "detente") $image = "detente.png";

              ?>

              <img src="image/<?= $image ?>" alt="<?= htmlspecialchars($cat) ?>">


              <?php if (!empty($v["categorie"])): ?>
                <span class="badge"><?= htmlspecialchars($v["categorie"]) ?></span>
              <?php else: ?>
                <span class="badge">Sans catégorie</span>
              <?php endif; ?>
            </div>


          <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($v["titre"]) ?></h3>

            <?php if (!empty($v["description"])): ?>
              <p class="card-desc"><?= htmlspecialchars($v["description"]) ?></p>
            <?php else: ?>
              <p class="card-desc muted">Aucune description.</p>
            <?php endif; ?>

            <ul class="meta">
              <li>Départ : <?= htmlspecialchars(fmt_date($v["date_depart"])) ?></li>

              <?php if (!empty($v["date_retour"])): ?>
                <li>Retour : <?= htmlspecialchars(fmt_date($v["date_retour"])) ?></li>
              <?php endif; ?>

              
            </ul>

            <div class="card-actions">
              <button class="btn ghost" type="button" data-id="<?= (int)$v["id"] ?>">Modifier</button>
              <button class="btn danger" type="button" data-id="<?= (int)$v["id"] ?>">Supprimer</button>
            </div>
          </div>

        </article>
      <?php endforeach; ?>
    <?php endif; ?>

  </section>
    


  <div id="pagination" class="pagination">
    <?php if ($totalPages > 1): ?>
  
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a 
        href="?page=<?= $i ?>
          <?= $search ? '&q='.urlencode($search) : '' ?>
          <?= $categorie ? '&categorie='.urlencode($categorie) : '' ?>"
          class="page-btn <?= $i === $page ? 'active' : '' ?>"
        >
          <?= $i ?>
        </a>
      <?php endfor; ?>
  
    <?php endif; ?>
  </div>


</main>
<script src="./appScript.js?v=<?= time() ?>"></script>
</body>
</html>