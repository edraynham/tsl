<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class Weather
{
    /**
     * Current conditions at a point. Cached per ground to limit API use.
     *
     * @return array{
     *     temp_c: float,
     *     summary: string,
     *     wind_mph: int,
     *     wind_from: string,
     *     observed_at: string|null,
     * }|null
     */
    public static function currentForGround(float $latitude, float $longitude, int $groundId): ?array
    {
        return Cache::remember(
            'weather.ground.'.$groundId,
            now()->addMinutes(15),
            function () use ($latitude, $longitude): ?array {
                try {
                    $response = Http::timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'current' => 'temperature_2m,weather_code,wind_speed_10m,wind_direction_10m',
                        'timezone' => 'Europe/London',
                        'wind_speed_unit' => 'mph',
                    ]);

                    if (! $response->successful()) {
                        return null;
                    }

                    $current = $response->json('current');
                    if (! is_array($current)) {
                        return null;
                    }

                    $temp = $current['temperature_2m'] ?? null;
                    if (! is_numeric($temp)) {
                        return null;
                    }

                    $windDeg = (float) ($current['wind_direction_10m'] ?? 0);
                    $windSpeed = (float) ($current['wind_speed_10m'] ?? 0);

                    return [
                        'temp_c' => round((float) $temp, 1),
                        'summary' => self::weatherCodeLabel((int) ($current['weather_code'] ?? -1)),
                        'wind_mph' => (int) round($windSpeed),
                        'wind_from' => self::compassFromDegrees($windDeg),
                        'observed_at' => is_string($current['time'] ?? null) ? $current['time'] : null,
                    ];
                } catch (\Throwable) {
                    return null;
                }
            }
        );
    }

    private static function weatherCodeLabel(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear sky',
            $code === 1 => 'Mainly clear',
            $code === 2 => 'Partly cloudy',
            $code === 3 => 'Overcast',
            $code >= 45 && $code <= 48 => 'Fog',
            $code >= 51 && $code <= 57 => 'Drizzle',
            $code >= 61 && $code <= 67 => 'Rain',
            $code >= 71 && $code <= 77 => 'Snow',
            $code >= 80 && $code <= 82 => 'Rain showers',
            $code >= 85 && $code <= 86 => 'Snow showers',
            $code >= 95 => 'Thunderstorm',
            default => 'Mixed conditions',
        };
    }

    private static function compassFromDegrees(float $degrees): string
    {
        $dirs = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $idx = ((int) floor(($degrees + 11.25) / 22.5)) % 16;

        return $dirs[$idx];
    }
}
