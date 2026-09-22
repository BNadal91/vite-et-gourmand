<div class="container py-5">
    <nav aria-label="Fil d'Ariane">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/compte">Mon espace</a></li>
            <li class="breadcrumb-item active" aria-current="page">Commande <?= e($c['numero_commande']) ?></li>
        </ol>
    </nav>
    <h1 class="h2">Commande <?= e($c['numero_commande']) ?> <?= statut_badge($c['statut']) ?></h1>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="form-card">
                <?php require ROOT_PATH . '/views/partials/commande-detail.php'; ?>
                <div class="conditions-alerte p-3 mt-3 small"><strong>Conditions du menu :</strong> <?= e($c['menu_conditions']) ?></div>

                <?php if ($c['statut'] === 'en_attente'): ?>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="/compte/commandes/<?= e($c['numero_commande']) ?>/modifier" class="btn btn-outline-primary">Modifier ma commande</a>
                        <form method="post" action="/compte/commandes/<?= e($c['numero_commande']) ?>/annuler" data-confirm="Voulez-vous vraiment annuler cette commande ?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger">Annuler ma commande</button>
                        </form>
                    </div>
                    <p class="small text-muted mt-2 mb-0">La modification et l'annulation sont possibles tant que notre équipe n'a pas accepté la commande.</p>
                <?php endif; ?>
            </div>

            <?php if ($c['statut'] === 'terminee'): ?>
                <section class="form-card mt-4" id="avis" aria-labelledby="titre-avis">
                    <h2 id="titre-avis" class="h4">Votre avis</h2>
                    <?php if ($c['avis_id']): ?>
                        <p class="mb-0">Merci ! Vous avez donné la note de <strong><?= (int) $c['avis_note'] ?>/5</strong>.
                            <?= $c['avis_statut'] === 'valide' ? 'Votre avis est publié sur notre page d\'accueil.' : ($c['avis_statut'] === 'refuse' ? 'Votre avis n\'a pas été retenu pour publication.' : 'Il est en cours de validation par notre équipe.') ?></p>
                    <?php else: ?>
                        <form method="post" action="/compte/commandes/<?= e($c['numero_commande']) ?>/avis" novalidate>
                            <?= csrf_field() ?>
                            <fieldset class="mb-3">
                                <legend class="form-label fs-6 required">Note</legend>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="note" id="note<?= $i ?>" value="<?= $i ?>" <?= (int) old('note') === $i ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="note<?= $i ?>"><?= $i ?> <span aria-hidden="true">★</span><span class="visually-hidden"> sur 5</span></label>
                                    </div>
                                <?php endfor; ?>
                                <?= field_error('note') ?>
                            </fieldset>
                            <div class="mb-3">
                                <label for="description" class="form-label required">Commentaire</label>
                                <textarea class="form-control<?= aria_invalid('description') ?>" id="description" name="description" rows="4" maxlength="1000" required><?= old('description') ?></textarea>
                                <?= field_error('description') ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Publier mon avis</button>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
        <div class="col-lg-5">
            <div class="form-card"><?php require ROOT_PATH . '/views/partials/suivi.php'; ?></div>
        </div>
    </div>
</div>
