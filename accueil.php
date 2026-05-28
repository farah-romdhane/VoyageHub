<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>VoyageHub – Connexion</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="page-auth">

  <!-- HERO / ACCUEIL -->
  <div class="auth-hero">
    <div class="auth-logo">✈️</div>
    <h1 class="auth-title">VoyageHub</h1>
    <p class="auth-subtitle">Gérez vos voyages de rêve</p>
  </div>

  <div class="auth-card">
    <h2 class="auth-card-title">Connexion</h2>

    <form method="POST" action="connexion.php" class="auth-form">

      <div class="input-group">
        <input type="email" name="email" placeholder="email@example.com" required>
      </div>

      <div class="input-group">
        <input type="password" name="pass" placeholder="••••••••" required>
      </div>

      <button type="submit" class="auth-btn">Se connecter</button>
    </form>

    <p class="auth-footer">
      Pas de compte ?
      <a href="inscription.php">S’inscrire</a>
    </p>

  </div>

</body>
</html>
