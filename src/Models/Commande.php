<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Mongo;

final class Commande extends Model
{
    /** Cycle de vie : états accessibles depuis chaque état (machine à états). */
    public const TRANSITIONS = [
        'en_attente'              => ['accepte'],
        'accepte'                 => ['en_preparation'],
        'en_preparation'          => ['en_cours_livraison'],
        'en_cours_livraison'      => ['livre'],
        'livre'                   => ['attente_retour_materiel', 'terminee'],
        'attente_retour_materiel' => ['terminee'],
        'terminee'                => [],
        'annulee'                 => [],
    ];

    private const SELECT = 'SELECT c.*, m.titre AS menu_titre, m.conditions AS menu_conditions, m.nombre_personne_minimum,
            m.prix_par_personne, a.avis_id, a.note AS avis_note, a.statut AS avis_statut
        FROM commande c
        JOIN menu m ON m.menu_id = c.menu_id
        LEFT JOIN avis a ON a.numero_commande = c.numero_commande';

    public static function genererNumero(): string
    {
        return 'VG-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Création d'une commande dans une transaction : on verrouille la ligne du menu
     * (SELECT … FOR UPDATE) pour éviter de vendre plus que le stock disponible
     * si deux clients commandent au même moment.
     */
    public function creer(array $d): string
    {
        $this->db->beginTransaction();
        try {
            $stock = $this->one('SELECT quantite_restante FROM menu WHERE menu_id = ? AND actif = 1 FOR UPDATE', [$d['menu_id']]);
            if (!$stock || (int) $stock['quantite_restante'] < 1) {
                throw new \DomainException("Ce menu n'est plus disponible à la commande.");
            }
            $this->run('UPDATE menu SET quantite_restante = quantite_restante - 1 WHERE menu_id = ?', [$d['menu_id']]);
            $numero = self::genererNumero();
            $this->run(
                'INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_prestation, heure_livraison, adresse_livraison,
                    code_postal_livraison, ville_livraison, distance_km, nom_client, prenom_client, email_client, telephone_client,
                    nombre_personne, prix_menu, reduction, prix_livraison, prix_total, statut, pret_materiel)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,\'en_attente\',?)',
                [$numero, $d['utilisateur_id'], $d['menu_id'], $d['date_prestation'], $d['heure_livraison'], $d['adresse_livraison'],
                 $d['code_postal_livraison'], $d['ville_livraison'], $d['distance_km'], $d['nom_client'], $d['prenom_client'],
                 $d['email_client'], $d['telephone_client'], $d['nombre_personne'], $d['prix_menu'], $d['reduction'],
                 $d['prix_livraison'], $d['prix_total'], $d['pret_materiel'] ? 1 : 0]
            );
            $this->ajouterSuivi($numero, 'en_attente', 'Commande passée en ligne');
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        $this->synchroniserMongo($numero);
        return $numero;
    }

    public function find(string $numero): ?array
    {
        return $this->one(self::SELECT . ' WHERE c.numero_commande = ?', [$numero]);
    }

    /** Contrôle d'appartenance (anti-IDOR) : un client ne voit que SES commandes. */
    public function findForUser(string $numero, int $userId): ?array
    {
        return $this->one(self::SELECT . ' WHERE c.numero_commande = ? AND c.utilisateur_id = ?', [$numero, $userId]);
    }

    public function forUser(int $userId): array
    {
        return $this->all(self::SELECT . ' WHERE c.utilisateur_id = ? ORDER BY c.date_commande DESC', [$userId]);
    }

    public function suivi(string $numero): array
    {
        return $this->all('SELECT * FROM commande_suivi WHERE numero_commande = ? ORDER BY date_modification, suivi_id', [$numero]);
    }

    /** Liste pour l'espace employé avec filtres par statut et par client. */
    public function search(?string $statut, ?string $client): array
    {
        $where = [];
        $params = [];
        if ($statut) {
            $where[] = 'c.statut = ?';
            $params[] = $statut;
        }
        if ($client) {
            $where[] = "(CONCAT(c.prenom_client, ' ', c.nom_client) LIKE ? OR c.email_client LIKE ? OR c.nom_client LIKE ?)";
            $like = '%' . addcslashes($client, '%_\\') . '%';
            array_push($params, $like, $like, $like);
        }
        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
            " ORDER BY FIELD(c.statut, 'en_attente', 'accepte', 'en_preparation', 'en_cours_livraison', 'livre', 'attente_retour_materiel', 'terminee', 'annulee'), c.date_prestation";
        return $this->all($sql, $params);
    }

    public function ajouterSuivi(string $numero, string $statut, ?string $commentaire = null): void
    {
        $this->run('INSERT INTO commande_suivi (numero_commande, statut, commentaire) VALUES (?, ?, ?)', [$numero, $statut, $commentaire]);
    }

    public function changerStatut(string $numero, string $statut, ?string $commentaire = null): void
    {
        $extra = $statut === 'terminee' ? ', restitution_materiel = pret_materiel' : '';
        $this->run("UPDATE commande SET statut = ?$extra WHERE numero_commande = ?", [$statut, $numero]);
        $this->ajouterSuivi($numero, $statut, $commentaire);
        Mongo::updateStatut($numero, $statut);
    }

    /** Annulation : le stock du menu est rendu disponible. */
    public function annuler(string $numero, string $motif, ?string $modeContact): void
    {
        $this->db->beginTransaction();
        try {
            $c = $this->one('SELECT menu_id, statut FROM commande WHERE numero_commande = ? FOR UPDATE', [$numero]);
            if (!$c || in_array($c['statut'], ['annulee', 'terminee'], true)) {
                throw new \DomainException('Cette commande ne peut plus être annulée.');
            }
            $this->run('UPDATE commande SET statut = \'annulee\', motif_annulation = ?, mode_contact = ? WHERE numero_commande = ?',
                [$motif, $modeContact, $numero]);
            $this->run('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = ?', [$c['menu_id']]);
            $this->ajouterSuivi($numero, 'annulee', $motif);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        Mongo::updateStatut($numero, 'annulee');
    }

    /** Modification de la prestation (le menu choisi ne peut pas être changé). */
    public function modifier(string $numero, array $d, ?string $trace = null): void
    {
        $this->run(
            'UPDATE commande SET date_prestation = ?, heure_livraison = ?, adresse_livraison = ?, code_postal_livraison = ?,
                ville_livraison = ?, distance_km = ?, telephone_client = ?, nombre_personne = ?, prix_menu = ?, reduction = ?,
                prix_livraison = ?, prix_total = ?, mode_contact = COALESCE(?, mode_contact)
             WHERE numero_commande = ?',
            [$d['date_prestation'], $d['heure_livraison'], $d['adresse_livraison'], $d['code_postal_livraison'], $d['ville_livraison'],
             $d['distance_km'], $d['telephone_client'], $d['nombre_personne'], $d['prix_menu'], $d['reduction'],
             $d['prix_livraison'], $d['prix_total'], $d['mode_contact'] ?? null, $numero]
        );
        $c = $this->find($numero);
        $this->ajouterSuivi($numero, $c['statut'], $trace ?? 'Commande modifiée');
        $this->synchroniserMongo($numero);
    }

    public function synchroniserMongo(string $numero): void
    {
        $c = $this->find($numero);
        if ($c) {
            Mongo::upsertCommande($c);
        }
    }

    /** Toutes les commandes au format attendu par la collection MongoDB. */
    public function pourStatistiques(): array
    {
        return $this->all('SELECT c.numero_commande, c.menu_id, m.titre AS menu_titre, c.nombre_personne, c.prix_menu,
                c.prix_livraison, c.prix_total, c.statut, c.date_commande
            FROM commande c JOIN menu m ON m.menu_id = c.menu_id');
    }
}
