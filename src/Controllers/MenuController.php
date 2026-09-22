<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Catalogue;
use App\Models\Menu;

final class MenuController
{
    /** Vue globale de tous les menus (accessible aux visiteurs et aux personnes connectées). */
    public function index(): void
    {
        $cat = new Catalogue();
        View::render('pages/menus', [
            'titre'   => 'Nos menus',
            'menus'   => (new Menu())->search($this->filtres()),
            'themes'  => $cat->themes(),
            'regimes' => $cat->regimes(),
            'f'       => $this->filtres(),
            'scripts' => ['menus.js'],
        ]);
    }

    /** API JSON utilisée par les filtres dynamiques (fetch, sans rechargement de page). */
    public function api(): void
    {
        $menus = (new Menu())->search($this->filtres());
        View::json(array_map(static fn (array $m) => [
            'id'                      => (int) $m['menu_id'],
            'titre'                   => $m['titre'],
            'description'             => $m['description'],
            'theme'                   => $m['theme'],
            'regime'                  => $m['regime'],
            'nombre_personne_minimum' => (int) $m['nombre_personne_minimum'],
            'prix_par_personne'       => (float) $m['prix_par_personne'],
            'prix_minimum'            => (float) $m['prix_minimum'],
            'quantite_restante'       => (int) $m['quantite_restante'],
            'image'                   => $m['image'],
            'image_alt'               => $m['image_alt'],
        ], $menus));
    }

    public function show(string $id): void
    {
        $menu = (new Menu())->detail((int) $id);
        if (!$menu) {
            http_response_code(404);
            View::render('pages/erreur', ['titre' => 'Menu introuvable', 'code' => 404, 'message' => "Ce menu n'existe pas ou n'est plus proposé."]);
            return;
        }
        View::render('pages/menu', ['titre' => $menu['titre'], 'menu' => $menu]);
    }

    private function filtres(): array
    {
        $num = static fn (string $k): ?float => isset($_GET[$k]) && is_numeric($_GET[$k]) && (float) $_GET[$k] >= 0 ? (float) $_GET[$k] : null;
        return [
            'prix_max'            => $num('prix_max'),
            'prix_min_fourchette' => $num('prix_min_fourchette'),
            'prix_max_fourchette' => $num('prix_max_fourchette'),
            'theme'               => $num('theme'),
            'regime'              => $num('regime'),
            'personnes'           => $num('personnes'),
        ];
    }
}
