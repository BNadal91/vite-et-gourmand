<?php
declare(strict_types=1);

namespace App\Models;

final class Avis extends Model
{
    public function valides(int $limit = 6): array
    {
        return $this->all(
            "SELECT a.*, u.prenom, LEFT(u.nom, 1) AS initiale, m.titre AS menu_titre
             FROM avis a
             LEFT JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
             JOIN commande c ON c.numero_commande = a.numero_commande
             JOIN menu m ON m.menu_id = c.menu_id
             WHERE a.statut = 'valide'
             ORDER BY a.date_avis DESC LIMIT " . max(1, $limit)
        );
    }

    public function moyenne(): array
    {
        return $this->one("SELECT ROUND(AVG(note), 1) AS moyenne, COUNT(*) AS total FROM avis WHERE statut = 'valide'") ?? ['moyenne' => null, 'total' => 0];
    }

    public function parStatut(?string $statut): array
    {
        $sql = 'SELECT a.*, u.prenom, u.nom, u.email, m.titre AS menu_titre
                FROM avis a
                LEFT JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
                JOIN commande c ON c.numero_commande = a.numero_commande
                JOIN menu m ON m.menu_id = c.menu_id';
        if ($statut) {
            return $this->all($sql . ' WHERE a.statut = ? ORDER BY a.date_avis DESC', [$statut]);
        }
        return $this->all($sql . " ORDER BY FIELD(a.statut, 'en_attente', 'valide', 'refuse'), a.date_avis DESC");
    }

    public function creer(int $userId, string $numero, int $note, string $description): void
    {
        $this->run('INSERT INTO avis (note, description, utilisateur_id, numero_commande) VALUES (?, ?, ?, ?)',
            [$note, $description, $userId, $numero]);
    }

    public function moderer(int $avisId, string $statut): void
    {
        $this->run('UPDATE avis SET statut = ? WHERE avis_id = ?', [$statut, $avisId]);
    }
}
