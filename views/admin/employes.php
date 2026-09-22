<div class="container py-5">
    <h1 class="h2">Administration</h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>
    <div class="row g-4">
        <div class="col-lg-5">
            <form method="post" action="/admin/employes" class="form-card" novalidate>
                <?= csrf_field() ?>
                <h2 class="h5">Créer un compte employé</h2>
                <p class="small">L'employé recevra un e-mail l'informant de la création de son compte. Le mot de passe n'y figure pas :
                    communiquez-le lui en main propre. Il n'est pas possible de créer un compte administrateur.</p>
                <div class="mb-3">
                    <label for="prenom" class="form-label required">Prénom</label>
                    <input type="text" class="form-control<?= aria_invalid('prenom') ?>" id="prenom" name="prenom" value="<?= old('prenom') ?>" required>
                    <?= field_error('prenom') ?>
                </div>
                <div class="mb-3">
                    <label for="nom" class="form-label required">Nom</label>
                    <input type="text" class="form-control<?= aria_invalid('nom') ?>" id="nom" name="nom" value="<?= old('nom') ?>" required>
                    <?= field_error('nom') ?>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label required">E-mail (identifiant de connexion)</label>
                    <input type="email" class="form-control<?= aria_invalid('email') ?>" id="email" name="email" value="<?= old('email') ?>" required autocomplete="off">
                    <?= field_error('email') ?>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label required">Mot de passe</label>
                    <input type="password" class="form-control<?= aria_invalid('password') ?>" id="password" name="password" required autocomplete="new-password" aria-describedby="pwd-rules">
                    <?php require ROOT_PATH . '/views/partials/password-rules.php'; ?>
                    <?= field_error('password') ?>
                </div>
                <button type="submit" class="btn btn-primary">Créer le compte</button>
            </form>
        </div>
        <div class="col-lg-7">
            <h2 class="h5">Employés</h2>
            <div class="table-responsive">
                <table class="table align-middle bg-white">
                    <caption class="visually-hidden">Comptes employés</caption>
                    <thead><tr><th scope="col">Nom</th><th scope="col">E-mail</th><th scope="col">État</th><th scope="col"><span class="visually-hidden">Action</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($employes as $u): ?>
                        <tr>
                            <td><?= e($u['prenom'] . ' ' . $u['nom']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= (int) $u['actif'] ? '<span class="badge text-bg-success">Actif</span>' : '<span class="badge text-bg-secondary">Désactivé</span>' ?></td>
                            <td>
                                <form method="post" action="/admin/employes/<?= (int) $u['utilisateur_id'] ?>/statut" <?= (int) $u['actif'] ? 'data-confirm="Désactiver ce compte ? L\'employé ne pourra plus se connecter."' : '' ?>>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="actif" value="<?= (int) $u['actif'] ? '0' : '1' ?>">
                                    <button class="btn btn-sm <?= (int) $u['actif'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"><?= (int) $u['actif'] ? 'Désactiver' : 'Réactiver' ?><span class="visually-hidden"> le compte de <?= e($u['prenom']) ?></span></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
