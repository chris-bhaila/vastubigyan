<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalizes the users.role string column into a proper roles lookup
     * table, referenced by foreign key. Role ids are fixed (1=user,
     * 2=admin, 3=super_admin) to match App\Enums\Role's backing values.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'user'],
            ['id' => 2, 'name' => 'admin'],
            ['id' => 3, 'name' => 'super_admin'],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->default(1)->after('role');
        });

        DB::table('users')->where('role', 'user')->update(['role_id' => 1]);
        DB::table('users')->where('role', 'admin')->update(['role_id' => 2]);
        DB::table('users')->where('role', 'super_admin')->update(['role_id' => 3]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_id', 'role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role_name')->default('user')->after('role');
        });

        DB::table('users')->where('role', 1)->update(['role_name' => 'user']);
        DB::table('users')->where('role', 2)->update(['role_name' => 'admin']);
        DB::table('users')->where('role', 3)->update(['role_name' => 'super_admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_name', 'role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });

        Schema::dropIfExists('roles');
    }
};
