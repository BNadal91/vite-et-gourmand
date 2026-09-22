<?php
$m = $menu ?? [];
$selPlats = isset($_SESSION['old']['plats']) ? array_map('intval', (array) $_SESSION['old']['plats']) : ($m['plat_ids'] ?? []);
$action = $menu ? '/employe/menus/' . (int) $m['menu_id'] : '/employe/menus/nouveau';
?>
<div class="container py-5">
    <h1 class="h2"><?= e($titre) ?></h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>
    <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="form-card" novalidate>
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label for="titre" class="form-label required">Titre</label>
                <input type="text" class="form-control<?= aria_invalid('titre') ?>" id="titre" name="titre" value="<?= old('titre', $m['titre'] ?? '') ?>" maxlength="120" required>
                <?= field_error('titre') ?>
            </div>
            <div class="col-md-2">
                <label for="theme_id" class="form-label required">Thème</label>
                <select class="form-select" id="theme_id" name="theme_id">
                    <?php foreach ($themes as $t): ?><option value="<?= (int) $t['theme_id'] ?>" <?= (int) old('theme_id', $m['theme_id'] ?? 0) === (int) $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="regime_id" class="form-label required">Régime</label>
                <select class="form-select" id="regime_id" name="regime_id">
                    <?php foreach ($regimes as $rg): ?><option value="<?= (int) $rg['regime_id'] ?>" <?= (int) old('regime_id', $m['regime_id'] ?? 0) === (int) $rg['regime_id'] ? 'selected' : '' ?>><?= e($rg['libelle']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label for="description" class="form-label required">Description (présentation du menu)</label>
                <textarea class="form-control<?= aria_invalid('description') ?>" id="description" name="description" rows="3" required><?= old('description', $m['description'] ?? '') ?></textarea>
                <?= field_error('description') ?>
            </div>
            <div class="col-md-4">
                <label for="nombre_personne_minimum" class="form-label required">Nombre de personnes minimum</label>
                <input type="number" min="1" class="form-control<?= aria_invalid('nombre_personne_minimum') ?>" id="nombre_personne_minimum" name="nombre_personne_minimum" value="<?= old('nombre_personne_minimum', $m['nombre_personne_minimum'] ?? '') ?>" required>
                <?= field_error('nombre_personne_minimum') ?>
            </div>
            <div class="col-md-4">
                <label for="prix_par_personne" class="form-label required">Prix par personne (€)</label>
                <input type="number" min="0" step="0.01" class="form-control<?= aria_invalid('prix_par_personne') ?>" id="prix_par_personne" name="prix_par_personne" value="<?= old('prix_par_personne', $m['prix_par_personne'] ?? '') ?>" required>
                <?= field_error('prix_par_personne') ?>
            </div>
            <div class="col-md-4">
                <label for="quantite_restante" class="form-label required">Stock disponible (nombre de commandes)</label>
                <input type="number" min="0" class="form-control<?= aria_invalid('quantite_restante') ?>" id="quantite_restante" name="quantite_restante" value="<?= old('quantite_restante', $m['quantite_restante'] ?? '') ?>" required>
                <?= field_error('quantite_restante') ?>
            </div>
            <div class="col-12">
                <label for="conditions" class="form-label required">Conditions (délai de commande, stockage…)</label>
                <textarea class="form-control<?= aria_invalid('conditions') ?>" id="conditions" name="conditions" rows="3" required><?= old('conditions', $m['conditions'] ?? '') ?></textarea>
                <?= field_error('conditions') ?>
            </div>

            <fieldset class="col-12" id="plats">
                <legend class="form-label fs-6 required">Plats proposés (un plat peut appartenir à plusieurs menus)</legend>
                <?= field_error('plats') ?>
                <div class="row">
                    <?php foreach (['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'] as $type => $label): ?>
                        <div class="col-md-4">
                            <p class="fw-bold mb-1"><?= $label ?></p>
                            <?php foreach ($plats as $p): if ($p['type_plat'] !== $type) continue; ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="plats[]" value="<?= (int) $p['plat_id'] ?>" id="plat<?= (int) $p['plat_id'] ?>" <?= in_array((int) $p['plat_id'], $selPlats, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="plat<?= (int) $p['plat_id'] ?>"><?= e($p['titre_plat']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="small mt-2"><a href="/employe/plats/nouveau">Créer un nouveau plat</a></p>
            </fieldset>

            <fieldset class="col-12">
                <legend class="form-label fs-6">Galerie d'images</legend>
                <?php if (!empty($m['images'])): ?>
                    <div class="row g-2 mb-3">
                        <?php foreach ($m['images'] as $img): ?>
                            <div class="col-6 col-md-3 text-center">
                                <img src="<?= e($img['chemin']) ?>" alt="<?= e($img['texte_alternatif']) ?>" class="img-fluid rounded mb-1">
                                <button type="submit" form="suppr-img-<?= (int) $img['image_id'] ?>" class="btn btn-sm btn-outline-danger">Supprimer<span class="visually-hidden"> l'image <?= e($img['texte_alternatif']) ?></span></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label for="image" class="form-label">Ajouter une image (JPEG, PNG ou WebP, 2 Mo max.)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="col-md-6">
                        <label for="image_alt" class="form-label">Description de l'image (texte alternatif)</label>
                        <input type="text" class="form-control" id="image_alt" name="image_alt" maxlength="255">
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Enregistrer le menu</button>
            <a href="/employe/menus" class="btn btn-outline-primary">Retour à la liste</a>
        </div>
    </form>
    <?php foreach ($m['images'] ?? [] as $img): ?>
        <form method="post" action="/employe/images/<?= (int) $img['image_id'] ?>/supprimer" id="suppr-img-<?= (int) $img['image_id'] ?>" data-confirm="Supprimer cette image ?"><?= csrf_field() ?></form>
    <?php endforeach; ?>
</div>
