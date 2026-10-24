<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rework the Premium App and Web + App Premium packages to the new
     * per-analysis pricing shown on the pricing page.
     */
    public function up(): void
    {
        DB::table('packages')->where('slug', 'premium-app')->update([
            'price' => 1000,
            'analysis_count' => 1,
            'updated_at' => now(),
        ]);

        DB::table('packages')->where('slug', 'web-app-premium')->update([
            'price' => 9000,
            'analysis_count' => 10,
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('packages')->where('slug', 'premium-app')->update([
            'price' => 1100,
            'analysis_count' => 1,
            'updated_at' => now(),
        ]);

        DB::table('packages')->where('slug', 'web-app-premium')->update([
            'price' => 29400,
            'analysis_count' => 25,
            'updated_at' => now(),
        ]);
    }
};
