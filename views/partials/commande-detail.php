<?php /** @var array $c */ ?>
<dl class="row mb-0">
    <dt class="col-sm-5">Menu</dt><dd class="col-sm-7"><?= e($c['menu_titre']) ?></dd>
    <dt class="col-sm-5">Client</dt><dd class="col-sm-7"><?= e($c['prenom_client'] . ' ' . $c['nom_client']) ?></dd>
    <dt class="col-sm-5">E-mail / GSM</dt><dd class="col-sm-7"><?= e($c['email_client']) ?> — <?= e($c['telephone_client']) ?></dd>
    <dt class="col-sm-5">Date commande</dt><dd class="col-sm-7"><?= date_fr($c['date_commande'], true) ?></dd>
    <dt class="col-sm-5">Prestation</dt><dd class="col-sm-7"><?= date_fr($c['date_prestation']) ?> à <?= e(substr($c['heure_livraison'], 0, 5)) ?></dd>
    <dt class="col-sm-5">Lieu</dt><dd class="col-sm-7"><?= e($c['adresse_livraison']) ?>, <?= e($c['code_postal_livraison'] . ' ' . $c['ville_livraison']) ?>
        <?php if ((float) $c['distance_km'] > 0): ?><span class="text-muted">(<?= e(str_replace('.', ',', (string) $c['distance_km'])) ?> km)</span><?php endif; ?></dd>
    <dt class="col-sm-5">Nombre de personnes</dt><dd class="col-sm-7"><?= (int) $c['nombre_personne'] ?></dd>
    <dt class="col-sm-5">Prêt de matériel</dt><dd class="col-sm-7"><?= (int) $c['pret_materiel'] ? 'Oui' . ((int) $c['restitution_materiel'] ? ' (restitué)' : '') : 'Non' ?></dd>
    <dt class="col-sm-5">Prix du menu</dt><dd class="col-sm-7"><?= prix((float) $c['prix_menu'] + (float) $c['reduction']) ?></dd>
    <?php if ((float) $c['reduction'] > 0): ?>
        <dt class="col-sm-5">Réduction 10 %</dt><dd class="col-sm-7">− <?= prix($c['reduction']) ?></dd>
    <?php endif; ?>
    <dt class="col-sm-5">Livraison</dt><dd class="col-sm-7"><?= (float) $c['prix_livraison'] > 0 ? prix($c['prix_livraison']) : 'Offerte (Bordeaux)' ?></dd>
    <dt class="col-sm-5">Total</dt><dd class="col-sm-7 fw-bold fs-5"><?= prix($c['prix_total']) ?></dd>
    <?php if ($c['motif_annulation']): ?>
        <dt class="col-sm-5">Motif d'annulation</dt><dd class="col-sm-7"><?= e($c['motif_annulation']) ?></dd>
    <?php endif; ?>
</dl>
