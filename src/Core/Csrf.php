<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Protection contre la falsification de requêtes inter-sites (CSRF) :
 * un jeton aléatoire est stocké en session et doit accompagner chaque POST
 * (champ caché « _csrf » ou en-tête « X-CSRF-Token » pour les appels fetch()).
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function verify(): void
    {
        $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!is_string($sent) || !hash_equals(self::token(), $sent)) {
            http_response_code(419);
            View::render('pages/erreur', ['titre' => 'Session expirée', 'code' => 419,
                'message' => 'Le formulaire a expiré. Merci de recharger la page et de réessayer.']);
            exit;
        }
    }
}
