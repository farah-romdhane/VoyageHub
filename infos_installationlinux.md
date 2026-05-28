

# demarrer

---

Ouvrir un terminal et exécuter :

  `sudo apt update;`
  `sudo apt install apache2`

Démarrer Apache :

  `sudo systemctl start apache2;`
  `sudo systemctl enable apache2`

Vérifier l’état :

  `sudo systemctl status apache2`

normalement vous allez voir :
  Active: active (running)

Tester dans le navigateur :
  http://localhost



##  INSTALLER PHP

---

Tapez :

  `sudo apt install php libapache2-mod-php php-mysql`

Redémarrer Apache :

  `sudo systemctl restart apache2`


## INSTALLER MYSQL

---

Installer MySQL Server :

  `sudo apt install mysql-server`

Démarrer MySQL :

  `sudo systemctl start mysql;`
  `sudo systemctl enable mysql`

Vérifier :

  `sudo systemctl status mysql`

VOus allez normalement voir:
  Active: active (running)
  Status: "Server is operational"


## INSTALLER PHPMYADMIN

---

Pour installer phpMyAdmin :

  `sudo apt install phpmyadmin`

VRAIMENT IMPORTANT PENDANT L’INSTALLATION :
- Choisir "`apache2`" avec la barre ESPACE
- Valider avec ENTER
- Accepter la configuration automatique de la base
- Définir un mot de passe (ex : root, 1234, coucou, bref...)


### IL SE PEUT QUIL Y AI UN PROBLÈME ICI, MAIS ON PEUT LE BYPASS

---

Sur Ubuntu, phpMyAdmin peut être installé MAIS pas exposé à Apache

Solution (pour l'instant) :

  `sudo ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin`
  `sudo systemctl restart apache2`

Re-tester :
  http://localhost/phpmyadmin


### IL SE PEUT QUIL Y AI UN AUTRE PROBLÈME ICI, MAIS ON PEUT AUSSI LE BYPASS

---

Ubuntu utilise auth_socket par défaut pour Mysql, si phpmyAdmin refuse le login root :

Dans le terminal :

  sudo mysql

Vous allez voir que vous etes dans le terminal de commandes SQL (donc pas du bash)
Puis dans MySQL :

```sql
  ALTER USER 'root'@'localhost'
  IDENTIFIED WITH mysql_native_password
  BY 'root';

  FLUSH PRIVILEGES;
  EXIT;
  ```

Se connecter dans phpMyAdmin avec :
- utilisateur : root
- mot de passe : root


## SI VOUS VOYEZ UN AVERTISSEMENT SYSTEMD

---

Msg possible :

  Warning: The unit file ... changed on disk.
  Run 'systemctl daemon-reload'

solution simple :

  `sudo systemctl daemon-reload`
  `sudo systemctl restart mysql`
  `sudo systemctl restart apache2`

Ce message n’est PAS une erreur, donc pas de stress
