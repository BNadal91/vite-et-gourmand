<?php
// Routeur pour le serveur de développement intégré de PHP : php -S localhost:8000 -t public public/router.php
$file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (PHP_SAPI === 'cli-server' && is_file($file) && !str_ends_with($file, '.php')) {
    return false; // fichier statique servi directement
}
require __DIR__ . '/index.php';
