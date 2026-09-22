<?php
$p = $plat ?? [];
$sel = isset($_SESSION['old']['allergenes']) ? array_map('intval', (array) $_SESSION['old']['allergenes']) : ($p['allergene_ids'] ?? []);
$action = $plat ? '/employe/plats/' . (int) $p['plat_id'] : '/employe/plats/nouveau';
?>
<div class="container py-5">
    <h1 class="h2"><?= e($titre) ?></h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>
    <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="form-card" novalidate>
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label for="titre_plat" class="form-label required">Nom du plat</label>
                <input type="text" class="form-control<?= aria_invalid('titre_plat') ?>" id="titre_plat" name="titre_plat" value="<?= old('titre_plat', $p['titre_plat'] ?? '') ?>" maxlength="120" required>
                <?= field_error('titre_plat') ?>
            </div>
            <div class="col-md-4">
                <label for="type_plat" class="form-label required">Type</label>
                <select class="form-select" id="type_plat" name="type_plat">
                    <?php foreach (['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'] as $k => $l): ?>
                        <option value="<?= $k ?>" <?= old('type_plat', $p['type_plat'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" value="<?= old('description', $p['description'] ?? '') ?>" maxlength="255">
            </div>
            <div class="col-md-6">
                <label for="photo" class="form-label">Photo (JPEG, PNG ou WebP, 2 Mo max.)</label>
                <input type="file" class="form-control<?= aria_invalid('photo') ?>" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                <?= field_error('photo') ?>
                <?php if (!empty($p['a_photo'])): ?><img src="/plats/<?= (int) $p['plat_id'] ?>/photo" alt="Photo actuelle du plat" class="img-thumbnail mt-2" width="120"><?php endif; ?>
            </div>
            <fieldset class="col-12">
                <legend class="form-label fs-6">Allergènes présents</legend>
                <div class="row">
                    <?php foreach ($allergenes as $a): ?>
                        <div class="col-6 col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="allergenes[]" value="<?= (int) $a['allergene_id'] ?>" id="al<?= (int) $a['allergene_id'] ?>" <?= in_array((int) $a['allergene_id'], $sel, true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="al<?= (int) $a['allergene_id'] ?>"><?= e($a['libelle']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Enregistrer le plat</button>
            <a href="/employe/plats" class="btn btn-outline-primary">Retour</a>
        </div>
    </form>
</div>
