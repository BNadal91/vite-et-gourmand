<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\Mongo;
use App\Core\Validator;
use App\Core\View;
use App\Models\Menu;
use App\Models\Utilisateur;

/** Espace administrateur : comptes employés et statistiques (MongoDB). */
final class AdminController
{
    public function index(): void
    {
        View::render('admin/index', [
            'titre'   => 'Administration',
            'menus'   => (new Menu())->options(),
            'mongoOk' => Mongo::enabled(),
            'scripts' => ['admin-stats.js'],
            'chart'   => true,
        ]);
    }

    public function employes(): void
    {
        View::render('admin/employes', ['titre' => 'Comptes employés', 'employes' => (new Utilisateur())->employes(), 'scripts' => ['password.js']]);
    }

    /**
     * Création d'un compte employé. Le mot de passe n'est PAS envoyé par e-mail :
     * l'employé doit se rapprocher de l'administrateur pour l'obtenir.
     * Aucun compte administrateur ne peut être créé depuis l'application.
     */
    public function createEmploye(): void
    {
        $v = (new Validator($_POST))
            ->required('email', 'E-mail')->email('email')
            ->required('nom', 'Nom')->maxLength('nom', 80, 'Nom')
            ->required('prenom', 'Prénom')->maxLength('prenom', 80, 'Prénom')
            ->password('password');
        $users = new Utilisateur();
        if (!$v->fails() && $users->emailExists($v->value('email'))) {
            $v->addError('email', 'Un compte existe déjà avec cette adresse.');
        }
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/admin/employes');
        }
        $users->create([
            'email' => $v->value('email'), 'password' => $v->value('password'),
            'nom' => $v->value('nom'), 'prenom' => $v->value('prenom'),
        ], Auth::EMPLOYE); // rôle forcé côté serveur : jamais lu depuis le formulaire
        Mailer::send($v->value('email'), 'Votre compte employé Vite & Gourmand', 'compte-employe', [
            'prenom' => $v->value('prenom'), 'email' => $v->value('email'),
        ]);
        clear_old();
        flash('success', 'Compte employé créé. Communiquez le mot de passe à l\'employé en main propre.');
        redirect('/admin/employes');
    }

    public function toggleEmploye(string $id): void
    {
        $actif = ($_POST['actif'] ?? '') === '1';
        (new Utilisateur())->setActif((int) $id, $actif);
        flash('success', $actif ? 'Le compte a été réactivé.' : 'Le compte a été désactivé : l\'employé ne peut plus se connecter.');
        redirect('/admin/employes');
    }

    /** API JSON : commandes et chiffre d'affaires par menu (données issues de MongoDB). */
    public function apiStats(): void
    {
        $menu = isset($_GET['menu']) && ctype_digit((string) $_GET['menu']) ? (int) $_GET['menu'] : null;
        $isDate = static fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : null;
        if (!Mongo::enabled()) {
            View::json(['erreur' => 'La base MongoDB n\'est pas configurée.'], 503);
            return;
        }
        try {
            $stats = Mongo::statsParMenu($menu ?: null, $isDate($_GET['du'] ?? null), $isDate($_GET['au'] ?? null));
            View::json([
                'menus'  => $stats,
                'total'  => [
                    'nombre_commandes' => array_sum(array_column($stats, 'nombre_commandes')),
                    'chiffre_affaires' => round(array_sum(array_column($stats, 'chiffre_affaires')), 2),
                ],
                'source' => 'mongodb',
            ]);
        } catch (\Throwable $e) {
            error_log('[Stats] ' . $e->getMessage());
            View::json(['erreur' => 'Statistiques momentanément indisponibles.'], 503);
        }
    }
}
