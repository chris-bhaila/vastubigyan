<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One immutable row per analysis, created once alongside it and never
     * touched again. See AnalysisInput for the enforcement.
     */
    public function up(): void
    {
        Schema::create('analysis_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->unique()->restrictOnDelete();

            // Private information
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('gender', 20);
            $table->string('phone', 20);
            $table->string('contact_address', 255);
            $table->unsignedSmallInteger('birth_year');
            $table->unsignedTinyInteger('birth_month');
            $table->unsignedTinyInteger('birth_day');
            $table->time('time_of_birth');
            $table->string('nachhetra', 50);

            // Land information: plot location
            $table->string('country', 100);
            $table->string('state', 100);
            $table->string('district', 100);
            $table->string('municipality', 100);
            $table->unsignedTinyInteger('ward');
            $table->string('village', 100);
            $table->string('tole', 100);

            // Land information: measurements
            $table->decimal('plot_area_sqft', 10, 2);
            $table->decimal('length', 10, 2);
            $table->decimal('width', 10, 2);
            $table->decimal('plinth_area', 10, 2);
            $table->decimal('angula_length_inches', 4, 2);

            // Land information: dates
            $table->date('visit_date');
            $table->unsignedTinyInteger('silanyas_month');
            $table->unsignedSmallInteger('constructed_year');

            // Land information: documents (private-disk paths)
            $table->string('plot_with_road');
            $table->string('floor_plan');

            $table->string('integrity_hash', 64);
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_inputs');
    }
};
