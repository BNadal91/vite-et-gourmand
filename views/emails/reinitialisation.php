<p>Bonjour <?= e($prenom) ?>,</p>
<p>Vous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous pour en choisir un nouveau
(lien valable 1 heure, utilisable une seule fois) :</p>
<p><a href="<?= e($lien) ?>"><?= e($lien) ?></a></p>
<p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail : votre mot de passe reste inchangé.</p>
