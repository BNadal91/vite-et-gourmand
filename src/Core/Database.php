<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Connexion unique (singleton) à la base relationnelle via PDO.
 * - requêtes préparées natives (ATTR_EMULATE_PREPARES = false) contre l'injection SQL
 * - exceptions en cas d'erreur, jamais affichées à l'utilisateur
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                Env::get('DB_HOST', '127.0.0.1'),
                Env::get('DB_PORT', '3306'),
                Env::get('DB_NAME', 'vite_gourmand')
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            // Connexion chiffrée (TLS) si l'hébergeur de la base l'exige
            $ca = Env::get('DB_SSL_CA');
            $pem = Env::get('DB_SSL_CA_PEM'); // contenu du certificat passé en secret (Fly.io)
            if (!$ca && $pem) {
                $ca = sys_get_temp_dir() . '/db-ca.pem';
                if (!is_file($ca)) {
                    file_put_contents($ca, str_replace('\n', "\n", $pem));
                }
            }
            if ($ca) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            }
            self::$pdo = new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASSWORD', ''), $options);
            // Aligne le fuseau de MySQL sur celui de PHP (Europe/Paris)
            self::$pdo->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");
        }
        return self::$pdo;
    }
}
