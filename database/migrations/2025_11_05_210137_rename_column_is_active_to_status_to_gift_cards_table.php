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
        DB::statement("ALTER TABLE gift_cards CHANGE is_active status TINYINT(1) NOT NULL DEFAULT 0");
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'used', 'expired'])->default('inactive')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE gift_cards CHANGE status is_active ENUM('active', 'inactive', 'used', 'expired') NOT NULL DEFAULT 'inactive'");
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->boolean('status')->default(true)->change();
        });
    }
};
