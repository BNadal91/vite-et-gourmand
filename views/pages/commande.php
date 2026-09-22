<?php
$valeurs = [
    'adresse_livraison'     => $user['adresse_postale'],
    'code_postal_livraison' => $user['code_postal'],
    'ville_livraison'       => $user['ville'],
    'telephone_client'      => $user['telephone'],
];
$menuChoisi = (int) old('menu_id', $menuId);
?>
<div class="container py-5">
    <h1>Commander un menu</h1>
    <p class="lead">Vérifiez vos informations, choisissez votre menu et le nombre de convives : le prix se met à jour automatiquement.</p>

    <form method="post" action="/commande" id="form-commande" class="row g-4" novalidate>
        <?= csrf_field() ?>
        <div class="col-lg-7">
            <div class="form-card">
                <fieldset class="mb-4">
                    <legend class="h5">Vos coordonnées</legend>
                    <p class="small text-muted">Remplies automatiquement depuis votre compte. <a href="/compte/profil">Modifier mon profil</a></p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" id="nom" class="form-control" value="<?= e($user['nom']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" id="prenom" class="form-control" value="<?= e($user['prenom']) ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input type="email" id="email" class="form-control" value="<?= e($user['email']) ?>" readonly>
                        </div>
                    </div>
                </fieldset>

                <?php require ROOT_PATH . '/views/partials/commande-champs.php'; ?>

                <fieldset class="mb-4">
                    <legend class="h5">Menu choisi</legend>
                    <label for="menu_id" class="form-label required">Menu</label>
                    <select class="form-select<?= aria_invalid('menu_id') ?>" id="menu_id" name="menu_id" required>
                        <option value="">— Choisir un menu —</option>
                        <?php foreach ($menus as $m): ?>
                            <option value="<?= (int) $m['menu_id'] ?>" data-min="<?= (int) $m['nombre_personne_minimum'] ?>"
                                <?= (int) $m['quantite_restante'] < 1 ? 'disabled' : '' ?>
                                <?= $menuChoisi === (int) $m['menu_id'] ? 'selected' : '' ?>>
                                <?= e($m['titre']) ?> — <?= prix($m['prix_par_personne']) ?>/pers. (min. <?= (int) $m['nombre_personne_minimum'] ?>)<?= (int) $m['quantite_restante'] < 1 ? ' — épuisé' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= field_error('menu_id') ?>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="pret_materiel" name="pret_materiel" <?= old('pret_materiel') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pret_materiel">J'ai besoin d'un prêt de matériel (vaisselle, présentoirs, plats de service)</label>
                        <div class="form-text">Le matériel doit être restitué sous 10 jours ouvrés après la prestation, faute de quoi 600 € de frais seront facturés (<a href="/cgv#materiel">voir les CGV</a>).</div>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="sticky-recap">
                <?php require ROOT_PATH . '/views/partials/recap-prix.php'; ?>
                <div class="form-check my-3">
                    <input class="form-check-input" type="checkbox" id="accepte_conditions" name="accepte_conditions" value="1" required>
                    <label class="form-check-label" for="accepte_conditions">J'ai lu les conditions du menu et les <a href="/cgv">conditions générales de vente</a>.</label>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Valider ma commande</button>
            </div>
        </div>
    </form>
</div>
