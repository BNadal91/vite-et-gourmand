<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Moteur de rendu : une vue PHP est incluse dans le gabarit commun (layout).
 * Toutes les variables affichées doivent passer par e() (échappement XSS).
 */
final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layout/main'): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require ROOT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        require ROOT_PATH . '/views/' . $layout . '.php';
    }

    /** Rendu d'un gabarit d'e-mail en chaîne de caractères. */
    public static function fetch(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require ROOT_PATH . '/views/' . $view . '.php';
        return (string) ob_get_clean();
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
