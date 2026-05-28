<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>VoyageHub – Inscription</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="page-auth">

  <!-- HERO -->
  <div class="auth-hero">
    <div class="auth-logo">✈️</div>
    <h1 class="auth-title">VoyageHub</h1>
    <p class="auth-subtitle">Créez votre compte</p>
  </div>

  <!-- CARTE INSCRIPTION -->
  <div class="auth-card">
    <h2 class="auth-card-title">Inscription</h2>

    <form method="POST" action="traitement.php" class="auth-form">
      <input type="text" name="nom" placeholder="Nom" required>
      <input type="text" name="prenom" placeholder="Prénom" required>
      <input type="text" name="pseudo" placeholder="Pseudo" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="pass" placeholder="Mot de passe" required>

      <button type="submit" name="ok" class="auth-btn">
        S’inscrire
      </button>
    </form>

    <p class="auth-footer">
      Déjà un compte ?
      <a href="connexion.php">Se connecter</a>
    </p>
  </div>

</body>
</html>
