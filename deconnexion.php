<?php
// session_save_path(__DIR__ . "/sessions"); // pour stockage sessions diro
session_start();
session_destroy(); //mettre fin à la session ; le navigateur va oublier l'email + token stockés en connexion
header("Location: accueil.php"); //redirection automatique
exit;
