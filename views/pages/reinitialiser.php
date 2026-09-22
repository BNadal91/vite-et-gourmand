<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="form-card">
                <h1 class="h2">Choisir un nouveau mot de passe</h1>
                <form method="post" action="/reinitialiser-mot-de-passe" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label required">Nouveau mot de passe</label>
                        <input type="password" class="form-control<?= aria_invalid('password') ?>" id="password" name="password" required minlength="10" autocomplete="new-password" aria-describedby="pwd-rules">
                        <?php require ROOT_PATH . '/views/partials/password-rules.php'; ?>
                        <?= field_error('password') ?>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label required">Confirmer le mot de passe</label>
                        <input type="password" class="form-control<?= aria_invalid('password_confirmation') ?>" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        <?= field_error('password_confirmation') ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</div>
