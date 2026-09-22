<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\Upload;
use App\Core\Validator;
use App\Core\View;
use App\Models\Avis;
use App\Models\Catalogue;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\Plat;

/**
 * Espace employé (également accessible à l'administrateur) :
 * commandes, menus, plats, horaires et modération des avis.
 */
final class EmployeController
{
    // ------------------------------------------------------------------ Commandes
    public function commandes(): void
    {
        $statut = isset($_GET['statut']) && array_key_exists($_GET['statut'], STATUTS) ? $_GET['statut'] : null;
        $client = isset($_GET['client']) && is_string($_GET['client']) ? mb_substr(trim($_GET['client']), 0, 100) : null;
        View::render('employe/commandes', [
            'titre'     => 'Gestion des commandes',
            'commandes' => (new Commande())->search($statut, $client ?: null),
            'statut'    => $statut,
            'client'    => $client,
        ], 'layout/main');
    }

    public function commande(string $numero): void
    {
        $model = new Commande();
        $c = $model->find($numero) ?? $this->notFound();
        View::render('employe/commande', [
            'titre'       => 'Commande ' . $numero,
            'c'           => $c,
            'suivi'       => $model->suivi($numero),
            'transitions' => Commande::TRANSITIONS[$c['statut']] ?? [],
        ]);
    }

    public function statut(string $numero): void
    {
        $model = new Commande();
        $c = $model->find($numero) ?? $this->notFound();
        $nouveau = (string) ($_POST['statut'] ?? '');
        if (!in_array($nouveau, Commande::TRANSITIONS[$c['statut']] ?? [], true)) {
            flash('danger', 'Ce changement de statut n\'est pas autorisé.');
            redirect('/employe/commandes/' . $numero);
        }
        // Règle métier : une commande avec prêt de matériel passe obligatoirement par « attente du retour »
        if ($nouveau === 'terminee' && $c['statut'] === 'livre' && (int) $c['pret_materiel'] === 1) {
            flash('danger', 'Du matériel a été prêté : passez d\'abord la commande en « attente du retour de matériel ».');
            redirect('/employe/commandes/' . $numero);
        }
        $model->changerStatut($numero, $nouveau, 'Statut mis à jour par l\'équipe');

        if ($nouveau === 'attente_retour_materiel') {
            Mailer::send($c['email_client'], 'Restitution du matériel prêté — commande ' . $numero, 'retour-materiel', ['c' => $c]);
        }
        if ($nouveau === 'terminee') {
            Mailer::send($c['email_client'], 'Votre avis nous intéresse — commande ' . $numero, 'commande-terminee', ['c' => $c]);
        }
        flash('success', 'Statut mis à jour : ' . statut_label($nouveau) . '.');
        redirect('/employe/commandes/' . $numero);
    }

    /**
     * Annulation ou modification par l'employé : obligatoire d'avoir contacté le client
     * au préalable (mode de contact + motif enregistrés).
     */
    public function annuler(string $numero): void
    {
        $c = (new Commande())->find($numero) ?? $this->notFound();
        $v = (new Validator($_POST))->in('mode_contact', ['gsm', 'mail'], 'Mode de contact')
            ->required('motif', 'Motif')->maxLength('motif', 450, 'Motif');
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/employe/commandes/' . $numero . '#annulation');
        }
        try {
            (new Commande())->annuler($numero, 'Contact client par ' . strtoupper($v->value('mode_contact')) . ' — ' . $v->value('motif'), $v->value('mode_contact'));
        } catch (\DomainException $e) {
            flash('danger', $e->getMessage());
            redirect('/employe/commandes/' . $numero);
        }
        Mailer::send($c['email_client'], 'Annulation de votre commande ' . $numero, 'annulation', ['c' => $c, 'motif' => $v->value('motif')]);
        flash('success', 'La commande a été annulée.');
        redirect('/employe/commandes/' . $numero);
    }

    public function modifierForm(string $numero): void
    {
        $c = (new Commande())->find($numero) ?? $this->notFound();
        View::render('employe/modifier-commande', ['titre' => 'Modifier la commande ' . $numero, 'c' => $c, 'scripts' => ['commande.js']]);
    }

    public function modifier(string $numero): void
    {
        $c = (new Commande())->find($numero) ?? $this->notFound();
        if (in_array($c['statut'], ['annulee', 'terminee'], true)) {
            flash('danger', 'Cette commande ne peut plus être modifiée.');
            redirect('/employe/commandes/' . $numero);
        }
        [$v, $data] = (new CommandeController())->valider($_POST, $c);
        $v->in('mode_contact', ['gsm', 'mail'], 'Mode de contact')->required('motif', 'Motif');
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/employe/commandes/' . $numero . '/modifier');
        }
        $data['mode_contact'] = $v->value('mode_contact');
        (new Commande())->modifier($numero, $data, 'Modifiée par l\'équipe après contact par ' . strtoupper($v->value('mode_contact')) . ' — ' . $v->value('motif'));
        clear_old();
        flash('success', 'La commande a été modifiée.');
        redirect('/employe/commandes/' . $numero);
    }

    // ------------------------------------------------------------------ Menus
    public function menus(): void
    {
        View::render('employe/menus', ['titre' => 'Gestion des menus', 'menus' => (new Menu())->allForAdmin()]);
    }

    public function menuForm(?string $id = null): void
    {
        $menu = $id ? ((new Menu())->detail((int) $id, false) ?? $this->notFound()) : null;
        $cat = new Catalogue();
        View::render('employe/menu-form', [
            'titre'   => $menu ? 'Modifier le menu' : 'Nouveau menu',
            'menu'    => $menu,
            'themes'  => $cat->themes(),
            'regimes' => $cat->regimes(),
            'plats'   => (new Plat())->allWithAllergenes(),
        ]);
    }

    public function menuSave(?string $id = null): void
    {
        $v = (new Validator($_POST))
            ->required('titre', 'Titre')->maxLength('titre', 120, 'Titre')
            ->required('description', 'Description')
            ->intBetween('nombre_personne_minimum', 1, 1000, 'Nombre de personnes minimum')
            ->number('prix_par_personne', 0, 'Prix par personne')
            ->required('conditions', 'Conditions')
            ->intBetween('quantite_restante', 0, 10000, 'Stock disponible')
            ->intBetween('theme_id', 1, PHP_INT_MAX, 'Thème')
            ->intBetween('regime_id', 1, PHP_INT_MAX, 'Régime');
        $plats = array_filter((array) ($_POST['plats'] ?? []), 'is_numeric');
        if ($plats === []) {
            $v->addError('plats', 'Sélectionnez au moins un plat.');
        }
        $path = $id ? '/employe/menus/' . (int) $id : '/employe/menus/nouveau';
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, $path);
        }
        $menuId = (new Menu())->save($id ? (int) $id : null, $_POST, $plats);

        $err = null;
        $img = Upload::image('image', $err);
        if ($img) {
            (new Menu())->addImage($menuId, Upload::store($img), trim((string) ($_POST['image_alt'] ?? '')) ?: 'Photo du menu ' . $v->value('titre'));
        } elseif ($err) {
            flash('warning', $err);
        }
        clear_old();
        flash('success', 'Le menu a été enregistré.');
        redirect('/employe/menus/' . $menuId);
    }

    public function menuDelete(string $id): void
    {
        $r = (new Menu())->delete((int) $id);
        flash('success', $r === 'archive' ? 'Ce menu a déjà été commandé : il a été archivé (retiré du catalogue) pour conserver l\'historique.' : 'Le menu a été supprimé.');
        redirect('/employe/menus');
    }

    public function menuReactiver(string $id): void
    {
        (new Menu())->reactiver((int) $id);
        flash('success', 'Le menu est de nouveau visible dans le catalogue.');
        redirect('/employe/menus');
    }

    public function imageDelete(string $id): void
    {
        $img = (new Menu())->deleteImage((int) $id);
        if ($img && str_starts_with($img['chemin'], '/uploads/')) {
            @unlink(ROOT_PATH . '/public' . $img['chemin']);
        }
        flash('success', 'Image supprimée.');
        redirect('/employe/menus/' . (int) ($img['menu_id'] ?? 0));
    }

    // ------------------------------------------------------------------ Plats
    public function plats(): void
    {
        View::render('employe/plats', ['titre' => 'Gestion des plats', 'plats' => (new Plat())->allWithAllergenes()]);
    }

    public function platForm(?string $id = null): void
    {
        $plat = $id ? ((new Plat())->find((int) $id) ?? $this->notFound()) : null;
        View::render('employe/plat-form', [
            'titre'      => $plat ? 'Modifier le plat' : 'Nouveau plat',
            'plat'       => $plat,
            'allergenes' => (new Catalogue())->allergenes(),
        ]);
    }

    public function platSave(?string $id = null): void
    {
        $v = (new Validator($_POST))
            ->required('titre_plat', 'Nom du plat')->maxLength('titre_plat', 120, 'Nom du plat')
            ->in('type_plat', ['entree', 'plat', 'dessert'], 'Type')
            ->maxLength('description', 255, 'Description');
        $err = null;
        $photo = Upload::image('photo', $err);
        if ($err) {
            $v->addError('photo', $err);
        }
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, $id ? '/employe/plats/' . (int) $id : '/employe/plats/nouveau');
        }
        (new Plat())->save($id ? (int) $id : null, [
            'titre_plat' => $v->value('titre_plat'), 'type_plat' => $v->value('type_plat'), 'description' => $v->value('description'),
        ], array_filter((array) ($_POST['allergenes'] ?? []), 'is_numeric'), $photo);
        clear_old();
        flash('success', 'Le plat a été enregistré.');
        redirect('/employe/plats');
    }

    public function platDelete(string $id): void
    {
        (new Plat())->delete((int) $id);
        flash('success', 'Le plat a été supprimé (et retiré des menus qui le contenaient).');
        redirect('/employe/plats');
    }

    // ------------------------------------------------------------------ Horaires
    public function horaires(): void
    {
        View::render('employe/horaires', ['titre' => 'Horaires d\'ouverture', 'horaires' => (new Catalogue())->horaires()]);
    }

    public function horairesSave(): void
    {
        $cat = new Catalogue();
        foreach ($cat->horaires() as $h) {
            $id = (int) $h['horaire_id'];
            $ferme = !empty($_POST['ferme'][$id]);
            $o = (string) ($_POST['ouverture'][$id] ?? '');
            $f = (string) ($_POST['fermeture'][$id] ?? '');
            $valid = static fn (string $t): bool => (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t);
            if ($ferme) {
                $cat->updateHoraire($id, null, null);
            } elseif ($valid($o) && $valid($f) && $o < $f) {
                $cat->updateHoraire($id, $o, $f);
            } else {
                flash('warning', 'Horaire invalide pour ' . $h['jour'] . ' : non modifié.');
            }
        }
        flash('success', 'Les horaires ont été mis à jour.');
        redirect('/employe/horaires');
    }

    // ------------------------------------------------------------------ Avis
    public function avis(): void
    {
        $statut = in_array($_GET['statut'] ?? '', ['en_attente', 'valide', 'refuse'], true) ? $_GET['statut'] : null;
        View::render('employe/avis', ['titre' => 'Modération des avis', 'avis' => (new Avis())->parStatut($statut), 'statut' => $statut]);
    }

    public function avisModerer(string $id): void
    {
        $decision = (string) ($_POST['decision'] ?? '');
        if (in_array($decision, ['valide', 'refuse'], true)) {
            (new Avis())->moderer((int) $id, $decision);
            flash('success', $decision === 'valide' ? 'Avis validé : il est désormais visible sur la page d\'accueil.' : 'Avis refusé.');
        }
        redirect('/employe/avis');
    }

    private function notFound(): never
    {
        http_response_code(404);
        View::render('pages/erreur', ['titre' => 'Introuvable', 'code' => 404, 'message' => "L'élément demandé n'existe pas."]);
        exit;
    }
}
