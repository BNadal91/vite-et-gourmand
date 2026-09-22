<?php use App\Core\Auth; ?>
<div class="container py-5">
    <nav aria-label="Fil d'Ariane">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/compte">Mon espace</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mes informations</li>
        </ol>
    </nav>
    <h1 class="h2">Mes informations personnelles</h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <form method="post" action="/compte/profil" class="form-card" novalidate>
                <?= csrf_field() ?>
                <div class="row g-3">
                    <?php
                    $champs = [
                        ['nom', 'Nom', 'text', 'family-name', 6], ['prenom', 'Prénom', 'text', 'given-name', 6],
                        ['email', 'Adresse e-mail', 'email', 'email', 6], ['telephone', 'GSM', 'tel', 'tel', 6],
                        ['adresse_postale', 'Adresse postale', 'text', 'street-address', 12],
                        ['code_postal', 'Code postal', 'text', 'postal-code', 4], ['ville', 'Ville', 'text', 'address-level2', 5],
                        ['pays', 'Pays', 'text', 'country-name', 3],
                    ];
                    foreach ($champs as [$name, $label, $type, $ac, $col]): ?>
                        <div class="col-md-<?= $col ?>">
                            <label for="<?= $name ?>" class="form-label <?= $name !== 'pays' ? 'required' : '' ?>"><?= $label ?></label>
                            <input type="<?= $type ?>" class="form-control<?= aria_invalid($name) ?>" id="<?= $name ?>" name="<?= $name ?>" value="<?= old($name, $user[$name] ?? '') ?>" autocomplete="<?= $ac ?>">
                            <?= field_error($name) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary mt-4">Enregistrer</button>
            </form>
        </div>
        <div class="col-lg-5">
            <form method="post" action="/compte/mot-de-passe" class="form-card mb-4" id="mot-de-passe" novalidate>
                <?= csrf_field() ?>
                <h2 class="h5">Changer de mot de passe</h2>
                <div class="mb-3">
                    <label for="current_password" class="form-label required">Mot de passe actuel</label>
                    <input type="password" class="form-control<?= aria_invalid('current_password') ?>" id="current_password" name="current_password" autocomplete="current-password" required>
                    <?= field_error('current_password') ?>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label required">Nouveau mot de passe</label>
                    <input type="password" class="form-control<?= aria_invalid('password') ?>" id="password" name="password" autocomplete="new-password" required aria-describedby="pwd-rules">
                    <?php require ROOT_PATH . '/views/partials/password-rules.php'; ?>
                    <?= field_error('password') ?>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label required">Confirmation</label>
                    <input type="password" class="form-control<?= aria_invalid('password_confirmation') ?>" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                    <?= field_error('password_confirmation') ?>
                </div>
                <button type="submit" class="btn btn-outline-primary">Modifier le mot de passe</button>
            </form>

            <?php if (Auth::is(Auth::UTILISATEUR)): ?>
            <form method="post" action="/compte/supprimer" class="form-card border border-danger" data-confirm="Cette action est définitive. Supprimer votre compte ?" novalidate>
                <?= csrf_field() ?>
                <h2 class="h5 text-danger">Supprimer mon compte</h2>
                <p class="small">Conformément au RGPD, vous pouvez supprimer votre compte et vos données personnelles. Vos commandes passées sont conservées de façon anonyme pour nos obligations comptables.</p>
                <label for="confirm_password" class="form-label">Confirmez avec votre mot de passe</label>
                <input type="password" class="form-control mb-3" id="confirm_password" name="confirm_password" autocomplete="current-password" required>
                <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
