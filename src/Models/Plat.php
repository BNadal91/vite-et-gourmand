<?php
declare(strict_types=1);

namespace App\Models;

final class Plat extends Model
{
    public function allWithAllergenes(): array
    {
        return $this->all(
            "SELECT p.plat_id, p.titre_plat, p.type_plat, p.description, (p.photo IS NOT NULL) AS a_photo,
                    GROUP_CONCAT(a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes,
                    (SELECT COUNT(*) FROM menu_plat mp WHERE mp.plat_id = p.plat_id) AS nb_menus
             FROM plat p
             LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
             GROUP BY p.plat_id
             ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.titre_plat"
        );
    }

    public function find(int $id): ?array
    {
        $p = $this->one('SELECT plat_id, titre_plat, type_plat, description, (photo IS NOT NULL) AS a_photo FROM plat WHERE plat_id = ?', [$id]);
        if ($p) {
            $p['allergene_ids'] = array_map('intval', array_column(
                $this->all('SELECT allergene_id FROM plat_allergene WHERE plat_id = ?', [$id]), 'allergene_id'));
        }
        return $p;
    }

    public function photo(int $id): ?array
    {
        return $this->one('SELECT photo, photo_mime FROM plat WHERE plat_id = ? AND photo IS NOT NULL', [$id]);
    }

    public function save(?int $id, array $d, array $allergeneIds, ?array $photo): int
    {
        $this->db->beginTransaction();
        try {
            if ($id) {
                $this->run('UPDATE plat SET titre_plat = ?, type_plat = ?, description = ? WHERE plat_id = ?',
                    [$d['titre_plat'], $d['type_plat'], $d['description'], $id]);
            } else {
                $this->run('INSERT INTO plat (titre_plat, type_plat, description) VALUES (?, ?, ?)',
                    [$d['titre_plat'], $d['type_plat'], $d['description']]);
                $id = (int) $this->db->lastInsertId();
            }
            if ($photo) {
                $stmt = $this->db->prepare('UPDATE plat SET photo = ?, photo_mime = ? WHERE plat_id = ?');
                $stmt->bindValue(1, $photo['data'], \PDO::PARAM_LOB);
                $stmt->bindValue(2, $photo['mime']);
                $stmt->bindValue(3, $id, \PDO::PARAM_INT);
                $stmt->execute();
            }
            $this->run('DELETE FROM plat_allergene WHERE plat_id = ?', [$id]);
            $stmt = $this->db->prepare('INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (?, ?)');
            foreach (array_unique(array_map('intval', $allergeneIds)) as $aid) {
                $stmt->execute([$id, $aid]);
            }
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM plat WHERE plat_id = ?', [$id]);
    }
}
