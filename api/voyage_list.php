<?php
// Mode strict : force PHP à respecter les types (meilleure sécurité / fiabilité)
declare(strict_types=1);

// Indique que ce script retourne du JSON (API côté serveur)
header("Content-Type: application/json; charset=utf-8");

// Démarrage de la session (pour récupérer email + token)
//session_save_path(__DIR__ . "/../sessions"); // stockage sessions sur diro
session_start();

// PROTECTION D’ACCÈS
if (!isset($_SESSION["email"], $_SESSION["token"])) {
  header("Location: connexion.php");
  exit;
}

/**
 * Envoie une réponse JSON d’erreur et termine le script.
 * @param string $msg  Message d’erreur à retourner
 * @param int    $code Code HTTP (400, 401, 500, etc.)
 */
function json_error(string $msg, int $code = 400): void {
  http_response_code($code);
  echo json_encode(
    ["ok" => false, "error" => $msg],
    JSON_UNESCAPED_UNICODE
  );
  exit;
}

  // Connexion à la base de données

try {
  $bdd = new PDO(
    "mysql:host=localhost;dbname=demo4_users",
    "root",
    "root",
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION // Exceptions en cas d’erreur SQL
    ]
  );
} catch (PDOException $e) {
  // Erreur serveur : impossible de se connecter à la BD
  json_error("Erreur connexion BD.", 500);
}

// Récupération des infos de session
$email = $_SESSION["email"] ?? null;
$token = $_SESSION["token"] ?? null;

// Si aucune session valide => accès refusé
if (!$email || !$token) {
  json_error("Non authentifié.", 401);
}

// Vérification que le token correspond bien à l’utilisateur en BD
$req = $bdd->prepare(
  "SELECT id FROM users WHERE email = :email AND token = :token"
);
$req->execute([
  "email" => $email,
  "token" => $token
]);

$row = $req->fetch(PDO::FETCH_ASSOC);

// Token invalide ou session expirée
if (!$row) {
  json_error("Session invalide.", 401);
}

// ID utilisateur utilisé pour filtrer ses voyages uniquement
$user_id = (int)$row["id"];


// Recherche texte (titre, description ou catégorie)
$search = trim($_GET["q"] ?? "");

// Filtre par catégorie exacte
$categorie = trim($_GET["categorie"] ?? "");

// Pagination
$page = max(1, (int)($_GET["page"] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;
// COUNT total (avec mêmes filtres)
$countSql = "SELECT COUNT(*) FROM voyages WHERE user_id = :uid";

if ($search !== "") {
  $countSql .= " AND (
    titre LIKE :q
    OR description LIKE :q
    OR categorie LIKE :q
  )";
}

if ($categorie !== "") {
  $countSql .= " AND categorie = :cat";
}

$countStmt = $bdd->prepare($countSql);
$countStmt->bindValue(":uid", $user_id, PDO::PARAM_INT);

if ($search !== "") {
  $countStmt->bindValue(":q", "%".$search."%", PDO::PARAM_STR);
}
if ($categorie !== "") {
  $countStmt->bindValue(":cat", $categorie, PDO::PARAM_STR);
}

$countStmt->execute();
$totalVoyages = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalVoyages / $limit));

//Si page invalide, on la ramène dans [1..totalPages] 
if ($page > $totalPages) {
  $page = $totalPages;
  $offset = ($page - 1) * $limit;
}

// Select query
$sql = "
  SELECT
    id,
    titre,
    date_depart,
    date_retour,
    categorie,
    description,
    updated_at
  FROM voyages
  WHERE user_id = :uid
";

if ($search !== "") {
  $sql .= " AND (
    titre LIKE :q
    OR description LIKE :q
    OR categorie LIKE :q
  )";
}

if ($categorie !== "") {
  $sql .= " AND categorie = :cat";
}

$sql .= " ORDER BY date_depart ASC LIMIT :limit OFFSET :offset";

$stmt = $bdd->prepare($sql);

$stmt->bindValue(":uid", $user_id, PDO::PARAM_INT);

if ($search !== "") {
  $stmt->bindValue(":q", "%".$search."%", PDO::PARAM_STR);
}

if ($categorie !== "") {
  $stmt->bindValue(":cat", $categorie, PDO::PARAM_STR);
}

$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

$stmt->execute();
$voyages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Reponse
echo json_encode(
  [
    "ok" => true,
    "voyages" => $voyages,
    "page" => $page,
    "totalPages" => $totalPages,
    "totalVoyages" => $totalVoyages,
    "limit" => $limit
  ],
  JSON_UNESCAPED_UNICODE
);
exit;