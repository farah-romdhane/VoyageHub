<?php
// a modifer: ajouter les tuiles ici
session_start();  //on doit le permettre car il est possible qu'à ce stade la personne s'est bien connectée donc on doit initla session

$bdd = new PDO(
  "mysql:host=localhost;dbname=demo4_users",
  "root",
  "root",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
// On demande au navigateur "Est ce qu'une session est en cours? cette personne avec tels email +token a t'elle initialisé une connexion?
$email = $_SESSION["email"] ?? null;
$token = $_SESSION["token"] ?? null;

$user = null; //on ne sait pas a ce stade de qui on parle!!!

if ($email && $token) {
  $req = $bdd->prepare(
    "SELECT pseudo, email FROM users WHERE email = :email AND token = :token"
  );
  $req->execute([
    "email" => $email,
    "token" => $token
  ]);
  $user = $req->fetch(); //ici on aura recu une réponse de la db; soit l'utilisateur, soit une table vide
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>VoyageHub</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if ($user): ?> <!-- utilisateur connecté -->

  <header>
    <div>
      <h1>VoyageHub</h1>
      <p>
        Bienvenue,
        <span id="user-name"><?= htmlspecialchars($user["pseudo"]) ?></span>
      </p>
    </div>

    <div>
      <button type="button" id="btn-add-trip">+ Nouveau voyage</button>
      <a href="deconnexion.php">Déconnexion</a>
    </div>
  </header>

  <main>
    <!-- les tuiles voyages -->
  </main>

<?php else: ?> <!-- utilisateur NON connecté -->

  <p>Accès refusé</p>
  <a href="connexion.php">Connexion</a>

<?php endif; ?>
<?php
// Récupération du terme de recherche (GET)
$search = $_GET["q"] ?? "";
?>
<!-- ================= RECHERCHE / FILTRES ================= -->
<section id="barre_recherche">

  <form method="get" action="home.php">
    <input
      type="text"
      name="q"
      placeholder="Rechercher par titre, description ou destination..."
      value="<?= htmlspecialchars($search) ?>"
    >

    <!-- Bouton filtres -->
    <button type="button" id="bouton_filtres">
      Filtres
    </button>

    <!-- Bouton rechercher -->
    <button type="submit" id="bouton_rechercher">
      Rechercher
    </button>

  </form>

</section>
</body>
</html>

