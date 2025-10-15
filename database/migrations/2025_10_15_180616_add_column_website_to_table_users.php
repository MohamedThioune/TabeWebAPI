<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('website')->nullable()->after('password');
            $table->text('bio')->nullable()->after('password');
            $table->text('address')->nullable()->after('password');
            $table->string('city')->nullable()->after('password');
            $table->string('country')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('website');
            $table->dropColumn('bio');
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('country');
        });
    }
};
