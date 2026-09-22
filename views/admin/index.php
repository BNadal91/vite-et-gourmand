<div class="container py-5">
    <h1 class="h2">Administration</h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>

    <h2 class="section-titre h4">Statistiques des commandes</h2>
    <p class="small text-muted">Données issues de la base NoSQL (MongoDB, collection <code>commandes_stats</code>), hors commandes annulées.</p>
    <?php if (!$mongoOk): ?>
        <p class="alert alert-warning">La connexion MongoDB n'est pas configurée (variable MONGODB_URI).</p>
    <?php endif; ?>

    <form id="form-stats" class="filtres row g-3 align-items-end mb-4" aria-label="Filtres des statistiques">
        <div class="col-md-4">
            <label for="stats-menu" class="form-label">Menu</label>
            <select id="stats-menu" name="menu" class="form-select">
                <option value="">Tous les menus</option>
                <?php foreach ($menus as $m): ?><option value="<?= (int) $m['menu_id'] ?>"><?= e($m['titre']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="stats-du" class="form-label">Du</label>
            <input type="date" id="stats-du" name="du" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="stats-au" class="form-label">Au</label>
            <input type="date" id="stats-au" name="au" class="form-control">
        </div>
        <div class="col-md-2"><button type="reset" class="btn btn-outline-primary w-100">Réinitialiser</button></div>
    </form>

    <div class="row g-3 mb-4" aria-live="polite">
        <div class="col-md-6"><div class="stat-tile"><div>Nombre de commandes</div><div class="valeur" id="total-commandes">–</div></div></div>
        <div class="col-md-6"><div class="stat-tile"><div>Chiffre d'affaires</div><div class="valeur" id="total-ca">–</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="form-card">
                <h3 class="h5">Nombre de commandes par menu</h3>
                <canvas id="chart-commandes" height="320" role="img" aria-label="Graphique en barres du nombre de commandes par menu (détail dans le tableau ci-contre)"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="form-card">
                <h3 class="h5">Chiffre d'affaires par menu</h3>
                <table class="table table-sm mb-0">
                    <caption class="visually-hidden">Commandes et chiffre d'affaires par menu</caption>
                    <thead><tr><th scope="col">Menu</th><th scope="col" class="text-end">Commandes</th><th scope="col" class="text-end">CA TTC</th></tr></thead>
                    <tbody id="table-stats"><tr><td colspan="3">Chargement…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
