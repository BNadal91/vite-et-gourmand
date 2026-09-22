<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\Validator;
use App\Core\View;
use App\Models\Utilisateur;

final class AuthController
{
    private const MAX_TENTATIVES = 5; // au-delà : blocage 15 minutes

    public function registerForm(): void
    {
        if (Auth::check()) {
            redirect('/compte');
        }
        View::render('pages/inscription', ['titre' => 'Créer un compte', 'scripts' => ['password.js']]);
    }

    public function register(): void
    {
        $v = new Validator($_POST);
        $v->required('nom', 'Nom')->maxLength('nom', 80, 'Nom')
          ->required('prenom', 'Prénom')->maxLength('prenom', 80, 'Prénom')
          ->required('telephone', 'Numéro de GSM')->phone('telephone')
          ->required('email', 'Adresse e-mail')->email('email')->maxLength('email', 180, 'Adresse e-mail')
          ->required('adresse_postale', 'Adresse postale')->maxLength('adresse_postale', 255, 'Adresse postale')
          ->required('code_postal', 'Code postal')->postalCode('code_postal')
          ->required('ville', 'Ville')->maxLength('ville', 100, 'Ville')
          ->password('password')
          ->same('password', 'password_confirmation', 'Les deux mots de passe ne correspondent pas.');
        if (empty($_POST['rgpd'])) {
            $v->addError('rgpd', 'Vous devez accepter la politique de confidentialité pour créer un compte.');
        }
        $users = new Utilisateur();
        if (!$v->fails() && $users->emailExists($v->value('email'))) {
            // Message volontairement neutre pour limiter l'énumération de comptes
            $v->addError('email', 'Impossible de créer un compte avec cette adresse e-mail.');
        }
        if ($v->fails()) {
            back_with_errors($v->errors(), $_POST, '/inscription');
        }

        $data = [
            'nom' => $v->value('nom'), 'prenom' => $v->value('prenom'),
            'telephone' => preg_replace('/[\s.\-]/', '', $v->value('telephone')),
            'email' => $v->value('email'), 'password' => $v->value('password'),
            'adresse_postale' => $v->value('adresse_postale'), 'code_postal' => $v->value('code_postal'),
            'ville' => $v->value('ville'), 'pays' => 'France',
        ];
        $id = $users->create($data, Auth::UTILISATEUR); // rôle « utilisateur » attribué d'office
        Mailer::send($data['email'], 'Bienvenue chez Vite & Gourmand', 'bienvenue', ['prenom' => $data['prenom']]);

        clear_old();
        Auth::login($users->find($id));
        flash('success', 'Votre compte a bien été créé. Un e-mail de bienvenue vous a été envoyé.');
        $this->redirectAfterLogin('/compte');
    }

    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('/compte');
        }
        View::render('pages/connexion', ['titre' => 'Connexion']);
    }

    public function login(): void
    {
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $users = new Utilisateur();

        if ($users->tentativesRecentes($email, client_ip()) >= self::MAX_TENTATIVES) {
            flash('danger', 'Trop de tentatives de connexion. Merci de réessayer dans 15 minutes.');
            back_with_errors([], ['email' => $email], '/connexion');
        }

        $user = $users->findByEmail($email);
        // password_verify est exécuté même si le compte n'existe pas (temps de réponse constant)
        $hash = $user['password'] ?? '$2y$12$eC8FQSBX.YgkiKa5B/z.duWjq3/NIq1R5E.aBcDbg5TXsqSbCDwjy';
        $ok = password_verify($password, $hash) && $user !== null && (int) $user['actif'] === 1;

        if (!$ok) {
            $users->enregistrerEchec($email, client_ip());
            flash('danger', 'Identifiants incorrects.'); // message générique : on ne dit pas si l'e-mail existe
            back_with_errors([], ['email' => $email], '/connexion');
        }

        $users->effacerEchecs($email);
        $users->rehashIfNeeded($user, $password);
        Auth::login($user);
        clear_old();
        flash('success', 'Bonjour ' . $user['prenom'] . ', vous êtes connecté(e).');

        $default = match ($user['role']) {
            Auth::ADMIN => '/admin',
            Auth::EMPLOYE => '/employe',
            default => '/compte',
        };
        $this->redirectAfterLogin($default);
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'Vous êtes déconnecté(e).');
        redirect('/');
    }

    public function forgotForm(): void
    {
        View::render('pages/mot-de-passe-oublie', ['titre' => 'Mot de passe oublié']);
    }

    public function forgot(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $users = new Utilisateur();
            $user = $users->findByEmail($email);
            if ($user && (int) $user['actif'] === 1) {
                $token = $users->creerJetonReinitialisation((int) $user['utilisateur_id']);
                Mailer::send($user['email'], 'Réinitialisation de votre mot de passe', 'reinitialisation', [
                    'prenom' => $user['prenom'],
                    'lien'   => base_url() . '/reinitialiser-mot-de-passe?token=' . $token,
                ]);
            }
        }
        // Même message que le compte existe ou non (pas d'énumération des comptes)
        flash('info', 'Si un compte correspond à cette adresse, un e-mail contenant un lien de réinitialisation vient de vous être envoyé. Le lien est valable 1 heure.');
        redirect('/connexion');
    }

    public function resetForm(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/', $token) || !(new Utilisateur())->jetonValide($token)) {
            flash('danger', 'Ce lien de réinitialisation est invalide ou a expiré.');
            redirect('/mot-de-passe-oublie');
        }
        View::render('pages/reinitialiser', ['titre' => 'Nouveau mot de passe', 'token' => $token, 'scripts' => ['password.js']]);
    }

    public function reset(): void
    {
        $token = (string) ($_POST['token'] ?? '');
        $users = new Utilisateur();
        $jeton = preg_match('/^[a-f0-9]{64}$/', $token) ? $users->jetonValide($token) : null;
        if (!$jeton) {
            flash('danger', 'Ce lien de réinitialisation est invalide ou a expiré.');
            redirect('/mot-de-passe-oublie');
        }
        $v = (new Validator($_POST))->password('password')
            ->same('password', 'password_confirmation', 'Les deux mots de passe ne correspondent pas.');
        if ($v->fails()) {
            back_with_errors($v->errors(), [], '/reinitialiser-mot-de-passe?token=' . $token);
        }
        $users->updatePassword((int) $jeton['utilisateur_id'], $v->value('password'));
        $users->consommerJeton((int) $jeton['reinitialisation_id']); // jeton à usage unique
        flash('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');
        redirect('/connexion');
    }

    private function redirectAfterLogin(string $default): never
    {
        $to = $_SESSION['redirect_after_login'] ?? $default;
        unset($_SESSION['redirect_after_login']);
        redirect($to);
    }
}
