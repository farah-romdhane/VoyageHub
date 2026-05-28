-- dbname: sur local demo4_users et sur diro aqelhamz_voyagehub
CREATE DATABASE IF NOT EXISTS demo4_users;
USE demo4_users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pseudo VARCHAR(100) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  mdp VARCHAR(255) NOT NULL,
  token VARCHAR(255) DEFAULT '0'
);

CREATE TABLE voyages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  titre VARCHAR(100) NOT NULL,
  date_depart DATE NOT NULL,
  date_retour DATE,
  categorie VARCHAR(50),
  description TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);