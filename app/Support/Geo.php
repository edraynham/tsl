<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

final class Geo
{
    /**
     * Great-circle distance in miles (WGS84 sphere).
     */
    public static function haversineMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthMiles = 3958.7613;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthMiles * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Whether request coordinates look like valid lat/lng (loose; UK-focused use case).
     */
    public static function validUserCoords(mixed $lat, mixed $lng): bool
    {
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return false;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        return $lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0;
    }

    /**
     * Geocode a UK place or postcode via OpenStreetMap Nominatim (one result).
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function geocodeUkSearch(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => self::nominatimUserAgent(),
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'json',
                    'q' => $query,
                    'countrycodes' => 'gb',
                    'limit' => 1,
                ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $rows = $response->json();
        if (! is_array($rows) || $rows === [] || ! isset($rows[0]['lat'], $rows[0]['lon'])) {
            return null;
        }

        return [
            'lat' => (float) $rows[0]['lat'],
            'lng' => (float) $rows[0]['lon'],
        ];
    }

    private static function nominatimUserAgent(): string
    {
        $name = config('app.name', 'TSL');
        $host = parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';

        return "{$name} ({$host})";
    }
}
