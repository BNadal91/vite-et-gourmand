<?php $messages = flashes(); if ($messages): ?>
<div class="container mt-3" role="status" aria-live="polite">
    <?php foreach ($messages as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($f['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer ce message"></button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (errors() !== []): ?>
<div class="container mt-3">
    <div class="alert alert-danger" role="alert" tabindex="-1" id="resume-erreurs">
        <p class="fw-bold mb-1">Le formulaire contient <?= count(errors()) ?> erreur(s) :</p>
        <ul class="mb-0">
            <?php foreach (errors() as $field => $msg): ?>
                <li><a href="#<?= e($field) ?>" class="alert-link"><?= e($msg) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
