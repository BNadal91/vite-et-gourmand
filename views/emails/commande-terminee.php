<p>Bonjour <?= e($c['prenom_client']) ?>,</p>
<p>Votre commande <strong>n° <?= e($c['numero_commande']) ?></strong> (<?= e($c['menu_titre']) ?>) est terminée. Nous espérons que vous vous êtes régalés !</p>
<p>Connectez-vous à votre compte pour nous donner votre avis (note de 1 à 5 et commentaire) :
<a href="<?= e(base_url()) ?>/compte/commandes/<?= e($c['numero_commande']) ?>#avis">donner mon avis</a>.</p>
