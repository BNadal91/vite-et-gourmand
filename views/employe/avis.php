<div class="container py-5">
    <h1 class="h2">Espace employé</h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>
    <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Filtrer les avis">
        <a href="/employe/avis" class="btn btn-sm <?= $statut ? 'btn-outline-primary' : 'btn-primary' ?>">Tous</a>
        <a href="/employe/avis?statut=en_attente" class="btn btn-sm <?= $statut === 'en_attente' ? 'btn-primary' : 'btn-outline-primary' ?>">En attente</a>
        <a href="/employe/avis?statut=valide" class="btn btn-sm <?= $statut === 'valide' ? 'btn-primary' : 'btn-outline-primary' ?>">Validés</a>
        <a href="/employe/avis?statut=refuse" class="btn btn-sm <?= $statut === 'refuse' ? 'btn-primary' : 'btn-outline-primary' ?>">Refusés</a>
    </div>
    <?php if (!$avis): ?><p class="alert alert-info">Aucun avis.</p><?php endif; ?>
    <div class="row g-3">
        <?php foreach ($avis as $a): ?>
            <div class="col-md-6">
                <article class="avis-card">
                    <div class="d-flex justify-content-between">
                        <p class="etoiles mb-1" aria-label="Note : <?= (int) $a['note'] ?> sur 5"><?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></p>
                        <span class="badge <?= $a['statut'] === 'valide' ? 'text-bg-success' : ($a['statut'] === 'refuse' ? 'text-bg-secondary' : 'text-bg-warning') ?>"><?= e(['en_attente' => 'En attente', 'valide' => 'Validé', 'refuse' => 'Refusé'][$a['statut']] ?? $a['statut']) ?></span>
                    </div>
                    <p>« <?= e($a['description']) ?> »</p>
                    <p class="small text-muted"><?= e(($a['prenom'] ?? 'Compte supprimé') . ' ' . ($a['nom'] ?? '')) ?> — <?= e($a['menu_titre']) ?> — <?= date_fr($a['date_avis']) ?></p>
                    <?php if ($a['statut'] === 'en_attente'): ?>
                        <form method="post" action="/employe/avis/<?= (int) $a['avis_id'] ?>" class="d-flex gap-2">
                            <?= csrf_field() ?>
                            <button name="decision" value="valide" class="btn btn-sm btn-success">Valider</button>
                            <button name="decision" value="refuse" class="btn btn-sm btn-outline-danger">Refuser</button>
                        </form>
                    <?php endif; ?>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</div>
