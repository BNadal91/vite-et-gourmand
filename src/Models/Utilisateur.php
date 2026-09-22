<?php
declare(strict_types=1);

namespace App\Models;

final class Utilisateur extends Model
{
    private const SELECT = 'SELECT u.*, r.libelle AS role FROM utilisateur u JOIN role r ON r.role_id = u.role_id';

    public function find(int $id): ?array
    {
        return $this->one(self::SELECT . ' WHERE u.utilisateur_id = ?', [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->one(self::SELECT . ' WHERE u.email = ?', [mb_strtolower(trim($email))]);
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        return $this->one('SELECT 1 FROM utilisateur WHERE email = ? AND utilisateur_id <> ?',
            [mb_strtolower(trim($email)), $exceptId ?? 0]) !== null;
    }

    public function create(array $d, string $role): int
    {
        $this->run(
            'INSERT INTO utilisateur (email, password, nom, prenom, telephone, adresse_postale, code_postal, ville, pays, role_id, consentement_rgpd)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, (SELECT role_id FROM role WHERE libelle = ?), ?)',
            [
                mb_strtolower(trim($d['email'])),
                password_hash($d['password'], PASSWORD_DEFAULT),
                $d['nom'], $d['prenom'], $d['telephone'] ?? null,
                $d['adresse_postale'] ?? null, $d['code_postal'] ?? null, $d['ville'] ?? null, $d['pays'] ?? 'France',
                $role,
                $role === 'utilisateur' ? date('Y-m-d H:i:s') : null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateProfil(int $id, array $d): void
    {
        $this->run(
            'UPDATE utilisateur SET nom = ?, prenom = ?, telephone = ?, adresse_postale = ?, code_postal = ?, ville = ?, pays = ?, email = ?
             WHERE utilisateur_id = ?',
            [$d['nom'], $d['prenom'], $d['telephone'], $d['adresse_postale'], $d['code_postal'], $d['ville'], $d['pays'],
             mb_strtolower(trim($d['email'])), $id]
        );
    }

    public function updatePassword(int $id, string $password): void
    {
        $this->run('UPDATE utilisateur SET password = ? WHERE utilisateur_id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    /** Re-hachage transparent si l'algorithme par défaut de PHP évolue. */
    public function rehashIfNeeded(array $user, string $password): void
    {
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $this->updatePassword((int) $user['utilisateur_id'], $password);
        }
    }

    public function employes(): array
    {
        return $this->all(self::SELECT . " WHERE r.libelle = 'employe' ORDER BY u.nom, u.prenom");
    }

    public function setActif(int $id, bool $actif): void
    {
        // On ne peut (dés)activer que des comptes employés
        $this->run("UPDATE utilisateur SET actif = ? WHERE utilisateur_id = ?
                    AND role_id = (SELECT role_id FROM role WHERE libelle = 'employe')", [$actif ? 1 : 0, $id]);
    }

    /** Droit à l'effacement (RGPD) : suppression du compte, les commandes sont conservées anonymisées. */
    public function supprimer(int $id): void
    {
        $this->run('DELETE FROM utilisateur WHERE utilisateur_id = ?', [$id]);
    }

    // ---- Anti brute-force ----
    public function tentativesRecentes(string $email, string $ip, int $minutes = 15): int
    {
        // Échecs pour ce compte depuis cette adresse (seuil 5) et échecs globaux de l'adresse
        // (seuil 20, contre les attaques qui essaient beaucoup de comptes différents).
        $r = $this->one('SELECT
                            SUM(email = ?) AS compte,
                            COUNT(*) AS adresse
                         FROM tentative_connexion
                         WHERE ip = ? AND date_tentative > (NOW() - INTERVAL ? MINUTE)',
            [mb_strtolower($email), $ip, $minutes]);
        $compte = (int) ($r['compte'] ?? 0);
        $adresse = (int) ($r['adresse'] ?? 0);
        return $adresse >= 20 ? max($compte, 5) : $compte;
    }

    public function enregistrerEchec(string $email, string $ip): void
    {
        $this->run('INSERT INTO tentative_connexion (email, ip) VALUES (?, ?)', [mb_strtolower($email), $ip]);
    }

    public function effacerEchecs(string $email): void
    {
        $this->run('DELETE FROM tentative_connexion WHERE email = ?', [mb_strtolower($email)]);
    }

    // ---- Réinitialisation du mot de passe ----
    public function creerJetonReinitialisation(int $userId): string
    {
        $token = bin2hex(random_bytes(32));           // 256 bits d'entropie
        $this->run('UPDATE reinitialisation_mdp SET utilise = 1 WHERE utilisateur_id = ?', [$userId]);
        $this->run('INSERT INTO reinitialisation_mdp (utilisateur_id, token_hash, expire_le) VALUES (?, ?, NOW() + INTERVAL 1 HOUR)',
            [$userId, hash('sha256', $token)]);
        return $token; // seul le lien envoyé par e-mail contient le jeton en clair
    }

    public function jetonValide(string $token): ?array
    {
        return $this->one('SELECT * FROM reinitialisation_mdp WHERE token_hash = ? AND utilise = 0 AND expire_le > NOW()',
            [hash('sha256', $token)]);
    }

    public function consommerJeton(int $id): void
    {
        $this->run('UPDATE reinitialisation_mdp SET utilise = 1 WHERE reinitialisation_id = ?', [$id]);
    }
}
