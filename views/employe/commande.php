<div class="container py-5">
    <h1 class="h2">Commande <?= e($c['numero_commande']) ?> <?= statut_badge($c['statut']) ?></h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="form-card mb-4">
                <?php require ROOT_PATH . '/views/partials/commande-detail.php'; ?>
            </div>

            <?php if ($transitions): ?>
            <form method="post" action="/employe/commandes/<?= e($c['numero_commande']) ?>/statut" class="form-card mb-4">
                <?= csrf_field() ?>
                <h2 class="h5">Mettre à jour le statut</h2>
                <label for="statut" class="form-label">Nouveau statut</label>
                <div class="d-flex gap-2">
                    <select class="form-select" id="statut" name="statut">
                        <?php foreach ($transitions as $t): ?>
                            <?php if ($t === 'terminee' && $c['statut'] === 'livre' && (int) $c['pret_materiel']) continue; ?>
                            <option value="<?= e($t) ?>"><?= e(statut_label($t)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Valider</button>
                </div>
                <?php if ($c['statut'] === 'livre' && (int) $c['pret_materiel']): ?>
                    <p class="small mt-2 mb-0">Du matériel a été prêté : le client recevra un e-mail lui rappelant le délai de 10 jours ouvrés (600 € de frais au-delà).</p>
                <?php endif; ?>
            </form>
            <?php endif; ?>

            <?php if (!in_array($c['statut'], ['annulee', 'terminee'], true)): ?>
            <section class="form-card border border-warning" id="annulation" aria-labelledby="titre-annul">
                <h2 id="titre-annul" class="h5">Modifier ou annuler la commande</h2>
                <p class="small">Obligatoire : contactez d'abord le client par téléphone (<?= e($c['telephone_client']) ?>) ou par e-mail,
                    puis indiquez le mode de contact utilisé et le motif.</p>
                <a href="/employe/commandes/<?= e($c['numero_commande']) ?>/modifier" class="btn btn-outline-primary mb-3">Modifier la prestation</a>
                <form method="post" action="/employe/commandes/<?= e($c['numero_commande']) ?>/annuler" data-confirm="Confirmer l'annulation de la commande ?" novalidate>
                    <?= csrf_field() ?>
                    <fieldset class="mb-3">
                        <legend class="form-label fs-6 required">Client contacté par</legend>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="mode_contact" id="mode_contact" value="gsm" required>
                            <label class="form-check-label" for="mode_contact">Appel GSM</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="mode_contact" id="mode_mail" value="mail">
                            <label class="form-check-label" for="mode_mail">E-mail</label>
                        </div>
                        <?= field_error('mode_contact') ?>
                    </fieldset>
                    <div class="mb-3">
                        <label for="motif" class="form-label required">Motif de l'annulation</label>
                        <textarea class="form-control<?= aria_invalid('motif') ?>" id="motif" name="motif" rows="3" maxlength="450" required><?= old('motif') ?></textarea>
                        <?= field_error('motif') ?>
                    </div>
                    <button type="submit" class="btn btn-outline-danger">Annuler la commande</button>
                </form>
            </section>
            <?php endif; ?>
        </div>
        <div class="col-lg-5">
            <div class="form-card"><?php require ROOT_PATH . '/views/partials/suivi.php'; ?></div>
        </div>
    </div>
</div>
