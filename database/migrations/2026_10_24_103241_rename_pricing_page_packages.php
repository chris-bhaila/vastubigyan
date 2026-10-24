<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename the packages to match the per-analysis pricing shown on the
     * pricing page.
     */
    public function up(): void
    {
        DB::table('packages')->where('slug', 'premium-app')->update([
            'name' => '1 Analysis',
            'updated_at' => now(),
        ]);

        DB::table('packages')->where('slug', 'web-app-premium')->update([
            'name' => '10 Analysis',
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('packages')->where('slug', 'premium-app')->update([
            'name' => 'Premium App',
            'updated_at' => now(),
        ]);

        DB::table('packages')->where('slug', 'web-app-premium')->update([
            'name' => 'Web + App Premium',
            'updated_at' => now(),
        ]);
    }
};
