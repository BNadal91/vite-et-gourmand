<?php
/**
 * Contrôleur frontal : toutes les requêtes passent par ce fichier.
 * Seul le dossier /public est exposé par le serveur web (le code source,
 * les fichiers .env et SQL restent inaccessibles depuis Internet).
 */
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CommandeController;
use App\Controllers\CompteController;
use App\Controllers\ContactController;
use App\Controllers\EmployeController;
use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Core\Auth;
use App\Core\Env;
use App\Core\Router;
use App\Core\View;

require dirname(__DIR__) . '/src/bootstrap.php';

// ---- Session sécurisée ----
$https = (($_SERVER['HTTPS'] ?? '') === 'on') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name('VGSESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $https,     // cookie transmis uniquement en HTTPS en production
    'httponly' => true,       // inaccessible en JavaScript (vol de session par XSS)
    'samesite' => 'Lax',      // limite l'envoi du cookie depuis un site tiers (CSRF)
]);
ini_set('session.use_strict_mode', '1');
session_start();

// Expiration de session après 30 minutes d'inactivité
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
    Auth::logout();
}
$_SESSION['last_activity'] = time();

// ---- En-têtes de sécurité HTTP ----
// CSP stricte : uniquement des ressources du site lui-même, aucun script en ligne → bloque l'essentiel des attaques XSS
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; font-src 'self'; img-src 'self' data:; connect-src 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; base-uri 'self'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
if ($https) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

$E = [Auth::EMPLOYE, Auth::ADMIN];   // espace employé : employé + administrateur
$A = [Auth::ADMIN];
$U = [Auth::UTILISATEUR, Auth::EMPLOYE, Auth::ADMIN];

$r = new Router();

// Pages publiques
$r->get('/', [HomeController::class, 'index']);
$r->get('/mentions-legales', [HomeController::class, 'mentions']);
$r->get('/cgv', [HomeController::class, 'cgv']);
$r->get('/confidentialite', [HomeController::class, 'confidentialite']);
$r->get('/plats/{id}/photo', [HomeController::class, 'photoPlat']);
$r->get('/menus', [MenuController::class, 'index']);
$r->get('/api/menus', [MenuController::class, 'api']);
$r->get('/menus/{id}', [MenuController::class, 'show']);
$r->get('/contact', [ContactController::class, 'form']);
$r->post('/contact', [ContactController::class, 'send']);

// Authentification
$r->get('/inscription', [AuthController::class, 'registerForm']);
$r->post('/inscription', [AuthController::class, 'register']);
$r->get('/connexion', [AuthController::class, 'loginForm']);
$r->post('/connexion', [AuthController::class, 'login']);
$r->post('/deconnexion', [AuthController::class, 'logout']);
$r->get('/mot-de-passe-oublie', [AuthController::class, 'forgotForm']);
$r->post('/mot-de-passe-oublie', [AuthController::class, 'forgot']);
$r->get('/reinitialiser-mot-de-passe', [AuthController::class, 'resetForm']);
$r->post('/reinitialiser-mot-de-passe', [AuthController::class, 'reset']);

// Commande (personne authentifiée)
$r->get('/commande', [CommandeController::class, 'form'], $U);
$r->post('/commande', [CommandeController::class, 'store'], $U);
$r->post('/api/prix', [CommandeController::class, 'apiPrix'], $U);

// Espace utilisateur
$r->get('/compte', [CompteController::class, 'index'], $U);
$r->get('/compte/profil', [CompteController::class, 'profilForm'], $U);
$r->post('/compte/profil', [CompteController::class, 'profil'], $U);
$r->post('/compte/mot-de-passe', [CompteController::class, 'password'], $U);
$r->post('/compte/supprimer', [CompteController::class, 'delete'], $U);
$r->get('/compte/commandes/{numero}', [CompteController::class, 'show'], $U);
$r->get('/compte/commandes/{numero}/modifier', [CompteController::class, 'editForm'], $U);
$r->post('/compte/commandes/{numero}/modifier', [CompteController::class, 'update'], $U);
$r->post('/compte/commandes/{numero}/annuler', [CompteController::class, 'cancel'], $U);
$r->post('/compte/commandes/{numero}/avis', [CompteController::class, 'avis'], $U);

// Espace employé
$r->get('/employe', [EmployeController::class, 'commandes'], $E);
$r->get('/employe/commandes', [EmployeController::class, 'commandes'], $E);
$r->get('/employe/commandes/{numero}', [EmployeController::class, 'commande'], $E);
$r->post('/employe/commandes/{numero}/statut', [EmployeController::class, 'statut'], $E);
$r->post('/employe/commandes/{numero}/annuler', [EmployeController::class, 'annuler'], $E);
$r->get('/employe/commandes/{numero}/modifier', [EmployeController::class, 'modifierForm'], $E);
$r->post('/employe/commandes/{numero}/modifier', [EmployeController::class, 'modifier'], $E);
$r->get('/employe/menus', [EmployeController::class, 'menus'], $E);
$r->get('/employe/menus/nouveau', [EmployeController::class, 'menuForm'], $E);
$r->post('/employe/menus/nouveau', [EmployeController::class, 'menuSave'], $E);
$r->get('/employe/menus/{id}', [EmployeController::class, 'menuForm'], $E);
$r->post('/employe/menus/{id}', [EmployeController::class, 'menuSave'], $E);
$r->post('/employe/menus/{id}/supprimer', [EmployeController::class, 'menuDelete'], $E);
$r->post('/employe/menus/{id}/reactiver', [EmployeController::class, 'menuReactiver'], $E);
$r->post('/employe/images/{id}/supprimer', [EmployeController::class, 'imageDelete'], $E);
$r->get('/employe/plats', [EmployeController::class, 'plats'], $E);
$r->get('/employe/plats/nouveau', [EmployeController::class, 'platForm'], $E);
$r->post('/employe/plats/nouveau', [EmployeController::class, 'platSave'], $E);
$r->get('/employe/plats/{id}', [EmployeController::class, 'platForm'], $E);
$r->post('/employe/plats/{id}', [EmployeController::class, 'platSave'], $E);
$r->post('/employe/plats/{id}/supprimer', [EmployeController::class, 'platDelete'], $E);
$r->get('/employe/horaires', [EmployeController::class, 'horaires'], $E);
$r->post('/employe/horaires', [EmployeController::class, 'horairesSave'], $E);
$r->get('/employe/avis', [EmployeController::class, 'avis'], $E);
$r->post('/employe/avis/{id}', [EmployeController::class, 'avisModerer'], $E);

// Espace administrateur
$r->get('/admin', [AdminController::class, 'index'], $A);
$r->get('/admin/employes', [AdminController::class, 'employes'], $A);
$r->post('/admin/employes', [AdminController::class, 'createEmploye'], $A);
$r->post('/admin/employes/{id}/statut', [AdminController::class, 'toggleEmploye'], $A);
$r->get('/api/admin/stats', [AdminController::class, 'apiStats'], $A);

try {
    $r->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $e) {
    // Journalisation interne, message neutre pour l'utilisateur (pas de fuite d'information)
    error_log('[Erreur] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if (Env::get('APP_DEBUG') === 'true') {
        echo '<pre>' . e((string) $e) . '</pre>';
        exit;
    }
    View::render('pages/erreur', ['titre' => 'Erreur', 'code' => 500, 'message' => 'Une erreur est survenue. Merci de réessayer plus tard.']);
}
