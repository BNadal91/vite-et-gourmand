<?php
/**
 * Champs communs de la prestation (création et modification de commande).
 * @var array $valeurs  valeurs par défaut
 */
$val = static fn (string $k) => old($k, $valeurs[$k] ?? '');
?>
<fieldset class="mb-4">
    <legend class="h5">Adresse et date de la prestation</legend>
    <div class="row g-3">
        <div class="col-12">
            <label for="adresse_livraison" class="form-label required">Adresse de la prestation</label>
            <input type="text" class="form-control<?= aria_invalid('adresse_livraison') ?>" id="adresse_livraison" name="adresse_livraison"
                   value="<?= $val('adresse_livraison') ?>" required maxlength="255" autocomplete="street-address">
            <?= field_error('adresse_livraison') ?>
        </div>
        <div class="col-md-4">
            <label for="code_postal_livraison" class="form-label required">Code postal</label>
            <input type="text" class="form-control<?= aria_invalid('code_postal_livraison') ?>" id="code_postal_livraison" name="code_postal_livraison"
                   value="<?= $val('code_postal_livraison') ?>" required pattern="\d{5}" inputmode="numeric" autocomplete="postal-code">
            <?= field_error('code_postal_livraison') ?>
        </div>
        <div class="col-md-8">
            <label for="ville_livraison" class="form-label required">Ville</label>
            <input type="text" class="form-control<?= aria_invalid('ville_livraison') ?>" id="ville_livraison" name="ville_livraison"
                   value="<?= $val('ville_livraison') ?>" required maxlength="100" autocomplete="address-level2">
            <?= field_error('ville_livraison') ?>
        </div>
        <div class="col-12 <?= isset(errors()['distance_km']) ? '' : 'd-none' ?>" id="bloc-distance">
            <label for="distance_km" class="form-label">Distance approximative depuis Bordeaux (km)</label>
            <input type="number" min="0" max="500" step="0.1" class="form-control<?= aria_invalid('distance_km') ?>" id="distance_km" name="distance_km" value="<?= $val('distance_km') ?>">
            <?= field_error('distance_km') ?>
        </div>
        <div class="col-md-6">
            <label for="date_prestation" class="form-label required">Date de la prestation</label>
            <input type="date" class="form-control<?= aria_invalid('date_prestation') ?>" id="date_prestation" name="date_prestation"
                   value="<?= $val('date_prestation') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            <?= field_error('date_prestation') ?>
        </div>
        <div class="col-md-6">
            <label for="heure_livraison" class="form-label required">Heure de livraison souhaitée</label>
            <input type="time" class="form-control<?= aria_invalid('heure_livraison') ?>" id="heure_livraison" name="heure_livraison"
                   value="<?= e(substr(html_entity_decode($val('heure_livraison')), 0, 5)) ?>" required>
            <?= field_error('heure_livraison') ?>
        </div>
        <div class="col-md-6">
            <label for="telephone_client" class="form-label required">GSM</label>
            <input type="tel" class="form-control<?= aria_invalid('telephone_client') ?>" id="telephone_client" name="telephone_client"
                   value="<?= $val('telephone_client') ?>" required autocomplete="tel">
            <?= field_error('telephone_client') ?>
        </div>
    </div>
</fieldset>

<fieldset class="mb-4">
    <legend class="h5">Nombre de personnes</legend>
    <label for="nombre_personne" class="form-label required">Nombre de convives</label>
    <input type="number" class="form-control<?= aria_invalid('nombre_personne') ?>" id="nombre_personne" name="nombre_personne"
           value="<?= $val('nombre_personne') ?>" min="1" required aria-describedby="aide-nombre">
    <div id="aide-nombre" class="form-text">Minimum imposé par le menu ; −10 % à partir de 5 personnes de plus que le minimum.</div>
    <?= field_error('nombre_personne') ?>
</fieldset>
