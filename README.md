# Application Web en Tuiles

Projet réalisé dans le cadre du cours **IFT3225 – Technologie de l’Internet** à l’Université de Montréal.

---

# Description

Cette application web permet aux utilisateurs de gérer des tuiles de manière dynamique.

Chaque tuile représente un élément de l’application (voyage, tâche, recette, bibliothèque, etc.).

Les utilisateurs peuvent :

- ajouter des tuiles
- modifier des tuiles
- supprimer des tuiles
- rechercher et filtrer des tuiles
- gérer leurs données après authentification

Les mises à jour sont effectuées dynamiquement grâce aux requêtes asynchrones.

---

# Fonctionnalités

## Authentification utilisateur
- Création de compte
- Connexion utilisateur
- Gestion des sessions
- Mots de passe sécurisés avec hash

## Gestion des tuiles
- Ajouter une tuile
- Modifier une tuile
- Supprimer une tuile
- Afficher les tuiles

## Recherche et filtrage
Recherche par :
- titre
- catégorie
- utilisateur
- date
- description

## Interface responsive
L’affichage s’adapte automatiquement à la taille de l’écran.

## Pagination
- Maximum de 15 tuiles par page

## Requêtes asynchrones
Les modifications sont envoyées au serveur sans rechargement de page grâce à Fetch API.

---

# Technologies utilisées

## Backend
- PHP
- MySQL
- phpMyAdmin pour la gestion de la base de données

## Frontend
- HTML5
- CSS3
- JavaScript
- Fetch API pour les requêtes asynchrones

## Environnement local
- MAMP pour le développement et les tests locaux

## Déploiement final
- Serveur du DIRO

Lien du site :

https://www-ens.iro.umontreal.ca/~aqelhamz/ift3225/accueil.php

---

# Structure d’une tuile

Chaque tuile contient :
- un titre
- une date
- une catégorie
- une description

---

# Structure du projet


Projet1-3225/
│

├── css/

├── js/

├── php/

├── sql/

  │   └── schema.sql
  
├── accueil.php

├── connexion.php

├── inscription.php

├── client.php

└── README.md


---

# Étapes de déploiement local

## 1) Préparer MAMP

- Ouvrir MAMP
- Cliquer sur Start Servers

---

## 2) Mettre le projet dans le bon dossier

Trouver le dossier web de MAMP :

/Applications/MAMP/htdocs/

Copier le dossier du projet :

Projet1-3225

dans le dossier htdocs.

---

## 3) Créer la base de données et les tables

### Via phpMyAdmin

Ouvrir :

http://localhost:8888/phpMyAdmin/

ou utiliser le bouton WebStart dans MAMP.

### Créer la base de données

- Aller dans l’onglet Databases
- Créer une base nommée :

demo4_users

### Importer le script SQL

- Cliquer sur la base de données
- Aller dans l’onglet Import
- Importer le fichier :

sql/schema.sql

### Vérification

Vérifier que les tables suivantes existent :
- users
- voyages

---

# Lancer l’application

Ouvrir :

http://localhost:8888/accueil.php

ou :

http://localhost/accueil.php

Créer un compte utilisateur.

Ensuite ouvrir :

http://localhost:8888/connexion.php

Après connexion, l’utilisateur sera redirigé vers :

client.php

---

# Base de données

Le projet utilise MySQL pour stocker :
- les utilisateurs
- les tuiles
- les catégories

Les scripts SQL sont inclus dans le projet.

---

# Sécurité

- Les mots de passe sont hashés
- Validation des formulaires côté client et serveur
- Vérification syntaxique des adresses courriel

---

# Outils utilisés

- Visual Studio Code
- MAMP
- phpMyAdmin
- GitHub

---

# Remarques

- Toutes les pages respectent le standard HTML5
- L’application fonctionne en local avec MAMP
- Le projet utilise des requêtes asynchrones avec Fetch API
- Le projet est déployé sur le serveur du DIRO
