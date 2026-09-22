<?php /** @var array $m */ ?>
<article class="card menu-card h-100">
    <img src="<?= e($m['image'] ?: '/assets/img/menus/defaut.svg') ?>" class="card-img-top" alt="<?= e($m['image_alt'] ?? '') ?>" loading="lazy" width="600" height="400">
    <div class="card-body d-flex flex-column">
        <div class="mb-2">
            <span class="badge badge-theme"><?= e($m['theme']) ?></span>
            <span class="badge badge-regime"><?= e($m['regime']) ?></span>
        </div>
        <h3 class="card-title h5"><?= e($m['titre']) ?></h3>
        <p class="card-text flex-grow-1"><?= e($m['description']) ?></p>
        <ul class="list-unstyled menu-infos">
            <li><strong>Minimum :</strong> <?= (int) $m['nombre_personne_minimum'] ?> personnes</li>
            <li><strong>Prix :</strong> <?= prix($m['prix_minimum']) ?> pour <?= (int) $m['nombre_personne_minimum'] ?> pers.
                <span class="text-muted">(<?= prix($m['prix_par_personne']) ?>/pers.)</span></li>
        </ul>
        <a href="/menus/<?= (int) $m['menu_id'] ?>" class="btn btn-primary mt-auto">
            Voir le détail<span class="visually-hidden"> du menu <?= e($m['titre']) ?></span>
        </a>
    </div>
</article>
