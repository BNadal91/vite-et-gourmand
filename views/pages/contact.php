<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-7">
            <div class="form-card">
                <h1 class="h2">Nous contacter</h1>
                <p>Une question sur un menu, un devis pour un évènement ? Écrivez-nous, nous vous répondons par e-mail.</p>
                <form method="post" action="/contact" novalidate>
                    <?= csrf_field() ?>
                    <div class="hp-field" aria-hidden="true">
                        <label for="site_web">Ne pas remplir ce champ</label>
                        <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="titre" class="form-label required">Titre de votre demande</label>
                        <input type="text" class="form-control<?= aria_invalid('titre') ?>" id="titre" name="titre" value="<?= old('titre') ?>" required maxlength="150">
                        <?= field_error('titre') ?>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label required">Description</label>
                        <textarea class="form-control<?= aria_invalid('description') ?>" id="description" name="description" rows="6" required maxlength="3000"><?= old('description') ?></textarea>
                        <?= field_error('description') ?>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="form-label required">Votre adresse e-mail (pour vous répondre)</label>
                        <input type="email" class="form-control<?= aria_invalid('email') ?>" id="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>" required autocomplete="email">
                        <?= field_error('email') ?>
                    </div>
                    <p class="small text-muted">Votre adresse est utilisée uniquement pour répondre à votre demande (<a href="/confidentialite">en savoir plus</a>).</p>
                    <button type="submit" class="btn btn-primary btn-lg">Envoyer</button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <h2 class="section-titre">Nos coordonnées</h2>
            <address>
                <strong>Vite &amp; Gourmand</strong><br>
                12 cours de l'Intendance, 33000 Bordeaux<br>
                Téléphone : <a href="tel:+33556000000">05 56 00 00 00</a><br>
                E-mail : <a href="mailto:contact@vite-gourmand.fr">contact@vite-gourmand.fr</a>
            </address>
            <p>Nos horaires d'ouverture sont indiqués en bas de chaque page.</p>
        </div>
    </div>
</div>
