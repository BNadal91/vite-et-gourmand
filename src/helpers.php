<?php
/**
 * Fonctions utilitaires globales.
 */
declare(strict_types=1);

use App\Core\Csrf;

/** Échappement HTML systématique contre les failles XSS. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    // On n'accepte que des chemins internes (pas de redirection ouverte vers un site tiers)
    if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
        $path = '/';
    }
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/** Mémorise les anciennes saisies et erreurs pour réafficher un formulaire invalide. */
function back_with_errors(array $errors, array $old, string $path): never
{
    unset($old['password'], $old['password_confirmation'], $old['_csrf']);
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    redirect($path);
}

function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function errors(): array
{
    static $errors = null;
    if ($errors === null) {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
    }
    return $errors;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

/** Attributs ARIA et message d'erreur d'un champ (RGAA : erreur liée au champ). */
function field_error(string $field): string
{
    $err = errors()[$field] ?? null;
    return $err ? '<div class="invalid-feedback d-block" id="err-' . e($field) . '">' . e($err) . '</div>' : '';
}

function aria_invalid(string $field): string
{
    return isset(errors()[$field]) ? ' is-invalid" aria-invalid="true" aria-describedby="err-' . e($field) : '';
}

function csrf_field(): string
{
    return Csrf::field();
}

function prix(float|string|null $montant): string
{
    return number_format((float) $montant, 2, ',', ' ') . ' €';
}

function date_fr(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '';
    }
    $d = new DateTimeImmutable($date);
    return $withTime ? $d->format('d/m/Y à H:i') : $d->format('d/m/Y');
}

const STATUTS = [
    'en_attente'              => 'En attente de validation',
    'accepte'                 => 'Acceptée',
    'en_preparation'          => 'En préparation',
    'en_cours_livraison'      => 'En cours de livraison',
    'livre'                   => 'Livrée',
    'attente_retour_materiel' => 'En attente du retour de matériel',
    'terminee'                => 'Terminée',
    'annulee'                 => 'Annulée',
];

function statut_label(string $s): string
{
    return STATUTS[$s] ?? $s;
}

function statut_badge(string $s): string
{
    $class = match ($s) {
        'en_attente' => 'text-bg-warning',
        'annulee' => 'text-bg-secondary',
        'terminee' => 'text-bg-success',
        'attente_retour_materiel' => 'text-bg-danger',
        default => 'text-bg-info',
    };
    return '<span class="badge ' . $class . '">' . e(statut_label($s)) . '</span>';
}

function type_plat_label(string $t): string
{
    return ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'][$t] ?? $t;
}

function base_url(): string
{
    return rtrim((string) App\Core\Env::get('APP_URL', 'http://localhost:8000'), '/');
}

/**
 * Adresse IP réelle du visiteur. Derrière le proxy de Fly.io, REMOTE_ADDR est l'adresse
 * interne du proxy (identique pour tout le monde) : on lit alors l'en-tête Fly-Client-IP,
 * que le proxy de Fly.io écrit lui-même (il ne peut pas être imposé par le visiteur).
 */
function client_ip(): string
{
    $fly = $_SERVER['HTTP_FLY_CLIENT_IP'] ?? '';
    if ($fly !== '' && filter_var($fly, FILTER_VALIDATE_IP)) {
        return $fly;
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function is_current(string $path): string
{
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $active = $path === '/' ? $current === '/' : str_starts_with((string) $current, $path);
    return $active ? ' active" aria-current="page' : '';
}
