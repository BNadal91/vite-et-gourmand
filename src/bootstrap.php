<?php
/**
 * Point d'amorçage de l'application : autoload, configuration, session sécurisée.
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

// Autoloader PSR-4 minimaliste : App\Core\Router => src/Core/Router.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = ROOT_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

App\Core\Env::load(ROOT_PATH . '/.env');

date_default_timezone_set('Europe/Paris');

// Les erreurs ne sont jamais affichées en production (fuite d'informations)
$debug = App\Core\Env::get('APP_DEBUG', 'false') === 'true';
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require ROOT_PATH . '/src/helpers.php';
