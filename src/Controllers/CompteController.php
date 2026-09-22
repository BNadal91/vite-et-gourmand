<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Validator;
use App\Core\View;
use App\Models\Avis;
use App\Models\Commande;
use App\Models\Utilisateur;

/** Espace utilisateur : commandes, suivi, avis, informations personnelles. */
final class CompteController
{
    public function index(): void
    {
        $user = Auth::user();
        View::render('compte/index', [
            'titre'     => 'Mon espace',
            'user'      => $user,
            'commandes' => (new Commande())->forUser((int) $user['utilisateur_id']),
        ]);
    }

    public function show(string $numero): void
    {
        $c = $this->commandeOr404($numero);
        View::render('compte/commande', [
            'titre' => 'Commande ' . $c['numero_commande'],
            'c'     => $c,
            'suivi' => (new Commande())->suivi($numero),
        ]);
    }

    public function editForm(string $numero): void
    {
        $c = $this->commandeOr404($numero);
        if ($c['statut'] !== 'en_attente') {
            flash('warning', 'Cette commande a déjà été acceptée : elle ne peut plus être modifiée. Contactez-nous si besoin.');
            redirect('/compte/commandes/' . $numero);
        }
        View::render('compte/modifier', ['titre' => 'Modifier ma commande', 'c' => $c, 'scripts' => ['commande.js']]);
    }

    /** Modification par le client : tout sauf le menu, uniquement avant acceptation. */
    public function update(string $numero): void
    {
        $c = $this->commandeOr404($numero);
        if ($c['statut'] !== 'en_attente') {
            flash('warning', 'Cette commande ne peut plus être modifiée.');
            redirect('/compte/commandes/' . $numero);
        }
        [$v, $data] = (new CommandeController())->valider($_POST, $c);
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/compte/commandes/' . $numero . '/modifier');
        }
        (new Commande())->modifier($numero, $data, 'Commande modifiée par le client');
        clear_old();
        flash('success', 'Votre commande a été mise à jour.');
        redirect('/compte/commandes/' . $numero);
    }

    public function cancel(string $numero): void
    {
        $c = $this->commandeOr404($numero);
        if ($c['statut'] !== 'en_attente') {
            flash('warning', 'Cette commande a déjà été acceptée : elle ne peut plus être annulée en ligne.');
            redirect('/compte/commandes/' . $numero);
        }
        (new Commande())->annuler($numero, 'Annulée par le client', null);
        flash('success', 'Votre commande a été annulée.');
        redirect('/compte');
    }

    /** Dépôt d'un avis (note 1 à 5 + commentaire) une fois la commande terminée. */
    public function avis(string $numero): void
    {
        $c = $this->commandeOr404($numero);
        if ($c['statut'] !== 'terminee' || $c['avis_id']) {
            flash('warning', 'Vous ne pouvez pas (ou plus) donner votre avis sur cette commande.');
            redirect('/compte/commandes/' . $numero);
        }
        $v = (new Validator($_POST))->intBetween('note', 1, 5, 'Note')
            ->required('description', 'Commentaire')->maxLength('description', 1000, 'Commentaire');
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/compte/commandes/' . $numero . '#avis');
        }
        (new Avis())->creer((int) Auth::user()['utilisateur_id'], $numero, (int) $v->value('note'), $v->value('description'));
        clear_old();
        flash('success', 'Merci pour votre avis ! Il sera publié après validation par notre équipe.');
        redirect('/compte/commandes/' . $numero);
    }

    public function profilForm(): void
    {
        View::render('compte/profil', ['titre' => 'Mes informations', 'user' => Auth::user(), 'scripts' => ['password.js']]);
    }

    public function profil(): void
    {
        $user = Auth::user();
        $v = new Validator($_POST);
        $v->required('nom', 'Nom')->maxLength('nom', 80, 'Nom')
          ->required('prenom', 'Prénom')->maxLength('prenom', 80, 'Prénom')
          ->required('telephone', 'GSM')->phone('telephone')
          ->required('email', 'E-mail')->email('email')
          ->required('adresse_postale', 'Adresse postale')->maxLength('adresse_postale', 255, 'Adresse')
          ->required('code_postal', 'Code postal')->postalCode('code_postal')
          ->required('ville', 'Ville')->maxLength('ville', 100, 'Ville');
        $users = new Utilisateur();
        if (!$v->fails() && $users->emailExists($v->value('email'), (int) $user['utilisateur_id'])) {
            $v->addError('email', 'Cette adresse e-mail ne peut pas être utilisée.');
        }
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/compte/profil');
        }
        $users->updateProfil((int) $user['utilisateur_id'], [
            'nom' => $v->value('nom'), 'prenom' => $v->value('prenom'),
            'telephone' => preg_replace('/[\s.\-]/', '', $v->value('telephone')),
            'email' => $v->value('email'), 'adresse_postale' => $v->value('adresse_postale'),
            'code_postal' => $v->value('code_postal'), 'ville' => $v->value('ville'),
            'pays' => $v->value('pays') ?: 'France',
        ]);
        clear_old();
        flash('success', 'Vos informations ont été mises à jour.');
        redirect('/compte/profil');
    }

    public function password(): void
    {
        $user = Auth::user();
        $full = (new Utilisateur())->findByEmail($user['email']);
        $v = (new Validator($_POST))->password('password')
            ->same('password', 'password_confirmation', 'Les deux mots de passe ne correspondent pas.');
        if (!password_verify((string) ($_POST['current_password'] ?? ''), $full['password'])) {
            $v->addError('current_password', 'Le mot de passe actuel est incorrect.');
        }
        if ($v->fails()) {
            back_with_errors($v->errors(), [], '/compte/profil#mot-de-passe');
        }
        (new Utilisateur())->updatePassword((int) $user['utilisateur_id'], $v->value('password'));
        flash('success', 'Votre mot de passe a été modifié.');
        redirect('/compte/profil');
    }

    /** RGPD — droit à l'effacement. */
    public function delete(): void
    {
        $user = Auth::user();
        $full = (new Utilisateur())->findByEmail($user['email']);
        if ($user['role'] !== Auth::UTILISATEUR || !password_verify((string) ($_POST['confirm_password'] ?? ''), $full['password'])) {
            flash('danger', 'Suppression impossible : mot de passe incorrect.');
            redirect('/compte/profil');
        }
        (new Utilisateur())->supprimer((int) $user['utilisateur_id']);
        Auth::logout();
        flash('success', 'Votre compte et vos données personnelles ont été supprimés.');
        redirect('/');
    }

    private function commandeOr404(string $numero): array
    {
        $c = (new Commande())->findForUser($numero, (int) Auth::user()['utilisateur_id']);
        if (!$c) {
            http_response_code(404);
            View::render('pages/erreur', ['titre' => 'Commande introuvable', 'code' => 404, 'message' => "Cette commande n'existe pas."]);
            exit;
        }
        return $c;
    }
}
