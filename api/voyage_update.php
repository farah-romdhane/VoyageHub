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
  echo json_encode(["ok" => false, "error" => $msg]);
  exit;
}

function json_ok(array $data = []): void {
  echo json_encode(array_merge(["ok" => true], $data));
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  json_error("POST requis", 405);
}

$id = (int)($_POST["id"] ?? 0);
if ($id <= 0) json_error("ID invalide");

$email = $_SESSION["email"] ?? null;
$token = $_SESSION["token"] ?? null;
if (!$email || !$token) json_error("Non authentifié", 401);

$bdd = new PDO(
  "mysql:host=localhost;dbname=demo4_users",
  "root",
  "root",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$req = $bdd->prepare("SELECT id FROM users WHERE email=:e AND token=:t");
$req->execute(["e"=>$email,"t"=>$token]);
$user = $req->fetch();
if (!$user) json_error("Session invalide", 401);

$stmt = $bdd->prepare("
  UPDATE voyages
  SET titre=:titre,
      categorie=:categorie,
      description=:description,
      date_depart=:dd,
      date_retour=:dr
  WHERE id=:id AND user_id=:uid
");

$stmt->execute([
  "titre" => $_POST["titre"] ?? "",
  "categorie" => $_POST["categorie"] ?: null,
  "description" => $_POST["description"] ?: null,
  "dd" => $_POST["date_depart"],
  "dr" => $_POST["date_retour"] ?: null,
  "id" => $id,
  "uid" => $user["id"]
]);

json_ok();