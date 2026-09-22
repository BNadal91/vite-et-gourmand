<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="form-card">
                <h1 class="h2">Mot de passe oublié</h1>
                <p>Indiquez l'adresse e-mail de votre compte : nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>
                <form method="post" action="/mot-de-passe-oublie" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-4">
                        <label for="email" class="form-label required">Adresse e-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Recevoir le lien de réinitialisation</button>
                </form>
                <p class="mt-3 mb-0"><a href="/connexion">Retour à la connexion</a></p>
            </div>
        </div>
    </div>
</div>
