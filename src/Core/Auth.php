<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Utilisateur;

/**
 * Authentification et contrôle d'accès par rôle.
 * La session ne contient que l'identifiant ; le rôle et l'état du compte
 * sont relus en base à chaque requête (un compte désactivé est déconnecté immédiatement).
 */
final class Auth
{
    public const UTILISATEUR = 'utilisateur';
    public const EMPLOYE = 'employe';
    public const ADMIN = 'administrateur';

    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = $_SESSION['user_id'] ?? null;
            if ($id) {
                $u = (new Utilisateur())->find((int) $id);
                if ($u && (int) $u['actif'] === 1) {
                    self::$user = $u;
                } else {
                    self::logout();
                }
            }
        }
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function is(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true); // empêche la fixation de session
        $_SESSION['user_id'] = (int) $user['utilisateur_id'];
        self::$loaded = false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        self::$user = null;
    }

    /** Refuse l'accès si l'utilisateur n'a pas l'un des rôles demandés. */
    public static function requireRole(array $roles): void
    {
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
            flash('info', 'Merci de vous connecter ou de créer un compte pour accéder à cette page.');
            redirect('/connexion');
        }
        if (!self::is(...$roles)) {
            http_response_code(403);
            View::render('pages/erreur', ['titre' => 'Accès refusé', 'code' => 403,
                'message' => "Vous n'avez pas les droits nécessaires pour accéder à cette page."]);
            exit;
        }
    }
}
