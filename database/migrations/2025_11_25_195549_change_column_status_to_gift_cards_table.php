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
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'used', 'expired', 'pending'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('gift_cards', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'used', 'expired'])->default('inactive');
        });
    }
};
