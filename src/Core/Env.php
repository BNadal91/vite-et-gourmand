<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Chargement des variables d'environnement depuis un fichier .env
 * (les secrets ne sont jamais versionnés dans Git).
 */
final class Env
{
    public static function load(string $file): void
    {
        if (!is_file($file)) {
            return; // en production, les variables sont injectées par l'hébergeur
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}
