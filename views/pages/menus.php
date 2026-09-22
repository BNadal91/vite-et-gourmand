<div class="container py-5">
    <h1 class="mb-2">Nos menus</h1>
    <p class="lead mb-4">Retrouvez tous nos menus et affinez votre recherche grâce aux filtres : les résultats se mettent à jour instantanément.</p>

    <div class="row g-4">
        <aside class="col-lg-3" aria-labelledby="titre-filtres">
            <form class="filtres" id="form-filtres" action="/menus" method="get" novalidate>
                <h2 id="titre-filtres" class="h5">Filtrer les menus</h2>

                <div class="mb-3">
                    <label for="prix_max" class="form-label">Prix maximum (€)</label>
                    <input type="number" min="0" step="10" class="form-control" id="prix_max" name="prix_max" value="<?= e($f['prix_max']) ?>" aria-describedby="aide-prix">
                    <div id="aide-prix" class="form-text">Prix pour le nombre minimum de personnes.</div>
                </div>

                <fieldset class="mb-3">
                    <legend class="form-label fs-6">Fourchette de prix (€)</legend>
                    <div class="d-flex gap-2">
                        <div><label for="prix_min_fourchette" class="visually-hidden">Prix minimum</label>
                            <input type="number" min="0" step="10" class="form-control" id="prix_min_fourchette" name="prix_min_fourchette" placeholder="de" value="<?= e($f['prix_min_fourchette']) ?>"></div>
                        <div><label for="prix_max_fourchette" class="visually-hidden">Prix maximum de la fourchette</label>
                            <input type="number" min="0" step="10" class="form-control" id="prix_max_fourchette" name="prix_max_fourchette" placeholder="à" value="<?= e($f['prix_max_fourchette']) ?>"></div>
                    </div>
                </fieldset>

                <div class="mb-3">
                    <label for="theme" class="form-label">Thème</label>
                    <select class="form-select" id="theme" name="theme">
                        <option value="">Tous les thèmes</option>
                        <?php foreach ($themes as $t): ?>
                            <option value="<?= (int) $t['theme_id'] ?>" <?= (int) $f['theme'] === (int) $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="regime" class="form-label">Régime</label>
                    <select class="form-select" id="regime" name="regime">
                        <option value="">Tous les régimes</option>
                        <?php foreach ($regimes as $rg): ?>
                            <option value="<?= (int) $rg['regime_id'] ?>" <?= (int) $f['regime'] === (int) $rg['regime_id'] ? 'selected' : '' ?>><?= e($rg['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="personnes" class="form-label">Nombre de convives</label>
                    <input type="number" min="1" class="form-control" id="personnes" name="personnes" value="<?= e($f['personnes']) ?>" aria-describedby="aide-personnes">
                    <div id="aide-personnes" class="form-text">Affiche les menus commandables pour ce nombre de personnes (minimum requis inférieur ou égal).</div>
                </div>

                <div class="d-grid gap-2">
                    <noscript><button type="submit" class="btn btn-primary">Filtrer</button></noscript>
                    <button type="reset" class="btn btn-outline-primary" id="reset-filtres">Réinitialiser les filtres</button>
                </div>
            </form>
        </aside>

        <section class="col-lg-9" aria-labelledby="titre-resultats">
            <h2 id="titre-resultats" class="visually-hidden">Résultats</h2>
            <p id="compteur" class="fw-semibold" role="status" aria-live="polite"><?= count($menus) ?> menu(s) trouvé(s)</p>
            <div class="row g-4" id="liste-menus">
                <?php foreach ($menus as $m): ?>
                    <div class="col-md-6 col-xl-4"><?php require ROOT_PATH . '/views/partials/menu-card.php'; ?></div>
                <?php endforeach; ?>
            </div>
            <p id="aucun-menu" class="alert alert-info <?= $menus ? 'd-none' : '' ?>">Aucun menu ne correspond à vos critères. Essayez d'élargir votre recherche.</p>
        </section>
    </div>
</div>
