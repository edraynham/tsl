<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->default('')->after('id');
            $table->string('last_name')->default('')->after('first_name');
        });

        foreach (DB::table('users')->select('id', 'name')->get() as $row) {
            $trimmed = trim((string) $row->name);
            $parts = $trimmed === '' ? ['', ''] : preg_split('/\s+/u', $trimmed, 2);
            $first = $parts[0] ?? '';
            $last = $parts[1] ?? '';
            DB::table('users')->where('id', $row->id)->update([
                'first_name' => $first,
                'last_name' => $last,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->after('email_verified_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
        });

        foreach (DB::table('users')->select('id', 'first_name', 'last_name')->get() as $row) {
            $name = trim($row->first_name.' '.$row->last_name);
            DB::table('users')->where('id', $row->id)->update(['name' => $name]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
