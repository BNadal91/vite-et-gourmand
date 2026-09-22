<?php use App\Core\Auth; ?>
<nav class="espace-nav mb-4" aria-label="Menu de l'espace employé">
    <ul class="nav nav-pills flex-wrap gap-1">
        <li class="nav-item"><a class="nav-link<?= is_current('/employe/commandes') ?: (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/employe' ? ' active" aria-current="page' : '') ?>" href="/employe/commandes">Commandes</a></li>
        <li class="nav-item"><a class="nav-link<?= is_current('/employe/menus') ?>" href="/employe/menus">Menus</a></li>
        <li class="nav-item"><a class="nav-link<?= is_current('/employe/plats') ?>" href="/employe/plats">Plats</a></li>
        <li class="nav-item"><a class="nav-link<?= is_current('/employe/horaires') ?>" href="/employe/horaires">Horaires</a></li>
        <li class="nav-item"><a class="nav-link<?= is_current('/employe/avis') ?>" href="/employe/avis">Avis clients</a></li>
        <?php if (Auth::is(Auth::ADMIN)): ?>
            <li class="nav-item"><a class="nav-link<?= is_current('/admin/employes') ?>" href="/admin/employes">Comptes employés</a></li>
            <li class="nav-item"><a class="nav-link<?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/admin' ? ' active" aria-current="page' : '' ?>" href="/admin">Statistiques</a></li>
        <?php endif; ?>
    </ul>
</nav>
