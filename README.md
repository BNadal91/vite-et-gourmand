# Vite & Gourmand — application web de traiteur

Application web permettant à **Vite & Gourmand** (traiteur bordelais, Julie et José) de présenter ses menus et de recevoir des commandes en ligne.
Projet réalisé dans le cadre de l'ECF du titre professionnel **Développeur Web et Web Mobile**.

- **Application en ligne** : https://vite-et-gourmand.fly.dev
- **Gestion de projet** : voir `docs/gestion-de-projet.pdf` (lien du tableau Kanban dans la copie à rendre)

## Fonctionnalités

| Profil | Fonctionnalités |
|---|---|
| Visiteur | Accueil (présentation, équipe, avis validés), vue globale des menus avec **filtres dynamiques sans rechargement** (prix max, fourchette de prix, thème, régime, nombre de personnes), détail d'un menu (galerie, plats, allergènes, conditions mises en évidence), contact, création de compte |
| Utilisateur | Commande (formulaire pré-rempli, prix détaillé en direct, remise de 10 %, livraison 5 € + 0,59 €/km hors Bordeaux), e-mail de confirmation, suivi horodaté, modification/annulation tant que la commande n'est pas acceptée, avis (note 1 à 5 + commentaire) une fois la commande terminée, gestion du profil, suppression du compte (RGPD) |
| Employé | Gestion des commandes (filtres par statut et par client, changement de statut, annulation/modification avec mode de contact et motif obligatoires), CRUD menus/plats/allergènes/images, horaires, modération des avis |
| Administrateur | Tout ce que fait l'employé + création/désactivation des comptes employés, statistiques (nombre de commandes par menu en graphique, chiffre d'affaires filtrable par menu et par période) **issues de MongoDB** |

## Stack technique

- **Front-end** : HTML5, CSS3, Bootstrap 5.3 (servi localement), JavaScript natif (fetch), Chart.js 4
- **Back-end** : PHP 8.3 sans framework, architecture MVC, PDO (requêtes préparées)
- **Base relationnelle** : MySQL 8 / MariaDB 10.6+
- **Base NoSQL** : MongoDB (extension officielle `ext-mongodb`) — statistiques
- **Déploiement** : Docker, Fly.io (application), Aiven (MySQL), MongoDB Atlas (NoSQL), Brevo (SMTP)

## Comptes de démonstration

| Rôle | Identifiant | Mot de passe |
|---|---|---|
| Administrateur | jose@vite-gourmand.fr | `Jose#Gourmand2026` |
| Employée | julie@vite-gourmand.fr | `Julie#Gourmand2026` |
| Utilisateur | client@vite-gourmand.fr | `Client#Gourmand2026` |

## Installation en local

### Prérequis

- PHP ≥ 8.2 avec les extensions `pdo_mysql`, `mbstring`, `fileinfo` et `mongodb` (`pecl install mongodb`)
- MySQL 8 ou MariaDB 10.6+
- MongoDB 6+ (local ou cluster gratuit MongoDB Atlas)
- Git

> Alternative la plus simple : **Docker Desktop**, voir « Option B » ci-dessous.

### Option A — installation classique

```bash
# 1. Récupérer le code
git clone https://github.com/<votre-compte>/vite-et-gourmand.git
cd vite-et-gourmand

# 2. Configurer l'environnement
cp .env.example .env          # puis adapter les identifiants si besoin

# 3. Créer la base, l'utilisateur applicatif, les tables et le jeu de données
mysql -u root -p < database/00_database.sql
mysql -u root -p vite_gourmand < database/01_schema.sql
mysql -u root -p vite_gourmand < database/02_data.sql

# 4. Alimenter MongoDB à partir des commandes (statistiques admin)
php bin/sync_mongo.php

# 5. Lancer le serveur de développement
php -S localhost:8000 -t public public/router.php
```

Ouvrir http://localhost:8000.

En développement, `MAIL_DRIVER=log` : les e-mails (bienvenue, confirmation, réinitialisation…) sont écrits dans `storage/logs/mails.log`.

### Option B — Docker

```bash
cp .env.example .env
docker compose up -d --build           # MariaDB importe automatiquement les 3 scripts SQL
docker compose exec app php bin/sync_mongo.php
```

Application disponible sur http://localhost:8000.

## Déploiement (Fly.io + Aiven + MongoDB Atlas)

1. **MySQL** : créer un service gratuit *Aiven for MySQL*, télécharger le certificat `ca.pem`, puis importer le schéma :
   `mysql --ssl-ca=ca.pem -h <hote> -P <port> -u avnadmin -p defaultdb < database/01_schema.sql` (idem avec `02_data.sql`).
2. **MongoDB** : créer un cluster gratuit M0 sur MongoDB Atlas, un utilisateur, et autoriser l'accès réseau (`0.0.0.0/0`).
3. **SMTP** : créer un compte Brevo et générer une clé SMTP.
4. **Fly.io** :
   ```bash
   fly launch --no-deploy          # réutilise fly.toml
   fly secrets set APP_URL=https://vite-et-gourmand.fly.dev \
       DB_HOST=... DB_PORT=... DB_NAME=defaultdb DB_USER=avnadmin DB_PASSWORD=... \
       DB_SSL_CA_PEM="$(cat ca.pem)" \
       MONGODB_URI="mongodb+srv://..." \
       SMTP_USER=... SMTP_PASSWORD=... MAIL_FROM=... MAIL_CONTACT=...
   fly deploy
   fly ssh console -C "php /var/www/html/bin/sync_mongo.php"
   ```

La procédure détaillée et justifiée figure dans `docs/documentation-technique.pdf`.

## Organisation du dépôt Git

- `main` : version stable, déployée
- `develop` : branche d'intégration
- `feature/*` : une branche par fonctionnalité, créée depuis `develop`, fusionnée dans `develop` après test, puis `develop` fusionnée dans `main`

## Arborescence

```
public/          point d'entrée unique (index.php), assets (CSS, JS, images, polices)
src/Core/        routeur, base de données, MongoDB, authentification, CSRF, validation, e-mails, upload
src/Controllers/ contrôleurs (accueil, menus, auth, commande, compte, contact, employé, admin)
src/Models/      accès aux données (PDO) et règles de gestion (tarifs)
views/           gabarits PHP (pages, espaces, e-mails)
database/        scripts SQL de création et d'insertion de données
bin/             scripts en ligne de commande (synchronisation MongoDB)
docs/            documentations PDF (technique, manuel d'utilisation, charte graphique, gestion de projet)
```

## Sécurité (résumé)

Requêtes préparées PDO, échappement systématique des sorties (XSS), jetons CSRF, CSP stricte et en-têtes de sécurité,
mots de passe hachés (`password_hash`), politique de mot de passe forte, limitation des tentatives de connexion,
régénération de l'identifiant de session, cookies `HttpOnly`/`Secure`/`SameSite`, contrôle d'accès par rôle,
vérification d'appartenance des commandes (anti-IDOR), validation serveur de toutes les saisies, upload d'images contrôlé,
jetons de réinitialisation hachés à usage unique, secrets hors dépôt (`.env`).
