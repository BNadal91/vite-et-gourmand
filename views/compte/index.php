<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="mb-0">Bonjour <?= e($user['prenom']) ?></h1>
        <div class="d-flex gap-2">
            <a href="/compte/profil" class="btn btn-outline-primary">Mes informations</a>
            <a href="/menus" class="btn btn-primary">Commander un menu</a>
        </div>
    </div>

    <h2 class="section-titre h3">Mes commandes</h2>
    <?php if (!$commandes): ?>
        <p class="alert alert-info">Vous n'avez pas encore passé de commande. <a href="/menus" class="alert-link">Découvrez nos menus</a>.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle bg-white">
                <caption class="visually-hidden">Liste de mes commandes</caption>
                <thead>
                <tr>
                    <th scope="col">N° de commande</th>
                    <th scope="col">Menu</th>
                    <th scope="col">Prestation</th>
                    <th scope="col">Personnes</th>
                    <th scope="col">Total</th>
                    <th scope="col">Statut</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($commandes as $c): ?>
                    <tr>
                        <td><?= e($c['numero_commande']) ?></td>
                        <td><?= e($c['menu_titre']) ?></td>
                        <td><?= date_fr($c['date_prestation']) ?> à <?= e(substr($c['heure_livraison'], 0, 5)) ?></td>
                        <td><?= (int) $c['nombre_personne'] ?></td>
                        <td><?= prix($c['prix_total']) ?></td>
                        <td><?= statut_badge($c['statut']) ?>
                            <?php if ($c['statut'] === 'terminee' && !$c['avis_id']): ?><br><span class="small text-success fw-semibold">Donnez votre avis !</span><?php endif; ?></td>
                        <td><a href="/compte/commandes/<?= e($c['numero_commande']) ?>" class="btn btn-sm btn-outline-primary">Détail<span class="visually-hidden"> de la commande <?= e($c['numero_commande']) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
