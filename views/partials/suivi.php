<?php /** @var array $suivi */ ?>
<h2 class="h5">Suivi de la commande</h2>
<ol class="timeline mt-3">
    <?php foreach ($suivi as $s): ?>
        <li>
            <strong><?= e(statut_label($s['statut'])) ?></strong><br>
            <span class="small text-muted">le <?= date_fr($s['date_modification'], true) ?></span>
            <?php if ($s['commentaire']): ?><br><span class="small"><?= e($s['commentaire']) ?></span><?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
