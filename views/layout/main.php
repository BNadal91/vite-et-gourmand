<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Models\Catalogue;

$horaires = (new Catalogue())->horaires();
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Vite & Gourmand, traiteur à Bordeaux depuis 25 ans : menus de Noël, Pâques, classiques et évènements, livrés chez vous.">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= e($titre ?? 'Accueil') ?> — Vite &amp; Gourmand</title>
    <!-- Bootstrap, Chart.js et polices sont servis localement : aucune dépendance à un CDN tiers -->
    <link href="/assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/fonts.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
</head>
<body>
<a class="visually-hidden-focusable skip-link" href="#contenu">Aller au contenu principal</a>

<header class="site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Menu principal">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="/assets/img/logo.svg" alt="" width="40" height="40">
                <span>Vite <span class="amp">&amp;</span> Gourmand</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav-principale"
                    aria-controls="nav-principale" aria-expanded="false" aria-label="Afficher ou masquer le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav-principale">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link<?= is_current('/') ?>" href="/">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link<?= is_current('/menus') ?>" href="/menus">Nos menus</a></li>
                    <li class="nav-item"><a class="nav-link<?= is_current('/contact') ?>" href="/contact">Contact</a></li>
                    <?php if ($user): ?>
                        <li class="nav-item"><a class="nav-link<?= is_current('/compte') ?>" href="/compte">Mon espace</a></li>
                        <?php if (Auth::is(Auth::EMPLOYE, Auth::ADMIN)): ?>
                            <li class="nav-item"><a class="nav-link<?= is_current('/employe') ?>" href="/employe">Espace employé</a></li>
                        <?php endif; ?>
                        <?php if (Auth::is(Auth::ADMIN)): ?>
                            <li class="nav-item"><a class="nav-link<?= is_current('/admin') ?>" href="/admin">Administration</a></li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <form method="post" action="/deconnexion" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-primary btn-sm ms-lg-2">Déconnexion</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link<?= is_current('/connexion') ?>" href="/connexion">Connexion</a></li>
                        <li class="nav-item"><a class="btn btn-primary btn-sm ms-lg-2" href="/inscription">Créer un compte</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="contenu" tabindex="-1">
    <?php require ROOT_PATH . '/views/partials/flash.php'; ?>
    <?= $content ?>
</main>

<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="h5">Vite &amp; Gourmand</h2>
                <p class="mb-1">Traiteur à Bordeaux depuis 25 ans</p>
                <address class="mb-0">12 cours de l'Intendance<br>33000 Bordeaux<br>
                    <a href="tel:+33556000000">05 56 00 00 00</a></address>
            </div>
            <div class="col-md-4">
                <h2 class="h5">Nos horaires</h2>
                <table class="table table-sm table-borderless horaires mb-0">
                    <caption class="visually-hidden">Horaires d'ouverture du lundi au dimanche</caption>
                    <tbody>
                    <?php foreach ($horaires as $h): ?>
                        <tr>
                            <th scope="row"><?= e($h['jour']) ?></th>
                            <td><?= $h['heure_ouverture'] ? e($h['heure_ouverture'] . ' – ' . $h['heure_fermeture']) : 'Fermé' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4">
                <h2 class="h5">Informations</h2>
                <ul class="list-unstyled">
                    <li><a href="/mentions-legales">Mentions légales</a></li>
                    <li><a href="/cgv">Conditions générales de vente</a></li>
                    <li><a href="/confidentialite">Politique de confidentialité</a></li>
                    <li><a href="/contact">Nous contacter</a></li>
                </ul>
            </div>
        </div>
        <p class="small mt-4 mb-0">&copy; <?= date('Y') ?> Vite &amp; Gourmand — Tous droits réservés.</p>
    </div>
</footer>

<script src="/assets/vendor/bootstrap.bundle.min.js"></script>
<?php if (!empty($chart)): ?>
<script src="/assets/vendor/chart.umd.js"></script>
<?php endif; ?>
<script src="/assets/js/app.js"></script>
<?php foreach ($scripts ?? [] as $s): ?>
<script src="/assets/js/<?= e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>
<?php clear_old(); ?>
