<?php use App\Core\Auth; ?>
<div class="container py-5">
    <nav aria-label="Fil d'Ariane">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/menus">Nos menus</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($menu['titre']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-7">
            <h1 class="mb-2"><?= e($menu['titre']) ?></h1>
            <div class="mb-3">
                <span class="badge badge-theme">Thème : <?= e($menu['theme']) ?></span>
                <span class="badge badge-regime">Régime : <?= e($menu['regime']) ?></span>
            </div>
            <p class="lead"><?= e($menu['description']) ?></p>

            <?php if ($menu['images']): ?>
            <section aria-label="Galerie d'images du menu" class="galerie mb-4">
                <div id="carousel-menu" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner">
                        <?php foreach ($menu['images'] as $i => $img): ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                <img src="<?= e($img['chemin']) ?>" alt="<?= e($img['texte_alternatif']) ?>" width="900" height="600">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($menu['images']) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-menu" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Image précédente</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-menu" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Image suivante</span>
                    </button>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>

            <section aria-labelledby="titre-composition">
                <h2 id="titre-composition" class="section-titre h3">Composition du menu</h2>
                <?php foreach (['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'] as $type => $label): ?>
                    <?php if ($menu['plats'][$type]): ?>
                        <h3 class="h5 mt-4"><?= $label ?></h3>
                        <ul class="list-unstyled">
                            <?php foreach ($menu['plats'][$type] as $p): ?>
                                <li class="plat-ligne d-flex gap-3 align-items-start">
                                    <?php if ($p['a_photo']): ?>
                                        <img src="/plats/<?= (int) $p['plat_id'] ?>/photo" alt="" width="72" height="72" class="rounded object-fit-cover">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= e($p['titre_plat']) ?></strong>
                                        <?php if ($p['description']): ?><br><span><?= e($p['description']) ?></span><?php endif; ?>
                                        <br><span class="allergenes"><strong>Allergènes :</strong> <?= e($p['allergenes'] ?: 'aucun allergène majeur') ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endforeach; ?>
            </section>
        </div>

        <!-- Sur mobile, conditions et prix passent en tête de page pour être vus avant la composition -->
        <div class="col-lg-5 order-first order-lg-last">
            <div class="conditions-alerte p-4 mb-4" role="note" aria-labelledby="titre-conditions">
                <h2 id="titre-conditions" class="h5 mb-2">⚠️ Conditions de ce menu — à lire avant de commander</h2>
                <p class="mb-0"><?= nl2br(e($menu['conditions'])) ?></p>
            </div>

            <div class="recap-prix p-4">
                <h2 class="h5">Informations pratiques</h2>
                <dl class="row mb-3">
                    <dt class="col-7">Nombre minimum de personnes</dt><dd class="col-5 text-end"><?= (int) $menu['nombre_personne_minimum'] ?></dd>
                    <dt class="col-7">Prix par personne</dt><dd class="col-5 text-end"><?= prix($menu['prix_par_personne']) ?></dd>
                    <dt class="col-7">Prix pour <?= (int) $menu['nombre_personne_minimum'] ?> personnes</dt><dd class="col-5 text-end total"><?= prix($menu['prix_minimum']) ?></dd>
                    <dt class="col-7">Disponibilité</dt>
                    <dd class="col-5 text-end"><?= (int) $menu['quantite_restante'] > 0
                            ? 'Plus que ' . (int) $menu['quantite_restante'] . ' commande(s) possible(s)'
                            : '<span class="text-danger fw-bold">Épuisé</span>' ?></dd>
                </dl>
                <p class="small">Réduction de 10 % à partir de <?= (int) $menu['nombre_personne_minimum'] + 5 ?> personnes.
                    Livraison gratuite à Bordeaux, 5 € + 0,59 €/km ailleurs.</p>
                <?php if ((int) $menu['quantite_restante'] > 0): ?>
                    <a href="/commande?menu=<?= (int) $menu['menu_id'] ?>" class="btn btn-primary btn-lg w-100">Commander ce menu</a>
                    <?php if (!Auth::check()): ?>
                        <p class="small mt-2 mb-0">Vous devrez vous <a href="/connexion">connecter</a> ou <a href="/inscription">créer un compte</a> pour finaliser la commande.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100" disabled>Menu épuisé</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
