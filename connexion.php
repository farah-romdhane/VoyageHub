<?php
// session_save_path(__DIR__ . "/sessions"); pour stockage sessions diro
session_start(); // car il existe le cas ou apres avoir terminé l'action de connexion -> la personne est authentifiée

// STEP 1: on parle de quelle db? on identifie notre db avec le server ou elle est host et les info pour la consulter sur phpmyadmin
// ici, on l'instancie immédiatement (c'est au choix)
$bdd = new PDO(
  "mysql:host=localhost;dbname=demo4_users",
  "root",
  "root",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$error_msg = ""; //vide pour l'instant

//STEP 3: pour pouvoir faire une connexion, on doit s'assurer qu'on a recu ces informations via POST

if ($_SERVER["REQUEST_METHOD"] === "POST") {

//STEP 4: on commence la validation -> on récupere chaque "input" du form et on les stocke dans des variables php pour pouvoir les manipuler
  $nom = $_POST["nom"];
  $email = $_POST["email"];
  $pass = $_POST["pass"];

   // "Trouve moi une entrée ou l'information recu en POST est déja la" (est ce que cette personne peut se connecter ou elle doit s'inscrire avant?)
  $req = $bdd->prepare(
    "SELECT id, mdp FROM users WHERE email = :email"
  );
  $req->execute(["email" => $email]);
  $user = $req->fetch();

  //SI ELLE A LA BONNE COMBINAISON DE CREDENTIALS:
  if ($user && password_verify($pass, $user["mdp"])) {
    //Le token servira a permettre une persistance de connexion ; si elle change de page elle va pas etre déconnectée
    $token = bin2hex(random_bytes(16));
    // cette information est variable; a chaque connexion on créera un token
    //on faudra donc UPDATE La table (on ne crée pas une nouvelle entrée, on modifie)
    $bdd->prepare(
      "UPDATE users SET token = :token WHERE id = :id"
    )->execute([
      "token" => $token,
      "id" => $user["id"]
    ]);

    // On stocke dans SESSION ces information, ca allège le backend;
    // on ne demandera pas au serveur de vérifier si la personne est connectée, on demande au navigateur)
    $_SESSION["email"] = $email;
    $_SESSION["token"] = $token;

    header("Location: client.php"); //REDIRECTION AUTOMATIQUE
    exit;
  } else {
    $error_msg = "Identifiants incorrects.";
  }
}
require __DIR__ ."/accueil.php" ;
?>