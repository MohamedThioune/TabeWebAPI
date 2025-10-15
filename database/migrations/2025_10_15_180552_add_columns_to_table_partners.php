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
        Schema::table('partners', function (Blueprint $table) {
            $table->enum('sector', ["Mode", "Beauty", "Gastronomy", "Technology", "Well-being", "Decoration" ])->default('Beauty')->nullable()->change();
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('sector');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
        });
    }
};
