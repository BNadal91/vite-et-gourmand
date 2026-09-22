<div class="container py-5">
    <h1 class="h2">Espace employé</h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Menus</h2>
        <a href="/employe/menus/nouveau" class="btn btn-primary">+ Nouveau menu</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle bg-white">
            <caption class="visually-hidden">Liste des menus</caption>
            <thead><tr><th scope="col">Titre</th><th scope="col">Thème / régime</th><th scope="col">Min.</th><th scope="col">Prix/pers.</th><th scope="col">Stock</th><th scope="col">État</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
            <?php foreach ($menus as $m): ?>
                <tr class="<?= (int) $m['actif'] ? '' : 'table-secondary' ?>">
                    <td><?= e($m['titre']) ?></td>
                    <td><?= e($m['theme']) ?> / <?= e($m['regime']) ?></td>
                    <td><?= (int) $m['nombre_personne_minimum'] ?></td>
                    <td><?= prix($m['prix_par_personne']) ?></td>
                    <td><?= (int) $m['quantite_restante'] ?></td>
                    <td><?= (int) $m['actif'] ? 'En ligne' : 'Archivé' ?></td>
                    <td class="d-flex gap-2">
                        <a href="/employe/menus/<?= (int) $m['menu_id'] ?>" class="btn btn-sm btn-outline-primary">Modifier<span class="visually-hidden"> <?= e($m['titre']) ?></span></a>
                        <?php if ((int) $m['actif']): ?>
                            <form method="post" action="/employe/menus/<?= (int) $m['menu_id'] ?>/supprimer" data-confirm="Supprimer ce menu ?">
                                <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Supprimer<span class="visually-hidden"> <?= e($m['titre']) ?></span></button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="/employe/menus/<?= (int) $m['menu_id'] ?>/reactiver">
                                <?= csrf_field() ?><button class="btn btn-sm btn-outline-success">Remettre en ligne</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
