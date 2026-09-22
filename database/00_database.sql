-- Création de la base et d'un utilisateur applicatif aux droits limités
-- (principe du moindre privilège : l'application ne peut ni supprimer de table ni gérer les droits).
-- À exécuter avec un compte administrateur MySQL/MariaDB : mysql -u root -p < database/00_database.sql
CREATE DATABASE IF NOT EXISTS vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'vg_app'@'localhost' IDENTIFIED BY 'ChangezMoi#2026';
CREATE USER IF NOT EXISTS 'vg_app'@'%' IDENTIFIED BY 'ChangezMoi#2026';
GRANT SELECT, INSERT, UPDATE, DELETE ON vite_gourmand.* TO 'vg_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON vite_gourmand.* TO 'vg_app'@'%';
FLUSH PRIVILEGES;
