<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\Validator;
use App\Core\View;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\Tarif;

final class CommandeController
{
    /** Formulaire de commande, pré-rempli avec le compte et le menu choisi. */
    public function form(): void
    {
        $menus = (new Menu())->search();
        $menuId = (int) ($_GET['menu'] ?? 0);
        View::render('pages/commande', [
            'titre'   => 'Commander un menu',
            'user'    => Auth::user(),
            'menus'   => $menus,
            'menuId'  => $menuId,
            'scripts' => ['commande.js'],
        ]);
    }

    /** API : calcul du prix détaillé (appelée en JavaScript à chaque modification du formulaire). */
    public function apiPrix(): void
    {
        $menu = (new Menu())->find((int) ($_POST['menu_id'] ?? 0));
        $nombre = (int) ($_POST['nombre_personne'] ?? 0);
        if (!$menu) {
            View::json(['erreur' => 'Menu introuvable.'], 422);
            return;
        }
        $ville = trim((string) ($_POST['ville_livraison'] ?? ''));
        $dist = $ville === '' ? ['km' => 0.0, 'source' => 'inconnue'] :
            Tarif::distance((string) ($_POST['adresse_livraison'] ?? ''), (string) ($_POST['code_postal_livraison'] ?? ''), $ville);
        if ($dist === null) {
            $dist = ['km' => $this->distanceSaisie(), 'source' => 'saisie'];
        }
        $min = (int) $menu['nombre_personne_minimum'];
        $calc = Tarif::calculer($menu, max($nombre, $min), $ville ?: 'Bordeaux', (float) $dist['km']);
        View::json($calc + [
            'nombre_minimum'      => $min,
            'nombre_valide'       => $nombre >= $min,
            'seuil_reduction'     => $min + Tarif::SEUIL_REDUCTION,
            'source_distance'     => $dist['source'],
            'conditions'          => $menu['conditions'],
            'quantite_restante'   => (int) $menu['quantite_restante'],
            'prix_par_personne'   => (float) $menu['prix_par_personne'],
        ]);
    }

    public function store(): void
    {
        $user = Auth::user();
        [$v, $data, $menu] = $this->valider($_POST, null);
        if (empty($_POST['accepte_conditions'])) {
            $v->addError('accepte_conditions', 'Vous devez confirmer avoir lu les conditions du menu et les CGV.');
        }
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/commande?menu=' . (int) ($_POST['menu_id'] ?? 0));
        }

        $data += [
            'utilisateur_id' => (int) $user['utilisateur_id'],
            'menu_id'        => (int) $menu['menu_id'],
            // Nom, prénom et e-mail proviennent du compte : non modifiables dans le formulaire
            'nom_client'     => $user['nom'],
            'prenom_client'  => $user['prenom'],
            'email_client'   => $user['email'],
            'pret_materiel'  => !empty($_POST['pret_materiel']),
        ];
        try {
            $numero = (new Commande())->creer($data);
        } catch (\DomainException $e) {
            flash('danger', $e->getMessage());
            back_with_errors([], $_POST, '/commande?menu=' . (int) $menu['menu_id']);
        }

        Mailer::send($user['email'], 'Confirmation de votre commande ' . $numero, 'confirmation-commande', [
            'prenom' => $user['prenom'], 'numero' => $numero, 'menu' => $menu, 'c' => $data,
        ]);
        clear_old();
        flash('success', "Merci ! Votre commande n° $numero est enregistrée. Un e-mail de confirmation vous a été envoyé.");
        redirect('/compte/commandes/' . $numero);
    }

    /**
     * Validation et recalcul serveur des données de prestation.
     * Réutilisé pour la création et la modification (client ou employé).
     * @return array{0:Validator,1:array,2:?array}
     */
    public function valider(array $input, ?array $commandeExistante): array
    {
        $v = new Validator($input);
        $menu = (new Menu())->find((int) ($commandeExistante['menu_id'] ?? ($input['menu_id'] ?? 0)), $commandeExistante === null);
        if (!$menu) {
            $v->addError('menu_id', 'Merci de choisir un menu disponible.');
            return [$v, [], null];
        }
        $v->required('adresse_livraison', 'Adresse de la prestation')->maxLength('adresse_livraison', 255, 'Adresse')
          ->required('code_postal_livraison', 'Code postal')->postalCode('code_postal_livraison')
          ->required('ville_livraison', 'Ville')->maxLength('ville_livraison', 100, 'Ville')
          ->required('date_prestation', 'Date de la prestation')->date('date_prestation', 'date de prestation', true)
          ->required('heure_livraison', 'Heure de livraison')->time('heure_livraison', 'Heure de livraison')
          ->required('telephone_client', 'Numéro de GSM')->phone('telephone_client')
          ->intBetween('nombre_personne', (int) $menu['nombre_personne_minimum'], 1000, 'Nombre de personnes');

        if (isset(($errors = $v->errors())['nombre_personne'])) {
            $v->addError('nombre_personne', sprintf('Ce menu se commande pour %d personnes minimum.', $menu['nombre_personne_minimum']));
        }

        $dist = Tarif::distance($v->value('adresse_livraison'), $v->value('code_postal_livraison'), $v->value('ville_livraison'));
        $km = $dist['km'] ?? $this->distanceSaisie($input);
        if ($dist === null && $km <= 0 && !Tarif::estBordeaux($v->value('ville_livraison'))) {
            $v->addError('distance_km', "Nous n'avons pas pu calculer la distance automatiquement : merci d'indiquer la distance approximative depuis Bordeaux.");
        }

        $calc = Tarif::calculer($menu, (int) $v->value('nombre_personne'), $v->value('ville_livraison'), (float) $km);
        $data = [
            'date_prestation'       => $v->value('date_prestation'),
            'heure_livraison'       => substr($v->value('heure_livraison'), 0, 5),
            'adresse_livraison'     => $v->value('adresse_livraison'),
            'code_postal_livraison' => $v->value('code_postal_livraison'),
            'ville_livraison'       => $v->value('ville_livraison'),
            'telephone_client'      => preg_replace('/[\s.\-]/', '', $v->value('telephone_client')),
            'nombre_personne'       => (int) $v->value('nombre_personne'),
            'distance_km'           => $calc['distance_km'],
            'prix_menu'             => $calc['prix_menu'],
            'reduction'             => $calc['reduction'],
            'prix_livraison'        => $calc['prix_livraison'],
            'prix_total'            => $calc['prix_total'],
        ];
        return [$v, $data, $menu];
    }

    private function distanceSaisie(?array $input = null): float
    {
        $val = str_replace(',', '.', (string) (($input ?? $_POST)['distance_km'] ?? '0'));
        return is_numeric($val) ? max(0.0, min(500.0, (float) $val)) : 0.0;
    }
}
