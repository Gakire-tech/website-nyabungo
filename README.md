# NYABUNGO RESTAURANT & BAR

Site web full-stack pour deux succursales, inspiré par le luxe et le raffinement.

## Structure du projet

- `public/` : Frontend public (HTML, PHP, JS, Tailwind CSS)
- `admin/` : Backend admin (PHP, JS, Tailwind CSS)
- `api/` : Endpoints API REST (PHP)
- `config/` : Fichiers de configuration (DB, etc.)
- `db/` : Scripts SQL, migrations
- `assets/` : Images, vidéos, logos
- `vendor/` : Dépendances (Composer)

## Technologies
- Frontend : HTML, PHP, JavaScript, Tailwind CSS
- Backend : PHP, MySQL
- API : RESTful

## Comment exécuter le projet

### 1. Prérequis

- **Serveur web** : Apache ou Nginx (WAMP, XAMPP, Laragon, MAMP…)
- **PHP** : version 7.4 ou supérieure
- **MySQL/MariaDB** : pour la base de données
- **Node.js & npm** (optionnel, pour Tailwind CSS si recompilation nécessaire)

### 2. Installation de la base de données

1. **Ouvrez phpMyAdmin** (ou un terminal MySQL).
2. **Créez une base de données** (ex : `nyabungo_resto_bar`).
3. **Importez le schéma** :
   - Ouvrez le fichier `db/schema.sql`.
   - Exécutez tout le script dans votre outil MySQL (phpMyAdmin, DBeaver, ligne de commande…).

### 3. Configuration de la connexion MySQL

- Ouvrez `config/database.php`.
- Modifiez les variables si besoin :
  ```php
  $DB_HOST = 'localhost';
  $DB_NAME = 'nyabungo_resto_bar';
  $DB_USER = 'root'; // ou votre utilisateur MySQL
  $DB_PASS = '';     // ou votre mot de passe MySQL
  ```

### 4. Lancer le serveur web

- Placez le dossier du projet dans le dossier web de votre serveur (ex : `C:/wamp64/www/website-resto-bar`).
- Démarrez Apache/MySQL via WAMP/XAMPP/Laragon.
- Accédez à l'URL :  
  `http://localhost/website-resto-bar/public/`  
  ou  
  `http://localhost/website-resto-bar/`

### 5. Accès à l'admin

- Rendez-vous sur  
  `http://localhost/website-resto-bar/admin/login.php`
- Connectez-vous avec un utilisateur admin (à créer dans la table `users` si besoin).

### 6. (Optionnel) Recompiler Tailwind CSS

Si vous modifiez le design ou les classes Tailwind :
- Ouvrez un terminal dans le dossier du projet.
- Installez les dépendances (si ce n'est pas déjà fait) :
  ```
  npm install
  ```
- Recompilez le CSS :
  ```
  npx tailwindcss -i ./public/css/tailwind.css -o ./public/css/tailwind.output.css --watch
  ```

### 7. Remplir la base de données

- Utilisez l'admin pour ajouter du contenu (menus, galeries, contenu dynamique…).
- Ou insérez des données de test via phpMyAdmin.

### 8. Tester l'API

- Les endpoints sont accessibles sous `/api/` (ex : `/api/menus/1/restaurant`, `/api/site_content/accueil`).

### 9. Débogage

- Si une page blanche s'affiche, vérifiez les erreurs PHP (activez `display_errors` dans `php.ini` ou ajoutez `ini_set('display_errors', 1);` en haut des fichiers).
- Vérifiez la console du navigateur pour les erreurs JS/API.

---

**Résumé rapide**

1. Importez la base SQL.
2. Configurez la connexion MySQL.
3. Placez le projet dans le dossier web.
4. Lancez le serveur et accédez à l'URL.
5. Connectez-vous à l'admin pour gérer le contenu.

---

Pour toute question ou problème, consultez la documentation technique ou contactez le développeur du projet. 