<?php
declare(strict_types=1);

namespace App\Models;

final class Menu extends Model
{
    private const SELECT = 'SELECT m.*, t.libelle AS theme, r.libelle AS regime,
            (m.prix_par_personne * m.nombre_personne_minimum) AS prix_minimum,
            (SELECT chemin FROM menu_image i WHERE i.menu_id = m.menu_id ORDER BY ordre LIMIT 1) AS image,
            (SELECT texte_alternatif FROM menu_image i WHERE i.menu_id = m.menu_id ORDER BY ordre LIMIT 1) AS image_alt
        FROM menu m
        JOIN theme t ON t.theme_id = m.theme_id
        JOIN regime r ON r.regime_id = m.regime_id';

    /**
     * Liste filtrée des menus actifs. Chaque filtre est optionnel et ajouté
     * sous forme de paramètre lié (aucune concaténation de valeur utilisateur).
     */
    public function search(array $f = []): array
    {
        $where = ['m.actif = 1'];
        $params = [];
        if (!empty($f['prix_max'])) {
            $where[] = '(m.prix_par_personne * m.nombre_personne_minimum) <= ?';
            $params[] = (float) $f['prix_max'];
        }
        if (!empty($f['prix_min_fourchette'])) {
            $where[] = '(m.prix_par_personne * m.nombre_personne_minimum) >= ?';
            $params[] = (float) $f['prix_min_fourchette'];
        }
        if (!empty($f['prix_max_fourchette'])) {
            $where[] = '(m.prix_par_personne * m.nombre_personne_minimum) <= ?';
            $params[] = (float) $f['prix_max_fourchette'];
        }
        if (!empty($f['theme'])) {
            $where[] = 'm.theme_id = ?';
            $params[] = (int) $f['theme'];
        }
        if (!empty($f['regime'])) {
            $where[] = 'm.regime_id = ?';
            $params[] = (int) $f['regime'];
        }
        if (!empty($f['personnes'])) {
            // « nombre de personnes minimum » : menus commandables pour ce nombre de convives
            $where[] = 'm.nombre_personne_minimum <= ?';
            $params[] = (int) $f['personnes'];
        }
        return $this->all(self::SELECT . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY t.theme_id, m.titre', $params);
    }

    public function allForAdmin(): array
    {
        return $this->all(self::SELECT . ' ORDER BY m.actif DESC, m.titre');
    }

    public function find(int $id, bool $onlyActive = true): ?array
    {
        return $this->one(self::SELECT . ' WHERE m.menu_id = ?' . ($onlyActive ? ' AND m.actif = 1' : ''), [$id]);
    }

    /** Détail complet : galerie + plats groupés par type + allergènes de chaque plat. */
    public function detail(int $id, bool $onlyActive = true): ?array
    {
        $menu = $this->find($id, $onlyActive);
        if (!$menu) {
            return null;
        }
        $menu['images'] = $this->images($id);
        $plats = $this->all(
            "SELECT p.plat_id, p.titre_plat, p.type_plat, p.description, (p.photo IS NOT NULL) AS a_photo,
                    GROUP_CONCAT(a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes
             FROM menu_plat mp
             JOIN plat p ON p.plat_id = mp.plat_id
             LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
             WHERE mp.menu_id = ?
             GROUP BY p.plat_id
             ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.titre_plat",
            [$id]
        );
        $menu['plats'] = ['entree' => [], 'plat' => [], 'dessert' => []];
        foreach ($plats as $p) {
            $menu['plats'][$p['type_plat']][] = $p;
        }
        $menu['plat_ids'] = array_map(static fn ($p) => (int) $p['plat_id'], $plats);
        return $menu;
    }

    public function images(int $id): array
    {
        return $this->all('SELECT * FROM menu_image WHERE menu_id = ? ORDER BY ordre, image_id', [$id]);
    }

    public function save(?int $id, array $d, array $platIds): int
    {
        $this->db->beginTransaction();
        try {
            $params = [$d['titre'], $d['description'], (int) $d['nombre_personne_minimum'], (float) str_replace(',', '.', $d['prix_par_personne']),
                $d['conditions'], (int) $d['quantite_restante'], (int) $d['theme_id'], (int) $d['regime_id']];
            if ($id) {
                $params[] = $id;
                $this->run('UPDATE menu SET titre=?, description=?, nombre_personne_minimum=?, prix_par_personne=?, conditions=?,
                            quantite_restante=?, theme_id=?, regime_id=? WHERE menu_id=?', $params);
            } else {
                $this->run('INSERT INTO menu (titre, description, nombre_personne_minimum, prix_par_personne, conditions, quantite_restante, theme_id, regime_id)
                            VALUES (?,?,?,?,?,?,?,?)', $params);
                $id = (int) $this->db->lastInsertId();
            }
            $this->run('DELETE FROM menu_plat WHERE menu_id = ?', [$id]);
            $stmt = $this->db->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)');
            foreach (array_unique(array_map('intval', $platIds)) as $pid) {
                $stmt->execute([$id, $pid]);
            }
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addImage(int $menuId, string $chemin, string $alt): void
    {
        $this->run('INSERT INTO menu_image (menu_id, chemin, texte_alternatif, ordre)
                    VALUES (?, ?, ?, (SELECT COALESCE(MAX(ordre), 0) + 1 FROM menu_image mi WHERE mi.menu_id = ?))',
            [$menuId, $chemin, $alt, $menuId]);
    }

    public function deleteImage(int $imageId): ?array
    {
        $img = $this->one('SELECT * FROM menu_image WHERE image_id = ?', [$imageId]);
        if ($img) {
            $this->run('DELETE FROM menu_image WHERE image_id = ?', [$imageId]);
        }
        return $img;
    }

    /**
     * Suppression : si le menu a déjà été commandé, on le désactive (archivage)
     * pour conserver l'historique des commandes ; sinon suppression définitive.
     */
    public function delete(int $id): string
    {
        $used = $this->one('SELECT 1 FROM commande WHERE menu_id = ? LIMIT 1', [$id]);
        if ($used) {
            $this->run('UPDATE menu SET actif = 0 WHERE menu_id = ?', [$id]);
            return 'archive';
        }
        $this->run('DELETE FROM menu WHERE menu_id = ?', [$id]);
        return 'supprime';
    }

    public function reactiver(int $id): void
    {
        $this->run('UPDATE menu SET actif = 1 WHERE menu_id = ?', [$id]);
    }

    public function options(): array
    {
        return $this->all('SELECT menu_id, titre FROM menu ORDER BY titre');
    }
}
