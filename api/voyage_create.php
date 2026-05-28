<?php
declare(strict_types=1);
header("Content-Type: application/json; charset=utf-8");

//session_save_path(__DIR__ . "/../sessions"); // stockage sessions sur diro
session_start();

// PROTECTION D’ACCÈS
if (!isset($_SESSION["email"], $_SESSION["token"])) {
  header("Location: connexion.php");
  exit;
}

function json_error(string $msg, int $code = 400): void {
  http_response_code($code);
  echo json_encode(["ok" => false, "error" => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

function json_ok(array $data = [], int $code = 200): void {
  http_response_code($code);
  echo json_encode(array_merge(["ok" => true], $data), JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  json_error("Méthode non autorisée (POST requis).", 405);
}

// Connexion DB
try {
  $bdd = new PDO(
    "mysql:host=localhost;dbname=demo4_users",
    "root",
    "root",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (PDOException $e) {
  json_error("Erreur connexion BD.", 500);
}

// Auth via session + token
$email = $_SESSION["email"] ?? null;
$token = $_SESSION["token"] ?? null;

if (!$email || !$token) {
  json_error("Non authentifié.", 401);
}

$req = $bdd->prepare("SELECT id FROM users WHERE email = :email AND token = :token");
$req->execute(["email" => $email, "token" => $token]);
$row = $req->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  json_error("Session invalide.", 401);
}

$user_id = (int)$row["id"];

// Récupérer + valider inputs
$titre = trim((string)($_POST["titre"] ?? ""));
$date_depart = trim((string)($_POST["date_depart"] ?? ""));
$date_retour = trim((string)($_POST["date_retour"] ?? ""));
$categorie = trim((string)($_POST["categorie"] ?? ""));
$description = trim((string)($_POST["description"] ?? ""));

if ($titre === "") {
  json_error("Le titre est obligatoire.");
}
if ($date_depart === "") {
  json_error("La date de départ est obligatoire.");
}

// Validation simple des dates (format YYYY-MM-DD)
$re_date = '/^\d{4}-\d{2}-\d{2}$/';
if (!preg_match($re_date, $date_depart)) {
  json_error("date_depart doit être au format YYYY-MM-DD.");
}
if ($date_retour !== "" && !preg_match($re_date, $date_retour)) {
  json_error("date_retour doit être au format YYYY-MM-DD (ou vide).");
}

// Vérif logique: retour >= départ
if ($date_retour !== "" && strcmp($date_retour, $date_depart) < 0) {
  json_error("La date de retour doit être après (ou égale à) la date de départ.");
}

// Normaliser les champs optionnels : si vide => NULL
$categorie = ($categorie === "") ? null : $categorie;
$description = ($description === "") ? null : $description;
$date_retour = ($date_retour === "") ? null : $date_retour;

// INSERT
try {
  $stmt = $bdd->prepare("
    INSERT INTO voyages (user_id, titre, date_depart, date_retour, categorie, description)
    VALUES (:user_id, :titre, :date_depart, :date_retour, :categorie, :description)
  ");

  $stmt->execute([
    "user_id" => $user_id,
    "titre" => $titre,
    "date_depart" => $date_depart,
    "date_retour" => $date_retour,
    "categorie" => $categorie,
    "description" => $description
  ]);

  $newId = (int)$bdd->lastInsertId();

  // Renvoie un objet voyage minimal (utile pour mettre à jour l’UI)
  json_ok([
    "id" => $newId,
    "voyage" => [
      "id" => $newId,
      "user_id" => $user_id,
      "titre" => $titre,
      "date_depart" => $date_depart,
      "date_retour" => $date_retour,
      "categorie" => $categorie,
      "description" => $description
    ]
  ], 201);

} catch (PDOException $e) {
  json_error("Erreur lors de la création du voyage.", 500);
}