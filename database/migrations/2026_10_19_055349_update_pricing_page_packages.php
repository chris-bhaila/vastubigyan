<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace the placeholder packages with the ones actually shown on the
     * pricing page, keyed by slug so the "Buy now" buttons can look them up.
     */
    public function up(): void
    {
        DB::table('packages')->where('name', '1 Analysis')->update([
            'name' => 'Premium App',
            'slug' => 'premium-app',
            'price' => 1100,
            'analysis_count' => 1,
            'updated_at' => now(),
        ]);

        DB::table('packages')->where('name', '10 Analyses')->update([
            'name' => 'Web + App Premium',
            'slug' => 'web-app-premium',
            'price' => 29400,
            'analysis_count' => 25,
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('packages')->where('slug', 'premium-app')->update([
            'name' => '1 Analysis',
            'slug' => null,
            'price' => 1000,
            'analysis_count' => 1,
            'updated_at' => now(),
        ]);

        DB::table('packages')->where('slug', 'web-app-premium')->update([
            'name' => '10 Analyses',
            'slug' => null,
            'price' => 10000,
            'analysis_count' => 10,
            'updated_at' => now(),
        ]);
    }
};
