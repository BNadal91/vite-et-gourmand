<div class="container py-5 text-center">
    <p class="display-5 text-muted mb-0"><?= (int) ($code ?? 500) ?></p>
    <h1><?= e($titre ?? 'Erreur') ?></h1>
    <p class="lead"><?= e($message ?? '') ?></p>
    <a href="/" class="btn btn-primary">Retour à l'accueil</a>
</div>
