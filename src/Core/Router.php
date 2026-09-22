<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Routeur minimaliste : associe une méthode HTTP + un chemin à un contrôleur.
 * Les paramètres dynamiques s'écrivent {id} et n'acceptent que des chiffres ou
 * des caractères sûrs (liste blanche via expression régulière).
 */
final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $roles = []): void
    {
        $this->add('GET', $path, $handler, $roles);
    }

    public function post(string $path, array $handler, array $roles = []): void
    {
        $this->add('POST', $path, $handler, $roles);
    }

    private function add(string $method, string $path, array $handler, array $roles): void
    {
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[A-Za-z0-9\-]+)', $path);
        $this->routes[] = [$method, '#^' . $pattern . '$#', $handler, $roles];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rawurldecode(parse_url($uri, PHP_URL_PATH) ?: '/');
        $path = $path !== '/' ? rtrim($path, '/') : '/';

        foreach ($this->routes as [$m, $regex, $handler, $roles]) {
            if ($m !== $method || !preg_match($regex, $path, $matches)) {
                continue;
            }
            // Contrôle d'accès par rôle (RBAC) avant d'exécuter le contrôleur
            if ($roles !== []) {
                Auth::requireRole($roles);
            }
            // Protection CSRF systématique sur toutes les requêtes POST
            if ($method === 'POST') {
                Csrf::verify();
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            [$class, $action] = $handler;
            (new $class())->$action(...array_values($params));
            return;
        }
        http_response_code(404);
        View::render('pages/erreur', ['titre' => 'Page introuvable', 'code' => 404,
            'message' => "La page demandée n'existe pas ou a été déplacée."]);
    }
}
