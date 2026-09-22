<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Env;

/**
 * Règles de gestion du prix (calculées côté serveur : le prix affiché en
 * JavaScript n'est qu'indicatif, le serveur recalcule toujours).
 *  - obligation de commander au moins le nombre minimum de personnes du menu
 *  - réduction de 10 % dès 5 personnes de plus que le minimum
 *  - livraison gratuite à Bordeaux, sinon 5 € + 0,59 € par kilomètre parcouru
 */
final class Tarif
{
    public const FRAIS_FIXES_LIVRAISON = 5.00;
    public const PRIX_KM = 0.59;
    public const TAUX_REDUCTION = 0.10;
    public const SEUIL_REDUCTION = 5;
    // Siège de Vite & Gourmand (point de départ des livraisons)
    public const SIEGE_LAT = 44.8412;
    public const SIEGE_LON = -0.5733;

    public static function estBordeaux(string $ville): bool
    {
        $v = mb_strtolower(trim($ville));
        $v = strtr($v, ['é' => 'e', 'è' => 'e', '-' => ' ']);
        return $v === 'bordeaux';
    }

    public static function calculer(array $menu, int $nombre, string $ville, float $distanceKm): array
    {
        $min = (int) $menu['nombre_personne_minimum'];
        $prixBrut = round((float) $menu['prix_par_personne'] * $nombre, 2);
        $reduction = $nombre >= $min + self::SEUIL_REDUCTION ? round($prixBrut * self::TAUX_REDUCTION, 2) : 0.0;
        $prixMenu = round($prixBrut - $reduction, 2);
        $livraison = self::estBordeaux($ville) ? 0.0 : round(self::FRAIS_FIXES_LIVRAISON + self::PRIX_KM * $distanceKm, 2);
        return [
            'prix_brut'      => $prixBrut,
            'reduction'      => $reduction,
            'prix_menu'      => $prixMenu,
            'prix_livraison' => $livraison,
            'prix_total'     => round($prixMenu + $livraison, 2),
            'distance_km'    => round($distanceKm, 2),
        ];
    }

    /**
     * Distance routière entre le siège et l'adresse de livraison :
     * 1) géocodage via l'API Adresse du gouvernement (api-adresse.data.gouv.fr)
     * 2) itinéraire routier via OSRM ; à défaut, distance à vol d'oiseau (formule de haversine) majorée de 30 %.
     * Retourne null si l'adresse est introuvable.
     */
    public static function distance(string $adresse, string $codePostal, string $ville): ?array
    {
        if (self::estBordeaux($ville)) {
            return ['km' => 0.0, 'ville' => 'Bordeaux', 'source' => 'bordeaux'];
        }
        if (Env::get('GEO_API', 'on') === 'off') {
            return null;
        }
        $q = http_build_query(['q' => "$adresse $codePostal $ville", 'limit' => 1]);
        $geo = self::getJson('https://api-adresse.data.gouv.fr/search/?' . $q);
        $feature = $geo['features'][0] ?? null;
        if (!$feature || ($feature['properties']['score'] ?? 0) < 0.4) {
            return null;
        }
        [$lon, $lat] = $feature['geometry']['coordinates'];
        $villeTrouvee = $feature['properties']['city'] ?? $ville;

        $route = self::getJson(sprintf('https://router.project-osrm.org/route/v1/driving/%F,%F;%F,%F?overview=false',
            self::SIEGE_LON, self::SIEGE_LAT, $lon, $lat));
        if (isset($route['routes'][0]['distance'])) {
            return ['km' => round($route['routes'][0]['distance'] / 1000, 2), 'ville' => $villeTrouvee, 'source' => 'route'];
        }
        return ['km' => round(self::haversine(self::SIEGE_LAT, self::SIEGE_LON, $lat, $lon) * 1.3, 2), 'ville' => $villeTrouvee, 'source' => 'estimation'];
    }

    public static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $r * asin(sqrt($a));
    }

    private static function getJson(string $url): ?array
    {
        $ctx = stream_context_create(['http' => ['timeout' => 4, 'header' => "User-Agent: ViteEtGourmand/1.0\r\n"]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}
