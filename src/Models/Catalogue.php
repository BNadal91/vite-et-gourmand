<?php
declare(strict_types=1);

namespace App\Models;

/** Données de référence : thèmes, régimes, allergènes, horaires. */
final class Catalogue extends Model
{
    public function themes(): array
    {
        return $this->all('SELECT * FROM theme ORDER BY theme_id');
    }

    public function regimes(): array
    {
        return $this->all('SELECT * FROM regime ORDER BY regime_id');
    }

    public function allergenes(): array
    {
        return $this->all('SELECT * FROM allergene ORDER BY libelle');
    }

    public function ajouterRegime(string $libelle): void
    {
        $this->run('INSERT IGNORE INTO regime (libelle) VALUES (?)', [$libelle]);
    }

    public function ajouterTheme(string $libelle): void
    {
        $this->run('INSERT IGNORE INTO theme (libelle) VALUES (?)', [$libelle]);
    }

    public function horaires(): array
    {
        return $this->all('SELECT * FROM horaire ORDER BY ordre');
    }

    public function updateHoraire(int $id, ?string $ouverture, ?string $fermeture): void
    {
        $this->run('UPDATE horaire SET heure_ouverture = ?, heure_fermeture = ? WHERE horaire_id = ?',
            [$ouverture ?: null, $fermeture ?: null, $id]);
    }

    public function enregistrerMessage(string $titre, string $description, string $email): void
    {
        $this->run('INSERT INTO message_contact (titre, description, email) VALUES (?, ?, ?)', [$titre, $description, $email]);
    }
}
