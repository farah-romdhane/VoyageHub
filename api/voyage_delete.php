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

/* ---------- Helpers JSON ---------- */
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

/* ---------- Méthode ---------- */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  json_error("Méthode non autorisée (POST requis).", 405);
}

/* ---------- Connexion BD ---------- */
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

/* ---------- Auth via session ---------- */
$email = $_SESSION["email"] ?? null;
$token = $_SESSION["token"] ?? null;

if (!$email || !$token) {
  json_error("Non authentifié.", 401);
}

$req = $bdd->prepare(
  "SELECT id FROM users WHERE email = :email AND token = :token"
);
$req->execute([
  "email" => $email,
  "token" => $token
]);
$row = $req->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  json_error("Session invalide.", 401);
}

$user_id = (int)$row["id"];

/* ---------- Input ---------- */
$id = (int)($_POST["id"] ?? 0);
if ($id <= 0) {
  json_error("ID invalide.");
}

/* ---------- DELETE sécurisé ---------- */
$stmt = $bdd->prepare(
  "DELETE FROM voyages WHERE id = :id AND user_id = :user_id"
);
$stmt->execute([
  "id" => $id,
  "user_id" => $user_id
]);

if ($stmt->rowCount() === 0) {
  json_error("Voyage introuvable ou non autorisé.", 404);
}

/* ---------- OK ---------- */
json_ok([
  "voyage" => [
    "id" => $id
  ]
]);