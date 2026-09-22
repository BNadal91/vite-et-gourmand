<?php
declare(strict_types=1);

namespace App\Core;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;

/**
 * Accès à la base NoSQL (MongoDB) via l'extension officielle ext-mongodb.
 * Utilisée pour les statistiques de l'administrateur : chaque commande est
 * dupliquée sous forme de document dénormalisé dans la collection « commandes_stats ».
 * Avantage : agrégations rapides ($group) sans jointures, sans charger la base métier.
 */
final class Mongo
{
    private static ?Manager $manager = null;
    public const COLLECTION = 'commandes_stats';

    public static function enabled(): bool
    {
        return extension_loaded('mongodb') && (string) Env::get('MONGODB_URI', '') !== '';
    }

    private static function manager(): Manager
    {
        if (self::$manager === null) {
            self::$manager = new Manager((string) Env::get('MONGODB_URI'));
        }
        return self::$manager;
    }

    private static function ns(): string
    {
        return Env::get('MONGODB_DB', 'vite_gourmand') . '.' . self::COLLECTION;
    }

    /** Insère ou remplace (upsert) le document statistique d'une commande. */
    public static function upsertCommande(array $c): void
    {
        if (!self::enabled()) {
            return;
        }
        try {
            $doc = [
                '_id'             => $c['numero_commande'],
                'menu_id'         => (int) $c['menu_id'],
                'menu_titre'      => $c['menu_titre'],
                'nombre_personne' => (int) $c['nombre_personne'],
                'prix_menu'       => (float) $c['prix_menu'],
                'prix_livraison'  => (float) $c['prix_livraison'],
                'prix_total'      => (float) $c['prix_total'],
                'statut'          => $c['statut'],
                'date_commande'   => new UTCDateTime((new \DateTimeImmutable($c['date_commande']))->getTimestamp() * 1000),
            ];
            $bulk = new BulkWrite();
            $bulk->update(['_id' => $doc['_id']], $doc, ['upsert' => true]);
            self::manager()->executeBulkWrite(self::ns(), $bulk);
        } catch (\Throwable $e) {
            // La base NoSQL ne doit jamais bloquer une commande : on journalise seulement
            error_log('[MongoDB] ' . $e->getMessage());
        }
    }

    public static function updateStatut(string $numero, string $statut): void
    {
        if (!self::enabled()) {
            return;
        }
        try {
            $bulk = new BulkWrite();
            $bulk->update(['_id' => $numero], ['$set' => ['statut' => $statut]]);
            self::manager()->executeBulkWrite(self::ns(), $bulk);
        } catch (\Throwable $e) {
            error_log('[MongoDB] ' . $e->getMessage());
        }
    }

    /**
     * Nombre de commandes et chiffre d'affaires par menu (hors commandes annulées),
     * avec filtres facultatifs : menu et période.
     */
    public static function statsParMenu(?int $menuId, ?string $du, ?string $au): array
    {
        $match = ['statut' => ['$ne' => 'annulee']];
        if ($menuId) {
            $match['menu_id'] = $menuId;
        }
        if ($du || $au) {
            $match['date_commande'] = [];
            if ($du) {
                $match['date_commande']['$gte'] = new UTCDateTime((new \DateTimeImmutable($du . ' 00:00:00'))->getTimestamp() * 1000);
            }
            if ($au) {
                $match['date_commande']['$lte'] = new UTCDateTime((new \DateTimeImmutable($au . ' 23:59:59'))->getTimestamp() * 1000);
            }
        }
        $command = new Command([
            'aggregate' => self::COLLECTION,
            'pipeline'  => [
                ['$match' => $match],
                ['$group' => [
                    '_id'              => '$menu_titre',
                    'nombre_commandes' => ['$sum' => 1],
                    'chiffre_affaires' => ['$sum' => '$prix_total'],
                ]],
                ['$sort' => ['nombre_commandes' => -1]],
            ],
            'cursor' => new \stdClass(),
        ]);
        $cursor = self::manager()->executeCommand(Env::get('MONGODB_DB', 'vite_gourmand'), $command);
        $result = [];
        foreach ($cursor as $row) {
            $result[] = [
                'menu'             => (string) $row->_id,
                'nombre_commandes' => (int) $row->nombre_commandes,
                'chiffre_affaires' => round((float) $row->chiffre_affaires, 2),
            ];
        }
        return $result;
    }

    /** Vide puis reconstruit la collection à partir de la base relationnelle. */
    public static function resynchroniser(array $commandes): int
    {
        $bulk = new BulkWrite();
        $bulk->delete([]);
        self::manager()->executeBulkWrite(self::ns(), $bulk);
        foreach ($commandes as $c) {
            self::upsertCommande($c);
        }
        return count($commandes);
    }

    public static function compter(): int
    {
        $cursor = self::manager()->executeQuery(self::ns(), new Query([]));
        return count($cursor->toArray());
    }
}
