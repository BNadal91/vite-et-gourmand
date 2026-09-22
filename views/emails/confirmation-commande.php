<p>Bonjour <?= e($prenom) ?>,</p>
<p>Nous avons bien reçu votre commande <strong>n° <?= e($numero) ?></strong>. Elle sera validée très prochainement par notre équipe.</p>
<ul>
<li>Menu : <?= e($menu['titre']) ?> pour <?= (int) $c['nombre_personne'] ?> personnes</li>
<li>Prestation : le <?= date_fr($c['date_prestation']) ?> à <?= e($c['heure_livraison']) ?></li>
<li>Adresse : <?= e($c['adresse_livraison'] . ', ' . $c['code_postal_livraison'] . ' ' . $c['ville_livraison']) ?></li>
<li>Menu : <?= prix($c['prix_menu']) ?><?= $c['reduction'] > 0 ? ' (réduction de ' . prix($c['reduction']) . ' incluse)' : '' ?></li>
<li>Livraison : <?= prix($c['prix_livraison']) ?></li>
<li><strong>Total : <?= prix($c['prix_total']) ?></strong></li>
</ul>
<p><strong>Rappel des conditions du menu :</strong> <?= e($menu['conditions']) ?></p>
<p>Vous pouvez suivre, modifier ou annuler votre commande (tant qu'elle n'est pas acceptée) depuis <a href="<?= e(base_url()) ?>/compte">votre espace</a>.</p>
