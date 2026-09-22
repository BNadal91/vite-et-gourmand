<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Avis;
use App\Models\Menu;
use App\Models\Plat;

final class HomeController
{
    public function index(): void
    {
        $avis = new Avis();
        $menus = array_slice((new Menu())->search(), 0, 3);
        View::render('pages/accueil', [
            'titre'   => 'Traiteur événementiel à Bordeaux depuis 25 ans',
            'avis'    => $avis->valides(6),
            'moyenne' => $avis->moyenne(),
            'menus'   => $menus,
        ]);
    }

    public function mentions(): void
    {
        View::render('pages/mentions', ['titre' => 'Mentions légales']);
    }

    public function cgv(): void
    {
        View::render('pages/cgv', ['titre' => 'Conditions générales de vente']);
    }

    public function confidentialite(): void
    {
        View::render('pages/confidentialite', ['titre' => 'Politique de confidentialité']);
    }

    /** Photo d'un plat stockée en BLOB dans la base. */
    public function photoPlat(string $id): void
    {
        $p = (new Plat())->photo((int) $id);
        if (!$p) {
            http_response_code(404);
            return;
        }
        header('Content-Type: ' . $p['photo_mime']);
        header('Cache-Control: public, max-age=86400');
        echo $p['photo'];
    }
}
