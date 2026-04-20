<?php

namespace Database\Seeders;

use App\Models\ShootingGround;
use Illuminate\Database\Seeder;

class ShootingGroundSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/shooting-grounds.csv');
        if (! is_readable($path)) {
            $this->command?->warn('Missing database/data/shooting-grounds.csv — skipping shooting grounds seed.');

            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if ($headers === false) {
            fclose($handle);

            return;
        }

        $headers = array_map(fn (string $h) => trim($h), $headers);

        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($data) < count($headers)) {
                $data = array_pad($data, count($headers), '');
            }
            $row = array_combine($headers, array_slice($data, 0, count($headers)));
            if ($row === false) {
                continue;
            }

            if (! $this->isUnitedKingdom($row)) {
                continue;
            }

            $slug = trim((string) ($row['Slug'] ?? ''));
            $name = trim((string) ($row['Name'] ?? ''));
            if ($slug === '' || $name === '') {
                continue;
            }

            ShootingGround::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'photo_url' => ShootingGround::photoUrlForSlug($slug),
                    'city' => $this->nullableString($row['City/Town'] ?? null),
                    'county' => $this->nullableString($row['County'] ?? null),
                    'postcode' => $this->nullableString($row['Postcode'] ?? null),
                    'full_address' => $this->nullableString($row['Full Address'] ?? null),
                    'latitude' => $this->nullableDecimal($row['Latitude'] ?? null),
                    'longitude' => $this->nullableDecimal($row['Longitude'] ?? null),
                    'website' => $this->nullableString($row['Website'] ?? null),
                    'facebook_url' => $this->nullableString($row['Facebook'] ?? null),
                    'instagram_url' => $this->nullableString($row['Instagram'] ?? null),
                    'description' => $this->nullableString($row['Description'] ?? null),
                    'opening_hours' => null,
                    'has_practice' => $this->yn($row['Practice'] ?? null),
                    'has_lessons' => $this->yn($row['Lessons'] ?? null),
                    'has_competitions' => $this->yn($row['Competitions'] ?? null),
                    'practice_notes' => $this->nullableString($row['Practice Notes'] ?? null),
                    'lesson_notes' => $this->nullableString($row['Lesson Notes'] ?? null),
                    'competition_notes' => $this->nullableString($row['Competition Notes'] ?? null),
                ]
            );
        }

        fclose($handle);
    }

    /**
     * UK-only dataset: exclude the Republic of Ireland (e.g. CPSA-style imports that include ROI rows).
     */
    private function isUnitedKingdom(array $row): bool
    {
        $county = strtolower((string) ($row['County'] ?? ''));
        if (str_contains($county, 'co. dublin')) {
            return false;
        }

        return true;
    }

    private function yn(mixed $value): bool
    {
        return strtolower(trim((string) $value)) === 'y';
    }

    private function nullableString(mixed $value): ?string
    {
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }

    private function nullableDecimal(mixed $value): ?string
    {
        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        return is_numeric($s) ? $s : null;
    }
}
