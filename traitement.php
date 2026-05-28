<?php
// STEP 1: on parle de quelle db? on identifie notre db avec le server ou elle est host et les info pour la consulter sur phpmyadmin
    $servername = "localhost";
    $username = "root";
    $password = "root";

//STEP 2,: POur utiliser notre DB, on va l'instancier (objet)
try {
    $bdd = new PDO("mysql:host=$servername;dbname=demo4_users", $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

//STEP 3: pour pouvoir faire une inscription, on doit s'assurer qu'on a recu ces informations via POST
if (isset($_POST["ok"])) {

//STEP 4: on commence la validation -> on récupere chaque "input" du form et on les stocke dans des variables php pour pouvoir les manipuler
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $pseudo = $_POST["pseudo"];
    $mdp = $_POST["pass"];
    $email = $_POST["email"];

    // (ici, on a rendu utilisable les info du form envoyés par post)

    //STEP 5:avant d'inscrire quelquun, on doit verifier "est-ce qu'elle existe déja?"
    //CA veut dire demander à la DB "Trouve moi une entrée ou l'information reuc en POST est déja la" (on utilisera l'email et le pseudo)

    //Pour le faire on doit préparer la forme de requete qu'on enverrais à la db
                                                                //cet email va changer de personne en personne
    $requete_email = $bdd->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $requete_email->execute(['email' => $email]); //on exécute la requete (-> == "." dans java)
    $email_exist = $requete_email->fetchColumn(); // combien de fois je la vois dans la colonne "email"

    $requete_pseudo = $bdd->prepare("SELECT COUNT(*) FROM users WHERE pseudo = :pseudo");
    $requete_pseudo->execute(['pseudo' => $pseudo]);
    $pseudo_exist = $requete_pseudo->fetchColumn();

    //STEP 6: CAS 1 - ON A DÉJA CES INFOS EN DB
    if ($email_exist > 0) {
        $error_msg = "Ce courriel est déjà utilisé !"; //on prépare un affichage conditionnel d'erreur
    } elseif ($pseudo_exist > 0) {
        $error_msg = "Ce pseudo est déjà utilisé !";
    } else {
    //STEP 6: CAS 2 - ON N'A PAS CES INFOS EN DB
        try {
            //STEP 6.1: on a validé les credentials (envoyé par POST) et les préconditions à une inscription (la DB n'a pas trouvé d'entrée correspondant a l'email/ pseudo donnés)
            // on prépare la requete pour ajouter la personne dans la table
            $requete = $bdd->prepare(
                "INSERT INTO users (pseudo, nom, prenom, mdp, email, token)
                 VALUES (:pseudo, :nom, :prenom, :mdp, :email, '0')"
            );
            $requete->execute([ //STEP 6.2: on exécute la requete en spécifiant quelles infos correspond à quelels colonnes
                "pseudo" => $pseudo,
                "nom" => $nom,
                "prenom" => $prenom,
                "mdp" => password_hash($mdp, PASSWORD_DEFAULT), /// /!\ IMPORTANT NE JAMAIS STOCKER LES MDP BRUTS
                "email" => $email
            ]);
        //STEP 6: CAS 3 - TOUT EST OK
            $success_msg = "Inscription réussie ! Vous pouvez désormais vous connecter"; // affichage conditionnel de réussite
        } catch (PDOException $e) {
            $error_msg = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}

?>
<!-- html -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page-auth">

  <!-- HERO -->
  <div class="auth-hero">
    <div class="auth-logo">✈️</div>
    <h1 class="auth-title">VoyageHub</h1>
    <p class="auth-subtitle">Gestion de votre inscription</p>
  </div>

  <!-- CARTE MESSAGE -->
  <div class="auth-card">

    <?php if (isset($error_msg)) : ?>

        <h2 class="auth-card-title">Erreur</h2>

        <p class="message-error">
            <?= htmlspecialchars($error_msg) ?>
        </p>

        <a href="inscription.php" class="auth-btn secondary">
            Réessayer
        </a>

    <?php elseif (isset($success_msg)) : ?>

        <h2 class="auth-card-title">Succès</h2>

        <p class="message-success">
            <?= htmlspecialchars($success_msg) ?>
        </p>

        <a href="connexion.php" class="auth-btn">
            Se connecter
        </a>

    <?php endif; ?>

  </div>

</body>
</html>