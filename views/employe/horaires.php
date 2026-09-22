<div class="container py-5">
    <h1 class="h2">Espace employé</h1>
    <?php require ROOT_PATH . '/views/employe/_nav.php'; ?>
    <form method="post" action="/employe/horaires" class="form-card col-lg-8">
        <?= csrf_field() ?>
        <h2 class="h4">Horaires d'ouverture (affichés dans le pied de page)</h2>
        <table class="table align-middle">
            <caption class="visually-hidden">Horaires par jour</caption>
            <thead><tr><th scope="col">Jour</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th><th scope="col">Fermé</th></tr></thead>
            <tbody>
            <?php foreach ($horaires as $h): $id = (int) $h['horaire_id']; ?>
                <tr>
                    <th scope="row"><?= e($h['jour']) ?></th>
                    <td><label class="visually-hidden" for="o<?= $id ?>">Ouverture le <?= e($h['jour']) ?></label>
                        <input type="time" class="form-control" id="o<?= $id ?>" name="ouverture[<?= $id ?>]" value="<?= e($h['heure_ouverture']) ?>"></td>
                    <td><label class="visually-hidden" for="f<?= $id ?>">Fermeture le <?= e($h['jour']) ?></label>
                        <input type="time" class="form-control" id="f<?= $id ?>" name="fermeture[<?= $id ?>]" value="<?= e($h['heure_fermeture']) ?>"></td>
                    <td><div class="form-check"><input class="form-check-input" type="checkbox" id="c<?= $id ?>" name="ferme[<?= $id ?>]" value="1" <?= $h['heure_ouverture'] ? '' : 'checked' ?>>
                        <label class="form-check-label" for="c<?= $id ?>"><span class="visually-hidden"><?= e($h['jour']) ?> : </span>fermé</label></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Enregistrer les horaires</button>
    </form>
</div>
