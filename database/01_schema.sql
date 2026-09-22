-- =====================================================================
--  Vite & Gourmand — Script de création de la base de données
--  SGBD : MySQL 8 / MariaDB 10.6+   —   Encodage : utf8mb4
--  Basé sur le MCD fourni (annexe 1) et enrichi pour couvrir le cahier
--  des charges (galerie d'images, suivi de commande, réinitialisation
--  de mot de passe, anti-bruteforce, messages de contact).
-- =====================================================================

-- La base est créée par 00_database.sql (en local). Chez un hébergeur,
-- exécuter ce script directement dans la base fournie.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS message_contact, avis, commande_suivi, commande, horaire,
  plat_allergene, allergene, menu_plat, plat, menu_image, menu, regime, theme,
  tentative_connexion, reinitialisation_mdp, utilisateur, role;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  Rôles et utilisateurs
-- ---------------------------------------------------------------------
CREATE TABLE role (
    role_id   INT AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL UNIQUE          -- utilisateur / employe / administrateur
) ENGINE=InnoDB;

CREATE TABLE utilisateur (
    utilisateur_id     INT AUTO_INCREMENT PRIMARY KEY,
    email              VARCHAR(180) NOT NULL UNIQUE,  -- sert d'identifiant (username)
    password           VARCHAR(255) NOT NULL,         -- hash bcrypt / argon2 (jamais en clair)
    nom                VARCHAR(80)  NOT NULL,
    prenom             VARCHAR(80)  NOT NULL,
    telephone          VARCHAR(20)  NULL,
    adresse_postale    VARCHAR(255) NULL,
    code_postal        VARCHAR(10)  NULL,
    ville              VARCHAR(100) NULL,
    pays               VARCHAR(60)  NULL DEFAULT 'France',
    role_id            INT NOT NULL,
    actif              TINYINT(1) NOT NULL DEFAULT 1, -- 0 = compte désactivé (départ d'un employé)
    consentement_rgpd  DATETIME NULL,                 -- date d'acceptation de la politique de confidentialité
    date_creation      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES role(role_id)
) ENGINE=InnoDB;

-- Jetons de réinitialisation : on ne stocke QUE le hash SHA-256 du jeton
CREATE TABLE reinitialisation_mdp (
    reinitialisation_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id      INT NOT NULL,
    token_hash          CHAR(64) NOT NULL UNIQUE,
    expire_le           DATETIME NOT NULL,
    utilise             TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_reinit_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Journal des tentatives de connexion échouées (limitation du brute-force)
CREATE TABLE tentative_connexion (
    tentative_id   INT AUTO_INCREMENT PRIMARY KEY,
    email          VARCHAR(180) NOT NULL,
    ip             VARCHAR(45)  NOT NULL,
    date_tentative DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentative (email, ip, date_tentative)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Catalogue : thèmes, régimes, menus, plats, allergènes
-- ---------------------------------------------------------------------
CREATE TABLE theme (
    theme_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle  VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE regime (
    regime_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle   VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE menu (
    menu_id                  INT AUTO_INCREMENT PRIMARY KEY,
    titre                    VARCHAR(120) NOT NULL,
    description              TEXT NOT NULL,
    nombre_personne_minimum  INT NOT NULL CHECK (nombre_personne_minimum >= 1),
    prix_par_personne        DECIMAL(8,2) NOT NULL CHECK (prix_par_personne >= 0),
    conditions               TEXT NOT NULL,          -- délai de commande, stockage...
    quantite_restante        INT NOT NULL DEFAULT 0 CHECK (quantite_restante >= 0), -- stock
    theme_id                 INT NOT NULL,           -- association « propose » (1,1)
    regime_id                INT NOT NULL,           -- association « adapte »  (1,1)
    actif                    TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_menu_theme  FOREIGN KEY (theme_id)  REFERENCES theme(theme_id),
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime(regime_id)
) ENGINE=InnoDB;

-- Galerie d'images d'un menu
CREATE TABLE menu_image (
    image_id          INT AUTO_INCREMENT PRIMARY KEY,
    menu_id           INT NOT NULL,
    chemin            VARCHAR(255) NOT NULL,
    texte_alternatif  VARCHAR(255) NOT NULL,          -- RGAA : alternative textuelle
    ordre             INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_image_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plat (
    plat_id      INT AUTO_INCREMENT PRIMARY KEY,
    titre_plat   VARCHAR(120) NOT NULL,
    type_plat    ENUM('entree','plat','dessert') NOT NULL,
    description  VARCHAR(255) NULL,
    photo        MEDIUMBLOB NULL,                     -- conforme au MCD (BLOB)
    photo_mime   VARCHAR(30) NULL
) ENGINE=InnoDB;

-- Association « propose » menu <-> plat (un plat peut être dans plusieurs menus)
CREATE TABLE menu_plat (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    CONSTRAINT fk_mp_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
    CONSTRAINT fk_mp_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE allergene (
    allergene_id INT AUTO_INCREMENT PRIMARY KEY,
    libelle      VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Association « contient » plat <-> allergène
CREATE TABLE plat_allergene (
    plat_id      INT NOT NULL,
    allergene_id INT NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    CONSTRAINT fk_pa_plat      FOREIGN KEY (plat_id)      REFERENCES plat(plat_id) ON DELETE CASCADE,
    CONSTRAINT fk_pa_allergene FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Horaires (pied de page)
-- ---------------------------------------------------------------------
CREATE TABLE horaire (
    horaire_id       INT AUTO_INCREMENT PRIMARY KEY,
    jour             VARCHAR(20) NOT NULL UNIQUE,
    heure_ouverture  VARCHAR(10) NULL,               -- NULL = fermé
    heure_fermeture  VARCHAR(10) NULL,
    ordre            TINYINT NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Commandes, suivi et avis
-- ---------------------------------------------------------------------
CREATE TABLE commande (
    numero_commande       VARCHAR(50) PRIMARY KEY,
    utilisateur_id        INT NULL,                  -- NULL si le compte a été supprimé (RGPD)
    menu_id               INT NOT NULL,
    date_commande         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_prestation       DATE NOT NULL,
    heure_livraison       TIME NOT NULL,
    adresse_livraison     VARCHAR(255) NOT NULL,
    code_postal_livraison VARCHAR(10) NOT NULL,
    ville_livraison       VARCHAR(100) NOT NULL,
    distance_km           DECIMAL(7,2) NOT NULL DEFAULT 0,
    nom_client            VARCHAR(80) NOT NULL,
    prenom_client         VARCHAR(80) NOT NULL,
    email_client          VARCHAR(180) NOT NULL,
    telephone_client      VARCHAR(20) NOT NULL,
    nombre_personne       INT NOT NULL,
    prix_menu             DECIMAL(10,2) NOT NULL,    -- prix après éventuelle réduction
    reduction             DECIMAL(10,2) NOT NULL DEFAULT 0,
    prix_livraison        DECIMAL(10,2) NOT NULL DEFAULT 0,
    prix_total            DECIMAL(10,2) NOT NULL,
    statut                VARCHAR(40) NOT NULL DEFAULT 'en_attente',
    pret_materiel         TINYINT(1) NOT NULL DEFAULT 0,
    restitution_materiel  TINYINT(1) NOT NULL DEFAULT 0,
    motif_annulation      VARCHAR(500) NULL,
    mode_contact          VARCHAR(20) NULL,          -- gsm / mail (contact préalable par l'employé)
    CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur(utilisateur_id) ON DELETE SET NULL,
    CONSTRAINT fk_commande_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id),
    INDEX idx_commande_statut (statut)
) ENGINE=InnoDB;

-- Historique horodaté de chaque changement d'état
CREATE TABLE commande_suivi (
    suivi_id          INT AUTO_INCREMENT PRIMARY KEY,
    numero_commande   VARCHAR(50) NOT NULL,
    statut            VARCHAR(40) NOT NULL,
    date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    commentaire       VARCHAR(500) NULL,
    CONSTRAINT fk_suivi_commande FOREIGN KEY (numero_commande)
        REFERENCES commande(numero_commande) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avis (
    avis_id          INT AUTO_INCREMENT PRIMARY KEY,
    note             TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    description      VARCHAR(1000) NOT NULL,
    statut           VARCHAR(20) NOT NULL DEFAULT 'en_attente',  -- en_attente / valide / refuse
    date_avis        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    utilisateur_id   INT NULL,
    numero_commande  VARCHAR(50) NOT NULL UNIQUE,                -- un avis par commande
    CONSTRAINT fk_avis_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur(utilisateur_id) ON DELETE SET NULL,
    CONSTRAINT fk_avis_commande FOREIGN KEY (numero_commande)
        REFERENCES commande(numero_commande) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Messages envoyés depuis la page contact (copie en base en plus du mail)
CREATE TABLE message_contact (
    message_id  INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    email       VARCHAR(180) NOT NULL,
    date_envoi  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
