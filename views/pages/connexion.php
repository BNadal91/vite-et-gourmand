<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="form-card">
                <h1 class="h2">Connexion</h1>
                <p>Espace réservé aux clients, aux employés et à l'administrateur.</p>
                <form method="post" action="/connexion" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label required">Adresse e-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required autocomplete="username">
                    </div>
                    <div class="mb-2">
                        <label for="password" class="form-label required">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                    </div>
                    <p class="mb-4"><a href="/mot-de-passe-oublie">Mot de passe oublié ?</a></p>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Se connecter</button>
                </form>
                <hr>
                <p class="mb-0">Pas encore de compte ? <a href="/inscription">Créer un compte</a></p>
            </div>
        </div>
    </div>
</div>
