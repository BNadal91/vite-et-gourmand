<section class="hero py-5">
    <div class="container py-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge badge-annees mb-3">Depuis 25 ans à Bordeaux</span>
                <h1 class="mb-3">Vos repas de fête, préparés avec passion et livrés chez vous</h1>
                <p class="lead mb-4">Noël, Pâques, repas de famille ou grand évènement : Julie et José composent pour vous des menus
                    faits maison, de saison, et s'occupent de tout, de la cuisine à la livraison.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/menus" class="btn btn-gold btn-lg">Découvrir nos menus</a>
                    <a href="/contact" class="btn btn-outline-light btn-lg">Demander un conseil</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <img src="/assets/img/hero-plat.svg" alt="" class="img-fluid" width="520" height="420">
            </div>
        </div>
    </div>
</section>

<section class="container py-5" aria-labelledby="titre-entreprise">
    <div class="row g-5 align-items-center">
        <div class="col-lg-6">
            <h2 id="titre-entreprise" class="section-titre">Qui sommes-nous ?</h2>
            <p><strong>Vite &amp; Gourmand</strong> est une entreprise familiale bordelaise fondée il y a 25 ans par
                <strong>Julie et José</strong>. Nous proposons nos prestations pour tous les évènements : simple repas,
                fêtes de Noël ou de Pâques, anniversaires, réceptions et mariages.</p>
            <p>Nos menus évoluent au fil des saisons et des fêtes. Chaque plat est cuisiné dans notre laboratoire à partir de
                produits frais, de préférence locaux : huîtres du Bassin d'Arcachon, agneau de Pauillac, canelés bordelais…</p>
            <p class="mb-0">Livraison gratuite dans Bordeaux, et partout en Gironde sur simple demande.</p>
        </div>
        <div class="col-lg-6">
            <div class="row g-3 text-center">
                <div class="col-6"><div class="stat-tile"><div class="valeur">25 ans</div><div>d'expérience</div></div></div>
                <div class="col-6"><div class="stat-tile"><div class="valeur"><?= $moyenne['moyenne'] ? e(str_replace('.', ',', (string) $moyenne['moyenne'])) . '/5' : '—' ?></div><div>note moyenne (<?= (int) $moyenne['total'] ?> avis)</div></div></div>
                <div class="col-6"><div class="stat-tile"><div class="valeur">100 %</div><div>fait maison</div></div></div>
                <div class="col-6"><div class="stat-tile"><div class="valeur">7 j/7</div><div>sur commande</div></div></div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white" aria-labelledby="titre-pro">
    <div class="container">
        <h2 id="titre-pro" class="section-titre text-center">Le professionnalisme de notre équipe</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="atout"><div class="icone" aria-hidden="true">👩‍🍳</div>
                    <h3 class="h5">Des cuisiniers passionnés</h3>
                    <p class="mb-0">Une équipe formée aux métiers de bouche, qui cuisine chaque commande à la minute.</p></div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="atout"><div class="icone" aria-hidden="true">🌿</div>
                    <h3 class="h5">Des produits de saison</h3>
                    <p class="mb-0">Circuits courts, producteurs girondins et menus végétariens et vegan pour tous.</p></div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="atout"><div class="icone" aria-hidden="true">🛡️</div>
                    <h3 class="h5">Hygiène et traçabilité</h3>
                    <p class="mb-0">Respect strict des normes HACCP et information complète sur les 14 allergènes.</p></div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="atout"><div class="icone" aria-hidden="true">🚚</div>
                    <h3 class="h5">Une logistique fiable</h3>
                    <p class="mb-0">L'équipe logistique de Julie livre à l'heure et installe le matériel si besoin.</p></div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5" aria-labelledby="titre-selection">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-2">
        <h2 id="titre-selection" class="section-titre mb-4">Une sélection de nos menus</h2>
        <a href="/menus" class="btn btn-outline-primary mb-4">Voir tous les menus</a>
    </div>
    <div class="row g-4">
        <?php foreach ($menus as $m): ?>
            <div class="col-md-6 col-lg-4"><?php require ROOT_PATH . '/views/partials/menu-card.php'; ?></div>
        <?php endforeach; ?>
    </div>
</section>

<section class="py-5 bg-white" aria-labelledby="titre-avis">
    <div class="container">
        <h2 id="titre-avis" class="section-titre text-center">Ils nous ont fait confiance</h2>
        <?php if (!$avis): ?>
            <p class="text-center">Aucun avis publié pour le moment.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($avis as $a): ?>
                    <div class="col-md-6 col-lg-4">
                        <figure class="avis-card mb-0">
                            <p class="etoiles mb-2" aria-label="Note : <?= (int) $a['note'] ?> sur 5">
                                <?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></p>
                            <blockquote class="mb-2"><p class="mb-0">« <?= e($a['description']) ?> »</p></blockquote>
                            <figcaption class="small text-muted">
                                <?= e(($a['prenom'] ?? 'Client') . ' ' . ($a['initiale'] ? $a['initiale'] . '.' : '')) ?>
                                — menu <?= e($a['menu_titre']) ?>, <?= date_fr($a['date_avis']) ?>
                            </figcaption>
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
