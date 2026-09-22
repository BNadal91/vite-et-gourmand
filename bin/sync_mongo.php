<?php
/**
 * Reconstruit la collection MongoDB « commandes_stats » à partir des commandes
 * de la base relationnelle (à lancer après l'import du jeu de données).
 * Usage : php bin/sync_mongo.php
 */
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Core\Mongo;
use App\Models\Commande;

if (PHP_SAPI !== 'cli') {
    exit('Script réservé à la ligne de commande.');
}
if (!Mongo::enabled()) {
    fwrite(STDERR, "MongoDB non configuré : vérifiez l'extension mongodb et la variable MONGODB_URI.\n");
    exit(1);
}
$n = Mongo::resynchroniser((new Commande())->pourStatistiques());
echo "$n commande(s) synchronisée(s) dans MongoDB (" . Mongo::compter() . " document(s)).\n";
