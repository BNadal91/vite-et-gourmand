<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <h1 class="h2">Créer un compte</h1>
                <p>Votre compte vous permet de commander nos menus, de suivre vos commandes et de donner votre avis.
                    Les champs marqués d'un astérisque (<span class="text-danger">*</span>) sont obligatoires.</p>
                <form method="post" action="/inscription" novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nom" class="form-label required">Nom</label>
                            <input type="text" class="form-control<?= aria_invalid('nom') ?>" id="nom" name="nom" value="<?= old('nom') ?>" required maxlength="80" autocomplete="family-name">
                            <?= field_error('nom') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label required">Prénom</label>
                            <input type="text" class="form-control<?= aria_invalid('prenom') ?>" id="prenom" name="prenom" value="<?= old('prenom') ?>" required maxlength="80" autocomplete="given-name">
                            <?= field_error('prenom') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="telephone" class="form-label required">Numéro de GSM</label>
                            <input type="tel" class="form-control<?= aria_invalid('telephone') ?>" id="telephone" name="telephone" value="<?= old('telephone') ?>" required autocomplete="tel" placeholder="06 12 34 56 78">
                            <?= field_error('telephone') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label required">Adresse e-mail</label>
                            <input type="email" class="form-control<?= aria_invalid('email') ?>" id="email" name="email" value="<?= old('email') ?>" required maxlength="180" autocomplete="email">
                            <?= field_error('email') ?>
                        </div>
                        <div class="col-12">
                            <label for="adresse_postale" class="form-label required">Adresse postale</label>
                            <input type="text" class="form-control<?= aria_invalid('adresse_postale') ?>" id="adresse_postale" name="adresse_postale" value="<?= old('adresse_postale') ?>" required maxlength="255" autocomplete="street-address">
                            <?= field_error('adresse_postale') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="code_postal" class="form-label required">Code postal</label>
                            <input type="text" class="form-control<?= aria_invalid('code_postal') ?>" id="code_postal" name="code_postal" value="<?= old('code_postal') ?>" required pattern="\d{5}" inputmode="numeric" autocomplete="postal-code">
                            <?= field_error('code_postal') ?>
                        </div>
                        <div class="col-md-8">
                            <label for="ville" class="form-label required">Ville</label>
                            <input type="text" class="form-control<?= aria_invalid('ville') ?>" id="ville" name="ville" value="<?= old('ville') ?>" required maxlength="100" autocomplete="address-level2">
                            <?= field_error('ville') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label required">Mot de passe</label>
                            <input type="password" class="form-control<?= aria_invalid('password') ?>" id="password" name="password" required minlength="10" autocomplete="new-password" aria-describedby="pwd-rules">
                            <?php require ROOT_PATH . '/views/partials/password-rules.php'; ?>
                            <?= field_error('password') ?>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label required">Confirmer le mot de passe</label>
                            <input type="password" class="form-control<?= aria_invalid('password_confirmation') ?>" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                            <?= field_error('password_confirmation') ?>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input<?= aria_invalid('rgpd') ?>" type="checkbox" id="rgpd" name="rgpd" value="1" required>
                                <label class="form-check-label" for="rgpd">J'accepte que mes données soient utilisées pour gérer mon compte et mes commandes,
                                    conformément à la <a href="/confidentialite">politique de confidentialité</a>. <span class="text-danger">*</span></label>
                                <?= field_error('rgpd') ?>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg mt-4">Créer mon compte</button>
                    <p class="mt-3 mb-0">Déjà client ? <a href="/connexion">Se connecter</a></p>
                </form>
            </div>
        </div>
    </div>
</div>
