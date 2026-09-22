<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Téléversement sécurisé d'images :
 * - type MIME réel vérifié avec finfo (on ne fait pas confiance à l'extension ni au navigateur)
 * - taille maximale 2 Mo
 * - nom de fichier aléatoire (pas de nom fourni par l'utilisateur, pas de traversée de répertoire)
 * - le dossier uploads interdit l'exécution de scripts (.htaccess)
 */
final class Upload
{
    public const MAX_SIZE = 2 * 1024 * 1024;
    public const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /** @return array{data:string,mime:string,ext:string}|null */
    public static function image(string $field, ?string &$error = null): ?array
    {
        $f = $_FILES[$field] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            $error = "Le fichier n'a pas pu être téléversé.";
            return null;
        }
        if ($f['size'] > self::MAX_SIZE) {
            $error = "L'image ne doit pas dépasser 2 Mo.";
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!isset(self::TYPES[$mime]) || @getimagesize($f['tmp_name']) === false) {
            $error = 'Formats acceptés : JPEG, PNG ou WebP.';
            return null;
        }
        return ['data' => (string) file_get_contents($f['tmp_name']), 'mime' => $mime, 'ext' => self::TYPES[$mime]];
    }

    /** Enregistre l'image dans public/uploads et renvoie son chemin public. */
    public static function store(array $image): string
    {
        $name = bin2hex(random_bytes(16)) . '.' . $image['ext'];
        $dir = ROOT_PATH . '/public/uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($dir . '/' . $name, $image['data']);
        return '/uploads/' . $name;
    }
}
