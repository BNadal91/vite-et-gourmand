<?php $valeurs = $c; ?>
<div class="container py-5">
    <h1 class="h2">Modifier la commande <?= e($c['numero_commande']) ?></h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>
    <form method="post" action="/employe/commandes/<?= e($c['numero_commande']) ?>/modifier" id="form-commande" class="row g-4" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="menu_id" id="menu_id" value="<?= (int) $c['menu_id'] ?>">
        <div class="col-lg-7">
            <div class="form-card">
                <p>Menu : <strong><?= e($c['menu_titre']) ?></strong> — client : <?= e($c['prenom_client'] . ' ' . $c['nom_client']) ?>, <?= e($c['telephone_client']) ?></p>
                <?php require ROOT_PATH . '/views/partials/commande-champs.php'; ?>
                <fieldset class="mb-3">
                    <legend class="h5">Contact préalable du client (obligatoire)</legend>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="mode_contact" id="mode_contact" value="gsm" <?= old('mode_contact') === 'gsm' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mode_contact">Appel GSM</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="mode_contact" id="mode_mail" value="mail" <?= old('mode_contact') === 'mail' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mode_mail">E-mail</label>
                    </div>
                    <?= field_error('mode_contact') ?>
                    <label for="motif" class="form-label required mt-3">Motif de la modification</label>
                    <textarea class="form-control<?= aria_invalid('motif') ?>" id="motif" name="motif" rows="2" maxlength="400"><?= old('motif') ?></textarea>
                    <?= field_error('motif') ?>
                </fieldset>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="sticky-recap">
                <?php require ROOT_PATH . '/views/partials/recap-prix.php'; ?>
                <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">Enregistrer</button>
                <a href="/employe/commandes/<?= e($c['numero_commande']) ?>" class="btn btn-link w-100">Retour</a>
            </div>
        </div>
    </form>
</div>
