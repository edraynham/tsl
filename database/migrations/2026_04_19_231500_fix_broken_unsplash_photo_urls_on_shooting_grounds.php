<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Unsplash removed some assets; replace stored URLs that still reference them.
 */
return new class extends Migration
{
    public function up(): void
    {
        $replacements = [
            'photo-1416879595882-3373a0480cae' => 'photo-1500530855697-b586d89ba3ee',
            'photo-1433838552652-f9a70b47378f' => 'photo-1511497584788-876760111969',
            'photo-1494548162004-4333fca3b7db' => 'photo-1518173946687-a4c8892bbd9f',
        ];

        foreach ($replacements as $from => $to) {
            $rows = DB::table('shooting_grounds')
                ->where('photo_url', 'like', '%'.$from.'%')
                ->get(['id', 'photo_url']);

            foreach ($rows as $row) {
                DB::table('shooting_grounds')
                    ->where('id', $row->id)
                    ->update([
                        'photo_url' => str_replace($from, $to, (string) $row->photo_url),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Cannot reliably restore removed Unsplash assets.
    }
};
