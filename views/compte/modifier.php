<?php $valeurs = $c; ?>
<div class="container py-5">
    <h1 class="h2">Modifier la commande <?= e($c['numero_commande']) ?></h1>
    <p>Vous pouvez modifier toutes les informations de la prestation, sauf le menu choisi : <strong><?= e($c['menu_titre']) ?></strong>.</p>
    <form method="post" action="/compte/commandes/<?= e($c['numero_commande']) ?>/modifier" id="form-commande" class="row g-4" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="menu_id" id="menu_id" value="<?= (int) $c['menu_id'] ?>">
        <div class="col-lg-7"><div class="form-card"><?php require ROOT_PATH . '/views/partials/commande-champs.php'; ?></div></div>
        <div class="col-lg-5">
            <div class="sticky-recap">
                <?php require ROOT_PATH . '/views/partials/recap-prix.php'; ?>
                <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">Enregistrer les modifications</button>
                <a href="/compte/commandes/<?= e($c['numero_commande']) ?>" class="btn btn-link w-100">Retour</a>
            </div>
        </div>
    </form>
</div>
