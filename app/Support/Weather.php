<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
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

    /**
     * Hourly conditions at the ground for the event day, closest to the event start time.
     * Uses Open-Meteo forecast for future start times and the archive API for past events.
     *
     * @return array{
     *     temp_c: float,
     *     summary: string,
     *     wind_mph: int,
     *     wind_from: string,
     *     observed_at: string|null,
     *     is_forecast: bool,
     * }|null
     */
    public static function forGroundAtEventTime(
        float $latitude,
        float $longitude,
        CarbonInterface $startsAt,
        int $competitionId,
    ): ?array {
        $tz = 'Europe/London';
        $atLocal = Carbon::parse($startsAt)->timezone($tz);

        $cacheKey = 'weather.competition.'.$competitionId.'.'.$atLocal->format('Y-m-d-Hi');
        $ttl = $atLocal->isFuture()
            ? now()->addHours(6)
            : now()->addDays(30);

        return Cache::remember(
            $cacheKey,
            $ttl,
            function () use ($latitude, $longitude, $atLocal): ?array {
                $useForecast = $atLocal->isFuture();
                $url = $useForecast
                    ? 'https://api.open-meteo.com/v1/forecast'
                    : 'https://archive-api.open-meteo.com/v1/archive';

                try {
                    $response = Http::timeout(10)->get($url, [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'hourly' => 'temperature_2m,weather_code,wind_speed_10m,wind_direction_10m',
                        'start_date' => $atLocal->toDateString(),
                        'end_date' => $atLocal->toDateString(),
                        'timezone' => 'Europe/London',
                        'wind_speed_unit' => 'mph',
                    ]);

                    if (! $response->successful()) {
                        return null;
                    }

                    $hourly = $response->json('hourly');
                    if (! is_array($hourly)) {
                        return null;
                    }

                    $times = $hourly['time'] ?? null;
                    if (! is_array($times) || $times === []) {
                        return null;
                    }

                    $idx = self::closestHourlyIndex($times, $atLocal);
                    $temp = $hourly['temperature_2m'][$idx] ?? null;
                    if (! is_numeric($temp)) {
                        return null;
                    }

                    $windDeg = (float) ($hourly['wind_direction_10m'][$idx] ?? 0);
                    $windSpeed = (float) ($hourly['wind_speed_10m'][$idx] ?? 0);
                    $timeStr = is_string($times[$idx] ?? null) ? $times[$idx] : null;

                    return [
                        'temp_c' => round((float) $temp, 1),
                        'summary' => self::weatherCodeLabel((int) ($hourly['weather_code'][$idx] ?? -1)),
                        'wind_mph' => (int) round($windSpeed),
                        'wind_from' => self::compassFromDegrees($windDeg),
                        'observed_at' => $timeStr,
                        'is_forecast' => $useForecast,
                    ];
                } catch (\Throwable) {
                    return null;
                }
            }
        );
    }

    /**
     * @param  list<string|mixed>  $times
     */
    private static function closestHourlyIndex(array $times, Carbon $target): int
    {
        $bestIdx = 0;
        $bestDiff = PHP_INT_MAX;
        foreach ($times as $i => $t) {
            if (! is_string($t)) {
                continue;
            }
            $ts = Carbon::parse($t, 'Europe/London');
            $diff = abs($ts->diffInSeconds($target));
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestIdx = (int) $i;
            }
        }

        return $bestIdx;
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
