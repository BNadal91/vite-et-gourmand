<div class="container py-5">
    <h1 class="h2">Espace employé</h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Plats</h2>
        <a href="/employe/plats/nouveau" class="btn btn-primary">+ Nouveau plat</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle bg-white">
            <caption class="visually-hidden">Liste des plats</caption>
            <thead><tr><th scope="col">Plat</th><th scope="col">Type</th><th scope="col">Allergènes</th><th scope="col">Menus</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
            <?php foreach ($plats as $p): ?>
                <tr>
                    <td><?= e($p['titre_plat']) ?></td>
                    <td><?= e(type_plat_label($p['type_plat'])) ?></td>
                    <td class="small"><?= e($p['allergenes'] ?: '—') ?></td>
                    <td><?= (int) $p['nb_menus'] ?></td>
                    <td class="d-flex gap-2">
                        <a href="/employe/plats/<?= (int) $p['plat_id'] ?>" class="btn btn-sm btn-outline-primary">Modifier<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></a>
                        <form method="post" action="/employe/plats/<?= (int) $p['plat_id'] ?>/supprimer" data-confirm="Supprimer ce plat ? Il sera retiré de tous les menus.">
                            <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Supprimer<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
