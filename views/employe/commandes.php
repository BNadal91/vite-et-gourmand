<div class="container py-5">
    <h1 class="h2">Espace employé</h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>

    <form class="filtres row g-3 align-items-end mb-4" method="get" action="/employe/commandes" role="search" aria-label="Filtrer les commandes">
        <div class="col-md-4">
            <label for="statut" class="form-label">Statut</label>
            <select class="form-select" id="statut" name="statut">
                <option value="">Tous les statuts</option>
                <?php foreach (STATUTS as $k => $label): ?>
                    <option value="<?= e($k) ?>" <?= $statut === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label for="client" class="form-label">Client (nom, prénom ou e-mail)</label>
            <input type="search" class="form-control" id="client" name="client" value="<?= e($client) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Filtrer</button>
            <a href="/employe/commandes" class="btn btn-outline-primary">Effacer</a>
        </div>
    </form>

    <p class="fw-semibold" role="status"><?= count($commandes) ?> commande(s)</p>
    <div class="table-responsive">
        <table class="table align-middle bg-white">
            <caption class="visually-hidden">Commandes filtrées</caption>
            <thead>
            <tr><th scope="col">N°</th><th scope="col">Client</th><th scope="col">Menu</th><th scope="col">Prestation</th>
                <th scope="col">Pers.</th><th scope="col">Total</th><th scope="col">Statut</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
            </thead>
            <tbody>
            <?php foreach ($commandes as $c): ?>
                <tr>
                    <td class="small"><?= e($c['numero_commande']) ?></td>
                    <td><?= e($c['prenom_client'] . ' ' . $c['nom_client']) ?><br><span class="small text-muted"><?= e($c['email_client']) ?></span></td>
                    <td><?= e($c['menu_titre']) ?><?= (int) $c['pret_materiel'] ? '<br><span class="badge text-bg-light border">Prêt de matériel</span>' : '' ?></td>
                    <td><?= date_fr($c['date_prestation']) ?> <?= e(substr($c['heure_livraison'], 0, 5)) ?><br><span class="small"><?= e($c['ville_livraison']) ?></span></td>
                    <td><?= (int) $c['nombre_personne'] ?></td>
                    <td><?= prix($c['prix_total']) ?></td>
                    <td><?= statut_badge($c['statut']) ?></td>
                    <td><a class="btn btn-sm btn-primary" href="/employe/commandes/<?= e($c['numero_commande']) ?>">Gérer<span class="visually-hidden"> la commande <?= e($c['numero_commande']) ?></span></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
