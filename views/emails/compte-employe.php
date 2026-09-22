<p>Bonjour <?= e($prenom) ?>,</p>
<p>Un compte employé a été créé pour vous sur l'application Vite &amp; Gourmand. Votre identifiant est : <strong><?= e($email) ?></strong>.</p>
<p>Pour des raisons de sécurité, votre mot de passe ne vous est pas communiqué par e-mail : <strong>rapprochez-vous de l'administrateur pour l'obtenir.</strong></p>
<p><a href="<?= e(base_url()) ?>/connexion">Accéder à la connexion</a></p>
